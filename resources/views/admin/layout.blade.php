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
    translate="no"
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
