@extends('admin.dashboard')

@section('title', 'Category Types')

@section('content')

    <x-session-alerts />
    <div
        class="p-4 bg-white block sm:flex items-center justify-between border-b border-gray-200 lg:mt-1.5 dark:bg-gray-800 dark:border-gray-700">
        <div class="w-full mb-1">
            <div class="mb-4">
                <x-breadcrumb />
                <h1 class="text-xl font-semibold text-gray-900 sm:text-2xl dark:text-white">@yield('title')</h1>
                <p class="text-gray-500 dark:text-gray-400 mt-1">Thiết lập các file SEO tĩnh để giúp các công cụ tìm kiếm
                    nhận dạng website nhanh chóng.</p>
            </div>
            <div class="flex justify-between items-center mb-6">

                <a href="{{ route('category-types.create') }}"
                    class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                    + Add New
                </a>
            </div>

        </div>
    </div>






    <div class="overflow-x-auto bg-white ">
        <table class="min-w-full border border-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="py-3 px-4 text-left text-sm font-medium text-gray-600">#</th>
                    <th class="py-3 px-4 text-left text-sm font-medium text-gray-600">Name</th>
                    <th class="py-3 px-4 text-left text-sm font-medium text-gray-600">Slug</th>
                    <th class="py-3 px-4 text-center text-sm font-medium text-gray-600">Active</th>
                    <th class="py-3 px-4 text-right text-sm font-medium text-gray-600">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($categoryTypes as $index => $type)
                    <tr class="border-t">
                        <td class="py-3 px-4 text-sm text-gray-700">{{ $index + 1 }}</td>
                        <td class="py-3 px-4 text-sm text-gray-800">{{ $type->name }}</td>
                        <td class="py-3 px-4 text-sm text-gray-800">{{ $type->slug }}</td>
                        <td class="py-3 px-4 text-center">
                            @if ($type->is_active)
                                <span class="px-2 py-1 text-xs bg-green-100 text-green-700 rounded-full">Active</span>
                            @else
                                <span class="px-2 py-1 text-xs bg-gray-200 text-gray-600 rounded-full">Inactive</span>
                            @endif
                        </td>
                        <td class="py-3 px-4 text-right">
                            <a href="{{ route('category-types.edit', $type->id) }}"
                                class="text-blue-600 hover:underline mr-3">Edit</a>
                            <form action="{{ route('category-types.destroy', $type->id) }}" method="POST" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" onclick="return confirm('Are you sure?')"
                                    class="text-red-600 hover:underline">Delete</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="py-4 text-center text-gray-500">No Category Types found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    </div>
    </div>
@endsection
