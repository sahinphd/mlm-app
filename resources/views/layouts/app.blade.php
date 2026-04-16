<!doctype html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ config('app.name', 'MLM App') }}</title>
        @if (app()->environment('local'))
            @vite(['resources/css/app.css', 'resources/js/app.js'])
        @else
            @vite(['resources/css/app.css', 'resources/js/app.js'])
        @endif
    </head>
    <body class="bg-gray-100 min-h-screen">
        <nav class="bg-white shadow p-4">
            <div class="max-w-4xl mx-auto flex justify-between items-center">
                <a href="/" class="font-semibold">{{ config('app.name', 'MLM') }}</a>
                <div class="space-x-3">
                    @auth
                        <a href="/payments" class="text-sm">My Payments</a>
                        @if(auth()->user()->isAdmin())
                            <a href="/admin/payments" class="text-sm">Admin</a>
                        @endif
                        <form method="POST" action="/logout" class="inline">@csrf<button class="text-sm">Logout</button></form>
                    @else
                        <a href="/login" class="text-sm">Login</a>
                        <a href="/register" class="text-sm">Register</a>
                    @endauth
                </div>
            </div>
        </nav>

        <main class="max-w-4xl mx-auto p-4">
            @yield('content')
        </main>
        @stack('scripts')
    </body>
</html>
