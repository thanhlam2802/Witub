import os
import json
import uuid
import logging
import httpx
import base64
from typing import Any

from fastapi import APIRouter, Request, HTTPException, File, UploadFile, Depends, Body
from fastapi.responses import JSONResponse, FileResponse, StreamingResponse
from pydantic import BaseModel, HttpUrl

from app.services.queue import queue, redis_conn
from app.modules.index import tasks

# --- Cấu hình ---
logger = logging.getLogger(__name__)
logger.setLevel(logging.INFO)
router = APIRouter()

# --- Các Model Dữ liệu (Để tạo tài liệu rõ ràng) ---
class URLRequest(BaseModel):
    url: HttpUrl
    class Config:
        json_schema_extra = {"example": {"url": "https://www.youtube.com/watch?v=dQw4w9WgXcQ"}}

class DownloadRequest(URLRequest):
    format_id: str
    class Config:
        json_schema_extra = {"example": {"url": "https://www.youtube.com/watch?v=dQw4w9WgXcQ", "format_id": "22"}}

class GalleryItemRequest(BaseModel):
    media_url: HttpUrl
    filename: str
    class Config:
        json_schema_extra = {"example": {"media_url": "https://example.com/image.jpg", "filename": "my_image.jpg"}}

class CookieUploadRequest(BaseModel):
    content_b64: str

# --- Helper: check admin ---
def _check_admin(request: Request):
    secret = os.environ.get("SESSION_SECRET_KEY", "dev-secret-key")
    admin_key = request.headers.get("x-admin-key", "")
    if not secret or not admin_key or admin_key != secret:
        raise HTTPException(status_code=403, detail="Forbidden: Invalid or missing admin key")
    return True

# --- API Endpoints ---

@router.post("/api/enqueue/info", summary="1. Bắt đầu phân tích một URL", tags=["Core API"])
async def enqueue_info(payload: URLRequest):
    """
    Nhận một URL, tạo một job để phân tích thông tin (lấy title, thumbnail,
    các định dạng, etc.) và đưa vào hàng đợi. Đây là bước đầu tiên trong luồng.

    - **Returns**: `job_id` và `meta_key` để theo dõi tiến trình ở Bước 2.
    """
    try:
        meta_key = f"job:{uuid.uuid4().hex}"
        redis_conn.hset(meta_key, mapping={"status": "queued", "type": "info", "url": str(payload.url)})
        job = queue.enqueue(tasks.extract_info_job, str(payload.url), meta_key)
        logger.info(f"Enqueued info job {job.id} for URL: {payload.url}")
        return JSONResponse({"status": "ok", "job_id": job.get_id(), "meta_key": meta_key})
    except Exception as e:
        raise HTTPException(status_code=500, detail=str(e))

@router.get("/api/job/{meta_key}", summary="2. Kiểm tra trạng thái job", tags=["Core API"])
async def job_status(meta_key: str):
    """
    Kiểm tra trạng thái của một job (`queued`, `running`, `finished`, `error`).
    Client (Laravel) sẽ gọi liên tục vào đây để cập nhật.

    - Nếu `status` là `finished`, kết quả chi tiết sẽ nằm trong trường `result`.
    """
    meta = redis_conn.hgetall(meta_key)
    if not meta: raise HTTPException(status_code=404, detail="Job not found")
    result_str = redis_conn.get(f"{meta_key}:result")
    if result_str:
        try: meta["result"] = json.loads(result_str)
        except json.JSONDecodeError: meta["result"] = result_str
    return JSONResponse(meta)

@router.post("/api/enqueue/download", summary="3a. Yêu cầu tải video theo định dạng", tags=["Core API"])
async def enqueue_download(payload: DownloadRequest):
    """
    Tạo một job để tải về một video với một `format_id` cụ thể.
    `format_id` này được lấy từ kết quả của job `/info`.
    """
    try:
        meta_key = f"job:{uuid.uuid4().hex}"
        redis_conn.hset(meta_key, mapping={"status": "queued", "type": "download"})
        job = queue.enqueue(tasks.download_video_job, str(payload.url), payload.format_id, meta_key)
        return JSONResponse({"status": "ok", "job_id": job.get_id(), "meta_key": meta_key})
    except Exception as e:
        raise HTTPException(status_code=500, detail=str(e))

