@extends('admin.dashboard')
@section('title', 'Setting Management')

@section('content')
    <x-session-alerts />
    <div
        class="p-4 bg-white block sm:flex items-center justify-between border-b border-gray-200 lg:mt-1.5 dark:bg-gray-800 dark:border-gray-700">
        <div class="w-full mb-1">
            <div class="mb-4">
                <x-breadcrumb />
                <h1 class="text-3xl font-bold mb-6 text-gray-800 dark:text-white">Quản lý Cài đặt Website</h1>
            </div>

            <div class="flex flex-col">
                <div class="overflow-x-auto">
                    <div class="inline-block min-w-full align-middle">
                        <div class="">
                            <form action="{{ route('settings.update') }}" method="POST">
                                @csrf
                                @method('PUT')

                                <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
                                    {{-- Cột 1: Cài đặt Bảo trì (Giữ nguyên) --}}
                                    <div class="lg:col-span-1">
                                        <div class="bg-white dark:bg-gray-700 border rounded-lg p-6">
                                            <h2
                                                class="text-xl font-semibold mb-4 text-gray-800 dark:text-white border-b pb-2">
                                                Chế độ Bảo
                                                trì
                                            </h2>
                                            <div class="flex items-center space-x-4">
                                                <label for="maintenance_toggle"
                                                    class="text-gray-700 dark:text-gray-300 font-medium">Kích
                                                    hoạt chế độ bảo trì:</label>
                                                <label for="maintenance_toggle"
                                                    class="relative inline-flex items-center cursor-pointer">
                                                    <input type="checkbox" id="maintenance_toggle" name="maintenance"
                                                        value="1" class="sr-only peer" @checked($maintenance)>
                                                    <div
                                                        class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 dark:peer-focus:ring-blue-800 rounded-full peer dark:bg-gray-600 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-500 peer-checked:bg-blue-600">
                                                    </div>
                                                </label>
                                            </div>
                                            <p class="mt-3 text-sm text-gray-500 dark:text-gray-400">
                                                Khi bật, tất cả người dùng (ngoại trừ quản trị viên) sẽ thấy trang bảo trì.
                                            </p>
                                        </div>
                                    </div>


                                    {{-- Cột 2: Cài đặt Tĩnh (Thông tin liên hệ) --}}
                                    <div class="lg:col-span-1">
                                        <div class="bg-white dark:bg-gray-700 border rounded-lg p-6">
                                            <h2
                                                class="text-xl font-semibold mb-4 text-gray-800 dark:text-white border-b pb-2">
                                                Thông tin
                                                Cơ bản
                                            </h2>
                                            {{-- Input: Logo Path --}}
                                            {{-- Input: Logo (Sử dụng selector ảnh) --}}
                                            <div class="mb-4">
                                                <label
                                                    class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Logo
                                                    Website</label>

                                                <div id="logo-preview-area"
                                                    class="mt-1 p-2 border border-dashed rounded-lg flex flex-col items-center">
                                                    <input type="hidden" id="footer_logo_input" name="footer[logo]"
                                                        value="{{ old('footer.logo', $footer['logo'] ?? '') }}">

                                                    <div id="logo-image-display"
                                                        class="w-32 h-16 mb-2 flex items-center justify-center
             {{ empty($footer['logo']) ? 'hidden' : '' }}">

                                                        {{-- Sử dụng asset() để hiển thị ảnh từ storage/public --}}
                                                        <img src="{{ asset($footer['logo'] ?? '') }}" alt="Logo Preview"
                                                            class="max-h-full max-w-full object-contain" />
                                                    </div>

                                                    <button type="button" id="select-logo-btn"
                                                        class="py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                                        Chọn Logo / SVG
                                                    </button>

                                                    <button type="button" id="remove-logo-btn"
                                                        class="mt-2 text-sm text-red-600 hover:text-red-800 {{ empty($footer['logo']) ? 'hidden' : '' }}">
                                                        Xóa Logo
                                                    </button>
                                                </div>

                                                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Hỗ trợ .svg hoặc
                                                    các định dạng ảnh
                                                    khác (PNG, WebP).</p>
                                                @error('footer.logo')
                                                    <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                                                @enderror
                                            </div>

                                            {{-- Input: Year --}}
                                            <div class="mb-4">
                                                <label for="footer_year"
                                                    class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Năm
                                                    bản
                                                    quyền</label>
                                                <input type="number" id="footer_year" name="footer[year]"
                                                    value="{{ old('footer.year', $footer['year'] ?? date('Y')) }}"
                                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-600 dark:border-gray-500 dark:text-white p-2">
                                                @error('footer.year')
                                                    <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                                                @enderror
                                            </div>

                                            {{-- Input: Description (Textarea) --}}
                                            <div class="mb-4">
                                                <label for="footer_description"
                                                    class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Mô
                                                    tả
                                                    Footer</label>
                                                <textarea id="footer_description" name="footer[description]" rows="3"
                                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-600 dark:border-gray-500 dark:text-white p-2">{{ old('footer.description', $footer['description'] ?? '') }}</textarea>
                                                @error('footer.description')
                                                    <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                                                @enderror
                                            </div>

                                            {{-- Input: Address --}}
                                            <div class="mb-4">
                                                <label for="footer_address"
                                                    class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Địa
                                                    chỉ</label>
                                                <input type="text" id="footer_address" name="footer[address]"
                                                    value="{{ old('footer.address', $footer['address'] ?? '') }}"
                                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-600 dark:border-gray-500 dark:text-white p-2">
                                                @error('footer.address')
                                                    <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                                                @enderror
                                            </div>

                                            {{-- Input: Hotline --}}
                                            <div class="mb-4">
                                                <label for="footer_hotline"
                                                    class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Hotline/Điện
                                                    thoại</label>
                                                <input type="text" id="footer_hotline" name="footer[hotline]"
                                                    value="{{ old('footer.hotline', $footer['hotline'] ?? '') }}"
                                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-600 dark:border-gray-500 dark:text-white p-2">
                                                @error('footer.hotline')
                                                    <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                                                @enderror
                                            </div>

                                            {{-- Input: Email --}}
                                            <div class="mb-4">
                                                <label for="footer_email"
                                                    class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Email
                                                    liên
                                                    hệ</label>
                                                <input type="email" id="footer_email" name="footer[email]"
                                                    value="{{ old('footer.email', $footer['email'] ?? '') }}"
                                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-600 dark:border-gray-500 dark:text-white p-2">
                                                @error('footer.email')
                                                    <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>


                                    {{-- Cột 3: Quản lý Menu Động --}}
                                    <div class="lg:col-span-1">
                                        <div class="bg-white dark:bg-gray-700 border rounded-lg p-6">
                                            <h2
                                                class="text-xl font-semibold mb-4 text-gray-800 dark:text-white border-b pb-2">
                                                Cài đặt
                                                Menu (Cột Footer)
                                            </h2>
                                            <div id="footer-menu-columns-container" class="space-y-6">
                                                {{-- Các cột menu động sẽ được render tại đây --}}
                                            </div>
                                            <button type="button" id="add-column-btn"
                                                class="mt-4 w-full justify-center py-2 px-4 border border-indigo-500 text-sm font-medium rounded-md text-indigo-600 hover:bg-indigo-50 dark:text-indigo-400 dark:hover:bg-gray-600">
                                                + Thêm Cột Menu Mới
                                            </button>
                                        </div>
                                    </div>


                                </div>

                                {{-- Nút lưu --}}
                                <div class="mt-8 pt-4 border-t border-gray-200 dark:border-gray-600 flex justify-end">
                                    <button type="submit"
                                        class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                        Lưu Cài đặt
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const container = document.getElementById('footer-menu-columns-container');
            const addColumnBtn = document.getElementById('add-column-btn');
            const logoInput = document.getElementById('footer_logo_input');
            const selectLogoBtn = document.getElementById('select-logo-btn');
            const removeLogoBtn = document.getElementById('remove-logo-btn');
            const logoDisplay = document.getElementById('logo-image-display');
            const logoImg = logoDisplay.querySelector('img');
            let columnIndex = 0;

            const footerData = @json($footer['menu_columns'] ?? []);

            // === HÀM TẠO MỤC CON (LINK ITEM) ===
            function createLinkItem(colIndex, itemIndex, text = '', url = '') {
                const itemHtml = `
                    <div class="flex items-center space-x-2 mb-2 bg-gray-50 dark:bg-gray-800 p-2 rounded-md border dark:border-gray-600 link-item">
                        <input type="text" name="footer[menu_columns][${colIndex}][items][${itemIndex}][text]"
                            value="${text}" placeholder="Tên liên kết" required
                            class="block w-1/2 rounded-md border-gray-300 shadow-sm text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white p-1">
                        <input type="text" name="footer[menu_columns][${colIndex}][items][${itemIndex}][url]"
                            value="${url}" placeholder="URL (/page)" required
                            class="block w-1/2 rounded-md border-gray-300 shadow-sm text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white p-1">
                        <button type="button" class="text-red-500 hover:text-red-700 text-lg remove-link-btn" title="Xóa liên kết">
                            &times;
                        </button>
                    </div>
                `;
                return itemHtml;
            }

            // === HÀM TẠO CỘT MENU CHÍNH ===
            function createColumn(colIndex, title = '', items = []) {
                const columnDiv = document.createElement('div');
                columnDiv.className =
                    'border dark:border-gray-600 p-4 rounded-lg bg-gray-100 dark:bg-gray-700 column-item';
                columnDiv.setAttribute('data-index', colIndex);

                let itemsHtml = '';
                let itemIndex = 0;
                items.forEach(item => {
                    itemsHtml += createLinkItem(colIndex, itemIndex++, item.text, item.url);
                });

                columnDiv.innerHTML = `
                    <div class="flex justify-between items-center mb-3">
                        <h4 class="font-semibold text-gray-800 dark:text-white">Cột #${colIndex + 1}</h4>
                        <button type="button" class="text-red-600 hover:text-red-800 remove-column-btn font-bold">Xóa Cột</button>
                    </div>

                    <div class="mb-3">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Tiêu đề Cột</label>
                        <input type="text" name="footer[menu_columns][${colIndex}][title]"
                            value="${title}" placeholder="Ví dụ: Công cụ, Pháp lý" required
                            class="block w-full rounded-md border-gray-300 shadow-sm dark:bg-gray-600 dark:border-gray-500 dark:text-white p-2">
                    </div>

                    <div class="links-container">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Các Liên kết:</label>
                        ${itemsHtml}
                    </div>

                    <button type="button" class="mt-2 text-sm text-indigo-600 hover:underline add-link-btn" data-col-index="${colIndex}">
                        + Thêm Liên kết
                    </button>
                `;

                // Logic xóa cột
                columnDiv.querySelector('.remove-column-btn').addEventListener('click', function() {
                    columnDiv.remove();
                });

                // Logic thêm liên kết
                columnDiv.querySelector('.add-link-btn').addEventListener('click', function() {
                    const linksContainer = columnDiv.querySelector('.links-container');
                    const newItemIndex = linksContainer.querySelectorAll('.link-item').length;
                    linksContainer.insertAdjacentHTML('beforeend', createLinkItem(colIndex, newItemIndex));
                });

                // Logic xóa liên kết
                columnDiv.addEventListener('click', function(e) {
                    if (e.target.classList.contains('remove-link-btn')) {
                        e.target.closest('.link-item').remove();
                    }
                });


                return columnDiv;
            }

            function openMediaLibrary() {

                const newPath = prompt(
                    "Nhập đường dẫn logo mới (VD: images/new-logo.svg hoặc storage/avatars/1.webp):");
                if (newPath) {
                    updateLogoDisplay(newPath);
                }
            }

            function updateLogoDisplay(path) {
                if (path) {
                    logoInput.value = path;
                    // Kiểm tra xem path có phải là path hoàn chỉnh (có storage/) hay không
                    const fullUrl = path.startsWith('http') ? path : `{{ asset('') }}${path}`;

                    logoImg.src = fullUrl;
                    logoDisplay.classList.remove('hidden');
                    removeLogoBtn.classList.remove('hidden');
                } else {
                    logoInput.value = '';
                    logoImg.src = '';
                    logoDisplay.classList.add('hidden');
                    removeLogoBtn.classList.add('hidden');
                }
            }

            // Xử lý sự kiện
            selectLogoBtn.addEventListener('click', openMediaLibrary);

            removeLogoBtn.addEventListener('click', function() {
                updateLogoDisplay('');
            });

            // Khởi tạo ban đầu cho logo
            if (logoInput.value && logoInput.value.length > 0) {
                logoDisplay.classList.remove('hidden');
                removeLogoBtn.classList.remove('hidden');
            } else {
                logoDisplay.classList.add('hidden');
                removeLogoBtn.classList.add('hidden');
            }
            // === XỬ LÝ SỰ KIỆN CHUNG ===
            addColumnBtn.addEventListener('click', function() {
                container.appendChild(createColumn(columnIndex++));
            });

            // === TẢI DỮ LIỆU ĐÃ LƯU KHI LOAD TRANG ===
            if (footerData.length > 0) {
                footerData.forEach(column => {
                    container.appendChild(createColumn(columnIndex++, column.title, column.items));
                });
            } else {
                // Tải cột mặc định nếu chưa có dữ liệu nào (hoặc dữ liệu mặc định rỗng)
                container.appendChild(createColumn(columnIndex++, "Công cụ"));
            }

        });
    </script>
@endpush
