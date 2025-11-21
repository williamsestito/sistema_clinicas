<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- CSRF ESSENCIAL -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Área do Cliente - Clínica Fácil')</title>

    {{-- Tailwind via CDN (rápido e prático) --}}
    <script src="https://cdn.tailwindcss.com"></script>

    {{-- Font Awesome --}}
    <link rel="stylesheet" href="{{ asset('vendor/fontawesome/css/all.min.css') }}">

    {{-- Alpine.js --}}
    <script src="https://unpkg.com/alpinejs" defer></script>

    {{-- Estilos globais --}}
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f9fafb;
        }

        /* Scroll mais bonito */
        ::-webkit-scrollbar { width: 6px; height: 6px; }
        ::-webkit-scrollbar-thumb { background: #9ca3af; border-radius: 4px; }
        ::-webkit-scrollbar-thumb:hover { background: #6b7280; }
    </style>
</head>

<body class="bg-gray-100 flex h-screen overflow-hidden">

    {{-- Sidebar --}}
    @include('partials.sidebar_client')

    {{-- Conteúdo Principal --}}
    <div class="flex-1 flex flex-col overflow-y-auto">

        {{-- Topbar --}}
        <header class="bg-white shadow-sm flex justify-between items-center px-6 py-3 border-b border-gray-200 sticky top-0 z-20">

            {{-- Título --}}
            <h1 class="text-lg sm:text-xl font-semibold text-gray-700 flex items-center gap-2">
                <i class="fa-solid fa-user text-green-600 text-base sm:text-lg"></i>
                <span>@yield('title', 'Área do Cliente')</span>
            </h1>

            {{-- Ações --}}
            <div class="flex items-center gap-3">

                {{-- Pesquisa (exibido apenas em telas maiores) --}}
                <input 
                    type="text" 
                    placeholder="Pesquisar..." 
                    class="hidden sm:block px-3 py-1 border border-gray-300 rounded-md text-sm text-gray-600 
                           focus:ring-1 focus:ring-green-400 outline-none transition w-48">

                {{-- Avatar com inicial --}}
                @php
                    $client = Auth::guard('client')->user();
                    $initial = $client ? strtoupper(mb_substr($client->name, 0, 1)) : '?';
                @endphp

                <div class="w-9 h-9 bg-green-600 text-white flex items-center justify-center rounded-full font-semibold text-sm shadow">
                    {{ $initial }}
                </div>
            </div>
        </header>

        {{-- Conteúdo dinâmico --}}
        <main class="flex-1 p-4 sm:p-6 bg-gray-100">
            @yield('content')
        </main>

    </div>

</body>
</html>
