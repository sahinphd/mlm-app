<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta http-equiv="X-UA-Compatible" content="ie=edge" />
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate" />
    <meta http-equiv="Pragma" content="no-cache" />
    <meta http-equiv="Expires" content="0" />
    <link rel="manifest" href="{{ asset('manifest.json') }}">
        <!-- Theme color (adaptive for light/dark mode) -->
        <meta name="theme-color" media="(prefers-color-scheme: light)" content="#1f65fc">
        <meta name="theme-color" media="(prefers-color-scheme: dark)" content="#000000">
        
    <title>{{ config('app.name', 'MLM App') }}</title>

    <!-- Vite Assets -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        /* ===== Google Translate Fix ===== */
        .goog-te-banner-frame.skiptranslate,
        body > .skiptranslate {
            display: none !important;
        }

        html {
            margin-top: 0 !important;
        }

        body {
            top: 0 !important;
        }

        .goog-logo-link {
            display: none !important;
        }

        .goog-te-gadget {
            font-size: 0 !important;
            color: transparent !important;
        }

        .goog-te-gadget span {
            display: none !important;
        }

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

        /* Dark Mode Support */
        .dark .goog-te-gadget .goog-te-combo {
            border-color: #374151 !important;
            color: #f3f4f6 !important;
            background-color: #111827 !important;
        }

        /* Floating Widget Box */
        #google_translate_wrapper {
            position: fixed;
            bottom: 20px;
            right: 20px;
            z-index: 9999;
            border-radius: 6px;
            padding: 4px;
        }
    </style>

    @stack('styles')
</head>

<body
    x-data="{ 
        page: 'ecommerce', 
        loaded: true, 
        darkMode: false, 
        stickyMenu: false, 
        sidebarToggle: false, 
        menuToggle: false, 
        dropdownOpen: false,
        scrollTop: false 
    }"
    x-init="
        darkMode = JSON.parse(localStorage.getItem('darkMode')) ?? false;
        $watch('darkMode', value => localStorage.setItem('darkMode', JSON.stringify(value)))
    "
    :class="{ 'dark bg-gray-900': darkMode === true }"
>

<!-- ===== Page Wrapper Start ===== -->
<div class="flex h-screen overflow-hidden bg-gray-50 dark:bg-gray-900">

    <!-- Sidebar -->
    @include('admin.partials.sidebar')

    <!-- Content Area -->
    <div class="relative flex flex-col flex-1 overflow-x-hidden overflow-y-auto">

        <!-- Header -->
        @include('admin.partials.header')

        <!-- Main Content -->
        <main class="flex-1">
            <div class="p-4 mx-auto max-w-screen-2xl md:p-6">
                @yield('content')
            </div>
        </main>

    </div>
</div>
<!-- ===== Page Wrapper End ===== -->


<!-- ===== Google Translate Widget ===== -->
<div id="google_translate_wrapper" class="bg-white dark:bg-gray-800 shadow-lg border dark:border-gray-700 z-[9999] fixed bottom-5 right-5 rounded-lg p-2">
    <div id="google_translate_element"></div>
</div>


<!-- ===== Scripts ===== -->
<script>
    function googleTranslateElementInit() {
        new google.translate.TranslateElement({
            pageLanguage: 'en',
            includedLanguages: 'en,bn,hi',
            autoDisplay: false
        }, 'google_translate_element');
    }
</script>

<!-- IMPORTANT: Always HTTPS -->
<script src="https://translate.google.com/translate_a/element.js?cb=googleTranslateElementInit"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    window.confirmSubmit = function(event, title, text) {
        event.preventDefault();
        const form = event.target;
        Swal.fire({
            title: title || 'Are you sure?',
            text: text || "You won't be able to revert this!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, proceed!'
        }).then((result) => {
            if (result.isConfirmed) {
                form.submit();
            }
        });
        return false;
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

@stack('scripts')

</body>
</html>