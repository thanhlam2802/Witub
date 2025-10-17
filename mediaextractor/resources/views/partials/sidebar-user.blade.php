<aside class="w-64 flex-shrink-0 bg-white border-r flex flex-col">
    {{-- Logo --}}
    <div class="h-16 flex items-center justify-center border-b">
        <h1 class="text-2xl font-bold text-blue-600">Witub</h1>
    </div>

    <nav class="flex-1 px-3 py-4 overflow-y-auto">
        <ul class="space-y-4">
            <!-- NHÓM: Công cụ Video -->
            <li>
                <h5 class="px-3 pt-2 pb-1 text-xs font-semibold text-gray-400 uppercase tracking-wider">
                    Công cụ Video
                </h5>
                <ul class="mt-1 space-y-1">
                    <li>
                        <a href="{{ route('studio.index') }}"
                            class="flex items-center px-3 py-2 text-base font-medium rounded-lg

                                {{ Request::is('studio') ? 'bg-blue-50 text-blue-600' : 'text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700' }}">
                            <i class="fa-solid fa-download w-5 text-center"></i>
                            <span class="ml-3">Tải Video</span>
                        </a>
                    </li>

                </ul>
            </li>

            <li>
                <h5 class="px-3 pt-2 pb-1 text-xs font-semibold text-gray-400 uppercase tracking-wider">
                    Công cụ Phụ đề
                </h5>

    </nav>

    {{-- Bọc trong @auth để đảm bảo chỉ người dùng đã đăng nhập mới thấy --}}
    @auth

        <div x-data="{ menuOpen: false, tooltipOpen: false }" class="relative">

            <div x-show="menuOpen" @click.away="menuOpen = false" x-transition:enter="transition ease-out duration-100"
                x-transition:enter-start="transform opacity-0 scale-95"
                x-transition:enter-end="transform opacity-100 scale-100" x-transition:leave="transition ease-in duration-75"
                x-transition:leave-start="transform opacity-100 scale-100"
                x-transition:leave-end="transform opacity-0 scale-95"
                class="absolute bottom-full left-4 right-4 mb-2 bg-white rounded-lg shadow-lg ring-1 ring-black ring-opacity-5 z-10"
                style="display: none;">
                <div class="py-2 px-2" role="menu" aria-orientation="vertical">
                    {{-- Các mục menu lấy từ ảnh chụp màn hình --}}
                    <a href="#"
                        class="flex items-center px-3 py-2 text-sm text-gray-700 hover:bg-gray-100 rounded-md">Nhập mã ưu
                        đãi</a>
                    <a href="#"
                        class="flex items-center px-3 py-2 text-sm text-gray-700 hover:bg-gray-100 rounded-md">Hồ sơ</a>
                    <a href="#"
                        class="flex items-center px-3 py-2 text-sm text-gray-700 hover:bg-gray-100 rounded-md">Quản lý thiết
                        bị</a>
                    <a href="#"
                        class="flex items-center px-3 py-2 text-sm text-gray-700 hover:bg-gray-100 rounded-md">Gói cước của
                        tôi</a>
                    <a href="#"
                        class="flex items-center px-3 py-2 text-sm text-gray-700 hover:bg-gray-100 rounded-md">Lịch sử thanh
                        toán</a>
                    <div class="border-t my-2"></div>
                    <a href="#"
                        class="flex items-center px-3 py-2 text-sm text-gray-700 hover:bg-gray-100 rounded-md">Kiếm tiền với
                        Vbee</a>
                    <a href="#"
                        class="flex items-center px-3 py-2 text-sm text-gray-700 hover:bg-gray-100 rounded-md">Ứng dụng di
                        động</a>
                    <div class="border-t my-2"></div>
                    <a href="#"
                        class="flex items-center px-3 py-2 text-sm text-gray-700 hover:bg-gray-100 rounded-md">Điều khoản sử
                        dụng</a>
                    <a href="#"
                        class="flex items-center px-3 py-2 text-sm text-gray-700 hover:bg-gray-100 rounded-md justify-between">
                        <span>Hỗ trợ</span>
                        <i class="fas fa-chevron-right text-xs"></i>
                    </a>
                    <a href="#"
                        class="flex items-center px-3 py-2 text-sm text-gray-700 hover:bg-gray-100 rounded-md justify-between">
                        {{-- Phần bên trái --}}
                        <span>
                            Ngôn ngữ
                        </span>


                        <div class="flex items-center space-x-2">

                            <img src="/images/vietnam.png" alt="Cờ Việt Nam" class="w-5 h-5 rounded-full object-cover">
                            <i class="fas fa-chevron-right text-xs text-gray-400"></i>
                        </div>
                    </a>
                    <div class="border-t my-2"></div>

                    <form method="POST" action="{{ route('logout') }}">
                        @csrf

                        <a href="{{ route('logout') }}" onclick="event.preventDefault(); this.closest('form').submit();"
                            class="flex items-center px-3 py-2 text-sm text-red-600 hover:bg-red-50 rounded-md"><i
                                class="fas fa-sign-out-alt w-6 text-center mr-2"></i>Đăng xuất</a>
                    </form>

                </div>
            </div>

            <div x-show="tooltipOpen && !menuOpen" x-transition
                class="absolute top-1/2 -translate-y-1/2 left-full ml-3 w-max bg-gray-700 text-white text-sm rounded-lg px-4 py-2 z-20"
                style="display: none;">
                <p class="font-semibold">{{ Auth::user()->full_name }}</p>
                <p class="text-xs">{{ Auth::user()->email }}</p>
                <div
                    class="absolute top-1/2 -translate-y-1/2 right-full h-0 w-0 border-y-4 border-y-transparent border-r-8 border-r-gray-700">
                </div>
            </div>

            <div class="p-4 border-t flex items-center cursor-pointer" @click="menuOpen = !menuOpen"
                @mouseenter="tooltipOpen = true" @mouseleave="tooltipOpen = false">

                @if (Auth::user()->avatar)
                    <img class="w-10 h-10 rounded-full object-cover" src="{{ Auth::user()->avatar }}"
                        alt="{{ Auth::user()->full_name }}">
                @else
                    @php
                        $nameParts = explode(' ', Auth::user()->full_name);
                        $initials =
                            count($nameParts) > 1
                                ? mb_substr($nameParts[0], 0, 1) . mb_substr(end($nameParts), 0, 1)
                                : mb_substr(Auth::user()->full_name, 0, 1);
                    @endphp
                    <div
                        class="w-10 h-10 bg-blue-500 rounded-full flex items-center justify-center text-white font-bold text-sm">
                        {{ strtoupper($initials) }}
                    </div>
                @endif

                <div class="ml-3 overflow-hidden">
                    <p class="text-sm font-semibold truncate">{{ Auth::user()->full_name }}</p>
                    <p class="text-xs text-gray-500 truncate">{{ Auth::user()->email }}</p>
                </div>

                <button class="ml-auto text-gray-500 hover:text-gray-800 flex-shrink-0">
                    <i class="fas fa-chevron-left"></i>
                </button>
            </div>

        </div>
    @endauth
</aside>
