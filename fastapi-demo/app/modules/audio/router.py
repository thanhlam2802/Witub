from fastapi import APIRouter, Request, Form
from fastapi.responses import RedirectResponse
from fastapi.templating import Jinja2Templates
from pathlib import Path
from .service import extract_audio

# templates directory for this module
templates = Jinja2Templates(directory=str(Path(__file__).parent / "templates"))

router = APIRouter()

@router.get("/extract", response_class=None)
async def show_extract_audio(request: Request):
    return templates.TemplateResponse("extract_audio.html", {"request": request})

@router.post("/extract")
async def post_extract_audio(request: Request, url: str = Form(...)):
    # call blocking service (optionally run in executor)
    result = extract_audio(url)
    return templates.TemplateResponse("extract_audio.html", {"request": request, "result": result})
