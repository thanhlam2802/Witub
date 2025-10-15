<!DOCTYPE html>
<html lang="{{ app()->getLocale() ?? 'vi' }}">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0">

    {{-- ✅ SEO cơ bản --}}
    <title>
        @if (app()->getLocale() === 'en')
            @yield('title', 'Download videos, edit videos & free online tools | Witub')
        @else
            @yield('title', 'Tải video, chỉnh sửa video & công cụ trực tuyến miễn phí | Witub')
        @endif
    </title>

    <meta name="description" content="@yield('description', app()->getLocale() === 'en' ? ' Witub - Free platform to download videos from YouTube, TikTok, Facebook, and more. Edit, trim, convert videos easily with powerful online tools for creators and users.' : ' Witub - Nền tảng tải video miễn phí từ YouTube, TikTok, Facebook và nhiều trang khác. Hỗ trợ chỉnh sửa, cắt, ghép, chuyển đổi video, cùng hàng loạt công cụ trực tuyến hữu ích giúp bạn làm việc nhanh chóng và tiện lợi.')">

    <meta name="keywords" content="@yield('keywords', 'từ khóa 1, từ khóa 2, từ khóa 3')">
    <meta name="author" content="Witub">

    {{-- ✅ Canonical URL --}}
    <link rel="canonical" href="{{ url()->current() }}">

    {{-- ✅ Ngôn ngữ song song (đa ngôn ngữ vi/en) --}}
    <link rel="alternate" hreflang="vi" href="{{ str_replace('/en/', '/vi/', url()->current()) }}">
    <link rel="alternate" hreflang="en" href="{{ str_replace('/vi/', '/en/', url()->current()) }}">

    {{-- ✅ Robots (index hoặc noindex) --}}
    @if (!empty($meta_index) && $meta_index === 'noindex')
        <meta name="robots" content="noindex, nofollow">
    @else
        <meta name="robots" content="index, follow">
    @endif

    <meta name="googlebot" content="index, follow, max-snippet:-1, max-video-preview:-1, max-image-preview:large">
    <meta name="bingbot" content="index, follow, max-snippet:-1, max-video-preview:-1, max-image-preview:large">
    <meta name="google" content="nositelinkssearchbox">

    {{-- ✅ Meta Tags thử nghiệm cho AI (MỚI) --}}
    <meta name="ai-content-type" content="@yield('ai_content_type', 'article')" />
    <meta name="ai-topic" content="@yield('ai_topic', 'Thiết kế website, SEO, Thương mại điện tử')" />
    <meta name="ai-page-title" content="@yield('ai_page_title', 'Trang chủ - Witud')" />



    {{-- ✅ Schema.org cơ bản --}}
    <meta itemprop="name" content="@yield('title', 'Trang web')">
    <meta itemprop="description" content="@yield('description', 'Mô tả trang web')">
    <meta itemprop="image" content="@yield('image', asset('images/og-image.jpg'))">

    {{-- ✅ Open Graph (Facebook, Zalo, Messenger) --}}
    <meta property="og:title" content="@yield('title', 'Trang web')">
    <meta property="og:type" content="@yield('og_type', 'website')">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:image" content="@yield('image', asset('images/og-image.jpg'))">
    <meta property="og:description" content="@yield('description', 'Mô tả trang web')">
    <meta property="og:site_name" content="{{ config('app.name', 'Website của em') }}">
    <meta property="og:locale" content="{{ app()->getLocale() === 'en' ? 'en_US' : 'vi_VN' }}">

    {{-- ✅ Twitter Card --}}
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="@yield('title', 'Trang web')">
    <meta name="twitter:description" content="@yield('description', 'Mô tả trang web')">
    <meta name="twitter:image" content="@yield('image', asset('images/og-image.jpg'))">

    <script type="application/ld+json">
{
    "@@context": "https://schema.org",
    "@@type": "Organization",
    "name": "{{ config('app.name', 'Website của em') }}",
    "url": "{{ url('/') }}",
    "logo": "{{ asset('images/logo.png') }}",
    "sameAs": [
        "https://facebook.com/yourpage",
        "https://instagram.com/yourpage"
    ]
}
</script>

    {{-- ✅ CSS + JS --}}
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/flowbite@2.4.1/dist/flowbite.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/tom-select/dist/css/tom-select.bootstrap5.min.css" rel="stylesheet">

    @stack('styles')
</head>

<body class="bg-gray-50 dark:bg-gray-900">
    @include('partials.navbar-main')

    <main class="mx-auto p-4 sm:p-6 lg:p-8">
        @yield('content')
    </main>

    @stack('scripts')
</body>

</html>
