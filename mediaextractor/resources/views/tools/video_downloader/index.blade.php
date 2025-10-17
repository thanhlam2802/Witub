@extends('service_layout')

@section('title', 'Tải video trực tuyến - ToolBox AI')

@section('content')
{{-- Vị trí này là thư mục nội dung cho trang "Tải video trực tuyến" --}}
<div class="max-w-6xl mx-auto">
    <div class="flex flex-col lg:flex-row gap-8">

        {{-- Phần Nội dung chính (Main Content Area) --}}
        <div class="lg:flex-grow">
            {{-- Toàn bộ nội dung của index.html nằm ở đây --}}
            <header class="text-center max-w-3xl mx-auto">
                <h1 class="text-4xl md:text-4xl font-bold text-gray-900">
                    Trình tải video & gallery
                </h1>
                {{-- ... (các phần tử khác) ... --}}
            </header>

            <main class="mt-8 max-w-4xl mx-auto">
                {{-- ... (Video/Gallery Info) ... --}}
            </main>

            <section class="mt-16 text-center">
                {{-- ... (Nền tảng được hỗ trợ) ... --}}
            </section>
        </div>

        {{-- Phần Sidebar/Thông tin thêm (Right Sidebar) --}}
        <aside class="lg:w-72 lg:flex-shrink-0">
            <div class="bg-white p-6 rounded-xl shadow-lg border border-gray-200 sticky top-4 space-y-4 text-sm">
                {{-- ... (Nội dung sidebar phải) ... --}}
            </div>
        </aside>
    </div>
</div>
@endsection

@section('scripts')
<script src="/static/js/app.js" defer></script>
@endsection
