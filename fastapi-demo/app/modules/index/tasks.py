import os
import json
import tempfile
import uuid
import logging
import re
import shutil
import requests
import zipfile
import time
from datetime import datetime
from typing import Any, Dict, List
from unidecode import unidecode

import yt_dlp
import instaloader

from app.services.queue import redis_conn

logger = logging.getLogger(__name__)
logger.setLevel(logging.INFO)

# --- THƯ MỤC LƯU TRỮ CHUNG BÊN TRONG DOCKER ---
SHARED_DOWNLOAD_DIR = "/downloads"

# --- Helper Functions ---
def _hset_meta(meta_key: str, mapping: Dict[str, Any]):
    """Helper để cập nhật trạng thái job vào Redis."""
    try:
        mapping_str = {k: (json.dumps(v) if isinstance(v, (dict, list)) else str(v)) for k, v in mapping.items()}
        redis_conn.hset(meta_key, mapping=mapping_str)
    except Exception as e:
        logger.exception(f"Failed to hset meta for {meta_key}: {e}")

def get_shared_download_path(filename: str) -> str:
    os.makedirs(SHARED_DOWNLOAD_DIR, exist_ok=True)
    return os.path.join(SHARED_DOWNLOAD_DIR, filename)

def slugify(text: str) -> str:
    """Chuyển đổi một chuỗi thành dạng "slug" an toàn cho tên file."""
    if not text:
        return "media"
    text = unidecode(text).lower()
    text = re.sub(r'[^a-z0-9\s-]', '', text)
    text = re.sub(r'[\s-]+', '-', text).strip('-')
    return text if text else "media"


def _format_size(size_bytes):
    if size_bytes is None:
        return ""
    try:
        size_bytes = int(size_bytes)
        if size_bytes == 0:
            return "0B"
        size_name = ("B", "KB", "MB", "GB", "TB")
        import math

        i = int(math.floor(math.log(size_bytes, 1024)))
        p = math.pow(1024, i)
        s = round(size_bytes / p, 2)
        return f"{s} {size_name[i]}"
    except (ValueError, TypeError):
        return ""


# --- Playwright helpers for fallback when yt-dlp fails ---
def _load_netscape_cookies_for_playwright(cookie_file_path: str, domain_filter: str = ".douyin.com") -> List[Dict[str, Any]]:
    """
    Đọc cookie file (Netscape) và trả về list cookie dict phù hợp cho Playwright.
    Chỉ pick cookies có domain chứa domain_filter.
    """
    cookies = []
    if not os.path.exists(cookie_file_path):
        return cookies

    with open(cookie_file_path, "r", encoding="utf-8", errors="ignore") as fh:
        for line in fh:
            line = line.strip()
            if not line or line.startswith("#"):
                continue
            parts = line.split("\t")
            if len(parts) < 7:
                continue
            domain, flag, path, secure_str, expiry, name, value = parts[:7]
            if domain_filter not in domain:
                continue
            cookie = {
                "name": name.replace("#HttpOnly_", "").lstrip("#HttpOnly_"),
                "value": value,
                "domain": domain if domain.startswith(".") else domain,
                "path": path or "/",
                "expires": int(expiry) if expiry.isdigit() else None,
                "httpOnly": line.startswith("#HttpOnly_") or name.startswith("#HttpOnly_"),
                "secure": True if secure_str.upper() == "TRUE" else False,
            }
            cookies.append(cookie)
    return cookies


