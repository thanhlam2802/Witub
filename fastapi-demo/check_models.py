# check_models.py
import os
import google.generativeai as genai
from dotenv import load_dotenv

load_dotenv()
api_key = os.getenv("GOOGLE_API_KEY")

if not api_key:
    print("Không tìm thấy GOOGLE_API_KEY trong file .env")
else:
    genai.configure(api_key=api_key)
    print("Các mô hình có sẵn hỗ trợ 'generateContent':")
    for m in genai.list_models():
        if 'generateContent' in m.supported_generation_methods:
            print(f"- {m.name}")