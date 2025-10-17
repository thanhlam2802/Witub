<header class="text-center max-w-3xl mx-auto">
    <h1 class="text-4xl md:text-4xl font-bold text-gray-900">
        Trình tải video & gallery
    </h1>
    <p class="mt-4 text-lg text-gray-600">
        Dán bất kỳ liên kết video hoặc bài đăng nào để bắt đầu tải xuống.
    </p>

    <div class="mt-6 flex flex-wrap items-center justify-center gap-4 sm:gap-6 text-sm text-gray-700">
        <span class="flex items-center gap-2"><i class="fa-solid fa-wand-magic-sparkles text-blue-500"></i>Không dính
            logo</span>
        <span class="flex items-center gap-2"><i class="fa-solid fa-film text-blue-500"></i>Hỗ trợ 4K & HD</span>
        <span class="flex items-center gap-2"><i class="fa-solid fa-bolt text-blue-500"></i>Tốc độ cao</span>
    </div>

    {{-- Form đã được chỉnh sửa để trỏ đến route API backend --}}
    <form id="getInfoForm" method="POST" action="{{ route('studio.fetch_video_info') }}"
        class="mt-8 flex flex-col sm:flex-row gap-2 max-w-xl mx-auto">
        @csrf
        <div class="relative w-full">
            {{-- Nút dán --}}
            <div id="paste-icon-container" class="absolute inset-y-0 left-0 flex items-center pl-3 cursor-pointer"
                title="Dán từ clipboard">
                <i class="fa-solid fa-paste text-gray-400 hover:text-blue-500 transition"></i>
            </div>
            <input id="urlInput" name="url" {{-- Tên trường dữ liệu cho Laravel Controller --}} type="url" required
                placeholder="Dán liên kết của bạn tại đây..."
                class="w-full px-5 py-4 pl-10 text-base border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:outline-none transition">
        </div>
        <button id="submit-button" type="submit"
            class="bg-blue-600 text-white font-semibold px-6 py-4 rounded-lg hover:bg-blue-700 whitespace-nowrap transition shadow-sm disabled:opacity-50 disabled:cursor-wait">
            <i class="fa-solid fa-rocket mr-2"></i> Bắt đầu
        </button>
    </form>
    <p class="mt-3 text-xs text-gray-500">
        Bằng cách sử dụng dịch vụ, bạn đồng ý với <a href="#" class="text-blue-600 hover:underline">Điều
            khoản</a>.
    </p>
</header>

