<aside 
  x-data="{ sidebarOpen: true }"
  class="bg-[#0B111B] text-gray-100 flex flex-col transition-all duration-300 min-h-screen"
  :class="sidebarOpen ? 'w-64' : 'w-20'">

  <!-- Logo -->
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
      $user = Auth::user();
  @endphp

  <nav class="flex-1 mt-2 text-[13px] font-medium space-y-2">

    {{-- Paciente --}}
    @if($user->role === 'client')
      <ul class="space-y-0.5 mt-2">
        <x-sidebar-link icon="fa-calendar-check" label="Meus Agendamentos" route="pacient.appointments" :sidebarOpen="$sidebarOpen ?? true" />
        <x-sidebar-link icon="fa-stethoscope" label="Agendar Consulta" route="pacient.schedule" :sidebarOpen="$sidebarOpen ?? true" />
        <x-sidebar-link icon="fa-user" label="Meus Dados" route="pacient.profile" :sidebarOpen="$sidebarOpen ?? true" />
      </ul>

      @php
          $camposObrigatorios = ['phone', 'address', 'city', 'state'];
          $faltando = collect($camposObrigatorios)->some(fn($campo) => empty($user->$campo));
      @endphp

      @if($faltando)
        <div 
          x-show="sidebarOpen"
          class="bg-yellow-100 text-yellow-800 text-xs mx-4 mt-3 p-2 rounded-md border border-yellow-300">
          ⚠️ Complete seus dados no menu <strong>“Meus Dados”</strong> para continuar agendando.
        </div>
      @endif

    {{-- Profissional --}}
    @elseif($user->role === 'professional')
      <div>
        <h3 
          x-show="sidebarOpen"
          class="text-[10px] font-semibold uppercase tracking-wide text-gray-400 px-4 py-1.5">
          Profissional
        </h3>
        <ul class="space-y-0.5">
          <x-sidebar-link icon="fa-chart-line" label="Dashboard" route="professional.dashboard" :sidebarOpen="$sidebarOpen ?? true" />
          <x-sidebar-link icon="fa-calendar-days" label="Minha Agenda" route="professional.schedule" :sidebarOpen="$sidebarOpen ?? true" />
          <x-sidebar-link icon="fa-user-injured" label="Pacientes" route="professional.pacients" :sidebarOpen="$sidebarOpen ?? true" />
          <x-sidebar-link icon="fa-stethoscope" label="Procedimentos" route="professional.procedures" :sidebarOpen="$sidebarOpen ?? true" />
          <x-sidebar-link icon="fa-calendar-xmark" label="Dias Bloqueados" route="professional.blocked" :sidebarOpen="$sidebarOpen ?? true" />
          <x-sidebar-link icon="fa-clock" label="Configurar Agenda" route="professional.schedule.config" :sidebarOpen="$sidebarOpen ?? true" />
        </ul>
      </div>

      <div>
        <h3 
          x-show="sidebarOpen"
          class="text-[10px] font-semibold uppercase tracking-wide text-gray-400 px-4 py-1.5">
          Relatórios
        </h3>
        <ul class="space-y-0.5">
          <x-sidebar-link icon="fa-clipboard-list" label="Atendimentos" route="professional.reports.appointments" :sidebarOpen="$sidebarOpen ?? true" />
          <x-sidebar-link icon="fa-money-bill-wave" label="Financeiro" route="professional.reports.finance" :sidebarOpen="$sidebarOpen ?? true" />
        </ul>
      </div>

    {{-- Admin / Owner / Frontdesk --}}
    @elseif(in_array($user->role, ['admin', 'owner', 'frontdesk']))
      <div 
        class="flex space-x-2 px-4 py-2 border-b border-gray-700 overflow-hidden transition-all duration-300"
        :class="sidebarOpen ? 'opacity-100 max-h-20' : 'opacity-0 max-h-0 p-0 border-0'">
        <button class="flex-1 bg-green-600 hover:bg-green-700 text-xs font-medium py-1.5 rounded-md transition">
          + Avaliação
        </button>
        <button class="flex-1 bg-blue-600 hover:bg-blue-700 text-xs font-medium py-1.5 rounded-md transition">
          + Evolução
        </button>
      </div>

      <div>
        <h3 
          class="text-[10px] font-semibold uppercase tracking-wide text-gray-400 px-4 py-1.5"
          x-show="sidebarOpen">
          Navegação Principal
        </h3>
        <ul class="space-y-0.5">
          <x-sidebar-link icon="fa-calendar" label="Agenda" route="agenda" :sidebarOpen="$sidebarOpen ?? true" />
          <x-sidebar-link icon="fa-user-injured" label="Pacientes" route="pacients.index" :sidebarOpen="$sidebarOpen ?? true" />
          <x-sidebar-link icon="fa-stethoscope" label="Atendimentos" :sidebarOpen="$sidebarOpen ?? true" />
          <x-sidebar-link icon="fa-camera" label="Procedimentos" :sidebarOpen="$sidebarOpen ?? true" />
        </ul>
      </div>
    @endif
  </nav>
</aside>
