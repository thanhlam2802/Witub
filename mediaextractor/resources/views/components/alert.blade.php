@props([
    'type' => 'info',
    'title' => null,
])

@php
    // Màu sắc cho từng loại alert
    $styles =
        [
            'info' => [
                'bg' => 'bg-blue-100',
                'text' => 'text-blue-700',
                'dark_text' => 'dark:text-blue-400',
            ],
            'success' => [
                'bg' => 'bg-green-100',
                'text' => 'text-green-700',
                'dark_text' => 'dark:text-green-400',
            ],
            'warning' => [
                'bg' => 'bg-yellow-100',
                'text' => 'text-yellow-700',
                'dark_text' => 'dark:text-yellow-400',
            ],
            'danger' => [
                'bg' => 'bg-red-100',
                'text' => 'text-red-700',
                'dark_text' => 'dark:text-red-400',
            ],
        ][$type] ?? $styles['info'];
@endphp

<div id="alertBox"
    {{ $attributes->merge([
        'class' => "fixed top-4 right-4 z-50 flex items-center p-4 mb-4 text-sm rounded-lg shadow-lg transition-opacity duration-1000 opacity-100 {$styles['bg']} {$styles['text']} dark:bg-gray-800 {$styles['dark_text']}",
        'role' => 'alert',
    ]) }}>
    <svg class="shrink-0 inline w-4 h-4 me-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor"
        viewBox="0 0 20 20">
        <path
            d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5ZM9.5 4a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3ZM12 15H8a1 1 0 0 1 0-2h1v-3H8a1 1 0 0 1 0-2h2a1 1 0 0 1 1 1v4h1a1 1 0 0 1 0 2Z" />
    </svg>
    <div>
        <span class="font-medium">{{ $title }}</span>
        {{ $slot }}
    </div>
</div>

<script>
    setTimeout(() => {
        const alertBox = document.getElementById('alertBox');
        if (alertBox) {
            alertBox.classList.add('opacity-0');
            setTimeout(() => alertBox.remove(), 1000);
        }
    }, 1000);
</script>
