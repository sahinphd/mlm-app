<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta
      name="viewport"
      content="width=device-width, initial-scale=1.0"
    />
    <meta http-equiv="X-UA-Compatible" content="ie=edge" />
    <title>{{ config('app.name', 'MLM App') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
  </head>
  <body
    x-data="{ 'loaded': true, 'darkMode': false }"
    x-init="
         darkMode = JSON.parse(localStorage.getItem('darkMode'));
         $watch('darkMode', value => localStorage.setItem('darkMode', JSON.stringify(value)))"
    :class="{'dark bg-gray-900': darkMode === true}"
  >
    <div class="relative p-6 bg-white z-1 dark:bg-gray-900 sm:p-0">
        <div class="relative flex flex-col justify-center w-full min-h-screen dark:bg-gray-900 sm:p-0 lg:flex-row">
            @yield('content')
            
            <div class="relative items-center hidden w-full h-full bg-brand-950 dark:bg-white/5 lg:grid lg:w-1/2">
                <div class="flex items-center justify-center z-1">
                    <div class="flex flex-col items-center max-w-xs">
                        <a href="{{ url('/') }}" class="block mb-4">
                            <img src="{{ asset('images/logo/auth-logo.svg') }}" alt="Logo" />
                        </a>
                        <p class="text-center text-gray-400 dark:text-white/60">
                            {{ config('app.name', 'MLM') }} - Your Uniform Responsive Dashboard
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
  </body>
</html>
