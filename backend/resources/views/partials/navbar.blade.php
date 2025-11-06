<header class="bg-white shadow flex items-center justify-between px-4 py-3">
  <div class="flex items-center w-full md:w-1/2">
    <input type="text" placeholder="Pesquisar Paciente" 
           class="w-full px-3 py-2 border rounded-lg text-sm focus:ring-2 focus:ring-green-400">
  </div>
  <div x-data="{ userMenuOpen: false }" class="relative ml-4">
    <button @click="userMenuOpen = !userMenuOpen" class="flex items-center space-x-2 focus:outline-none">
      <img src="https://ui-avatars.com/api/?name={{ urlencode(Auth::user()->name) }}" 
           class="w-8 h-8 rounded-full" alt="Avatar">
      <span class="hidden md:inline text-gray-700 text-sm font-medium">{{ Auth::user()->name }}</span>
    </button>
    <div 
      x-show="userMenuOpen"
      @click.away="userMenuOpen = false"
      class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg border border-gray-200 py-2 z-50"
      x-transition>
      <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Meu Perfil</a>
      <form method="POST" action="{{ route('logout') }}">
        @csrf
        <button type="submit" class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
          Sair
        </button>
      </form>
    </div>
  </div>
</header>