def playwright_fetch_and_download(url: str, download_folder: str, out_filename: str, cookie_file_path: str = None, headers: Dict[str, str] = None, timeout: int = 60):
    """
    Mở trang Douyin bằng Playwright (headless chromium), set cookies nếu có,
    tìm <video> src trong DOM và tải bằng requests vào download_folder/out_filename.
    Trả về full file path khi thành công hoặc raise Exception.
    """
    if sync_playwright is None:
        raise RuntimeError("playwright is not installed. Install with: pip install playwright && playwright install chromium")

    headers = headers or {
        "Referer": "https://www.douyin.com/",
        "User-Agent": "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120 Safari/537.36",
    }

    with sync_playwright() as pw:
        browser = pw.chromium.launch(headless=True, args=["--no-sandbox"])  # adjust args if running in container
        context = browser.new_context(user_agent=headers.get("User-Agent"))

        # load cookies into context
        if cookie_file_path and os.path.exists(cookie_file_path):
            cookies = _load_netscape_cookies_for_playwright(cookie_file_path, domain_filter=".douyin.com")
            if cookies:
                context.add_cookies(cookies)

        page = context.new_page()
        page.goto(url, wait_until="networkidle", timeout=timeout * 1000)

        # give some time for JS to build video tag
        time.sleep(0.8)

        # attempt to get video src via DOM
        video_src = None
        try:
            video_src = page.eval_on_selector("video", "el => (el.currentSrc || el.src) || null")
        except Exception:
            video_src = None

        # fallback: try video source tags
        if not video_src:
            try:
                video_src = page.eval_on_selector("video source", "el => el ? el.src : null")
            except Exception:
                video_src = None

        # fallback: search for any .mp4 URL in page content
        if not video_src:
            try:
                content = page.content()
                m = re.search(r"https?://[^\s'\"\\]+\.mp4[^\s'\"\\]*", content)
                if m:
                    video_src = m.group(0)
            except Exception:
                video_src = None

        # fallback: inspect network responses for .mp4
        if not video_src:
            video_src = None

            def _response_handler(response):
                nonlocal video_src
                try:
                    urlr = response.url
                    if urlr and (urlr.endswith(".mp4") or ".mp4?" in urlr):
                        video_src = urlr
                except Exception:
                    pass

            context.on("response", _response_handler)
            page.reload(wait_until="networkidle", timeout=timeout * 1000)
            time.sleep(0.8)
            context.off("response", _response_handler)

        browser.close()

    if not video_src:
        raise Exception("Playwright fallback couldn't find direct video URL.")

    # now download the video via requests using headers + cookies (if any)
    os.makedirs(download_folder, exist_ok=True)
    out_path = os.path.join(download_folder, out_filename)

    # prepare cookies for requests if cookie_file_path given (use dict)
    req_cookies = None
    if cookie_file_path and os.path.exists(cookie_file_path):
        req_cookies = {}
        with open(cookie_file_path, "r", encoding="utf-8", errors="ignore") as fh:
            for line in fh:
                if not line or line.startswith("#"):
                    continue
                parts = line.strip().split("\t")
                if len(parts) < 7:
                    continue
                name = parts[5]
                value = parts[6]
                req_cookies[name] = value

    with requests.get(video_src, stream=True, headers=headers, cookies=req_cookies, timeout=120) as r:
        r.raise_for_status()
        with open(out_path, "wb") as f:
            for chunk in r.iter_content(chunk_size=8192):
                if chunk:
                    f.write(chunk)

    return out_path


import os
import json
import tempfile
import uuid
import logging
import re
from datetime import datetime
from typing import Any, Dict
from unidecode import unidecode
import requests

import yt_dlp
import instaloader

from app.services.queue import redis_conn

logger = logging.getLogger(__name__)
logger.setLevel(logging.INFO)

SHARED_DOWNLOAD_DIR = "/downloads"

# --- Helper Functions ---
def _hset_meta(meta_key: str, mapping: Dict[str, Any]):
    # ... (Giữ nguyên)
    pass
def slugify(text: str) -> str:
    # ... (Giữ nguyên)
    pass
def _format_size(size_bytes):
    # ... (Giữ nguyên)
    pass
def get_shared_download_path(filename: str) -> str:
    os.makedirs(SHARED_DOWNLOAD_DIR, exist_ok=True)
    return os.path.join(SHARED_DOWNLOAD_DIR, filename)

# --- Class Downloader (Đã hoàn thiện) ---
class Downloader:
    def __init__(self, meta_key: str):
        self.meta_key = meta_key

    def _create_progress_hook(self):
        def progress_hook(d):
            if d.get('status') == 'downloading':
                try:
                    total = d.get('total_bytes') or d.get('total_bytes_estimate')
                    if total:
                        percent = round((d.get('downloaded_bytes', 0) / total) * 100)
                        _hset_meta(self.meta_key, {"progress": percent})
                except Exception: pass
        return progress_hook

    def download_with_yt_dlp(self, url: str, audio_only=False, format_id=None):
        _hset_meta(self.meta_key, {"status": "running"})
        cookie_file_path = "/code/cookies.txt"
        ydl_opts = {'quiet': True, 'no_warnings': True, 'skip_download': True}
        if os.path.exists(cookie_file_path): ydl_opts['cookiefile'] = cookie_file_path
        
        with yt_dlp.YoutubeDL(ydl_opts) as ydl: info = ydl.extract_info(url, download=False)
        
        job_uuid_part = self.meta_key.split(':')[-1][:8]
        clean_title = slugify(info.get('title', 'media'))
        
        final_filename = ""
        out_template = get_shared_download_path(f"{clean_title}_{job_uuid_part}_witub.%(ext)s")

        if audio_only:
            final_filename = f"{clean_title}_{job_uuid_part}_witub.mp3"
        elif format_id:
            video_format = next((f for f in info.get('formats', []) if f.get('format_id') == format_id), None)
            resolution = f"_{video_format['height']}p" if video_format and video_format.get('height') else ''
            ext = video_format.get('ext', 'mp4') if video_format else 'mp4'
            final_filename = f"{clean_title}{resolution}_{job_uuid_part}_witub.{ext}"
            
        filepath = get_shared_download_path(final_filename)

        download_opts = {"outtmpl": out_template, "quiet": True, "progress_hooks": [self._create_progress_hook()]}
        if os.path.exists(cookie_file_path): download_opts["cookiefile"] = cookie_file_path

        if audio_only:
            download_opts.update({"format": "bestaudio/best", "postprocessors": [{"key": "FFmpegExtractAudio", "preferredcodec": "mp3", "preferredquality": "192"}]})
        elif format_id:
            download_opts["format"] = format_id

        with yt_dlp.YoutubeDL(download_opts) as ydl: ydl.download([url])

        if audio_only:
            temp_path = os.path.splitext(out_template)[0].replace('%(ext)s', 'mp3')
            if os.path.exists(temp_path): os.rename(temp_path, filepath)
            else: raise Exception("Converted MP3 file not found.")
        
        if not os.path.exists(filepath): raise Exception("Download failed, file not found.")
        
        _hset_meta(self.meta_key, {"status": "finished", "result_path": filepath, "result_filename": os.path.basename(filepath), "progress": 100})



