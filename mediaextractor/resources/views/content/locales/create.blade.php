@extends('admin.dashboard')
@section('title', 'Edit locales')

@section('content')
    <div class="p-4 bg-white block border-b dark:bg-gray-800 dark:border-gray-700">
        <div class="mb-4">
            <x-breadcrumb />
            <h1 class="text-2xl font-bold mb-6">Thêm Ngôn ngữ mới</h1>
        </div>
    </div>
    <div class="bg-white p-6 rounded-lg shadow-md">
        <form action="{{ route('locales.store') }}" method="POST">
            @include('content.locales._form')
        </form>
    </div>

@endsection
