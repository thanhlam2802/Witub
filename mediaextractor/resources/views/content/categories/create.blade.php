@extends('admin.dashboard')
@section('title', 'Add New Category')

@section('content')
    <div class="p-4 bg-white block border-b border-gray-200 lg:mt-1.5 dark:bg-gray-800 dark:border-gray-700">
        <div class="w-full mb-1">
            <div class="mb-4">
                <x-breadcrumb />
                <h1 class="text-xl font-semibold text-gray-900 sm:text-2xl dark:text-white">Add New Category</h1>
            </div>
        </div>
    </div>

    {{-- Include the shared form --}}
    @include('content.categories._form')

@endsection
