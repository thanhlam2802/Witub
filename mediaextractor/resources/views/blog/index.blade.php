@extends('layouts.app')
@php
    $isCategoryPage = isset($currentCategory) && $currentCategory->id != 0;
    $currentTranslation = $isCategoryPage ? $currentCategory->translations->first() : null;
@endphp

@if ($isCategoryPage)
    @section('title', $currentTranslation->seo_title ?? $currentTranslation->name . ' | Witub')
    @section('description', $currentTranslation->seo_description ?? $currentTranslation->description)
    @section('keywords', $currentTranslation->seo_keywords ?? $currentTranslation->name)
@else
    @section('title', 'Blog Công nghệ, Lập trình & Marketing | Witub')
    @section('description',
        'Khám phá các bài viết, hướng dẫn và tin tức mới nhất về thiết kế website, lập trình
        Laravel, SEO và xu hướng công nghệ từ Witud. Cập nhật kiến thức và kỹ năng mỗi ngày.')
    @section('keywords', 'blog công nghệ, lập trình laravel, thủ thuật seo, tin tức it, witub')
@endif


@section('content')
    <div class="container  pt-24 mx-auto md:pt-32  dark:bg-gray-900">

        @php
            $currentTranslation = $currentCategory ? $currentCategory->translations->first() : null;
        @endphp

        {{-- Header sẽ hiển thị thông tin của danh mục đang active --}}
        @if ($currentTranslation)
            <header class="py-4 bg-gray-50/50 mb-8">
                @if ($isCategoryPage)
                    <h1 class="text-4xl md:text-6xl font-extrabold text-gray-800 mb-4">{{ $currentTranslation->name }}</h1>
                    <p class="text-lg text-gray-500 max-w-3xl">{{ $currentTranslation->description }}</p>
                @else
                    <h1 class="text-4xl md:text-6xl font-extrabold text-gray-800 mb-4">Blog Công nghệ & Tin tức</h1>
                    <p class="text-lg text-gray-500 max-w-3xl">Khám phá các bài viết, hướng dẫn và tin tức mới nhất từ Witud.
                    </p>
                @endif
            </header>

        @endif

        {{-- Thanh điều hướng --}}
        <div class="relative mb-4">
            <button id="scroll-left"
                class="absolute left-0 top-1/2 -translate-y-1/2 z-10 bg-white rounded-full w-8 h-8 flex items-center justify-center cursor-pointer border border-gray-300 shadow-md">
                &larr;
            </button>

            <nav id="nav-container" class="border-b overflow-x-auto scroll-smooth whitespace-nowrap scrollbar-hide h-12">
                <div class="flex items-center space-x-8 px-12 h-full">
                    {{-- 1. MỤC "MỚI NHẤT" --}}
                    @php
                        $isLatestActive = !$currentCategory || $currentCategory->id == 0;
                        $latestClasses = $isLatestActive
                            ? 'text-blue-600 border-b-2 border-blue-600 font-semibold'
                            : 'text-gray-600 border-b-2 border-transparent hover:text-black';
                    @endphp
                    <a href="{{ localized_route('blog.index') }}"
                        class="{{ $latestClasses }} flex items-center justify-center h-full px-2">
                        {{ __('blog.latest_title') }}
                    </a>

                    {{-- 2. CÁC DANH MỤC TỪ CSDL --}}
                    @foreach ($categories as $category)
                        @php
                            $translation = $category->translations->first();
                            if (!$translation) {
                                continue;
                            }

                            $isActive = $currentCategory && $category->id == $currentCategory->id;
                            $linkClasses = $isActive
                                ? 'text-blue-600 border-b-2 border-blue-600 font-semibold'
                                : 'text-gray-600 border-b-2 border-transparent hover:text-black';
                        @endphp
                        {{-- ✅ THAY ĐỔI: Sử dụng đúng route 'blog.resolver' --}}
                        <a href="{{ localized_route('blog.resolver', ['slug' => $translation->slug]) }}"
                            class="{{ $linkClasses }} flex items-center justify-center h-full px-2">
                            {{ $translation->name }}
                        </a>
                    @endforeach
                </div>
            </nav>

            <button id="scroll-right"
                class="absolute right-0 top-1/2 -translate-y-1/2 z-10 bg-white rounded-full w-8 h-8 flex items-center justify-center cursor-pointer border border-gray-300 shadow-md">
                &rarr;
            </button>
        </div>


        <main class="grid grid-cols-1 lg:grid-cols-3 lg:gap-8 ">
            <div class="lg:col-span-3 border-b-2 border-gray-200">
                @if ($featuredPost)
                    <x-post-card-featured :post="$featuredPost" />
                @endif
                <section class="my-8">
                    <h2 class="text-2xl font-bold text-gray-800 mb-4 sr-only">Các bài viết nổi bật khác</h2>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        @foreach ($secondaryPosts as $post)
                            <x-post-card-small :post="$post" />
                        @endforeach
                    </div>
                </section>
            </div>

            <div class="lg:col-span-2">

                <section id="post-container" class="space-y-8">
                    @foreach ($mainPosts as $post)
                        <x-post-card-list-item :post="$post" />
                    @endforeach
                </section>


                <div class="flex justify-center mt-8">
                    @if ($mainPostsPagination->hasMorePages())
                        <button id="load-more" data-next-page="{{ $mainPostsPagination->currentPage() + 1 }}"
                            class="flex items-center gap-2 px-6 py-2 border-2 border-pink-600 text-pink-600 rounded-full font-semibold hover:bg-pink-50 transition">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                            Xem thêm
                        </button>
                    @endif
                </div>
            </div>


            {{-- Sidebar --}}
            <aside class="lg:col-span-1 mt-8 lg:mt-0">
                <h3 class="text-xl font-semibold pb-2 mb-6">Xem nhiều nhất</h3>
                <div class="space-y-5">
                    @foreach ($popularPosts as $post)
                        <x-popular-post-item :post="$post" />
                    @endforeach
                </div>
            </aside>
        </main>
    </div>
    @push('scripts')
        <script>
            const navContainer = document.getElementById('nav-container');
            const scrollLeftBtn = document.getElementById('scroll-left');
            const scrollRightBtn = document.getElementById('scroll-right');
            const scrollAmount = 150;

            scrollLeftBtn.addEventListener('click', () => navContainer.scrollBy({
                left: -scrollAmount,
                behavior: 'smooth'
            }));
            scrollRightBtn.addEventListener('click', () => navContainer.scrollBy({
                left: scrollAmount,
                behavior: 'smooth'
            }));
        </script>

        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const loadMoreBtn = document.getElementById('load-more');
                const postContainer = document.getElementById('post-container');

                if (loadMoreBtn) {
                    loadMoreBtn.addEventListener('click', async () => {
                        const nextPage = loadMoreBtn.dataset.nextPage;

                        if (!nextPage) {
                            console.error('⚠️ Không có giá trị nextPage trong data-next-page');
                            return;
                        }

                        loadMoreBtn.disabled = true;
                        loadMoreBtn.textContent = 'Đang tải...';

                        try {
                            const response = await fetch(`?page=${nextPage}`, {
                                headers: {
                                    'X-Requested-With': 'XMLHttpRequest'
                                }
                            });

                            const data = await response.json();

                            if (data.html && data.html.trim() !== '') {
                                postContainer.insertAdjacentHTML('beforeend', data.html);
                                loadMoreBtn.dataset.nextPage = parseInt(nextPage) + 1;
                                loadMoreBtn.textContent = 'Xem thêm';
                                loadMoreBtn.disabled = false;
                            } else {
                                loadMoreBtn.remove(); // Hết bài thì ẩn nút
                            }
                        } catch (error) {
                            console.error('❌ Lỗi khi load thêm bài:', error);
                            loadMoreBtn.textContent = 'Lỗi tải bài';
                        }
                    });
                }
            });
        </script>
    @endpush
@endsection
