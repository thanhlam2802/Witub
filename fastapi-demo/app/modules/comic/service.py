from diffusers import StableDiffusionPipeline
from PIL import Image, ImageDraw, ImageFont
import torch, os

def generate_comic(prompts: list[str]):
    pipe = StableDiffusionPipeline.from_pretrained("runwayml/stable-diffusion-v1-5").to("cuda" if torch.cuda.is_available() else "cpu")
    images = [pipe(prompt).images[0] for prompt in prompts]

    # Ghép 4 ảnh (2x2)
    w, h = images[0].size
    canvas = Image.new("RGB", (w*2, h*2))
    for i, img in enumerate(images):
        x, y = (i % 2) * w, (i // 2) * h
        canvas.paste(img, (x, y))

    # Thêm chữ thoại
    draw = ImageDraw.Draw(canvas)
    font = ImageFont.load_default()
    for i, text in enumerate(prompts):
        draw.text((10, 10 + i * 20), f"{i+1}. {text}", fill="black", font=font)

    os.makedirs("outputs/comics", exist_ok=True)
    filepath = f"outputs/comics/comic_page.png"
    canvas.save(filepath)
    return filepath
