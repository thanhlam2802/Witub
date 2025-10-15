@extends('admin.dashboard')
@section('title', 'Categories Management')

@section('content')
    <x-session-alerts />
    <div
        class="p-4 bg-white block sm:flex items-center justify-between border-b border-gray-200 lg:mt-1.5 dark:bg-gray-800 dark:border-gray-700">
        <div class="w-full mb-1">
            <div class="mb-4">
                <x-breadcrumb />
                <h1 class="text-xl font-semibold text-gray-900 sm:text-2xl dark:text-white">Categories settings</h1>
            </div>
            <div class="sm:flex">
                <div class="items-center hidden mb-3 sm:flex sm:divide-x sm:divide-gray-100 sm:mb-0 dark:divide-gray-700">
                    {{-- Gộp search và filter vào chung một form --}}
                    <form class="flex items-center" action="{{ route('categories.index') }}" method="GET">

                        {{-- Search Input --}}
                        <div class="relative lg:w-64 xl:w-96">
                            <label for="search-categories" class="sr-only">Search</label>
                            <input type="text" name="search" id="search-categories"
                                class="bg-gray-50 border border-gray-300 text-gray-900 sm:text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500"
                                placeholder="Search by name..." value="{{ $search ?? '' }}">
                        </div>

                        {{-- Type Filter Select --}}
                        <div class="relative ml-4">
                            <select name="type" onchange="this.form.submit()"
                                class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-48 p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-primary-500 dark:focus:border-primary-500">
                                <option value="">All Types</option>
                                @foreach ($types as $type)
                                    <option value="{{ $type->id }}" @selected($typeFilter == $type->id)>
                                        {{ $type->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>


                        <button type="submit"
                            class="ml-4 px-4 py-2.5 text-sm font-medium text-white bg-blue-700 rounded-lg hover:bg-blue-800 focus:ring-4 focus:ring-blue-300">
                            Apply
                        </button>
                    </form>
                </div>
                <div class="flex items-center ml-auto space-x-2 sm:space-x-3">
                    <a href="{{ route('categories.create', ['type' => $typeFilter]) }}"
                        class="inline-flex items-center justify-center px-4 py-2 text-sm font-medium text-white bg-blue-700 rounded-lg hover:bg-blue-800 focus:ring-4 focus:ring-blue-300">
                        <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                                d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z"
                                clip-rule="evenodd"></path>
                        </svg>
                        Add New Category
                    </a>

                </div>
            </div>
        </div>

    </div>

    <div class="flex flex-col">
        <div class="overflow-x-auto">
            <div class="inline-block min-w-full align-middle">
                <div class="overflow-hidden shadow">
                    <table class="min-w-full divide-y divide-gray-200 table-fixed dark:divide-gray-600">
                        <thead class="bg-gray-100 dark:bg-gray-700">
                            <tr>
                                <th scope="col" class="p-4">
                                    <div class="flex items-center">
                                        <input id="checkbox-all" aria-describedby="checkbox-1" type="checkbox"
                                            class="w-4 h-4 border-gray-300 rounded bg-gray-50 focus:ring-3 focus:ring-primary-300 dark:focus:ring-primary-600 dark:ring-offset-gray-800 dark:bg-gray-700 dark:border-gray-600">
                                        <label for="checkbox-all" class="sr-only">checkbox</label>
                                    </div>
                                </th>
                                <th scope="col"
                                    class="p-4 text-xs font-medium text-left text-gray-500 uppercase dark:text-gray-400">
                                    Name
                                </th>
                                <th scope="col"
                                    class="p-4 text-xs font-medium text-left text-gray-500 uppercase dark:text-gray-400">
                                    Type
                                </th>
                                <th scope="col"
                                    class="p-4 text-xs font-medium text-left text-gray-500 uppercase dark:text-gray-400">
                                    Status
                                </th>
                                <th scope="col"
                                    class="p-4 text-xs font-medium text-left text-gray-500 uppercase dark:text-gray-400">
                                    Actions
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200 dark:bg-gray-800 dark:divide-gray-700">
                            @if ($is_search)
                                {{-- Display flat list for search results --}}
                                @forelse ($categories as $category)
                                    @include('content.categories._category-row', [
                                        'category' => $category,
                                        'level' => 0,
                                    ])
                                @empty
                                    <tr class="hover:bg-gray-100 dark:hover:bg-gray-700">
                                        <td colspan="5" class="p-4 text-center text-gray-500 dark:text-gray-400">
                                            No categories found.
                                        </td>
                                    </tr>
                                @endforelse
                            @else
                                @forelse ($categories as $category)
                                    @include('content.categories._category-row', [
                                        'category' => $category,
                                        'level' => 0,
                                    ])
                                @empty
                                    <tr class="hover:bg-gray-100 dark:hover:bg-gray-700">
                                        <td colspan="5" class="p-4 text-center text-gray-500 dark:text-gray-400">
                                            No categories found.
                                        </td>
                                    </tr>
                                @endforelse
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>


    @if ($is_search)
        <div
            class="sticky bottom-0 right-0 items-center w-full p-4 bg-white border-t border-gray-200 sm:flex sm:justify-between dark:bg-gray-800 dark:border-gray-700">
            {{ $categories->appends(request()->query())->links('vendor.pagination.custom') }}
        </div>
    @endif
    @push('scripts')
        {{-- ==================== SCRIPT MỚI: XỬ LÝ COLLAPSE TREE ==================== --}}
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const tableBody = document.querySelector('tbody');

                // Hàm để ẩn/hiện tất cả các con cháu của một category
                function toggleSubRows(categoryId, shouldBeVisible) {
                    const children = tableBody.querySelectorAll(`.child-of-${categoryId}`);

                    children.forEach(child => {
                        if (shouldBeVisible) {
                            child.classList.remove('hidden');
                        } else {
                            child.classList.add('hidden');

                            // Nếu hàng con này cũng là một hàng cha, đóng nó lại
                            const childToggleButton = child.querySelector(`.toggle-children[data-category-id]`);
                            if (childToggleButton && childToggleButton.classList.contains('rotate-90')) {
                                childToggleButton.classList.remove('rotate-90');
                                // Gọi đệ quy để đóng các cấp sâu hơn
                                const subCategoryId = childToggleButton.getAttribute('data-category-id');
                                toggleSubRows(subCategoryId, false);
                            }
                        }
                    });
                }

                tableBody.addEventListener('click', function(event) {
                    const toggleButton = event.target.closest('.toggle-children');

                    if (toggleButton) {
                        const categoryId = toggleButton.getAttribute('data-category-id');
                        const isExpanded = toggleButton.classList.toggle('rotate-90');

                        toggleSubRows(categoryId, isExpanded);
                    }
                });
            });
        </script>
        {{-- ==================== KẾT THÚC SCRIPT MỚI ==================== --}}
    @endpush

@endsection
