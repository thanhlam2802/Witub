{{-- File: resources/views/auth/register.blade.php (Đã loại bỏ trường Họ và tên) --}}
<script src="https://cdn.tailwindcss.com"></script>

<div class="min-h-screen flex items-center justify-center bg-gray-100 p-4">
    <div class="w-full max-w-md bg-white rounded-2xl shadow-lg p-8">
        <h2 class="text-2xl font-bold text-center mb-6 text-gray-800">Tạo tài khoản mới</h2>

        @if ($errors->any())
            <div class="mb-4 text-red-600">
                <ul class="list-disc list-inside text-sm">
                    @foreach ($errors->all() as $error)
                        <li>- {{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('auth.register') }}">
            @csrf
            {{-- Trường "Họ và tên" đã được xóa --}}
            <div class="mb-4">
                <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                <input id="email" type="email" name="email" value="{{ old('email') }}" required
                    class="w-full mt-1 p-3 border border-gray-300 rounded-lg focus:outline-none focus:ring focus:ring-blue-200">
            </div>
            <div class="mb-4">
                <label for="password" class="block text-sm font-medium text-gray-700">Mật khẩu</label>
                <input id="password" type="password" name="password" required
                    class="w-full mt-1 p-3 border border-gray-300 rounded-lg focus:outline-none focus:ring focus:ring-blue-200">
            </div>
            <div class="mb-6">
                <label for="password_confirmation" class="block text-sm font-medium text-gray-700">Xác nhận mật
                    khẩu</label>
                <input id="password_confirmation" type="password" name="password_confirmation" required
                    class="w-full mt-1 p-3 border border-gray-300 rounded-lg focus:outline-none focus:ring focus:ring-blue-200">
            </div>
            <button type="submit"
                class="w-full bg-blue-600 text-white font-semibold py-3 rounded-lg hover:bg-blue-700 transition">
                Đăng ký
            </button>
        </form>

        <div class="my-6 text-center text-gray-500">— hoặc —</div>
        <a href="{{ route('auth.google.redirect') }}"
            class="w-full inline-flex justify-center items-center border border-gray-300 py-3 rounded-lg hover:bg-gray-50 transition">
            <img src="https://www.svgrepo.com/show/475656/google-color.svg" alt="Google" class="w-5 h-5 mr-2">
            <span class="text-sm font-medium text-gray-700">Đăng ký bằng Google</span>
        </a>

        <p class="text-center text-sm text-gray-600 mt-6">
            Đã có tài khoản?
            <a href="{{ route('auth.login.view') }}" class="text-blue-600 hover:underline">Đăng nhập ngay</a>
        </p>
    </div>
</div>