# --- Jobs / Tasks ---
def extract_info_job(url: str, meta_key: str):
    _hset_meta(meta_key, {"status": "running", "type": "info"})

    cookie_file_path = os.path.join(os.getcwd(), "cookies.txt")
    base_ydl_opts = {'quiet': True, 'no_warnings': True, 'skip_download': True}


    platform = 'generic'
    if 'instagram.com' in url.lower():
        platform = 'instagram'
    elif 'threads.net' in url.lower():
        platform = 'threads'
    elif 'behance.net' in url.lower():
        platform = 'behance'
    elif 'douyin.com' in url.lower():
        platform = 'douyin'

    try:
        if platform == 'instagram':
            L = instaloader.Instaloader(download_videos=False, save_metadata=False, compress_json=False)
            shortcode_match = re.search(r"/(p|reel|tv)/([^/]+)", url)
            if not shortcode_match:
                raise ValueError("Không tìm thấy shortcode Instagram trong URL.")
            shortcode = shortcode_match.group(2)
            post = instaloader.Post.from_shortcode(L.context, shortcode)

            gallery_items, item_index = [], 1

            def add_item(node, index):
                is_video = node.is_video
                gallery_items.append({
                    "media_url": node.video_url if is_video else node.display_url,
                    "thumbnail_url": node.display_url,
                    "is_video": is_video,
                    "filename": f"{post.owner_username}_{shortcode}_{index}.{'mp4' if is_video else 'jpg'}"
                })

            if post.typename == 'GraphSidecar':
                for node in post.get_sidecar_nodes():
                    add_item(node, item_index)
                    item_index += 1
            else:
                add_item(post, item_index)

            result = {"type": "gallery", "title": post.caption, "uploader": post.owner_username, "items": gallery_items}

        elif platform in ['threads', 'behance']:
            ydl_opts = dict(base_ydl_opts)
            if os.path.exists(cookie_file_path):
                ydl_opts['cookiefile'] = cookie_file_path
                logger.info("Using cookies file for threads/behance info extraction.")
            with yt_dlp.YoutubeDL(ydl_opts) as ydl:
                info = ydl.extract_info(url, download=False)

            gallery_items = []
            if 'entries' in info:
                for index, entry in enumerate(info['entries']):
                    gallery_items.append({
                        "media_url": entry.get('url'),
                        "thumbnail_url": entry.get('thumbnail'),
                        "is_video": entry.get('duration') is not None,
                        "filename": f"{slugify(info.get('uploader'))}_{slugify(entry.get('title'))}_{index}.{entry.get('ext')}"
                    })
                result = {"type": "gallery", "title": info.get('title'), "uploader": info.get('uploader'), "items": gallery_items}
            else:
                result = {"type": "gallery", "title": info.get('title'), "uploader": info.get('uploader'),
                          "items": [{"media_url": info.get('url'), "thumbnail_url": info.get('thumbnail'),
                                     "is_video": info.get('duration') is not None,
                                     "filename": f"{slugify(info.get('uploader'))}_{slugify(info.get('title'))}.{info.get('ext')}"}]}

        else:
        
            if 'douyin.com' in url.lower():
                m = re.search(r'modal_id=(\d+)', url)
                if m:
                    modal_id = m.group(1)
                    url = f"https://www.douyin.com/video/{modal_id}"

            ydl_opts = dict(base_ydl_opts)
            common_headers = {
                "Referer": "https://www.douyin.com/",
                "User-Agent": "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120 Safari/537.36",
                "x-requested-with": "XMLHttpRequest"
            }
            ydl_opts["http_headers"] = common_headers
            if os.path.exists(cookie_file_path):
                ydl_opts['cookiefile'] = cookie_file_path
                logger.info("Using cookies file for generic/douyin info extraction.")

            with yt_dlp.YoutubeDL(ydl_opts) as ydl:
                info = ydl.extract_info(url, download=False)

            formats_with_audio, formats_without_audio = [], []
            for f in info.get("formats", []):
                # skip invalid entries
                if not f.get('url') or f.get('vcodec') == 'none':
                    continue

                entry = {
                    "format_id": f.get("format_id"),
                    "ext": f.get("ext"),
                    "resolution": f.get("resolution") or f"{f.get('height')}p",
                    "height": f.get("height", 0),
                    "filesize_str": _format_size(f.get("filesize") or f.get("filesize_approx"))
                }
                if f.get('acodec') != 'none':
                    formats_with_audio.append(entry)
                else:
                    formats_without_audio.append(entry)

            formats_with_audio.sort(key=lambda x: x.get('height', 0), reverse=True)
            formats_without_audio.sort(key=lambda x: x.get('height', 0), reverse=True)

            best_format = formats_with_audio[0] if formats_with_audio else None
            best_format_id = best_format.get('format_id') if best_format else None
            best_format_text = f"MP4 ({best_format.get('resolution')})" if best_format else "MP4"

            result = {
                "type": "video",
                "title": info.get("title"),
                "thumbnail": info.get("thumbnail"),
                "formats_with_audio": formats_with_audio,
                "formats_without_audio": formats_without_audio,
                "best_mp4_format_id": best_format_id,
                "best_mp4_format_text": best_format_text
            }

        redis_conn.set(f"{meta_key}:result", json.dumps(result))
        _hset_meta(meta_key, {"status": "finished"})
    except Exception as e:
        logger.exception(f"extract_info_job error for url={url}")
        _hset_meta(meta_key, {"status": "error", "error": str(e)})
        raise



