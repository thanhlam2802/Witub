@extends('admin.dashboard')
@section('title', 'Công cụ SEO Website')

@section('content')
    <x-session-alerts />
    <div
        class="p-4 bg-white block sm:flex items-center justify-between border-b border-gray-200 lg:mt-1.5 dark:bg-gray-800 dark:border-gray-700">
        <div class="w-full mb-1">
            <div class="mb-4">
                <x-breadcrumb />
                <h1 class="text-xl font-semibold text-gray-900 sm:text-2xl dark:text-white">@yield('title')</h1>
                <p class="text-gray-500 dark:text-gray-400 mt-1">Thiết lập các file SEO tĩnh để giúp các công cụ tìm kiếm
                    nhận dạng website nhanh chóng.</p>
            </div>
        </div>
    </div>

    <div class="flex flex-col">
        <div class="overflow-x-auto">
            <div class="inline-block min-w-full align-middle">
                <div class="">


                    <form action="{{ route('admin.seo_tools.save') }}" method="POST">
                        @csrf
                        <div class="space-y-6 bg-white p-6  dark:bg-gray-800 dark:border-gray-700">

                            {{-- Robots.txt Editor --}}
                            <div>
                                <label for="robots"
                                    class="block mb-2 text-sm font-semibold text-gray-900 dark:text-white">Nội
                                    dung File robots.txt</label>
                                <textarea id="robots" name="robots" rows="12"
                                    class="block p-2.5 w-full text-sm text-gray-900 bg-gray-50 rounded-lg border border-gray-300 font-mono focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500">{{ old('robots', $robotsContent ?? '') }}</textarea>
                                <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">File này giúp điều hướng Googlebot
                                    và các công cụ tìm kiếm crawl website của bạn hiệu quả hơn.</p>
                            </div>

                            {{-- Sitemap.xml Viewer --}}
                            <div>
                                <label for="sitemap"
                                    class="block mb-2 text-sm font-semibold text-gray-900 dark:text-white">Nội dung File
                                    sitemap.xml</label>
                                <textarea id="sitemap" readonly rows="12"
                                    class="block p-2.5 w-full text-sm text-gray-600 bg-gray-100 rounded-lg border border-gray-300 font-mono cursor-not-allowed dark:bg-gray-900 dark:border-gray-600 dark:text-gray-400">{{ $sitemapContent }}</textarea>
                                <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">File sitemap giúp Google biết các
                                    trang trên website bạn cần được index. Định dạng XML, chuẩn theo hướng dẫn <a
                                        href="https://www.sitemaps.org/protocol.html" target="_blank"
                                        class="text-blue-600 hover:underline">tại đây</a>.</p>
                            </div>

                            {{-- Sitemap Files List --}}
                            @if (!empty($sitemapFiles))
                                <div>
                                    <h3 class="block mb-3 text-sm font-semibold text-gray-900 dark:text-white">Danh sách
                                        File Sitemap</h3>
                                    <div class="space-y-3">
                                        @php $totalUrls = 0; @endphp
                                        @foreach ($sitemapFiles as $file)
                                            @php $totalUrls += $file['url_count']; @endphp
                                            <div
                                                class="flex flex-wrap items-center justify-between p-3 bg-gray-50 border rounded-lg dark:bg-gray-700 dark:border-gray-600 gap-2">
                                                <div class="flex-grow">
                                                    <p class="font-mono text-sm font-medium text-gray-900 dark:text-white">
                                                        {{ $file['name'] }}
                                                        <span
                                                            class="text-xs text-gray-500 dark:text-gray-400">({{ $file['size'] }})</span>
                                                    </p>
                                                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                                        Cập nhật lần cuối: {{ $file['last_modified'] }}
                                                    </p>
                                                </div>
                                                <div class="flex items-center space-x-4 flex-shrink-0">
                                                    <span
                                                        class="inline-flex items-center bg-green-100 text-green-800 text-xs font-medium px-2.5 py-1 rounded-full dark:bg-green-900 dark:text-green-300">
                                                        URL: {{ $file['url_count'] }}
                                                    </span>
                                                    <a href="https://search.google.com/search-console/sitemaps?resource_id={{ url('/') }}&sitemap={{ url($file['name']) }}"
                                                        target="_blank"
                                                        class="text-xs text-blue-600 hover:underline dark:text-blue-500">
                                                        Khai báo trên Google Search Console
                                                    </a>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                    <p class="mt-4 font-semibold text-sm text-gray-700 dark:text-gray-300">Tổng số URL trong
                                        các sitemap: {{ $totalUrls }}</p>
                                </div>
                            @endif


                            {{-- Action Buttons --}}
                            <div class="pt-4 border-t dark:border-gray-700">
                                <div class="flex items-center">
                                    <input id="regenerate_sitemap" name="regenerate_sitemap" type="checkbox" value="1"
                                        checked
                                        class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                                    <label for="regenerate_sitemap"
                                        class="ml-2 text-sm font-medium text-gray-900 dark:text-gray-300">Cập nhật</label>
                                </div>
                                <div class="flex items-center space-x-3 mt-4">
                                    <button type="button" onclick="window.location='{{ route('admin.dashboard') }}'"
                                        class="text-gray-900 bg-white border border-gray-300 focus:outline-none hover:bg-gray-100 focus:ring-4 focus:ring-gray-100 font-medium rounded-lg text-sm px-5 py-2.5 dark:bg-gray-800 dark:text-white dark:border-gray-600 dark:hover:bg-gray-700 dark:hover:border-gray-600 dark:focus:ring-gray-700">
                                        Hủy Bỏ
                                    </button>
                                    <button type="submit"
                                        class="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center">
                                        Lưu Dữ Liệu
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
