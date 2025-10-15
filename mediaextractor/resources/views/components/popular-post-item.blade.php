@props(['post'])
<a href="{{ $post['link'] }}" class="flex items-start gap-4 group">
    <div class="relative flex-shrink-0 w-32 h-20">
        <img src="{{ $post['image'] }}" alt="{{ $post['title'] }}" class="w-full h-full object-cover rounded-lg">
        <div class="absolute bottom-3 left-3 text-4xl font-semibold italic text-white/60 select-none"
            style="text-shadow: 1px 1px 3px rgba(0,0,0,0.4);">
            {{ $post['rank'] }}
        </div>
    </div>
    <div>
        <p class="text-blue-600 font-semibold text-xs mb-1">{{ $post['category'] }}</p>
        <h4 class="font-semibold text-base leading-tight group-hover:text-momo-pink">
            {{ $post['title'] }}
        </h4>
    </div>
</a>
