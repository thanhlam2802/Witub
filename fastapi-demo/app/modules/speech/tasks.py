import os
import tempfile
import logging
import re
from openai import OpenAI

from app.services.queue import redis_conn

logger = logging.getLogger(__name__)

# --- Helper Functions ---
def _hset_meta(meta_key: str, mapping: dict):
    try:
        redis_conn.hset(meta_key, mapping=mapping)
    except Exception as e:
        logger.exception(f"Failed to hset meta for {meta_key}: {e}")

def get_shared_download_path(filename: str) -> str:
    download_dir = "/downloads"
    os.makedirs(download_dir, exist_ok=True)
    return os.path.join(download_dir, filename)

def _extract_text_from_subtitle(subtitle_content: str) -> str:
    """
    Tách và chỉ lấy phần văn bản từ nội dung SRT/VTT.
    """
    # Nếu không có dấu thời gian, coi như là văn bản thuần
    if "-->" not in subtitle_content:
        return subtitle_content.strip()

    # Sử dụng regex để chỉ lấy các dòng không phải là số hoặc dấu thời gian
    lines = subtitle_content.strip().split('\n')
    text_lines = [
        line.strip() for line in lines 
        if line.strip() and not line.strip().isdigit() and "-->" not in line
    ]
    return " ".join(text_lines) # Nối các dòng lại thành một đoạn văn để AI đọc mượt hơn

# --- Worker Job for Text-to-Speech ---
def text_to_speech_job(text: str, voice: str, meta_key: str):
    _hset_meta(meta_key, {"status": "running", "type": "text_to_speech"})
    try:
        api_key = os.getenv("OPENAI_API_KEY")
        if not api_key:
            raise Exception("OPENAI_API_KEY is not set in the environment.")

        client = OpenAI(api_key=api_key)

        job_uuid_part = meta_key.split(':')[-1][:8]
        filename = f"speech_{job_uuid_part}.mp3"
        filepath = get_shared_download_path(filename)

        # NÂNG CẤP: "Sơ chế" văn bản trước khi gửi cho AI
        text_to_speak = _extract_text_from_subtitle(text)

        if not text_to_speak:
            raise Exception("Không tìm thấy nội dung văn bản hợp lệ để chuyển đổi.")

        response = client.audio.speech.create(
            model="tts-1",
            voice=voice,
            input=text_to_speak,
            response_format="mp3"
        )

        response.stream_to_file(filepath)
        
        if not os.path.exists(filepath):
            raise Exception("Failed to save audio file from OpenAI.")

        _hset_meta(meta_key, {"status": "finished", "result_path": filepath, "result_filename": filename})
        return {"path": filepath}
    except Exception as e:
        logger.exception("text_to_speech_job error")
        _hset_meta(meta_key, {"status": "error", "error": str(e)})
        raise