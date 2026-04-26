{{-- index page for users --}}
<!doctype html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link rel="manifest" href="{{ asset('manifest.json') }}">
        <!-- Theme color (adaptive for light/dark mode) -->
        <meta name="theme-color" media="(prefers-color-scheme: light)" content="#1f65fc">
        <meta name="theme-color" media="(prefers-color-scheme: dark)" content="#000000">
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
        <!-- Splash screen (initial) -->
        <div id="splash" class="fixed inset-0 flex items-center justify-center z-50" style="background:linear-gradient(135deg,#1f65fc,#2563eb);">
            <div class="text-center text-white">
                <!-- simple logo -->
                <div class="mx-auto mb-4" style="width:88px;height:88px;border-radius:18px;background:rgba(255,255,255,0.12);display:flex;align-items:center;justify-content:center;">
                    {{-- Use site logo if available in public path: public/logo.png --}}
                    <img src="{{ asset('logo.png') }}" alt="{{ config('app.name', 'MLM App') }}" style="width:48px;height:48px;border-radius:12px;object-fit:contain;" />
                </div>
                <div class="text-xl font-semibold">{{ config('app.name', 'MLM App') }}</div>
                <div class="mt-2 text-sm opacity-90">Loading...</div>
                <div class="mt-2 text-sm opacity-90">Developed by Mabia.in</div>
            </div>
        </div>

        <style>
            /* splash fade animation */
            #splash { transition: opacity 450ms ease, visibility 450ms; }
            #splash.hidden { opacity: 0; visibility: hidden; pointer-events: none; }
        </style>
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
            // record when splash was shown so we can ensure a minimum visible time
            const splashShownAt = Date.now();

            if ('serviceWorker' in navigator) {
                navigator.serviceWorker.register("{{ asset('service-worker.js') }}")
                .then(() => console.log("PWA Ready"))
                .catch(err => console.log(err));
            }

            // ensure splash stays visible for at least 3 seconds
            window.addEventListener('load', function() {
                const splash = document.getElementById('splash');
                if (!splash) return;
                const elapsed = Date.now() - splashShownAt;
                const minVisible = 3000; // ms
                const delay = Math.max(0, minVisible - elapsed);
                // hide after remaining time to reach minimum visibility
                setTimeout(() => splash.classList.add('hidden'), delay);
                // force hide after 5s in case something hangs
                setTimeout(() => { if (splash && !splash.classList.contains('hidden')) splash.classList.add('hidden'); }, 5000);
            });

            // Handle Logout Cache Clear
            document.addEventListener('submit', function(e) {
                if (e.target && e.target.action && e.target.action.includes('/logout')) {
                    if ('serviceWorker' in navigator && navigator.serviceWorker.controller) {
                        navigator.serviceWorker.controller.postMessage({ type: 'LOGOUT' });
                    }
                }
            });
        </script>
        <script src="https://translate.google.com/translate_a/element.js?cb=googleTranslateElementInit"></script>

        @stack('scripts')
    </body>
</html>
