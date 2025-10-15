@extends('admin.dashboard')
@section('title', 'Edit Post')

@section('content')
    <div class="p-4 bg-white block border-b dark:bg-gray-800 dark:border-gray-700">
        <div class="mb-4">
            <x-breadcrumb />
            <h1 class="text-xl font-semibold sm:text-2xl text-gray-900 dark:text-white">Edit Post</h1>
        </div>
    </div>

    @include('content.posts._form')
@endsection
