import os
import redis
from rq import Queue

# Lấy REDIS_URL từ biến môi trường.
# Nếu không có, mặc định kết nối tới localhost (dành cho lúc phát triển không dùng Docker)
REDIS_URL = os.getenv("REDIS_URL", "redis://localhost:6379/0")

# Sử dụng from_url để kết nối từ một chuỗi URL duy nhất.
# decode_responses=True rất quan trọng để Redis trả về string thay vì bytes.
redis_conn = redis.from_url(REDIS_URL, decode_responses=True)

# Khởi tạo queue
queue = Queue("default", connection=redis_conn)

# Thêm một hàm kiểm tra nhỏ để xác nhận kết nối
def check_redis_connection():
    try:
        redis_conn.ping()
        print("✅ Redis connection successful.")
        return True
    except redis.exceptions.ConnectionError as e:
        print(f"❌ Redis connection failed: {e}")
        return False

# Chạy kiểm tra khi module được import
check_redis_connection()