# app/routers/index.py
from fastapi import APIRouter, Request
from fastapi.responses import JSONResponse
from pydantic import BaseModel, HttpUrl
import yt_dlp

router = APIRouter()

# ---- SCHEMA ----
class VideoRequest(BaseModel):
    url: HttpUrl

# ---- ROUTES ----
@router.get("/")
async def home(request: Request):
    """
    Render trang chủ
    """
    return request.app.state.templates.TemplateResponse("index.html", {"request": request})


@router.post("/api/get_info")
async def get_video_info(video: VideoRequest):
    """
    Nhận URL từ frontend, dùng yt_dlp để phân tích metadata video.
    """
    url = str(video.url)

    try:
        # Chỉ lấy thông tin (không tải video)
        ydl_opts = {
            'quiet': True,
            'skip_download': True,
            'extract_flat': False,
        }

        with yt_dlp.YoutubeDL(ydl_opts) as ydl:
            info = ydl.extract_info(url, download=False)

        # Dữ liệu cần thiết trả về frontend
        data = {
            "title": info.get("title"),
            "duration": info.get("duration"),
            "uploader": info.get("uploader"),
            "thumbnail": info.get("thumbnail"),
            "webpage_url": info.get("webpage_url"),
            "formats": [
                {
                    "format_id": f.get("format_id"),
                    "ext": f.get("ext"),
                    "resolution": f.get("resolution") or f"{f.get('height')}p",
                    "filesize": f.get("filesize"),
                    "url": f.get("url"),
                }
                for f in info.get("formats", [])
                if f.get("url")
            ],
        }

        return JSONResponse({"success": True, "data": data})

    except Exception as e:
        return JSONResponse({"success": False, "error": str(e)})
