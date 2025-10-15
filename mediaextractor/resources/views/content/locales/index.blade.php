@extends('admin.dashboard')
@section('title', 'loacales Management')

@section('content')
    <x-session-alerts />


    <div
        class="p-4 bg-white block sm:flex items-center justify-between border-b border-gray-200 lg:mt-1.5 dark:bg-gray-800 dark:border-gray-700">
        <div class="w-full mb-1">
            <div class="mb-4">
                <x-breadcrumb />
                <h1 class="text-xl font-semibold text-gray-900 sm:text-2xl dark:text-white">All Loacales</h1>
            </div>
            <div class="sm:flex">


                {{-- Add Post Button --}}
                <div class="flex items-center ml-auto space-x-2 sm:space-x-3">
                    <a href="{{ route('locales.create') }}"
                        class="inline-flex items-center justify-center px-3 py-2 text-sm font-medium text-center text-white bg-blue-700 rounded-lg hover:bg-blue-800">
                        <svg class="w-5 h-5 mr-2 -ml-1" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                                d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z"
                                clip-rule="evenodd"></path>
                        </svg>
                        Add Loacale
                    </a>
                </div>
            </div>

        </div>



    </div>
    <div class="flex flex-col">
        <div class="w-full overflow-x-auto">
            <div class="inline-block min-w-full align-middle">
                <div class="overflow-hidden shadow ">
                    <table class="min-w-[1000px] w-full divide-y divide-gray-200 dark:divide-gray-700 table-auto">
                        <thead class="bg-gray-100 dark:bg-gray-700">
                            <tr>
                                <th scope="col" class="p-3 w-24 text-xs font-medium text-gray-500 uppercase text-left">
                                    <div class="flex items-center">
                                        <input id="checkbox-all" aria-describedby="checkbox-1" type="checkbox"
                                            class="w-4 h-4 border-gray-300 rounded bg-gray-50 focus:ring-3 focus:ring-primary-300 dark:focus:ring-primary-600 dark:ring-offset-gray-800 dark:bg-gray-700 dark:border-gray-600">
                                        <label for="checkbox-all" class="sr-only">checkbox</label>
                                    </div>
                                </th>
                                <th class="p-3 w-24 text-xs font-medium text-gray-500 uppercase text-left"> Mã Ngôn ngữ
                                </th>
                                <th class="p-3 w-72 text-xs font-medium text-gray-500 uppercase text-left">Tên Ngôn ngữ
                                </th>
                                <th class="p-3 w-32 text-xs font-medium text-gray-500 uppercase text-left">Mặc định</th>
                                <th class="p-3 w-28 text-xs font-medium text-gray-500 uppercase text-left"> Trạng thái

                                <th class="p-3 w-28 text-xs font-medium text-gray-500 uppercase text-left">Actions</th>
                            </tr>
                        </thead>

                        <tbody class="bg-white divide-y divide-gray-200 dark:bg-gray-800 dark:divide-gray-700">
                            @forelse ($locales as $locale)
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                    <td class="p-3 ">
                                        <input type="checkbox"
                                            class="w-4 h-4 border-gray-300 rounded focus:ring-2 focus:ring-primary-300">
                                    </td>


                                    <td class="p-3">

                                        {{ $locale->locale_code }}
                                    </td>

                                    <td class="p-3 text-sm text-gray-900 dark:text-white leading-tight">
                                        {{ $locale->language_name }}
                                    </td>

                                    <td class="p-3">
                                        @if ($locale->is_default)
                                            <span
                                                class="bg-green-100 text-green-800 text-xs font-semibold mr-2 px-2.5 py-0.5 rounded-full">Yes</span>
                                        @endif
                                    </td>


                                    <td class="p-3 text-sm font-medium text-gray-900 dark:text-white">
                                        <span
                                            class="{{ $locale->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }} text-xs font-semibold mr-2 px-2.5 py-0.5 rounded-full">
                                            {{ $locale->is_active ? 'Active' : 'Inactive' }}
                                        </span>
                                    </td>




                                    <!-- Actions -->
                                    <td class="p-3 text-center">
                                        <div class="flex flex-col items-center justify-center gap-2">
                                            <!-- Nút Edit -->
                                            <a href="{{ route('locales.edit', $locale) }}"
                                                class="inline-flex items-center justify-center w-24 px-3 py-1.5 text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 rounded-md focus:ring-2 focus:ring-blue-300">
                                                <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20"
                                                    xmlns="http://www.w3.org/2000/svg">
                                                    <path
                                                        d="M17.414 2.586a2 2 0 00-2.828 0L7 10.172V13h2.828l7.586-7.586a2 2 0 000-2.828z">
                                                    </path>
                                                    <path fill-rule="evenodd"
                                                        d="M2 6a2 2 0 012-2h4a1 1 0 010 2H4v10h10v-4a1 1 0 112 0v4a2 2 0 01-2 2H4a2 2 0 01-2-2V6z"
                                                        clip-rule="evenodd"></path>
                                                </svg>
                                                Edit
                                            </a>

                                            <!-- Nút Delete -->
                                            <form action="{{ route('locales.destroy', $locale) }}" method="POST"
                                                class="inline-block w-24"
                                                onsubmit="return confirm('Are you sure you want to delete this loacale?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit"
                                                    class="inline-flex items-center justify-center w-full px-3 py-1.5 text-sm font-medium text-white bg-red-600 hover:bg-red-700 rounded-md focus:ring-2 focus:ring-red-300">
                                                    <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20"
                                                        xmlns="http://www.w3.org/2000/svg">
                                                        <path fill-rule="evenodd"
                                                            d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z"
                                                            clip-rule="evenodd"></path>
                                                    </svg>
                                                    Delete
                                                </button>
                                            </form>
                                        </div>
                                    </td>

                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="p-4 text-center text-gray-500 dark:text-gray-400">
                                        No posts found.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>
@endsection
