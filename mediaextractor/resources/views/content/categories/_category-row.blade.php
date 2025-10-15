<tr class="hover:bg-gray-100 dark:hover:bg-gray-700 child-of-{{ $category->parent_id ?? 'root' }} {{ $level > 0 ? 'hidden' : '' }}"
    id="row-{{ $category->id }}">
    <td class="w-4 p-4 align-top">
        <div class="flex items-center">
            <input id="checkbox-{{ $category->id }}" aria-describedby="checkbox-1" type="checkbox"
                class="w-4 h-4 border-gray-300 rounded bg-gray-50 focus:ring-3 focus:ring-primary-300 dark:focus:ring-primary-600 dark:ring-offset-gray-800 dark:bg-gray-700 dark:border-gray-600">
            <label for="checkbox-{{ $category->id }}" class="sr-only">checkbox</label>
        </div>
    </td>

    <td class="p-4 text-sm text-gray-900 whitespace-normal dark:text-white align-top"
        style="padding-left: {{ $level * 1.5 + 1 }}rem;">

        <div class="flex items-center">
            {{-- THÊM MỚI: Icon xổ xuống nếu có danh mục con --}}
            @if ($category->recursiveChildren->isNotEmpty())
                <button type="button" class="toggle-children mr-2 text-gray-500 hover:text-gray-800"
                    data-category-id="{{ $category->id }}">
                    <svg class="w-4 h-4 transition-transform duration-200" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                    </svg>
                </button>
            @else
                {{-- Thêm khoảng trống để các dòng thẳng hàng --}}
                <span class="w-4 h-4 mr-2 inline-block"></span>
            @endif

            <div>
                {{-- Vietnamese Translation --}}
                <div>
                    <div class="font-semibold text-base">
                        <span class="font-bold text-gray-500">VI:</span>
                        {{ $category->translations->firstWhere('locale_code', 'vi')->name ?? 'N/A' }}
                    </div>
                    <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                        <span class="font-bold">Slug:</span>
                        {{ $category->translations->firstWhere('locale_code', 'vi')->slug ?? '...' }}
                    </div>
                </div>
                {{-- English Translation --}}
                <div class="mt-3">
                    <div class="font-semibold text-base">
                        <span class="font-bold text-gray-500">EN:</span>
                        {{ $category->translations->firstWhere('locale_code', 'en')->name ?? 'N/A' }}
                    </div>
                    <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                        <span class="font-bold">Slug:</span>
                        {{ $category->translations->firstWhere('locale_code', 'en')->slug ?? '...' }}
                    </div>
                </div>
            </div>
        </div>
    </td>

    {{-- Các cột còn lại giữ nguyên --}}
    <td class="p-4 text-base font-medium text-gray-900 whitespace-nowrap dark:text-white align-top">
        {{ $category->type->name ?? 'N/A' }}
    </td>
    <td class="p-4 text-base font-normal text-gray-900 whitespace-nowrap dark:text-white align-top">
        <div class="flex items-center">
            @if ($category->is_active)
                <div class="h-2.5 w-2.5 rounded-full bg-green-400 mr-2"></div> Active
            @else
                <div class="h-2.5 w-2.5 rounded-full bg-red-500 mr-2"></div> Inactive
            @endif
        </div>
    </td>
    <td class="p-4 space-x-2 whitespace-nowrap align-top">
        {{-- Edit Button --}}
        <a href="{{ route('categories.edit', $category->id) }}"
            class="inline-flex items-center px-3 py-2 text-sm font-medium text-center text-white rounded-lg bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:ring-primary-300 dark:bg-primary-600 dark:hover:bg-primary-700 dark:focus:ring-primary-800">
            <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                <path d="M17.414 2.586a2 2 0 00-2.828 0L7 10.172V13h2.828l7.586-7.586a2 2 0 000-2.828z"></path>
                <path fill-rule="evenodd"
                    d="M2 6a2 2 0 012-2h4a1 1 0 010 2H4v10h10v-4a1 1 0 112 0v4a2 2 0 01-2 2H4a2 2 0 01-2-2V6z"
                    clip-rule="evenodd"></path>
            </svg>
            Edit
        </a>
        {{-- Delete Button --}}
        <form action="{{ route('categories.destroy', $category->id) }}" method="POST" class="inline-block"
            onsubmit="return confirm('Are you sure you want to delete this category?');">
            @csrf
            @method('DELETE')
            <button type="submit"
                class="inline-flex items-center px-3 py-2 text-sm font-medium text-center text-white bg-red-600 rounded-lg hover:bg-red-800 focus:ring-4 focus:ring-red-300 dark:focus:ring-red-900">
                <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                    <path fill-rule="evenodd"
                        d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z"
                        clip-rule="evenodd"></path>
                </svg>
                Delete
            </button>
        </form>
    </td>
</tr>


@if ($category->recursiveChildren->isNotEmpty())
    @foreach ($category->recursiveChildren as $child)
        @include('content.categories._category-row', [
            'category' => $child,
            'level' => $level + 1,
        ])
    @endforeach
@endif
