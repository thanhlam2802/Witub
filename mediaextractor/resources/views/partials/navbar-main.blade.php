<nav class="fixed z-50 w-full bg-white border-b border-gray-200 sm:py-2 dark:bg-gray-800 dark:border-gray-700">
    <div class="container py-3 mx-auto">
        <div class="flex items-center justify-between">
            <div class="flex items-center justify-start">
                {{-- Logo --}}
                <a href="{{ localized_route('home') }}" class="flex mr-4">
                    <img src="/images/logo.png" class="h-8 mr-3" alt="Thewinhouse Logo" />
                    <span class="self-center text-2xl font-semibold whitespace-nowrap dark:text-white">Thewinhouse</span>
                </a>

                {{-- Menu desktop --}}
                <div class="hidden sm:flex sm:ml-6">
                    <ul class="flex space-x-8">
                        <li>
                            <a href="{{ localized_route('home') }}"
                                class="text-sm font-medium text-gray-700 hover:text-primary-700 dark:text-gray-400 dark:hover:text-primary-500">
                                {{ __('navbar.Home') }}
                            </a>
                        </li>
                        <li>
                            <a href=""
                                class="text-sm font-medium text-gray-700 hover:text-primary-700 dark:text-gray-400 dark:hover:text-primary-500">
                                {{ __('navbar.Team') }}
                            </a>
                        </li>
                        <li>
                            <a href=""
                                class="text-sm font-medium text-gray-700 hover:text-primary-700 dark:text-gray-400 dark:hover:text-primary-500">
                                {{ __('navbar.Pricing') }}
                            </a>
                        </li>
                        <li>
                            <a href=""
                                class="text-sm font-medium text-gray-700 hover:text-primary-700 dark:text-gray-400 dark:hover:text-primary-500">
                                {{ __('navbar.Contact') }}
                            </a>
                        </li>
                    </ul>
                </div>
            </div>

            <div class="flex items-center">
                {{-- Language dropdown --}}
                <div class="hidden sm:flex items-center">
                    @php
                        $currentLocale = app()->getLocale();
                    @endphp

                    <button id="language-dropdown-button" data-dropdown-toggle="language-dropdown"
                        class="flex items-center justify-center px-4 py-2 text-sm font-medium text-gray-900 rounded-lg cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-700 dark:text-white dark:hover:text-white mr-2">
                        <svg class="w-5 h-5 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <circle cx="12" cy="12" r="10" />
                        </svg>
                        {{ strtoupper($currentLocale) }}
                    </button>


                    <div id="language-dropdown"
                        class="z-50 hidden my-4 text-base list-none bg-white divide-y divide-gray-100 rounded-lg shadow dark:bg-gray-700">
                        <ul class="py-2 font-medium" role="none">
                            <li>
                                <a href="{{ route('language.switch', ['locale' => 'vi']) }}"
                                    class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 dark:text-gray-400 dark:hover:bg-gray-600 dark:hover:text-white"
                                    role="menuitem">
                                    Tiếng Việt (VI)
                                </a>


                            </li>
                            <li>
                                <a href="{{ route('language.switch', ['locale' => 'en']) }}"
                                    class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 dark:text-gray-400 dark:hover:bg-gray-600 dark:hover:text-white"
                                    role="menuitem">
                                    English (EN)
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>

                {{-- Nút Login/Register --}}
                <div class="flex items-center">
                    @guest

                        <a href="{{ route('auth.login.view') }}"
                            class="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center inline-flex items-center dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800">
                            <svg class="w-5 h-5 mr-2 -ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1">
                                </path>
                            </svg>
                            {{ __('Login/Register') }}
                        </a>
                    @endguest


                    {{-- Toggle mobile --}}
                    <button data-collapse-toggle="mobile-menu" type="button"
                        class="inline-flex items-center justify-center p-2 ml-3 text-gray-400 rounded-lg sm:hidden hover:text-gray-900 focus:outline-none focus:ring-2 focus:ring-primary-300 dark:hover:bg-gray-700 dark:hover:text-white"
                        aria-controls="mobile-menu-2" aria-expanded="false">
                        <span class="sr-only">Open main menu</span>
                        <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                            <path fill-rule="evenodd"
                                d="M3 5a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zM3 10a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zM3 15a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1z"
                                clip-rule="evenodd"></path>
                        </svg>
                    </button>
                </div>
            </div>
        </div>

        {{-- Mobile Menu --}}
        <div class="hidden sm:hidden" id="mobile-menu">
            <ul class="pt-2">
                <li><a href="{{ localized_route('home') }}"
                        class="block py-2 pl-3 pr-4 text-base font-normal text-gray-900 bg-gray-100 dark:bg-gray-700 dark:text-white">{{ __('Home') }}</a>
                </li>
                <li><a href=""
                        class="block px-3 py-2 text-base font-normal text-gray-600 hover:bg-gray-50 hover:text-gray-900 dark:hover:bg-gray-700 dark:text-gray-400 dark:hover:text-white">{{ __('Team') }}</a>
                </li>
                <li><a href=""
                        class="block px-3 py-2 text-base font-normal text-gray-600 hover:bg-gray-50 hover:text-gray-900 dark:hover:bg-gray-700 dark:text-gray-400 dark:hover:text-white">{{ __('Pricing') }}</a>
                </li>
                <li><a href=""
                        class="block px-3 py-2 text-base font-normal text-gray-600 hover:bg-gray-50 hover:text-gray-900 dark:hover:bg-gray-700 dark:text-gray-400 dark:hover:text-white">{{ __('Contact') }}</a>
                </li>

                <li class="border-t border-gray-200 dark:border-gray-700 mt-2">
                    <span class="block px-3 py-2 text-sm font-semibold text-gray-500">Language</span>
                    <a href="{{ route('language.switch', ['locale' => 'vi']) }}"
                        class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 dark:text-gray-400 dark:hover:bg-gray-600 dark:hover:text-white"
                        role="menuitem">
                        Tiếng Việt (VI)
                    </a>
                    <a href="{{ route('language.switch', ['locale' => 'en']) }}"
                        class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 dark:text-gray-400 dark:hover:bg-gray-600 dark:hover:text-white"
                        role="menuitem">
                        English (EN)
                    </a>

                </li>
            </ul>
        </div>
</nav>
