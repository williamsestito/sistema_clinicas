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
  <title>@yield('title', 'Clínica Fácil')</title>

  <!-- Favicon -->
  <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('assets/favicon/apple-touch-icon.png') }}">
  <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('assets/favicon/favicon-32x32.png') }}">
  <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('assets/favicon/favicon-16x16.png') }}">
  <link rel="manifest" href="{{ asset('assets/favicon/site.webmanifest') }}">
  <link rel="shortcut icon" href="{{ asset('assets/favicon/favicon.ico') }}" type="image/x-icon">

  <!-- Estilos -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

  <!-- Tailwind -->
  <script src="https://cdn.tailwindcss.com"></script>

  <!-- Alpine.js + Plugins -->
  <script src="https://cdn.jsdelivr.net/npm/@alpinejs/collapse@3.x.x/dist/cdn.min.js" defer></script>
  <script src="https://cdn.jsdelivr.net/npm/@alpinejs/mask@3.x.x/dist/cdn.min.js" defer></script>
  <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>

  <!-- TinyMCE WYSIWYG -->
  <script src="https://cdn.jsdelivr.net/npm/tinymce@7/tinymce.min.js" referrerpolicy="origin"></script>

  <!-- Axios -->
  <script src="https://cdn.jsdelivr.net/npm/axios@1/dist/axios.min.js"></script>

  <!-- SweetAlert2 -->
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

  <meta name="theme-color" content="#1a5632">
</head>

<body class="flex flex-col min-h-screen bg-gray-100 text-gray-900 antialiased">

  <div class="flex flex-1 overflow-hidden">

    {{-- Sidebar Dinâmica --}}
    @include('partials.sidebar')

    {{-- Conteúdo principal --}}
    <div class="flex flex-col flex-1 min-h-screen overflow-y-auto">

      {{-- Navbar superior --}}
      @include('partials.navbar')

      {{-- Conteúdo das páginas --}}
      <main class="flex-1 p-4 sm:p-6 overflow-y-auto">
        @yield('content')
      </main>
    </div>
  </div>

  {{-- Overlay no mobile quando sidebar aberta --}}
  <div 
    x-show="sidebarOpen && window.innerWidth < 1024"
    @click="sidebarOpen = false"
    class="fixed inset-0 bg-black bg-opacity-40 z-30 transition-opacity lg:hidden">
  </div>

  {{-- SweetAlert: toast de sucesso --}}
  @if(session('success'))
  <script>
    document.addEventListener('DOMContentLoaded', function () {
      Swal.fire({
        toast: true,
        position: 'top-end',
        icon: 'success',
        title: @json(session('success')),
        showConfirmButton: false,
        timer: 3000,
        timerProgressBar: true,
      });
    });
  </script>
  @endif

  {{-- SweetAlert: confirmação de exclusão --}}
  <script>
    function confirmDelete(formEl, entityName) {
      Swal.fire({
        title: 'Tem certeza?',
        text: 'Deseja realmente excluir ' + (entityName || 'este registro') + '? Esta ação não pode ser desfeita.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'Sim, excluir',
        cancelButtonText: 'Cancelar',
      }).then(function (result) {
        if (result.isConfirmed) {
          formEl.submit();
        }
      });
    }
  </script>

  @stack('scripts')

</body>
</html>
