{{-- index page for users --}}
<!doctype html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link rel="manifest" href="{{ asset('manifest.json') }}">
        <meta name="theme-color" content="#198754">
        <title>{{ config('app.name', 'MLM App') }}</title>
        @if (app()->environment('local'))
            @vite(['resources/css/app.css', 'resources/js/app.js'])
        @else
            @vite(['resources/css/app.css', 'resources/js/app.js'])
        @endif

        <style>
            /* ===== Google Translate Fix ===== */
            .goog-te-banner-frame.skiptranslate,
            body > .skiptranslate {
                display: none !important;
            }
            html { margin-top: 0 !important; }
            body { top: 0 !important; }
            .goog-logo-link { display: none !important; }
            .goog-te-gadget { font-size: 0 !important; color: transparent !important; }
            .goog-te-gadget span { display: none !important; }
            .goog-te-gadget .goog-te-combo {
                margin: 0 !important;
                padding: 2px 4px !important;
                border-radius: 4px !important;
                border: 1px solid #d1d5db !important;
                background-color: #fff !important;
                color: #111827 !important;
                font-size: 11px !important;
                outline: none !important;
                cursor: pointer !important;
            }
        </style>
    </head>
    <body class="bg-gray-100 min-h-screen">
        <nav class="bg-white shadow p-4">
            <div class="max-w-4xl mx-auto flex justify-between items-center">
                <a href="/" class="font-semibold">{{ config('app.name', 'MLM') }}</a>
                <div class="space-x-3">
                    @auth
                        <a href="/shop" class="text-sm">Shop</a>
                        <a href="/orders" class="text-sm">My Orders</a>
                        <a href="/payments" class="text-sm">My Payments</a>
                        <a href="/commissions" class="text-sm">My Commissions</a>
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

        <!-- ===== Google Translate Widget ===== -->
        <div id="google_translate_wrapper" class="bg-white shadow-lg border z-[9999] fixed bottom-5 right-5 rounded-lg p-1">
            <div id="google_translate_element"></div>
        </div>

        <script>
            function googleTranslateElementInit() {
                new google.translate.TranslateElement({
                    pageLanguage: 'en',
                    includedLanguages: 'en,bn,hi',
                    autoDisplay: false
                }, 'google_translate_element');
            }
        </script>
        <script>
            if ('serviceWorker' in navigator) {
                navigator.serviceWorker.register("{{ asset('service-worker.js') }}")
                .then(() => console.log("PWA Ready"))
                .catch(err => console.log(err));
            }
        </script>
        <script src="https://translate.google.com/translate_a/element.js?cb=googleTranslateElementInit"></script>

        @stack('scripts')
    </body>
</html>
