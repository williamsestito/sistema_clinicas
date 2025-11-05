<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Novo Cadastro - Clínica Fácil</title>

  <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('assets/favicon/apple-touch-icon.png') }}">
  <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('assets/favicon/favicon-32x32.png') }}">
  <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('assets/favicon/favicon-16x16.png') }}">
  <link rel="manifest" href="{{ asset('assets/favicon/site.webmanifest') }}">
  <link rel="shortcut icon" href="{{ asset('assets/favicon/favicon.ico') }}" type="image/x-icon">
  <meta name="theme-color" content="#16a34a">

  <script src="https://cdn.tailwindcss.com"></script>

  <style>
    body{
      background-image: url('{{ asset('assets/images/back_login.png') }}');
      background-size: cover;
      background-position: center;
      background-repeat: no-repeat;
      min-height: 100vh;
    }
  </style>
</head>

<body class="flex items-center justify-center min-h-screen">
  <div class="flex flex-col lg:flex-row bg-white/95 rounded-2xl shadow-2xl w-full max-w-5xl overflow-hidden border border-gray-200">
    <div class="hidden lg:flex lg:w-1/2 bg-cover bg-center"
         style="background-image:url('{{ asset('assets/images/podologia3.png') }}');">
      <div class="flex items-center justify-center w-full bg-black/30">
        <h1 class="text-white text-5xl font-bold tracking-wide uppercase">
          <span class="text-orange-400">Podologia</span> e Estética
        </h1>
      </div>
    </div>

    <div class="flex w-full lg:w-1/2 justify-center items-center p-6">
      <div class="w-full max-w-md">
        <div class="flex justify-center mb-6">
          <img src="{{ asset('assets/images/logoSys.png') }}" alt="Logo Clínica Fácil" class="w-48">
        </div>

        <h2 class="text-2xl font-semibold text-gray-700 text-center mb-6">Novo Cadastro</h2>

        @if (session('success'))
          <div class="bg-green-100 text-green-700 p-3 mb-4 rounded text-sm">
            {{ session('success') }}
          </div>
        @endif

        @if ($errors->any())
          <div class="bg-red-100 text-red-700 p-3 mb-4 rounded text-sm">
            {{ $errors->first() }}
          </div>
        @endif

        <form method="POST" action="{{ route('register.post') }}">
          @csrf

          <div class="mb-4">
            <input type="text" name="name" placeholder="Nome completo" required
                   value="{{ old('name') }}"
                   class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-green-400 focus:outline-none">
          </div>

          <div class="mb-4">
            <input type="email" name="email" placeholder="E-mail" required
                   value="{{ old('email') }}"
                   class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-green-400 focus:outline-none">
          </div>

          <div class="relative mb-4">
            <input type="password" id="password" name="password" placeholder="Senha" required
                   class="w-full px-4 py-2 pr-11 rounded-lg border border-gray-300 focus:ring-2 focus:ring-green-400 focus:outline-none">
            <button type="button" id="togglePassword"
                    class="absolute right-3 top-1/2 -translate-y-1/2 p-1 text-gray-500 hover:text-gray-700"
                    aria-label="Mostrar/ocultar senha">
              <!-- eye -->
              <svg id="eye" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                   viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round"
                      d="M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268 2.943 9.542 7-1.274 4.057-5.065 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                <circle cx="12" cy="12" r="3" />
              </svg>
              <!-- eye-off -->
              <svg id="eyeOff" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 hidden" fill="none"
                   viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round"
                      d="M13.875 18.825A10.05 10.05 0 0112 19c-4.477 0-8.268-2.943-9.542-7a10.43 10.43 0 012.37-3.95M6.1 6.1C7.948 4.757 9.94 4 12 4c4.477 0 8.268 2.943 9.542 7a10.43 10.43 0 01-1.51 2.74M3 3l18 18"/>
              </svg>
            </button>
          </div>

          <div class="relative mb-6">
            <input type="password" id="password_confirmation" name="password_confirmation" placeholder="Confirmar senha" required
                   class="w-full px-4 py-2 pr-11 rounded-lg border border-gray-300 focus:ring-2 focus:ring-green-400 focus:outline-none">
            <button type="button" id="toggleConfirm"
                    class="absolute right-3 top-1/2 -translate-y-1/2 p-1 text-gray-500 hover:text-gray-700"
                    aria-label="Mostrar/ocultar confirmação">
              <!-- eye -->
              <svg id="eye2" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                   viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round"
                      d="M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268 2.943 9.542 7-1.274 4.057-5.065 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                <circle cx="12" cy="12" r="3" />
              </svg>
              <!-- eye-off -->
              <svg id="eyeOff2" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 hidden" fill="none"
                   viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round"
                      d="M13.875 18.825A10.05 10.05 0 0112 19c-4.477 0-8.268-2.943-9.542-7a10.43 10.43 0 012.37-3.95M6.1 6.1C7.948 4.757 9.94 4 12 4c4.477 0 8.268 2.943 9.542 7a10.43 10.43 0 01-1.51 2.74M3 3l18 18"/>
              </svg>
            </button>
          </div>

          <button type="submit"
                  class="w-full bg-green-600 hover:bg-green-700 text-white font-semibold py-2 rounded-lg transition">
            Cadastrar
          </button>
        </form>

        <p class="text-center text-gray-600 text-sm mt-6">
          Já tem uma conta?
          <a href="{{ route('login') }}" class="text-green-600 hover:underline font-semibold">Voltar para Login</a>
        </p>
      </div>
    </div>
  </div>

  <script>
    const pass = document.getElementById('password');
    const pass2 = document.getElementById('password_confirmation');
    const toggle1 = document.getElementById('togglePassword');
    const toggle2 = document.getElementById('toggleConfirm');
    const eye1 = document.getElementById('eye');
    const eyeOff1 = document.getElementById('eyeOff');
    const eye2svg = document.getElementById('eye2');
    const eyeOff2svg = document.getElementById('eyeOff2');

    toggle1.addEventListener('click', () => {
      const t = pass.type === 'password' ? 'text' : 'password';
      pass.type = t; eye1.classList.toggle('hidden'); eyeOff1.classList.toggle('hidden');
    });
    toggle2.addEventListener('click', () => {
      const t = pass2.type === 'password' ? 'text' : 'password';
      pass2.type = t; eye2svg.classList.toggle('hidden'); eyeOff2svg.classList.toggle('hidden');
    });
  </script>
</body>
</html>
