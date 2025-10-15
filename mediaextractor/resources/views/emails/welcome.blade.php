<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chào mừng bạn!</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif, 'Apple Color Emoji', 'Segoe UI Emoji', 'Segoe UI Symbol';
            background-color: #f4f4f7;
            color: #333;
            line-height: 1.6;
            margin: 0;
            padding: 20px;
        }

        .container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }

        h1 {
            font-size: 24px;
            color: #111;
        }

        p {
            font-size: 16px;
            margin-bottom: 1em;
        }

        a {
            color: #007bff;
            text-decoration: none;
        }

        a:hover {
            text-decoration: underline;
        }

        .strong {
            font-weight: bold;
        }

        .footer {
            margin-top: 20px;
            text-align: center;
            font-size: 14px;
            color: #888;
        }
    </style>
</head>

<body>
    <div class="container">
        <h1>Chào mừng bạn đến với [Tên Ứng Dụng Của Bạn]! 🎉</h1>

        <p>Xin chào, <span class="strong">{{ $user->full_name }}</span>!</p>

        <p>Tôi là [Tên của bạn], người tạo ra [Tên Ứng Dụng Của Bạn]. Cảm ơn bạn rất nhiều vì đã tham gia cùng chúng
            tôi. Chào mừng bạn!</p>

        <p>Tài khoản của bạn (<span class="strong">{{ $user->email }}</span>) đã được kích hoạt hoàn toàn. Bạn có thể
            bắt đầu sử dụng ngay bằng cách truy cập vào trang quản lý của mình.</p>

        <p><a href="{{ route('home') }}"><strong>Đi tới Bảng điều khiển của bạn &rarr;</strong></a></p>

        <p>Tôi luôn sẵn sàng giải đáp nếu bạn có bất kỳ câu hỏi nào, vì vậy đừng ngần ngại liên hệ với tôi qua <a
                href="mailto:[Email hỗ trợ của bạn]">[Email hỗ trợ của bạn]</a> (hoặc bằng cách trả lời email này).</p>

        <p>Tôi hy vọng bạn thích sử dụng dịch vụ của chúng tôi! 😊</p>

        <p>
            Trân trọng,<br>
            [Tên của bạn]
        </p>
    </div>
    <div class="footer">
        <p>&copy; {{ date('Y') }} [Tên Công Ty Của Bạn]. All rights reserved.</p>
    </div>
</body>

</html>
