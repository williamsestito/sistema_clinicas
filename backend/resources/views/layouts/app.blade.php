<!DOCTYPE html>
<html lang="pt-BR" x-data="{ sidebarOpen: window.innerWidth >= 1024 }" x-init="
  window.addEventListener('resize', () => {
    if (window.innerWidth >= 1024) sidebarOpen = true;
    else sidebarOpen = false;
  });
">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>@yield('title', 'Clínica Fácil')</title>

  {{-- Favicon --}}
  <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('assets/favicon/apple-touch-icon.png') }}">
  <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('assets/favicon/favicon-32x32.png') }}">
  <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('assets/favicon/favicon-16x16.png') }}">
  <link rel="manifest" href="{{ asset('assets/favicon/site.webmanifest') }}">
  <link rel="shortcut icon" href="{{ asset('assets/favicon/favicon.ico') }}" type="image/x-icon">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

<script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
<script src="https://cdn.jsdelivr.net/npm/@alpinejs/mask@3.x.x/dist/cdn.min.js" defer></script>

  <meta name="theme-color" content="#1a5632">

  <script src="https://cdn.tailwindcss.com"></script>
  <script src="//unpkg.com/alpinejs" defer></script>
</head>


<body class="flex flex-col min-h-screen bg-gray-100">
  <div class="flex flex-1 overflow-hidden">
    @include('partials.sidebar')
    <div class="flex flex-col flex-1 min-h-screen overflow-y-auto">
      @include('partials.navbar')
      <main class="flex-1 p-4 sm:p-6 overflow-y-auto">
        @yield('content')
      </main>
    </div>
  </div>
</body>
</html>
