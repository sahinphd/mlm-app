<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta
      name="viewport"
      content="width=device-width, initial-scale=1.0"
    />
    <meta http-equiv="X-UA-Compatible" content="ie=edge" />
    <title>Admin - {{ config('app.name', 'MLM App') }}</title>
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
    x-data="{ 
        page: 'admin', 
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
         $watch('darkMode', value => localStorage.setItem('darkMode', JSON.stringify(value)))"
    :class="{'dark bg-gray-900': darkMode === true}"
  >
    <!-- ===== Page Wrapper Start ===== -->
    <div class="flex h-screen overflow-hidden">
      <!-- ===== Sidebar Start ===== -->
      @include('admin.partials.sidebar')
      <!-- ===== Sidebar End ===== -->

      <!-- ===== Content Area Start ===== -->
      <div
        class="relative flex flex-col flex-1 overflow-x-hidden overflow-y-auto"
      >
        <!-- ===== Header Start ===== -->
        @include('admin.partials.header')
        <!-- ===== Header End ===== -->

        <!-- ===== Main Content Start ===== -->
        <main>
          <div class="p-4 mx-auto max-w-screen-2xl md:p-6">
            @yield('content')
          </div>
        </main>
        <!-- ===== Main Content End ===== -->
      </div>
      <!-- ===== Content Area End ===== -->
    </div>
    <!-- ===== Page Wrapper End ===== -->

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
    @stack('scripts')
  </body>
</html>
