<!DOCTYPE html>
<html lang="pt-BR"
      x-data="{ sidebarOpen: window.innerWidth >= 1024 }"
      x-init="
        window.addEventListener('resize', () => {
            sidebarOpen = window.innerWidth >= 1024;
        });
      ">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    {{-- Título Dinâmico --}}
    <title>@yield('title', 'Clínica Fácil')</title>

    {{-- CSRF Token Global para AJAX --}}
    <meta name="csrf-token" content="{{ csrf_token() }}">

    {{-- Favicon --}}
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('assets/favicon/apple-touch-icon.png') }}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('assets/favicon/favicon-32x32.png') }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('assets/favicon/favicon-16x16.png') }}">
    <link rel="manifest" href="{{ asset('assets/favicon/site.webmanifest') }}">
    <link rel="shortcut icon" href="{{ asset('assets/favicon/favicon.ico') }}" type="image/x-icon">

    {{-- Icones --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    {{-- TailwindCSS CDN --}}
    <script src="https://cdn.tailwindcss.com"></script>

    {{-- Alpine + Masks --}}
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <script src="https://cdn.jsdelivr.net/npm/@alpinejs/mask@3.x.x/dist/cdn.min.js" defer></script>

    {{-- Tema para mobile --}}
    <meta name="theme-color" content="#1a5632">

    {{-- CSS adicional / Página específica --}}
    @stack('styles')
</head>

<body class="flex flex-col min-h-screen bg-gray-100 text-gray-900 antialiased">

    {{-- Estrutura principal --}}
    <div class="flex flex-1 overflow-hidden">

        {{-- Sidebar Dinâmica --}}
        @include('partials.sidebar')

        {{-- Conteúdo principal --}}
        <div class="flex flex-col flex-1 min-h-screen overflow-y-auto">

            {{-- Navbar --}}
            @include('partials.navbar')

            {{-- Conteúdo interno --}}
            <main class="flex-1 p-4 sm:p-6 overflow-y-auto">
                @yield('content')
            </main>

        </div>
    </div>

    {{-- Fundo escurecido para mobile --}}
    <div 
        x-show="sidebarOpen && window.innerWidth < 1024"
        @click="sidebarOpen = false"
        class="fixed inset-0 bg-black bg-opacity-40 z-30 transition-opacity lg:hidden">
    </div>

    {{-- Scripts globais --}}
    <script>
        // CSRF global para FETCH (Ajax)
        window.csrfToken = document.querySelector('meta[name="csrf-token"]').content;
    </script>

    {{-- Scripts adicionais injetáveis --}}
    @stack('scripts')

</body>
</html>
