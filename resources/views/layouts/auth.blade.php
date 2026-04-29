<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta
      name="viewport"
      content="width=device-width, initial-scale=1.0"
    />
    <meta http-equiv="X-UA-Compatible" content="ie=edge" />
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate" />
    <meta http-equiv="Pragma" content="no-cache" />
    <meta http-equiv="Expires" content="0" />
    <title>{{ config('app.name', 'MLM App') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
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
        .dark .goog-te-gadget .goog-te-combo {
            border-color: #374151 !important;
            color: #f3f4f6 !important;
            background-color: #111827 !important;
        }
    </style>
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
            
            <div class="relative items-center hidden w-full h-100% bg-brand-950 dark:bg-white/5 lg:grid lg:w-1/2">
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

    <!-- ===== Google Translate Widget ===== -->
    <div id="google_translate_wrapper" class="bg-white dark:bg-gray-800 shadow-lg border dark:border-gray-700 z-[9999] fixed bottom-5 right-5 rounded-lg p-1">
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
    <script src="https://translate.google.com/translate_a/element.js?cb=googleTranslateElementInit"></script>
    <script>
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', function() {
                navigator.serviceWorker.register("{{ asset('service-worker.js') }}")
                .then(() => console.log("PWA Ready"))
                .catch(err => console.log(err));
            });
        }
    </script>
  </body>
</html>
