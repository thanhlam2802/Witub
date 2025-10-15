@extends('layouts.app')
@php
    $currentTranslation = $post->translations->first();
@endphp

{{-- SEO Meta Tags --}}
@section('title', $currentTranslation->seo_title ?? $currentTranslation->title)
@section('description', $currentTranslation->seo_description ?? $currentTranslation->excerpt)
@section('keywords', $currentTranslation->seo_keywords)

{{-- Thêm thẻ meta robots động --}}
@section('meta_tags')
    @if ($post->is_indexable)
        <meta name="robots" content="index, follow" />
    @else
        <meta name="robots" content="noindex, nofollow" />
    @endif

    @if ($currentTranslation->seo_canonical_url)
        <link rel="canonical" href="{{ $currentTranslation->seo_canonical_url }}" />
    @endif
@endsection


{{-- BƯỚC 1: THÊM CSS CHO TRẠNG THÁI "ACTIVE" --}}
@push('styles')
    <style>
        #toc-list a.active-toc-link {
            color: #2563eb;
            /* Tailwind's text-blue-600 */
            font-weight: 600;
        }

        /* Thêm hiệu ứng chuyển động mượt mà (tùy chọn) */
        #toc-list a {
            transition: all 0.2s ease-in-out;
        }
    </style>
@endpush


@section('content')
    <div class="container pt-24 mx-auto md:pt-32 lg:px-0 dark:bg-gray-900">
        <div class="mb-8">
            {{-- Breadcrumb sử dụng dữ liệu thật --}}
            <nav class="text-sm drop-shadow-md bg-white/20 backdrop-blur-md rounded py-2 inline-block"
                aria-label="Breadcrumb">
                <ol class="inline-flex list-none p-0 items-center">
                    <li class="flex items-center">
                        <a href="#" class="text-gray-700 hover:text-gray-900 font-semibold">Blog</a>
                        <svg class="w-6 h-6 text-gray-400" fill="currentColor" viewBox="0 0 20 20"
                            xmlns="http://www.w3.org/2000/svg">
                            <path fill-rule="evenodd"
                                d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z"
                                clip-rule="evenodd"></path>
                        </svg>
                    </li>
                    <li class="flex items-center">
                        <span
                            class="text-gray-700">{{ \Illuminate\Support\Str::limit($currentTranslation->title, 50) }}</span>
                    </li>
                </ol>
            </nav>

            @php

                $thumbnailSrc =
                    $post->thumbnail && \Illuminate\Support\Str::startsWith($post->thumbnail, 'data:image')
                        ? $post->thumbnail
                        : asset('storage/' . $post->thumbnail);
            @endphp
            <img src="{{ $thumbnailSrc }}" alt="{{ $currentTranslation->title ?? '' }}"
                class="w-full h-auto max-h-[450px] object-cover rounded-xl mt-4">
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-12 lg:gap-12">
            <article class="lg:col-span-9" id="post-article">
                <div class="flex justify-between items-center mb-4">
                    <div>
                        {{-- Lấy tên danh mục đầu tiên --}}
                        <span class="font-bold text-red-600">
                            {{ $post->categories->first()?->translations->first()?->name ?? 'Uncategorized' }}</span>
                        <span class="text-gray-500 dark:text-gray-400 text-sm ml-3"> • 4 phút đọc</span>
                    </div>
                    <button class="flex items-center text-sm text-gray-600 dark:text-gray-300 hover:text-blue-600">
                        <span class="mr-2">Chia sẻ</span>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                            xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M8.684 13.342C8.886 12.938 9 12.482 9 12s-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6.002l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.366a3 3 0 105.367 2.684 3 3 0 00-5.367-2.684z">
                            </path>
                        </svg>
                    </button>
                </div>

                {{-- Tiêu đề và giới thiệu từ database --}}
                <h1 class="text-3xl md:text-4xl font-extrabold text-gray-900 dark:text-white mb-4 leading-tight">
                    {{ $currentTranslation->title }}
                </h1>
                <p class="text-lg text-gray-700 dark:text-gray-300 mb-8">
                    {!! $currentTranslation->excerpt !!}
                </p>

                {{-- Nội dung chính (body) từ database, render ra HTML --}}
                <div class="prose prose-lg dark:prose-invert max-w-none">
                    {!! $currentTranslation->content !!}
                </div>
            </article>

            <aside class="lg:col-span-3 mt-8 lg:mt-0">
                <div class="sticky top-24">
                    <div id="toc-container"
                        class="p-6 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl">
                        <h3
                            class="text-xs font-semibold uppercase text-gray-500 dark:text-gray-400 tracking-wider mb-4 pb-3 border-b border-gray-200 dark:border-gray-700">
                            MỤC LỤC
                        </h3>
                        <ul id="toc-list" class="space-y-1"></ul>
                    </div>
                </div>
            </aside>

        </div>
        @if ($relatedPosts && $relatedPosts->count() > 0)
            <div class="mt-16 pt-12 border-t max-w-6xl mx-auto">
                <h2 class="text-3xl font-extrabold text-gray-900 dark:text-white mb-8">
                    Bài viết liên quan
                </h2>

                <div class="space-y-8">
                    @foreach ($relatedPosts as $relatedPost)
                        {{-- Sử dụng component bạn đã cung cấp --}}
                        <x-post-card-related :post="$relatedPost" />
                    @endforeach
                </div>


                <div class="mt-12 flex justify-center">
                    <a href="{{ route('blog.index', ['locale' => app()->getLocale()]) }}"
                        class="flex items-center gap-2 px-6 py-2 border-2 border-pink-600 text-pink-600 rounded-full font-semibold hover:bg-pink-50 transition">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                        Xem thêm
                    </a>
                </div>

            </div>
        @endif
    </div>