@router.post("/api/enqueue/audio", summary="3b. Yêu cầu tải audio (MP3)", tags=["Core API"])
async def enqueue_audio(payload: URLRequest):
    """
    Tạo một job để tải về phiên bản âm thanh (MP3) của một video.
    """
    try:
        meta_key = f"job:{uuid.uuid4().hex}"
        redis_conn.hset(meta_key, mapping={"status": "queued", "type": "audio"})
        job = queue.enqueue(tasks.download_audio_job, str(payload.url), meta_key)
        return JSONResponse({"status": "ok", "job_id": job.get_id(), "meta_key": meta_key})
    except Exception as e:
        raise HTTPException(status_code=500, detail=str(e))

@router.post("/api/enqueue/gallery_item", summary="3c. Yêu cầu tải một item từ gallery", tags=["Core API"])
async def enqueue_gallery_item_download(payload: GalleryItemRequest):
    """
    Tạo job để tải một ảnh/video cụ thể từ một gallery (Instagram, Behance...).
    """
    try:
        meta_key = f"job:{uuid.uuid4().hex}"
        redis_conn.hset(meta_key, mapping={"status": "queued", "type": "gallery_item_download"})
        job = queue.enqueue(tasks.download_gallery_item_job, str(payload.media_url), payload.filename, meta_key)
        return JSONResponse({"status": "ok", "job_id": job.get_id(), "meta_key": meta_key})
    except Exception as e:
        raise HTTPException(status_code=500, detail=str(e))


@router.get("/api/job/{meta_key}/result", summary="4. Tải file kết quả", tags=["Core API"])
async def job_result(meta_key: str):
    """
    Sau khi job tải về có trạng thái `finished`, gọi endpoint này để nhận file.
    Trình duyệt sẽ tự động hiện hộp thoại tải xuống.
    """
    meta = redis_conn.hgetall(meta_key)
    if not meta: raise HTTPException(status_code=404, detail="Job not found")
    path = meta.get("result_path")
    filename = meta.get("result_filename") or (os.path.basename(path) if path else "download")
    if not path or not os.path.exists(path):
        raise HTTPException(status_code=404, detail="Result file not found or not ready yet.")
    return FileResponse(path, filename=filename, media_type="application/octet-stream")


@router.get("/api/image_proxy", summary="Proxy để hiển thị ảnh bị chặn CORS", tags=["Utilities"])
async def image_proxy(url: HttpUrl):
    """
    Tiện ích dùng để hiển thị các ảnh từ Instagram, Threads... mà không bị lỗi CORS.
    Chỉ cần truyền URL của ảnh vào tham số `url`.
    """
    headers = {'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36'}
    try:
        async with httpx.AsyncClient() as client:
            response = await client.get(str(url), headers=headers, follow_redirects=True, timeout=15)
            response.raise_for_status()
            content_type = response.headers.get('content-type', 'image/jpeg')
            return StreamingResponse(response.iter_bytes(), media_type=content_type)
    except Exception as e:
        raise HTTPException(status_code=500, detail=f"Internal proxy error: {e}")

@router.post("/api/admin/upload-cookies", dependencies=[Depends(_check_admin)], summary="Upload file cookies.txt", tags=["Admin"])
async def upload_cookies_json(payload: CookieUploadRequest):
    """
    (Chỉ dành cho Admin) Upload nội dung file cookies.txt.
    - **Yêu cầu**: Phải có header `x-admin-key` với giá trị là `SESSION_SECRET_KEY`.
    - **Body**: `{ "content_b64": "<nội dung file đã được mã hóa base64>" }`
    """
    target_path = os.path.join(os.getcwd(), "cookies.txt")
    try:
        data = base64.b64decode(payload.content_b64)
        with open(target_path, "wb") as f:
            f.write(data)
        logger.info(f"Cookies file uploaded and saved to {target_path}")
        return JSONResponse({"status": "ok", "message": "Cookies file updated successfully."})
    except Exception as e:
        raise HTTPException(status_code=500, detail=f"Failed to save file: {e}")