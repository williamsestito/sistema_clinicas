<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Recuperação de Senha - Clínica Fácil</title>
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
  <div class="bg-white/95 backdrop-blur-md p-8 rounded-2xl shadow-2xl w-full max-w-md border border-gray-200">
    <div class="flex justify-center mb-6">
      <img src="{{ asset('assets/images/logoSys.png') }}" alt="Logo Clínica Fácil" class="w-52">
    </div>

    <h2 class="text-center text-2xl font-semibold text-gray-700 mb-6">
      Recuperação de Senha
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

    <form method="POST" action="{{ route('password.email') }}">
      @csrf
      <div class="mb-6">
        <input type="email" name="email" placeholder="Digite seu e-mail" required
               class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-green-400 focus:outline-none">
      </div>
      <button type="submit"
              class="w-full bg-green-500 hover:bg-green-600 text-white font-semibold py-2 rounded-lg transition">
        Enviar link de redefinição
      </button>
    </form>

    <p class="text-center text-gray-600 text-sm mt-6">
      Lembrou sua senha?
      <a href="{{ route('login') }}" class="text-green-600 hover:underline font-semibold">
        Voltar ao Login
      </a>
    </p>
  </div>
</body>
</html>
