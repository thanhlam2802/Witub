import uuid
from typing import Optional
from fastapi import APIRouter, Request, HTTPException, File, UploadFile, Form
from pydantic import BaseModel
from langdetect import detect, LangDetectException

from app.services.queue import queue, redis_conn
from app.modules.translate import tasks

router = APIRouter(
    prefix="/translate-subtitles",
    tags=["Subtitle Translation"]
)

class DetectRequest(BaseModel):
    text: str

@router.get("/")
async def get_translate_subtitles_page(request: Request):
    return request.app.state.templates.TemplateResponse("translate_subtitles.html", {"request": request})

@router.post("/api/enqueue", summary="Tạo job dịch phụ đề")
async def enqueue_translate_subtitle(
    source_lang: str = Form(...),
    target_lang: str = Form(...),
    rewrite: bool = Form(False),
    output_format: str = Form(...),
    file: Optional[UploadFile] = File(None),
    subtitle_text: Optional[str] = Form(None)
):
    if not file and not subtitle_text:
        raise HTTPException(status_code=400, detail="Vui lòng cung cấp file hoặc dán nội dung.")
    
    file_content_str = (await file.read()).decode('utf-8') if file else subtitle_text
    original_filename = file.filename if file else "subtitle.txt"

    try:
        meta_key = f"job:{uuid.uuid4().hex}"
        redis_conn.hset(meta_key, mapping={"status": "queued", "type": "translate_subtitle"})
        
        # NÂNG CẤP: Thêm job_timeout='10m' (10 phút) cho công việc này
        job = queue.enqueue(
            tasks.translate_subtitle_job, 
            file_content_str, 
            original_filename, 
            source_lang, 
            target_lang, 
            rewrite,
            output_format,
            meta_key,
            job_timeout='10m' # <-- CHO PHÉP JOB CHẠY TRONG 10 PHÚT
        )
        return {"status": "ok", "job_id": job.get_id(), "meta_key": meta_key}
    except Exception as e:
        raise HTTPException(status_code=500, detail=str(e))

@router.post("/api/detect-language", summary="Nhận diện ngôn ngữ từ văn bản")
async def detect_language(payload: DetectRequest):
    try:
        if not payload.text or not payload.text.strip():
            return {"language": "unknown"}
        return {"language": detect(payload.text)}
    except LangDetectException:
        return {"language": "unknown"}
    except Exception as e:
        raise HTTPException(status_code=500, detail=str(e))