@props(['post'])
<article class="grid grid-cols-1 md:grid-cols-3 gap-6 border-b pb-8">
    <div class="md:col-span-1">
        <a href="{{ $post['link'] }}">
            <img src="{{ $post['image'] }}" alt="{{ $post['title'] }}" class="w-full h-full object-cover rounded-lg">
        </a>
    </div>
    <div class="md:col-span-2">
        <p class="font-semibold inline-block text-[13px] mb-1">{{ $post['category'] }}</p>
        <h3 class="font-semibold leading-tight text-[24px] mb-1 ">
            <a href="{{ $post['link'] }}">{{ $post['title'] }}</a>
        </h3>
        <p class="text-gray-600 leading-relaxed text-sm">{!! $post['excerpt'] !!}</p>
    </div>
</article>
