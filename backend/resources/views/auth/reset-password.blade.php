<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Redefinir Senha - Clínica Fácil</title>

  <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('assets/favicon/apple-touch-icon.png') }}">
  <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('assets/favicon/favicon-32x32.png') }}">
  <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('assets/favicon/favicon-16x16.png') }}">
  <link rel="manifest" href="{{ asset('assets/favicon/site.webmanifest') }}">
  <link rel="shortcut icon" href="{{ asset('assets/favicon/favicon.ico') }}" type="image/x-icon">

  <script src="https://cdn.tailwindcss.com"></script>

  <style>
    body {
      background-image: url('{{ asset('assets/images/back_login.png') }}');
      background-size: cover;
      background-position: center;
      background-repeat: no-repeat;
      min-height: 100vh;
    }
  </style>
</head>

<body class="flex items-center justify-center min-h-screen">

  <div class="bg-white/90 backdrop-blur-md p-8 rounded-2xl shadow-2xl w-full max-w-md border border-gray-200">

    <div class="flex justify-center mb-6">
      <img src="{{ asset('assets/images/logoSys.png') }}" alt="Logo Clínica Fácil" class="w-52">
    </div>

    <h2 class="text-center text-xl font-semibold text-gray-700 mb-6">
      Redefinir Senha
    </h2>

    @if (session('status'))
      <div class="bg-green-100 text-green-700 p-3 mb-4 rounded text-sm">
        {{ session('status') }}
      </div>
    @endif

    @if ($errors->any())
      <div class="bg-red-100 text-red-700 p-3 mb-4 rounded text-sm">
        {{ $errors->first() }}
      </div>
    @endif

    <form method="POST" action="{{ route('password.update') }}">
      @csrf

      <input type="hidden" name="token" value="{{ $token }}">

      <div class="mb-4">
        <input type="email" name="email" value="{{ $email ?? old('email') }}" placeholder="E-mail" required
               class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-green-400 focus:outline-none">
      </div>

      <div class="mb-4 relative">
        <input type="password" name="password" id="password" placeholder="Nova Senha" required
               class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-green-400 focus:outline-none pr-10">
        <button type="button" onclick="togglePassword('password', 'eye1')" class="absolute inset-y-0 right-3 flex items-center">
          <svg id="eye1" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
               stroke-width="1.5" stroke="gray" class="w-5 h-5">
            <path stroke-linecap="round" stroke-linejoin="round"
                  d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
            <path stroke-linecap="round" stroke-linejoin="round"
                  d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
          </svg>
        </button>
      </div>

      <div class="mb-6 relative">
        <input type="password" name="password_confirmation" id="password_confirmation" placeholder="Confirmar Senha" required
               class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-green-400 focus:outline-none pr-10">
        <button type="button" onclick="togglePassword('password_confirmation', 'eye2')" class="absolute inset-y-0 right-3 flex items-center">
          <svg id="eye2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
               stroke-width="1.5" stroke="gray" class="w-5 h-5">
            <path stroke-linecap="round" stroke-linejoin="round"
                  d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
            <path stroke-linecap="round" stroke-linejoin="round"
                  d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
          </svg>
        </button>
      </div>

      <button type="submit"
              class="w-full bg-green-500 hover:bg-green-600 text-white font-semibold py-2 rounded-lg transition">
        Redefinir Senha
      </button>
    </form>

    <p class="text-center text-gray-600 text-sm mt-6">
      Lembrou a senha?
      <a href="{{ route('login') }}" class="text-green-600 hover:underline font-semibold">
        Voltar ao Login
      </a>
    </p>

  </div>

  <script>
    function togglePassword(fieldId, eyeId) {
      const input = document.getElementById(fieldId);
      const eye = document.getElementById(eyeId);
      if (input.type === "password") {
        input.type = "text";
        eye.setAttribute("stroke", "black");
      } else {
        input.type = "password";
        eye.setAttribute("stroke", "gray");
      }
    }
  </script>
</body>
</html>