<main class="mt-8 max-w-4xl mx-auto">
    {{-- Khu vực hiển thị trạng thái/thông báo --}}
    <div id="status" class="text-center font-medium min-h-[46px]"></div>

    {{-- Khu vực thông tin Video --}}
    <div id="video-info-container" class="hidden bg-white p-6 mt-4 rounded-2xl shadow-lg border border-gray-200">
        <div class="flex flex-col md:flex-row gap-6">
            <img id="thumbnail" src="https://placehold.co/192x108/CCCCCC/333333?text=Thumbnail" alt="Video thumbnail"
                class="w-full md:w-48 h-auto object-cover rounded-lg" />
            <div class="flex-grow space-y-4">
                <h2 id="video-title" class="text-xl font-bold text-gray-900">Tiêu đề video sẽ xuất hiện ở
                    đây</h2>
                <div class="flex flex-wrap items-center gap-3">
                    {{-- Nút tải MP4 chính --}}
                    <div class="relative inline-flex rounded-lg shadow-sm" id="video-download-container">
                        <button id="download-mp4-btn"
                            class="flex items-center gap-2 pl-4 pr-3 py-2 bg-green-500 text-white font-semibold rounded-l-lg hover:bg-green-600 transition disabled:opacity-50 disabled:cursor-not-allowed">
                            <i class="fa-solid fa-video"></i>
                            <span id="main-video-format-text">MP4 1080p</span>
                        </button>
                        <button id="formats-dropdown-toggle"
                            class="px-3 py-2 bg-green-600 text-white rounded-r-lg hover:bg-green-700 transition">
                            <i class="fa-solid fa-chevron-down text-xs"></i>
                        </button>
                        <ul id="formats-dropdown-list"
                            class="absolute left-0 mt-12 w-56 bg-white border rounded-lg shadow-xl z-10 hidden max-h-60 overflow-y-auto">
                        </ul>
                    </div>
                    <button id="download-mp3-btn"
                        class="flex items-center gap-2 px-4 py-2 bg-pink-500 text-white font-semibold rounded-lg hover:bg-pink-600 transition shadow-sm"><i
                            class="fa-solid fa-music"></i><span>MP3</span></button>
                    <button id="download-thumbnail-btn"
                        class="flex items-center gap-2 px-4 py-2 bg-sky-500 text-white font-semibold rounded-lg hover:bg-sky-600 transition shadow-sm"><i
                            class="fa-solid fa-image"></i><span>Hình ảnh</span></button>
                </div>
                {{-- Thanh tiến trình --}}
                <div id="progress-container" class="hidden w-full bg-gray-200 rounded-full h-2.5 mt-4">
                    <div id="progress-bar" class="bg-green-600 h-2.5 rounded-full" style="width: 0%"></div>
                </div>
                <div id="progress-text" class="text-center text-sm text-gray-500 mt-1"></div>
            </div>
        </div>
    </div>

    {{-- Khu vực thông tin Gallery (nếu có) --}}
    <div id="gallery-container" class="hidden bg-white p-6 mt-4 rounded-2xl shadow-lg border border-gray-200">
        <div class="mb-4">
            <h2 id="gallery-title" class="text-xl font-bold text-gray-900 truncate">Tiêu đề Gallery</h2>
            <p id="gallery-uploader" class="text-sm text-gray-500">Người đăng: Tên người dùng</p>
        </div>
        <div id="gallery-grid" class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-4">
            {{-- Gallery items sẽ được thêm vào đây bằng JS --}}
        </div>
    </div>
</main>

{{-- Phần Nền tảng được hỗ trợ --}}
<section class="mt-16 text-center">
    <h2 class="text-3xl font-bold text-gray-900">Nền tảng được hỗ trợ</h2>
    <div id="supported-grid"
        class="mt-8 grid grid-cols-2 sm:grid-cols-4 md:grid-cols-6 lg:grid-cols-8 gap-4 max-w-5xl mx-auto">
        {{-- Ví dụ về các nền tảng hỗ trợ --}}
        <div class="flex flex-col items-center p-3">
            <i class="fab fa-youtube text-red-600 text-4xl"></i>
            <span class="mt-2 text-sm text-gray-700 font-medium">YouTube</span>
        </div>
        <div class="flex flex-col items-center p-3">
            <i class="fab fa-facebook text-blue-600 text-4xl"></i>
            <span class="mt-2 text-sm text-gray-700 font-medium">Facebook</span>
        </div>
        <div class="flex flex-col items-center p-3">
            <i class="fab fa-instagram text-pink-600 text-4xl"></i>
            <span class="mt-2 text-sm text-gray-700 font-medium">Instagram</span>
        </div>
        <div class="flex flex-col items-center p-3">
            <i class="fab fa-tiktok text-black text-4xl"></i>
            <span class="mt-2 text-sm text-gray-700 font-medium">TikTok</span>
        </div>
        <div class="flex flex-col items-center p-3">
            <i class="fab fa-twitter text-blue-400 text-4xl"></i>
            <span class="mt-2 text-sm text-gray-700 font-medium">Twitter</span>
        </div>
        <div class="flex flex-col items-center p-3">
            <i class="fab fa-vimeo-v text-blue-500 text-4xl"></i>
            <span class="mt-2 text-sm text-gray-700 font-medium">Vimeo</span>
        </div>
        <div class="flex flex-col items-center p-3">
            <i class="fa-solid fa-video text-orange-600 text-4xl"></i>
            <span class="mt-2 text-sm text-gray-700 font-medium">Bất kỳ</span>
        </div>
    </div>
</section>
