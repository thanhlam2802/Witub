<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Admin Dashboard')</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/flowbite@2.4.1/dist/flowbite.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/tom-select/dist/css/tom-select.bootstrap5.min.css" rel="stylesheet">

    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>
<style>
    .ts-dropdown {
        z-index: 9999 !important;
    }
</style>

<body class="bg-gray-50 dark:bg-gray-800">

    </head>

    <body class="bg-gray-50 dark:bg-gray-900">
        @include('partials.navbar-dashboard')
        <div class="flex pt-16 overflow-hidden bg-gray-50 dark:bg-gray-900">

            @include('partials.sidebar')

            <div id="main-content" class="relative w-full h-full overflow-y-auto bg-gray-50 lg:ml-64 dark:bg-gray-900">


                <main>

                    @yield('content')
                </main>


            </div>
        </div>
        @stack('scripts')
    </body>


</html>
