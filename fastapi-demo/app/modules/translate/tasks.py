import os
import tempfile
import logging
from io import StringIO
import webvtt
import google.generativeai as genai
from webvtt.errors import MalformedFileError
import re

from app.services.queue import redis_conn

logger = logging.getLogger(__name__)

def get_yt_dlp_extractors_job():
    """
    Chạy lệnh của yt-dlp để lấy danh sách và lưu vào cache.
    """
    cache_key = "yt_dlp_extractors_list"
    try:
        # Chạy lệnh shell để lấy danh sách
        result = subprocess.run(
            ['yt-dlp', '--list-extractors'],
            capture_output=True,
            text=True,
            check=True
        )
        # Tách danh sách thành từng dòng và loại bỏ các dòng trống
        extractors = sorted([line.strip() for line in result.stdout.split('\n') if line.strip()])
        
        # Lưu vào cache Redis trong 24 giờ
        redis_conn.set(cache_key, json.dumps(extractors), ex=86400)
        logger.info(f"Successfully fetched and cached {len(extractors)} yt-dlp extractors.")
        return extractors
    except Exception as e:
        logger.error(f"Failed to get yt-dlp extractors: {e}")
        # Nếu lỗi, thử lấy từ cache cũ (nếu có)
        cached_data = redis_conn.get(cache_key)
        if cached_data:
            return json.loads(cached_data)
        return []
# --- Helper Functions ---
def _hset_meta(meta_key: str, mapping: dict):
    """Helper để cập nhật trạng thái job vào Redis."""
    try:
        str_mapping = {k: str(v) for k, v in mapping.items()}
        redis_conn.hset(meta_key, mapping=str_mapping)
    except Exception as e:
        logger.exception(f"Failed to hset meta for {meta_key}: {e}")

# --- HÀM MỚI: Lấy đường dẫn đến "kho chung" ---
def get_shared_download_path(filename: str) -> str:
    """Tạo đường dẫn file bên trong kho chung /code/downloads."""
    download_dir = "/code/downloads"
    # Docker volume sẽ tự tạo thư mục, nhưng để an toàn, chúng ta vẫn kiểm tra
    os.makedirs(download_dir, exist_ok=True)
    return os.path.join(download_dir, filename)

# --- Worker Job for Translation ---
def translate_subtitle_job(file_content: str, original_filename: str, source_lang: str, target_lang: str, rewrite: bool, output_format: str, meta_key: str):
    _hset_meta(meta_key, {"status": "running", "type": "translate_subtitle"})
    try:
        api_key = os.getenv("GOOGLE_API_KEY")
        if not api_key: raise Exception("GOOGLE_API_KEY is not set.")
        
        genai.configure(api_key=api_key)
        model = genai.GenerativeModel('gemini-flash-latest')

        lang_map = {'vi': 'Vietnamese', 'en': 'English', 'zh-cn': 'Chinese', 'ja': 'Japanese', 'ko': 'Korean'}
        target_language_full = lang_map.get(target_lang, target_lang)
        
        rewrite_instruction = "Viết lại các câu đã dịch cho tự nhiên và hấp dẫn hơn, phù hợp với văn nói trong video, nhưng phải giữ nguyên ý nghĩa cốt lõi." if rewrite else ""
        
        prompt = f"""
        Nhiệm vụ của bạn là một chuyên gia dịch thuật phụ đề đa ngôn ngữ.
        Hãy dịch toàn bộ văn bản phụ đề sau đây sang ngôn ngữ "{target_language_full}".

        **YÊU CẦU QUAN TRỌNG:**
        1.  **Giữ nguyên cấu trúc:** Giữ nguyên số thứ tự, dấu thời gian (-->), và các dòng trống giữa các đoạn phụ đề.
        2.  **Chỉ dịch phần text:** Chỉ dịch phần nội dung văn bản của mỗi đoạn phụ đề.
        3.  **An toàn nội dung:** Chủ động loại bỏ hoặc thay thế các từ ngữ vi phạm chính sách.
        4.  {rewrite_instruction}
        5.  **Định dạng output:** Chỉ trả về nội dung phụ đề đã được dịch hoàn chỉnh. KHÔNG thêm bất kỳ lời giải thích hay ghi chú nào.

        **Nội dung cần dịch:**
        ---
        {file_content}
        ---
        """

        response = model.generate_content(prompt)
        translated_content = response.text.strip()
        
        output_content = ""
        if output_format == 'txt':
            try:
                vtt = webvtt.read_buffer(StringIO(translated_content))
                output_content = "\n".join([caption.text.strip() for caption in vtt])
            except MalformedFileError:
                lines = translated_content.split('\n')
                text_lines = [line.strip() for line in lines if not (line.strip().isdigit() or '-->' in line)]
                output_content = "\n".join(filter(None, text_lines))
        else: # Mặc định là 'srt'
            output_content = translated_content

        # Tạo file kết quả
   
        base, _ = os.path.splitext(original_filename)
        job_uuid_part = meta_key.split(':')[-1][:8] 
        new_filename = f"{base}_{target_lang}_{job_uuid_part}.{output_format}"
        
        filepath = get_shared_download_path(new_filename)
        
        with open(filepath, 'w', encoding='utf-8') as f:
            f.write(output_content)

        _hset_meta(meta_key, {"status": "finished", "result_path": filepath, "result_filename": new_filename})
    except Exception as e:
        logger.exception("translate_subtitle_job error")
        _hset_meta(meta_key, {"status": "error", "error": str(e)})
        raise

