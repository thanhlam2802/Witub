@props(['post'])
<article>
    <a href="{{ $post['link'] }}" class="group">
        <div class="overflow-hidden rounded-lg mb-3">
            <img src="{{ $post['image'] }}" alt="{{ $post['title'] }}"
                class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
        </div>
    </a>
    <div>
        <p class="font-semibold inline-block text-[13px] mb-1">{{ $post['category'] }}</p>
        <h3 class="font-semibold leading-tight text-[24px] mb-1 ">
            <a href="{{ $post['link'] }}">{{ $post['title'] }}</a>
        </h3>
        <p class="text-gray-600 leading-relaxed text-sm">{!! $post['excerpt'] !!}</p>
    </div>
</article>
