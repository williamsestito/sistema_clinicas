<aside 
  x-data="{ sidebarOpen: true }"
  class="bg-[#0B111B] text-gray-100 flex flex-col transition-all duration-300 min-h-screen"
  :class="sidebarOpen ? 'w-64' : 'w-20'">

  <!-- Logo e botão toggle -->
  <div class="flex items-center justify-between px-4 py-3 border-b border-gray-700">
    <img 
      src="{{ asset('assets/images/logo_branca.png') }}" 
      alt="Logo Clínica Fácil" 
      class="h-7 transition-all duration-300"
      :class="sidebarOpen ? 'opacity-100 w-auto' : 'opacity-0 w-0'">

    <button @click="sidebarOpen = !sidebarOpen" class="text-gray-300 focus:outline-none">
      <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7" />
      </svg>
    </button>
  </div>

  @php
      $client = Auth::guard('client')->user();
      $camposObrigatorios = ['phone', 'address', 'city', 'state'];
      $faltando = collect($camposObrigatorios)->some(fn($campo) => empty($client->$campo));
  @endphp

  <!-- Navegação principal -->
  <nav class="flex-1 mt-2 text-[13px] font-medium space-y-2">

    <h3 
      x-show="sidebarOpen"
      class="text-[10px] font-semibold uppercase tracking-wide text-gray-400 px-4 py-1.5">
      Paciente
    </h3>

    <ul class="space-y-0.5 mt-1">

      <!-- Meus Agendamentos -->
      <li>
        <a href="{{ route('client.appointments') }}"
           class="flex items-center gap-3 px-4 py-2 rounded-md transition 
                  {{ request()->routeIs('client.appointments') ? 'bg-green-600 text-white' : 'text-gray-300 hover:bg-gray-800 hover:text-white' }}">
          <i class="fa-solid fa-calendar-check w-5 text-center"></i>
          <span x-show="sidebarOpen" class="whitespace-nowrap">Meus Agendamentos</span>
        </a>
      </li>

      <!-- Agendar Consulta -->
      <li>
        <a href="{{ route('client.schedule') }}"
           class="flex items-center gap-3 px-4 py-2 rounded-md transition 
                  {{ request()->routeIs('client.schedule') ? 'bg-green-600 text-white' : 'text-gray-300 hover:bg-gray-800 hover:text-white' }}">
          <i class="fa-solid fa-stethoscope w-5 text-center"></i>
          <span x-show="sidebarOpen" class="whitespace-nowrap">Agendar Consulta</span>
        </a>
      </li>

      <!-- Meus Dados -->
      <li>
        <a href="{{ route('client.profile') }}"
           class="flex items-center gap-3 px-4 py-2 rounded-md transition 
                  {{ request()->routeIs('client.profile') ? 'bg-green-600 text-white' : 'text-gray-300 hover:bg-gray-800 hover:text-white' }}">
          <i class="fa-solid fa-user w-5 text-center"></i>
          <span x-show="sidebarOpen" class="whitespace-nowrap">Meus Dados</span>
        </a>
      </li>

    </ul>

    <!-- Aviso de dados incompletos -->
    @if($faltando)
      <div 
        x-show="sidebarOpen"
        x-transition
        class="bg-yellow-100 text-yellow-800 text-xs mx-4 mt-3 p-2 rounded-md border border-yellow-300 leading-snug">
        ⚠️ Complete seus dados em <strong>“Meus Dados”</strong> para continuar agendando.
      </div>
    @endif
  </nav>

  <!-- Rodapé (logout) -->
  <div class="px-3 py-3 border-t border-gray-700 mt-auto">
    <form method="POST" action="{{ route('client.logout') }}">
      @csrf
      <button type="submit"
              class="w-full flex items-center gap-3 px-3 py-2 rounded-md text-sm transition
                     text-red-400 hover:text-white hover:bg-red-600">
        <i class="fa-solid fa-arrow-right-from-bracket w-5 text-center"></i>
        <span x-show="sidebarOpen" class="whitespace-nowrap">Sair</span>
      </button>
    </form>
  </div>
</aside>
