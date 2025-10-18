<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Xác thực Email</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 font-sans antialiased">
    <div class="flex min-h-screen items-center justify-center">
        <div class="w-full max-w-lg rounded-lg bg-white p-8 shadow-lg">

            <div class="mb-6 text-center">
                <svg class="mx-auto h-12 w-12 text-blue-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75" />
                </svg>
                <h1 class="mt-4 text-2xl font-bold text-gray-900">
                    Xác thực địa chỉ email của bạn
                </h1>
            </div>

            <p class="mb-4 text-center text-gray-600">
                Cảm ơn bạn đã đăng ký! Trước khi tiếp tục, bạn vui lòng xác thực địa chỉ email bằng cách nhấp vào liên kết chúng tôi vừa gửi cho bạn.
            </p>

            <p class="mb-6 text-center text-gray-600">
                Nếu bạn không nhận được email, chúng tôi sẽ sẵn lòng gửi lại.
            </p>

            @if (session('message'))
                <div class="mb-4 rounded-md bg-green-100 p-3 text-center text-sm font-medium text-green-700">
                    {{ session('message') }}
                </div>
            @endif

            <div class="flex items-center justify-between gap-4">
                <form class="w-full" method="POST" action="{{ route('verification.send') }}">
                    @csrf
                    <button type="submit" class="w-full rounded-md bg-blue-600 px-4 py-3 text-sm font-semibold text-white shadow-sm hover:bg-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                        Gửi lại email xác thực
                    </button>
                </form>

                <form class="w-full" method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="w-full rounded-md bg-gray-200 px-4 py-3 text-sm font-semibold text-gray-700 shadow-sm hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-gray-300 focus:ring-offset-2">
                        Đăng xuất
                    </button>
                </form>
            </div>

        </div>
    </div>
</body>
</html>
