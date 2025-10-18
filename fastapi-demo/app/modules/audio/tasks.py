import yt_dlp
import os

def extract_audio(video_url: str):
    output_dir = "/tmp/audio"
    os.makedirs(output_dir, exist_ok=True)
    ydl_opts = {
        'format': 'bestaudio/best',
        'outtmpl': f'{output_dir}/%(title)s.%(ext)s',
        'noplaylist': True,
    }
    with yt_dlp.YoutubeDL(ydl_opts) as ydl:
        info = ydl.extract_info(video_url, download=True)
        return {
            "title": info.get("title"),
            "filepath": f"{output_dir}/{info.get('title')}.webm"
        }
