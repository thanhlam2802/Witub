from fastapi import APIRouter, Request, Form
from app.modules.comic.service import generate_comic
from app.core.templates import templates

router = APIRouter()

@router.get("/generate")
async def show_generate(request: Request):
    return templates.TemplateResponse("generate_comic.html", {"request": request})

@router.post("/generate")
async def post_generate(request: Request, prompt1: str = Form(...), prompt2: str = Form(...), prompt3: str = Form(...), prompt4: str = Form(...)):
    path = generate_comic([prompt1, prompt2, prompt3, prompt4])
    return templates.TemplateResponse("generate_comic.html", {"request": request, "comic_path": path})
