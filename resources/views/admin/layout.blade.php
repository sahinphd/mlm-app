<!doctype html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Admin - {{ config('app.name','MLM App') }}</title>
    <link href="/css/admin-style.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@3.5.3/dist/tailwind.min.css" rel="stylesheet">
  </head>
  <body class="bg-gray-50">
    @include('admin.partials.preloader')
    <div class="flex h-screen overflow-hidden">
      @include('admin.partials.sidebar')

      <div class="relative flex flex-col flex-1 overflow-x-hidden overflow-y-auto">
        @include('admin.partials.overlay')
        @include('admin.partials.header')

        <main class="p-4 mx-auto max-w-(--breakpoint-2xl) md:p-6">
          @yield('content')
        </main>
      </div>
    </div>
  </body>
</html>