@endsection

@push('scripts')
    {{-- BƯỚC 2: CẬP NHẬT JAVASCRIPT --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const articleContent = document.getElementById('post-article');
            const tocList = document.getElementById('toc-list');
            const tocContainer = document.getElementById('toc-container');
            const headings = articleContent.querySelectorAll('h2');

            // --- Phần 1: Tạo mục lục (giữ nguyên) ---
            if (headings.length === 0) {
                if (tocContainer) {
                    tocContainer.style.display = 'none';
                }
                return;
            }
            headings.forEach(heading => {
                const title = heading.textContent.trim();
                const id = 'section-' + title.toLowerCase().replace(/[^\w\s-]/g, '').replace(/\s+/g, '-')
                    .replace(/--+/g, '-');
                heading.id = id;
                heading.classList.add('scroll-mt-24');
                const listItem = document.createElement('li');
                const link = document.createElement('a');
                link.href = `#${id}`;
                link.textContent = title;
                link.className =
                    'block text-gray-600 dark:text-gray-300 hover:text-blue-600 dark:hover:text-blue-400 transition-colors duration-200 py-1 rounded';
                listItem.appendChild(link);
                tocList.appendChild(listItem);
            });

            // --- Phần 2: Thêm chức năng Active on Scroll (MỚI) ---
            const tocLinks = document.querySelectorAll('#toc-list a');
            const sections = Array.from(headings);
            // Offset để kích hoạt active sớm hơn, bù cho chiều cao của navbar
            const navbarOffset = 150;

            const highlightTocLink = () => {
                let currentActiveId = '';

                // Tìm xem section nào đang ở trong viewport
                sections.forEach(section => {
                    const sectionTop = section.offsetTop;
                    if (window.scrollY >= sectionTop - navbarOffset) {
                        currentActiveId = '#' + section.id;
                    }
                });

                // Cập nhật class 'active' cho các link trong mục lục
                tocLinks.forEach(link => {
                    link.classList.remove('active-toc-link');
                    if (link.getAttribute('href') === currentActiveId) {
                        link.classList.add('active-toc-link');
                    }
                });
            };

            // Lắng nghe sự kiện scroll của cửa sổ
            window.addEventListener('scroll', highlightTocLink);

            // Chạy một lần khi tải trang để active mục đầu tiên nếu cần
            highlightTocLink();
        });
    </script>
@endpush
