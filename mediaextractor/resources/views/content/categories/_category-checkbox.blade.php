@php

    $isChecked = old('categories')
        ? in_array($categoryItem->id, old('categories'))
        : isset($post) && $post->categories->contains($categoryItem->id);
@endphp

<li class="ml-{{ $level * 4 }}">
    <div class="flex items-center">
        <input id="category-{{ $categoryItem->id }}" name="categories[]" value="{{ $categoryItem->id }}" type="checkbox"
            class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500"
            @if ($isChecked) checked @endif>
        <label for="category-{{ $categoryItem->id }}" class="ml-2 text-sm font-medium text-gray-900 dark:text-gray-300">
            {{ $categoryItem->translations->firstWhere('locale_code', 'vi')->name ?? $categoryItem->id }}
        </label>
    </div>


    @if ($categoryItem->children->isNotEmpty())
        <ul class="mt-2 space-y-2">
            @foreach ($categoryItem->children as $child)
                @include('content.categories._category-checkbox', [
                    'categoryItem' => $child,
                    'level' => $level + 1,
                    'post' => $post ?? null,
                ])
            @endforeach
        </ul>
    @endif
</li>
