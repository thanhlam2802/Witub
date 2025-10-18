from fastapi import FastAPI, Request
from fastapi.staticfiles import StaticFiles
from fastapi.middleware.cors import CORSMiddleware
from starlette.middleware.sessions import SessionMiddleware
from fastapi.templating import Jinja2Templates
from pathlib import Path
import os

# --- Import các router từ các module ---
from app.modules.index.router import router as index_router
from app.modules.translate.router import router as translate_router
from app.modules.speech.router import router as speech_router

app = FastAPI(
    title="Witub ToolBox AI", 
    version="1.0.0",
    description="Một bộ công cụ đa năng sử dụng AI và xử lý nền."
)

# --- Cấu hình Middleware ---
app.add_middleware(
    CORSMiddleware,
    allow_origins=["*"], allow_credentials=True,
    allow_methods=["*"], allow_headers=["*"],
)
app.add_middleware(
    SessionMiddleware,
    secret_key=os.getenv("SESSION_SECRET_KEY", "dev-secret-key"),
)

# --- Cấu hình thư mục tĩnh và templates ---
BASE_DIR = Path(__file__).resolve().parent


app.mount("/static", StaticFiles(directory=BASE_DIR / "static"), name="static")
app.state.templates = Jinja2Templates(directory=BASE_DIR / "templates")



@app.get("/", tags=["Pages"])
async def home(request: Request):

    return app.state.templates.TemplateResponse("index.html", {"request": request})


app.include_router(index_router, tags=["Video Downloader"])
app.include_router(translate_router, tags=["Subtitle Translation"])
app.include_router(speech_router)

