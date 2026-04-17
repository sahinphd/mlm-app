<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ config('app.name', 'MLM App') }}</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body 
        x-data="{ 'darkMode': false }"
        x-init="
             darkMode = JSON.parse(localStorage.getItem('darkMode'));
             $watch('darkMode', value => localStorage.setItem('darkMode', JSON.stringify(value)))"
        :class="{'dark bg-gray-900': darkMode === true}"
        class="font-outfit bg-gray-50 text-gray-900"
    >
        <div class="relative min-h-screen flex flex-col items-center justify-center selection:bg-brand-500 selection:text-white">
            <div class="relative w-full max-w-2xl px-6 lg:max-w-7xl">
                <header class="grid grid-cols-2 items-center gap-2 py-10 lg:grid-cols-3">
                    <div class="flex lg:justify-center lg:col-start-2">
                        <img src="{{ asset('images/logo/logo.svg') }}" class="dark:hidden h-12" alt="Logo" />
                        <img src="{{ asset('images/logo/logo-dark.svg') }}" class="hidden dark:block h-12" alt="Logo" />
                    </div>
                    @if (Route::has('login'))
                        <nav class="-mx-3 flex flex-1 justify-end lg:col-start-3">
                            @auth
                                <a href="{{ url('/dashboard') }}" class="rounded-md px-3 py-2 text-black ring-1 ring-transparent transition hover:text-black/70 focus:outline-hidden dark:text-white dark:hover:text-white/80">
                                    Dashboard
                                </a>
                            @else
                                <a href="{{ route('login') }}" class="rounded-md px-3 py-2 text-black ring-1 ring-transparent transition hover:text-black/70 focus:outline-hidden dark:text-white dark:hover:text-white/80">
                                    Log in
                                </a>
                                @if (Route::has('register'))
                                    <a href="{{ route('register') }}" class="rounded-md px-3 py-2 text-black ring-1 ring-transparent transition hover:text-black/70 focus:outline-hidden dark:text-white dark:hover:text-white/80">
                                        Register
                                    </a>
                                @endif
                            @endauth
                        </nav>
                    @endif
                </header>

                <main class="mt-6">
                    <div class="grid gap-6 lg:grid-cols-2 lg:gap-8">
                        <div class="flex flex-col items-start gap-6 overflow-hidden rounded-2xl bg-white p-6 shadow-theme-sm ring-1 ring-gray-200 dark:bg-white/[0.03] dark:ring-gray-800 lg:p-10">
                            <div class="flex size-12 shrink-0 items-center justify-center rounded-full bg-brand-50 dark:bg-brand-500/10">
                                <svg class="size-6 fill-brand-500" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"></path>
                                </svg>
                            </div>
                            <div class="pt-3 sm:pt-5">
                                <h2 class="text-xl font-semibold text-gray-900 dark:text-white">Welcome to {{ config('app.name', 'MLM App') }}</h2>
                                <p class="mt-4 text-sm/relaxed text-gray-500 dark:text-gray-400">
                                    A uniform, responsive, and powerful dashboard built with TailAdmin and Laravel. Manage your network, track payments, and grow your business with ease.
                                </p>
                            </div>
                        </div>

                        <div class="flex flex-col items-start gap-6 overflow-hidden rounded-2xl bg-white p-6 shadow-theme-sm ring-1 ring-gray-200 dark:bg-white/[0.03] dark:ring-gray-800 lg:p-10">
                             <div class="flex size-12 shrink-0 items-center justify-center rounded-full bg-success-50 dark:bg-success-500/10">
                                <svg class="size-6 fill-success-500" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M12 22C6.477 22 2 17.523 2 12S6.477 2 12 2s10 4.477 10 10-4.477 10-10 10zm0-2a8 8 0 100-16 8 8 0 000 16zm-1-5h2v2h-2v-2zm0-8h2v6h-2V7z"></path>
                                </svg>
                            </div>
                            <div class="pt-3 sm:pt-5">
                                <h2 class="text-xl font-semibold text-gray-900 dark:text-white">Get Started</h2>
                                <p class="mt-4 text-sm/relaxed text-gray-500 dark:text-gray-400">
                                    Join our community today. Create an account, complete your profile, and start your journey towards financial freedom.
                                </p>
                                <a href="{{ route('register') }}" class="mt-6 inline-flex items-center justify-center rounded-lg bg-brand-500 px-5 py-3 text-sm font-medium text-white hover:bg-brand-600 transition">
                                    Create Account
                                </a>
                            </div>
                        </div>
                    </div>
                </main>

                <footer class="py-16 text-center text-sm text-gray-500 dark:text-gray-400">
                    &copy; {{ date('Y') }} {{ config('app.name', 'MLM App') }}. All rights reserved.
                </footer>
            </div>
        </div>
        
        <!-- Dark Mode Toggler (Floating) -->
        <div class="fixed bottom-6 right-6 z-50">
            <button
                class="flex h-12 w-12 items-center justify-center rounded-full bg-brand-500 text-white shadow-theme-lg hover:bg-brand-600 transition"
                @click.prevent="darkMode = !darkMode"
            >
                <svg x-show="!darkMode" class="size-6 fill-current" viewBox="0 0 20 20"><path d="M10 15a5 5 0 100-10 5 5 0 000 10zM10 1.5V3M10 17v1.5M1.5 10H3M17 10h1.5M4.343 4.343l1.061 1.061M14.596 14.596l1.061 1.061M4.343 15.657l1.061-1.061M14.596 5.404l1.061-1.061" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"></path></svg>
                <svg x-show="darkMode" class="size-6 fill-current" viewBox="0 0 20 20"><path d="M17.293 13.293A8 8 0 016.707 2.707a8.001 8.001 0 1010.586 10.586z"></path></svg>
            </button>
        </div>
    </body>
</html>
