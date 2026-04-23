<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta http-equiv="X-UA-Compatible" content="ie=edge" />
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
            padding: 4px 8px !important;
            border-radius: 6px !important;
            border: 1px solid #d1d5db !important;
            background-color: #fff !important;
            color: #111827 !important;
            font-size: 12px !important;
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
            border-radius: 8px;
            padding: 6px;
        }
    </style>

    @stack('styles')
</head>

<body
    x-data="{ page: 'ecommerce', loaded: true, darkMode: false, stickyMenu: false, sidebarToggle: false, scrollTop: false }"
    x-init="
        darkMode = JSON.parse(localStorage.getItem('darkMode')) ?? false;
        $watch('darkMode', value => localStorage.setItem('darkMode', JSON.stringify(value)))
    "
    :class="{ 'dark bg-gray-900': darkMode === true }"
>

<!-- ===== Page Wrapper Start ===== -->
<div class="flex h-screen overflow-hidden">

    <!-- Sidebar -->
    <x-sidebar />

    <!-- Content Area -->
    <div class="relative flex flex-col flex-1 overflow-x-hidden overflow-y-auto">

        <!-- Header -->
        <x-header />

        <!-- Main Content -->
        <main>
            <div class="p-4 mx-auto max-w-screen-2xl md:p-6">
                @yield('content')
            </div>
        </main>

    </div>
</div>
<!-- ===== Page Wrapper End ===== -->


<!-- ===== Google Translate Widget ===== -->
<div id="google_translate_wrapper" class="bg-white dark:bg-gray-800 shadow-lg border dark:border-gray-700">
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

@stack('scripts')

</body>
</html>