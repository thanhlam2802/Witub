from fastapi import APIRouter, Request, Form
from app.modules.subtitles.service import extract_subtitles, translate_subtitles, subtitles_to_speech
from app.core.templates import templates

router = APIRouter()

@router.get("/extract")
async def show_extract(request: Request):
    return templates.TemplateResponse("extract_subtitles.html", {"request": request})

@router.post("/extract")
async def post_extract(request: Request, url: str = Form(...)):
    result = extract_subtitles(url)
    return templates.TemplateResponse("extract_subtitles.html", {"request": request, "result": result})
