@props(['post'])
<article class="grid grid-cols-1 md:grid-cols-2 gap-6 ">
    <div class="">
        <a href="{{ $post['link'] }}">
            <img src="{{ $post['image'] }}" alt="{{ $post['title'] }}" class="w-full rounded-lg aspect-video object-cover">
        </a>
    </div>
    <div>
        <p class="font-semibold inline-block text-[13px] mb-1">{{ $post['category'] }}</p>
        <h3 class="font-semibold leading-tight text-[24px] mb-1 ">
            <a href="{{ $post['link'] }}">{{ $post['title'] }}</a>
        </h3>
        <div class="text-gray-600 leading-relaxed text-sm">{!! $post['excerpt'] !!}</div>
    </div>
</article>
