<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AIVoice Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link rel="stylesheet" href="https://cdn.hugeicons.com/font/hgi-stroke-rounded.css" />


</head>

<body class="bg-gray-50 dark:bg-gray-900">

    <div class="flex h-screen">

        @include('partials.sidebar-user')


        <div class="flex-1 flex flex-col overflow-hidden">
            <header class="flex justify-between items-center p-4 ">
                <div></div>
                <div class="flex items-center space-x-4">
                    <button class="relative">
                        <i class="fas fa-gift text-yellow-500 text-xl"></i>
                        <span
                            class="absolute -top-2 -right-2 bg-red-500 text-white text-xs rounded-full px-1.5 py-0.5">500K+</span>
                    </button>
                    <span class="text-sm font-semibold">3.000 ký tự</span>
                    <button>
                        <i class="fas fa-shopping-cart text-gray-600 text-xl"></i>
                    </button>
                </div>
            </header>

            <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-50 p-6">

                @yield('content')
            </main>
        </div>
    </div>

</body>

</html>
