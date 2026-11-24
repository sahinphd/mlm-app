{{-- index page for users --}}
<!doctype html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate" />
        <meta http-equiv="Pragma" content="no-cache" />
        <meta http-equiv="Expires" content="0" />
        <link rel="manifest" href="{{ asset('manifest.json') }}">
        <!-- Theme color (adaptive for light/dark mode) -->
        <meta name="theme-color" media="(prefers-color-scheme: light)" content="#1f65fc">
        <meta name="theme-color" media="(prefers-color-scheme: dark)" content="#000000">
        @hasSection('title')
            <title>@yield('title')</title>
        @else
            <title>{{ config('app.name', 'Dua Re Dokandar') }}</title>
        @endif
        @yield('meta_tags')
        
        <!-- Font Awesome -->
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />

        @if (app()->environment('local'))
            @vite(['resources/css/app.css', 'resources/js/app.js'])
        @else
            @vite(['resources/css/app.css', 'resources/js/app.js'])
        @endif

        <style>
            [x-cloak] { display: none !important; }
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
        @stack('styles')
    </head>
    <body class="bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-gray-100 min-h-screen flex flex-col font-outfit antialiased">
        <!-- Splash screen (initial) -->
        <div id="splash" class="fixed inset-0 flex items-center justify-center z-50" style="background:linear-gradient(135deg,#1f65fc,#2563eb);">
            <div class="text-center text-white">
                <div class="mx-auto mb-4" style="width:88px;height:88px;border-radius:18px;background:rgba(255,255,255,0.12);display:flex;align-items:center;justify-content:center;">
                    <img src="{{ asset('logo.png') }}" alt="{{ config('app.name', 'Dua Re Dokandar') }}" style="width:48px;height:48px;border-radius:12px;object-fit:contain;" />
                </div>
                <div class="text-xl font-semibold">{{ config('app.name', 'Dua Re Dokandar') }}</div>
                <div class="mt-2 text-sm opacity-90">Loading...</div>
                <div class="mt-2 text-sm opacity-90">Developed by Mabia.in</div>
            </div>
        </div>

        <style>
            #splash { transition: opacity 450ms ease, visibility 450ms; }
            #splash.hidden { opacity: 0; visibility: hidden; pointer-events: none; }
        </style>
        
        @include('admin.partials.impersonation_banner')

        <!-- Public Navigation Bar -->
        <header class="sticky top-0 z-40 w-full bg-white/95 dark:bg-gray-900/95 backdrop-blur border-b border-gray-200 dark:border-gray-800 shadow-xs">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex items-center justify-between h-16" x-data="{ mobileMenuOpen: false }">
                <!-- Brand Logo & Title -->
                <a href="/" class="flex items-center gap-2.5 font-bold text-lg md:text-xl text-gray-900 dark:text-white tracking-tight group">
                    <div class="w-9 h-9 rounded-xl bg-blue-600 flex items-center justify-center text-white shadow-xs group-hover:scale-105 transition-transform">
                        <i class="fas fa-store text-base"></i>
                    </div>
                    <span>Dua Re <span class="text-blue-600">Dokandar</span></span>
                </a>

                <!-- Desktop Nav Links -->
                <nav class="hidden md:flex items-center gap-6 text-sm font-semibold">
                    <a href="{{ url('/') }}" class="text-gray-700 dark:text-gray-200 hover:text-blue-600 dark:hover:text-blue-400 transition {{ request()->is('/') ? 'text-blue-600 dark:text-blue-400 font-bold' : '' }}">
                        <i class="fas fa-home mr-1 text-xs opacity-70"></i> Home
                    </a>
                    <a href="{{ route('shop.index') }}" class="text-gray-700 dark:text-gray-200 hover:text-blue-600 dark:hover:text-blue-400 transition flex items-center gap-1.5 {{ request()->is('shop*') ? 'text-blue-600 dark:text-blue-400 font-bold' : '' }}">
                        <i class="fas fa-shopping-bag text-xs text-blue-600"></i> Shop Catalog
                    </a>
                    <a href="{{ route('blog.index') }}" class="text-gray-700 dark:text-gray-200 hover:text-blue-600 dark:hover:text-blue-400 transition flex items-center gap-1.5 {{ request()->is('blog*') ? 'text-blue-600 dark:text-blue-400 font-bold' : '' }}">
                        <i class="fas fa-newspaper text-xs text-blue-600"></i> Blog
                    </a>
                    <a href="{{ url('/#about') }}" class="text-gray-700 dark:text-gray-200 hover:text-blue-600 dark:hover:text-blue-400 transition">
                        About
                    </a>
                    <a href="{{ url('/#contact') }}" class="text-gray-700 dark:text-gray-200 hover:text-blue-600 dark:hover:text-blue-400 transition">
                        Contact
                    </a>
                </nav>

                <!-- Actions: Cart & Auth Buttons -->
                <div class="flex items-center gap-2.5 sm:gap-3">
                    <!-- Nav Cart Button -->
                    <button type="button" 
                            onclick="window.dispatchEvent(new CustomEvent('open-cart'))" 
                            class="relative inline-flex items-center gap-1.5 px-3 py-2 rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 text-gray-700 dark:text-gray-200 hover:text-blue-600 dark:hover:text-blue-400 hover:border-blue-300 transition shadow-2xs" 
                            title="Open Shopping Cart">
                        <i class="fas fa-shopping-cart text-blue-600"></i>
                        <span class="text-xs font-bold hidden sm:inline">Cart</span>
                        <span id="navCartCount" class="inline-flex items-center justify-center min-w-[20px] h-5 px-1.5 text-[11px] font-bold text-white bg-blue-600 rounded-full">0</span>
                    </button>

                    @auth
                        <a href="{{ auth()->user()->isAdmin() ? url('/admin') : url('/dashboard') }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-blue-50 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 text-xs sm:text-sm font-semibold hover:bg-blue-100 transition">
                            <i class="fas fa-th-large text-xs"></i>
                            <span class="hidden sm:inline">Dashboard</span>
                        </a>
                        <a href="{{ route('orders.index') }}" class="hidden sm:inline-flex items-center gap-1.5 px-3 py-2 rounded-xl text-gray-700 dark:text-gray-200 text-xs sm:text-sm font-semibold hover:text-blue-600 transition">
                            <i class="fas fa-box text-xs"></i> Orders
                        </a>
                        <form method="POST" action="{{ route('logout') }}" class="inline">
                            @csrf
                            <button type="submit" class="px-2.5 py-2 text-xs text-gray-500 hover:text-red-600 transition" title="Log Out">
                                <i class="fas fa-sign-out-alt"></i>
                            </button>
                        </form>
                    @else
                        <a href="{{ route('login') }}" class="text-xs sm:text-sm font-semibold text-gray-700 dark:text-gray-200 hover:text-blue-600 px-2 py-1">
                            Log In
                        </a>
                        <a href="{{ route('register') }}" class="inline-flex items-center px-3.5 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-xs sm:text-sm font-semibold shadow-xs hover:shadow transition">
                            Register
                        </a>
                    @endauth

                    <!-- Mobile Menu Toggle Button -->
                    <button type="button" @click="mobileMenuOpen = !mobileMenuOpen" class="md:hidden p-2 rounded-xl text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 transition">
                        <i class="fas fa-bars text-lg"></i>
                    </button>
                </div>

                <!-- Mobile Menu Drawer Dropdown -->
                <div x-show="mobileMenuOpen" 
                     x-cloak 
                     @click.outside="mobileMenuOpen = false" 
                     x-transition 
                     class="absolute top-16 left-0 right-0 bg-white dark:bg-gray-900 border-b border-gray-200 dark:border-gray-800 p-4 shadow-lg md:hidden flex flex-col gap-3">
                    <a href="{{ url('/') }}" class="py-2 text-sm font-semibold text-gray-700 dark:text-gray-200 hover:text-blue-600">
                        <i class="fas fa-home w-5 text-blue-600"></i> Home
                    </a>
                    <a href="{{ route('shop.index') }}" class="py-2 text-sm font-semibold text-blue-600 flex items-center justify-between">
                        <span><i class="fas fa-shopping-bag w-5"></i> Shop Catalog</span>
                        <span class="text-xs bg-blue-100 text-blue-700 px-2 py-0.5 rounded-full font-bold">Buy Now</span>
                    </a>
                    <a href="{{ route('blog.index') }}" class="py-2 text-sm font-semibold text-gray-700 dark:text-gray-200 hover:text-blue-600">
                        <i class="fas fa-newspaper w-5 text-blue-600"></i> Blog Articles
                    </a>
                    <a href="{{ url('/#about') }}" class="py-2 text-sm font-semibold text-gray-700 dark:text-gray-200 hover:text-blue-600">
                        <i class="fas fa-info-circle w-5 text-gray-400"></i> About Us
                    </a>
                    <a href="{{ url('/#contact') }}" class="py-2 text-sm font-semibold text-gray-700 dark:text-gray-200 hover:text-blue-600">
                        <i class="fas fa-envelope w-5 text-gray-400"></i> Contact Us
                    </a>
                    @auth
                        <div class="border-t border-gray-100 dark:border-gray-800 pt-2 flex flex-col gap-2">
                            <a href="{{ auth()->user()->isAdmin() ? url('/admin') : url('/dashboard') }}" class="py-2 text-sm font-semibold text-blue-600">
                                <i class="fas fa-th-large w-5"></i> Open Dashboard
                            </a>
                            <a href="{{ route('orders.index') }}" class="py-2 text-sm font-semibold text-gray-700 dark:text-gray-200">
                                <i class="fas fa-box w-5"></i> My Orders
                            </a>
                        </div>
                    @endauth
                </div>
            </div>
        </header>

        <!-- Main Content Area -->
        <main class="w-full flex-grow">
            @yield('content')
        </main>

        <!-- Floating Language Drawer -->
        @include('partials.language_drawer')
        
        <script>
            // Global Cart Counter Synchronization
            function syncGlobalCartBadge() {
                try {
                    const raw = localStorage.getItem('dua_cart');
                    const cart = raw ? JSON.parse(raw) : [];
                    const totalCount = Array.isArray(cart) ? cart.reduce((sum, item) => sum + (parseInt(item.quantity) || 1), 0) : 0;
                    const badge = document.getElementById('navCartCount');
                    if (badge) {
                        badge.textContent = totalCount;
                        badge.classList.toggle('bg-blue-600', totalCount > 0);
                        badge.classList.toggle('bg-gray-400', totalCount === 0);
                    }
                } catch(e) {}
            }

            document.addEventListener('DOMContentLoaded', syncGlobalCartBadge);
            window.addEventListener('cart-updated', syncGlobalCartBadge);
            window.addEventListener('storage', syncGlobalCartBadge);

            // Splash Screen Handler
            const splashShownAt = Date.now();
            if ('serviceWorker' in navigator) {
                navigator.serviceWorker.register("{{ asset('service-worker.js') }}")
                .then(() => console.log("PWA Ready"))
                .catch(err => console.log(err));
            }

            window.addEventListener('load', function() {
                const splash = document.getElementById('splash');
                if (!splash) return;
                const elapsed = Date.now() - splashShownAt;
                const minVisible = 1200; // ms
                const delay = Math.max(0, minVisible - elapsed);
                setTimeout(() => splash.classList.add('hidden'), delay);
                setTimeout(() => { if (splash && !splash.classList.contains('hidden')) splash.classList.add('hidden'); }, 3000);
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

        @stack('scripts')
    </body>
</html>
