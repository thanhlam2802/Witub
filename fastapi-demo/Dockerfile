# Bước 1: Bắt đầu từ image Python 3.11 gọn nhẹ
FROM python:3.11-slim

# Bước 2: Cài đặt các công cụ hệ thống cần thiết
# NÂNG CẤP: Thêm 'git' vào danh sách cài đặt
RUN apt-get update && apt-get install -y --no-install-recommends \
    ffmpeg \
    git \
    && rm -rf /var/lib/apt/lists/*

# Bước 3: Đặt thư mục làm việc
WORKDIR /code

# Bước 4: Sao chép file requirements.txt
COPY ./requirements.txt /code/requirements.txt

# Bước 5: Cài đặt thư viện
# NÂNG CẤP TỐI THƯỢNG: Cài đặt yt-dlp trực tiếp từ mã nguồn trên GitHub
# Lệnh này sẽ lấy phiên bản mới nhất, bao gồm cả hỗ trợ cho Threads
RUN pip install --no-cache-dir --upgrade "git+https://github.com/yt-dlp/yt-dlp.git"

# Cài đặt các thư viện còn lại từ requirements.txt
# pip sẽ tự động bỏ qua yt-dlp vì nó đã được cài ở trên
RUN pip install --no-cache-dir -r /code/requirements.txt

# Bước 6: Sao chép toàn bộ code của dự án
COPY . /code/