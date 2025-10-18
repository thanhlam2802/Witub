import yt_dlp, os, json
from googletrans import Translator
from gtts import gTTS

def extract_subtitles(video_url: str):
    ydl_opts = {'writesubtitles': True, 'skip_download': True, 'subtitleslangs': ['en'], 'outtmpl': '/tmp/%(id)s'}
    with yt_dlp.YoutubeDL(ydl_opts) as ydl:
        info = ydl.extract_info(video_url, download=False)
        return {"title": info["title"], "id": info["id"]}

def translate_subtitles(subs_text: str, target_lang="vi"):
    translator = Translator()
    return translator.translate(subs_text, dest=target_lang).text

def subtitles_to_speech(subs_text: str, lang="vi"):
    tts = gTTS(subs_text, lang=lang)
    filepath = f"/tmp/subs_tts.mp3"
    tts.save(filepath)
    return filepath
