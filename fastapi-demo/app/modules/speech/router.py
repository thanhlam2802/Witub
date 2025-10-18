import uuid
from fastapi import APIRouter, Request, HTTPException
from pydantic import BaseModel

from app.services.queue import queue, redis_conn
from app.modules.speech import tasks

router = APIRouter(
    prefix="/subtitles-to-speech",
    tags=["Text to Speech"]
)

class TTSRequest(BaseModel):
    text: str
    voice: str

@router.get("/")
async def get_tts_page(request: Request):
    """
    Trả về trang giao diện HTML cho chức năng Text-to-Speech.
    """
    return request.app.state.templates.TemplateResponse("subtitles_to_speech.html", {"request": request})

@router.post("/api/enqueue")
async def enqueue_tts_job(payload: TTSRequest):
    """
    Nhận văn bản và giọng đọc, tạo job và đưa vào hàng đợi.
    """
    try:
        meta_key = f"job:{uuid.uuid4().hex}"
        redis_conn.hset(meta_key, mapping={"status": "queued", "type": "text_to_speech"})
        
        # Cho phép job chạy tối đa 5 phút
        job = queue.enqueue(
            tasks.text_to_speech_job, 
            payload.text, 
            payload.voice, 
            meta_key,
            job_timeout='5m'
        )
        return {"status": "ok", "job_id": job.get_id(), "meta_key": meta_key}
    except Exception as e:
        raise HTTPException(status_code=500, detail=str(e))