def download_gallery_job(url: str, meta_key: str):
    """Download a gallery (multiple items) and zip them."""
    _hset_meta(meta_key, {"status": "running", "type": "gallery_download"})
    try:
        # Reuse extract_info_job logic to get items
        extract_info_job(url, meta_key)
        result_str = redis_conn.get(f"{meta_key}:result")
        if not result_str:
            raise Exception("No gallery info found")
        info = json.loads(result_str)
        items = info.get('items') if isinstance(info, dict) else []
        if not items:
            raise Exception("No items to download in gallery")

        tmp_dir = tempfile.mkdtemp(prefix="gallery_")
        downloaded_files = []
        for idx, item in enumerate(items):
            media_url = item.get('media_url')
            filename = item.get('filename') or f"item_{idx}"
            try:
                resp = requests.get(media_url, stream=True, timeout=60)
                resp.raise_for_status()
                path = os.path.join(tmp_dir, filename)
                with open(path, 'wb') as f:
                    for chunk in resp.iter_content(8192):
                        if chunk:
                            f.write(chunk)
                downloaded_files.append(path)
            except Exception as ex:
                logger.warning("Failed to download gallery item %s: %s", media_url, ex)

        if not downloaded_files:
            raise Exception("No files downloaded for gallery")

        zip_name = f"gallery_{uuid.uuid4().hex}.zip"
        zip_path = os.path.join(SHARED_DOWNLOAD_DIR, zip_name)
        with zipfile.ZipFile(zip_path, 'w', zipfile.ZIP_DEFLATED) as zf:
            for file_path in downloaded_files:
                zf.write(file_path, arcname=os.path.basename(file_path))

        # cleanup tmp
        shutil.rmtree(tmp_dir)

        _hset_meta(meta_key, {"status": "finished", "result_path": zip_path, "result_filename": zip_name, "progress": 100})
    except Exception as e:
        logger.exception("download_gallery_job failed")
        _hset_meta(meta_key, {"status": "error", "error": str(e)})
        raise

