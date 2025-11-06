<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login - Clínica Fácil</title>
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
    
    {{-- Logo --}}
    <div class="flex justify-center mb-6">
      <img src="{{ asset('assets/images/logoSys.png') }}" alt="Logo Clínica Fácil" class="w-52">
    </div>

    {{-- Título --}}
    <h2 class="text-center text-2xl font-bold text-gray-700 mb-6">Acesso ao Sistema</h2>

    {{-- Mensagem de sucesso (logout ou ações) --}}
    @if (session('status'))
      <div class="bg-green-100 text-green-700 border border-green-400 rounded-md p-3 mb-4 text-sm flex items-center">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
        </svg>
        {{ session('status') }}
      </div>
    @endif

    {{-- Mensagens de erro (validação ou autenticação) --}}
    @if ($errors->any())
      <div class="bg-red-100 text-red-700 border border-red-400 rounded-md p-3 mb-4 text-sm flex items-center">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
        </svg>
        {{ $errors->first() }}
      </div>
    @endif

    {{-- Formulário de login --}}
    <form method="POST" action="{{ route('login.post') }}">
      @csrf

      <div class="mb-4">
        <input 
          type="email" 
          name="email" 
          value="{{ old('email') }}"
          placeholder="Email" 
          required
          class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-green-400 placeholder-gray-500">
      </div>

      <div class="mb-4">
        <input 
          type="password" 
          name="password" 
          placeholder="Senha" 
          required
          class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-green-400 placeholder-gray-500">
      </div>

      {{-- Lembrar-me e Esqueci senha --}}
      <div class="flex items-center justify-between mb-6">
        <label class="flex items-center text-gray-600 text-sm">
          <input type="checkbox" name="remember" class="mr-2 rounded border-gray-300">
          Lembrar-me
        </label>
        <a href="{{ route('password.request') }}" class="text-sm text-green-600 hover:underline">
          Esqueceu a senha?
        </a>
      </div>

      {{-- Botão Entrar --}}
      <button type="submit"
              class="w-full bg-green-500 hover:bg-green-600 text-white font-semibold py-2 rounded-lg transition">
        Entrar
      </button>
    </form>

    {{-- Divisor --}}
    <div class="flex items-center my-6">
      <div class="flex-grow border-t border-gray-300"></div>
      <span class="mx-2 text-gray-500 text-sm">ou</span>
      <div class="flex-grow border-t border-gray-300"></div>
    </div>

    {{-- Cadastro --}}
    <div class="text-center">
      <p class="text-gray-600 text-sm mb-2">É a primeira vez por aqui?</p>
      <a href="{{ route('register') }}" 
         class="bg-orange-500 hover:bg-orange-600 text-white py-2 px-4 rounded-lg text-sm font-semibold transition">
        Criar meu Cadastro
      </a>
    </div>

  </div>

</body>
</html>
