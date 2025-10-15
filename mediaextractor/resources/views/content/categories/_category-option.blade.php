@props(['categoryItem', 'level' => 0, 'selectedId' => null])

<option value="{{ $categoryItem->id }}"@selected($selectedId == $categoryItem->id)>
    {!! str_repeat('&nbsp;&nbsp;&nbsp;', $level) !!} &vdash;
    {{ optional($categoryItem->translations->firstWhere('locale_code', 'vi'))->name ?? $categoryItem->id }}
</option>

@if ($categoryItem->recursiveChildren->isNotEmpty())
    @foreach ($categoryItem->recursiveChildren as $child)
        @include('content.categories._category-option', [
            'categoryItem' => $child,
            'level' => $level + 1,
            'selectedId' => $selectedId,
        ])
    @endforeach
@endif
