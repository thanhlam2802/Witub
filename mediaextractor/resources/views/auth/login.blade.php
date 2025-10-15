<script src="https://cdn.tailwindcss.com"></script>


<div class="min-h-screen flex items-center justify-center bg-gray-100">
    <div class="w-full max-w-md bg-white rounded-2xl shadow-lg p-8">
        @if (session('success'))
            <div class="mb-4 p-4 bg-green-100 text-green-700 border border-green-200 rounded-lg">
                {{ session('success') }}
            </div>
        @endif

        @if (session('error'))
            <div class="mb-4 p-4 bg-red-100 text-red-700 border border-red-200 rounded-lg">
                {{ session('error') }}
            </div>
        @endif
        <h2 class="text-2xl font-bold text-center mb-6 text-gray-800">Đăng nhập tài khoản</h2>

        {{-- Hiển thị lỗi --}}
        @if ($errors->any())
            <div class="mb-4 text-red-600">
                <ul class="list-disc list-inside text-sm">
                    @foreach ($errors->all() as $error)
                        <li>- {{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- Form đăng nhập --}}
        <form method="POST" action="{{ route('auth.login') }}">
            @csrf

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

            <div class="flex justify-between items-center mb-4">
                <label class="flex items-center text-sm text-gray-600">
                    <input type="checkbox" name="remember" class="mr-2"> Ghi nhớ đăng nhập
                </label>
                <a href="" class="text-sm text-blue-600 hover:underline">
                    Quên mật khẩu?
                </a>
            </div>

            <button type="submit"
                class="w-full bg-blue-600 text-white font-semibold py-3 rounded-lg hover:bg-blue-700 transition">
                Đăng nhập
            </button>
        </form>

        {{-- Đăng nhập bằng Google --}}
        <div class="my-6 text-center text-gray-500">— hoặc —</div>

        <a href="{{ route('auth.google.redirect') }}"
            class="w-full inline-flex justify-center items-center border border-gray-300 py-3 rounded-lg hover:bg-gray-50 transition">
            <img src="https://www.svgrepo.com/show/475656/google-color.svg" alt="Google" class="w-5 h-5 mr-2">
            <span class="text-sm font-medium text-gray-700">Đăng nhập bằng Google</span>
        </a>

        <p class="text-center text-sm text-gray-600 mt-6">
            Chưa có tài khoản?
            <a href="{{ route('auth.register.view') }}" class="text-blue-600 hover:underline">Đăng ký ngay</a>
        </p>
    </div>
</div>
