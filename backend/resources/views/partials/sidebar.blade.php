<aside 
  class="bg-gray-800 text-gray-100 flex flex-col transition-all duration-300"
  :class="sidebarOpen ? 'w-64' : 'w-20'">

  <div class="flex items-center justify-between px-4 py-4 border-b border-gray-700">
    <img 
      src="{{ asset('assets/images/logo_branca.png') }}" 
      alt="Logo Clínica Fácil" 
      class="h-8 transition-all duration-300" 
      :class="sidebarOpen ? 'opacity-100' : 'opacity-0 w-0'">

    <button @click="sidebarOpen = !sidebarOpen" class="text-gray-300 focus:outline-none">
      <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7" />
      </svg>
    </button>
  </div>

  <nav class="flex-1 mt-4">
    <ul>
      <li class="hover:bg-gray-700">
        <a href="{{ route('dashboard') }}" class="flex items-center px-4 py-3 space-x-3">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-green-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M13 5v6h6" />
          </svg>
          <span :class="sidebarOpen ? 'inline' : 'hidden'" class="text-sm font-medium">Dashboard</span>
        </a>
      </li>
      <li class="hover:bg-gray-700">
        <a href="{{ route('dashboard') }}" class="flex items-center px-4 py-3 space-x-3">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-green-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10m-11 4h12m-2 4h-8" />
          </svg>
          <span :class="sidebarOpen ? 'inline' : 'hidden'" class="text-sm font-medium">Agenda</span>
        </a>
      </li>
      <li class="hover:bg-gray-700">
        <a href="#" class="flex items-center px-4 py-3 space-x-3">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-green-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
          </svg>
          <span :class="sidebarOpen ? 'inline' : 'hidden'" class="text-sm font-medium">Atendimentos</span>
        </a>
      </li>
    </ul>
  </nav>
</aside>
