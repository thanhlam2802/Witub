<footer class="py-12 bg-white xl:py-24 dark:bg-gray-800">
    <div class="container px-4 mx-auto xl:px-0">
        <div class="grid gap-12 xl:grid-cols-6 xl:gap-24">

            {{-- Cột 1: Thông tin Cơ bản (Giữ nguyên phần đã sửa trước đó) --}}
            <div class="col-span-2">
                <a href="{{ url('/') }}" class="flex mr-4">
                    <img src="{{ $footer['logo'] ?? '/images/witub-logo.svg' }}" class="h-8 mr-3" alt="Witub Logo" />
                    <span class="self-center text-2xl font-semibold whitespace-nowrap dark:text-white">Witub</span>
                </a>
                <p class="max-w-lg mt-4 text-gray-500 dark:text-gray-400">
                    {{ $footer['description'] ?? 'Witub là một nền tảng cung cấp các công cụ trực tuyến hữu ích, giúp bạn làm việc nhanh chóng và hiệu quả hơn.' }}
                </p>
                @if (!empty($footer['email']))
                    <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                        Email: <a href="mailto:{{ $footer['email'] }}"
                            class="hover:underline">{{ $footer['email'] }}</a>
                    </p>
                @endif
                @if (!empty($footer['address']))
                    <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                        Địa chỉ: {{ $footer['address'] }}
                    </p>
                @endif
                @if (!empty($footer['hotline']))
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                        Hotline: <a href="tel:{{ $footer['hotline'] }}"
                            class="hover:underline">{{ $footer['hotline'] }}</a>
                    </p>
                @endif
            </div>

            {{-- START: CÁC CỘT MENU ĐỘNG --}}
            @foreach ($footer['menu_columns'] ?? [] as $column)
                {{-- Đảm bảo chỉ render cột nếu có tiêu đề và ít nhất 1 mục con --}}
                @if (!empty($column['title']) && !empty($column['items']))
                    <div>
                        <h3 class="mb-6 text-sm font-semibold text-gray-600 uppercase dark:text-white">
                            {{ $column['title'] }}</h3>
                        <ul>
                            @foreach ($column['items'] as $item)
                                @if (!empty($item['url']) && !empty($item['text']))
                                    <li class="mb-4">
                                        <a href="{{ $item['url'] }}"
                                            class="font-normal text-gray-600 hover:underline dark:text-gray-400">
                                            {{ $item['text'] }}
                                        </a>
                                    </li>
                                @endif
                            @endforeach
                        </ul>
                    </div>
                @endif
            @endforeach


        </div>

        <hr class="my-8 border-gray-200 lg:my-12 dark:border-gray-700">

        <span class="block text-center text-gray-600 dark:text-gray-400">
            © {{ $footer['year'] ?? date('Y') }} <a href="{{ url('/') }}" target="_blank"
                rel="noreferrer">Witub</a>. Tất cả quyền được bảo lưu.
        </span>
    </div>
</footer>