def download_video_job(url: str, format_id: str, meta_key: str):
    """Tải video với tên file duy nhất để chống xung đột."""
    _hset_meta(meta_key, {"status": "running", "type": "download"})
    try:
        # Lấy một phần ID duy nhất của job (ví dụ: 'd94501a2')
        job_uuid_part = meta_key.split(':')[-1][:8]
        
        with yt_dlp.YoutubeDL({'quiet': True, 'skip_download': True}) as ydl:
            info = ydl.extract_info(url, download=False)
        
        video_format = next((f for f in info.get('formats', []) if f.get('format_id') == format_id), None)
        resolution = f"_{video_format['height']}p" if video_format and video_format.get('height') else ''
        clean_title = slugify(info.get('title', 'video'))
        ext = video_format.get('ext', 'mp4') if video_format else 'mp4'

        # NÂNG CẤP: Tạo tên file duy nhất bằng cách thêm job_uuid_part
        final_filename = f"{clean_title}{resolution}_{job_uuid_part}_witub.{ext}"
        
        filepath = get_shared_download_path(final_filename)
        
        ydl_opts = {"format": format_id, "outtmpl": filepath, "quiet": True}
        cookie_file_path = '/code/cookies.txt'
        if os.path.exists(cookie_file_path): ydl_opts['cookiefile'] = cookie_file_path
        
        with yt_dlp.YoutubeDL(ydl_opts) as ydl: ydl.download([url])
        
        if not os.path.exists(filepath): raise RuntimeError("Tải file thất bại.")
        _hset_meta(meta_key, {"status": "finished", "result_path": filepath, "result_filename": final_filename})
    except Exception as e:
        _hset_meta(meta_key, {"status": "error", "error": str(e)})
        raise

def download_audio_job(url: str, meta_key: str):
    """SỬA LỖI TOÀN DIỆN: Ra lệnh rõ ràng cho yt-dlp và ffmpeg."""
    _hset_meta(meta_key, {"status": "running", "type": "audio"})
    try:
        job_uuid_part = meta_key.split(':')[-1][:8]
        with yt_dlp.YoutubeDL({'quiet': True, 'skip_download': True}) as ydl:
            info = ydl.extract_info(url, download=False)
        clean_title = slugify(info.get('title', 'audio'))
        
        # 1. Ra lệnh tên file cuối cùng phải là .mp3
        final_filename = f"{clean_title}_{job_uuid_part}_witub.mp3"
        final_filepath = get_shared_download_path(final_filename)

        # 2. Ra lệnh cho yt-dlp lưu file tạm với một tên cơ sở, không có đuôi file
        temp_basename = get_shared_download_path(f"temp_{job_uuid_part}")

        ydl_opts = {
            "format": "bestaudio/best",
            "outtmpl": temp_basename, # Chỉ định tên cơ sở, không có .%(ext)s
            "postprocessors": [{
                "key": "FFmpegExtractAudio",
                "preferredcodec": "mp3",
                "preferredquality": "192"
            }],
            "quiet": True
        }
        cookie_file_path = '/code/cookies.txt'
        if os.path.exists(cookie_file_path): ydl_opts['cookiefile'] = cookie_file_path
        
        with yt_dlp.YoutubeDL(ydl_opts) as ydl:
            ydl.download([url])

        # 3. Tìm file .mp3 mà ffmpeg đã tạo ra
        temp_mp3_path = f"{temp_basename}.mp3"
        if os.path.exists(temp_mp3_path):
            # 4. Đổi tên file tạm thành tên file cuối cùng đẹp đẽ
            os.rename(temp_mp3_path, final_filepath)
        else:
            raise RuntimeError("Chuyển đổi MP3 thất bại: không tìm thấy file .mp3 tạm thời.")

        _hset_meta(meta_key, {"status": "finished", "result_path": final_filepath, "result_filename": os.path.basename(final_filepath)})
    except Exception as e:
        _hset_meta(meta_key, {"status": "error", "error": str(e)})
        raise

def download_gallery_item_job(media_url: str, filename: str, meta_key: str):
    """Tải item từ gallery với tên file duy nhất."""
    _hset_meta(meta_key, {"status": "running", "type": "gallery_item_download"})
    try:
        job_uuid_part = meta_key.split(':')[-1][:8]
        # NÂNG CẤP: Gắn UUID vào trước tên file gốc
        unique_filename = f"{job_uuid_part}_{filename}"
        
        filepath = get_shared_download_path(unique_filename)
        
        headers = {'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36'}
        with requests.get(media_url, stream=True, headers=headers) as r:
            r.raise_for_status()
            with open(filepath, 'wb') as f:
                for chunk in r.iter_content(chunk_size=8192):
                    if chunk: f.write(chunk)
        _hset_meta(meta_key, {"status": "finished", "result_path": filepath, "result_filename": unique_filename})
    except Exception as e:
        _hset_meta(meta_key, {"status": "error", "error": str(e)})
        raise