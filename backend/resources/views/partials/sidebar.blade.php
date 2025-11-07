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
      :class="sidebarOpen ? 'opacity-100' : 'opacity-0 w-0'">

    <button @click="sidebarOpen = !sidebarOpen" class="text-gray-300 focus:outline-none">
      <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7" />
      </svg>
    </button>
  </div>

  <!-- Botões rápidos -->
  <div class="flex space-x-2 px-4 py-2 border-b border-gray-700">
    <button class="flex-1 bg-green-600 hover:bg-green-700 text-xs font-medium py-1.5 rounded-md transition">
      + Avaliação
    </button>
    <button class="flex-1 bg-blue-600 hover:bg-blue-700 text-xs font-medium py-1.5 rounded-md transition">
      + Evolução
    </button>
  </div>

  <!-- Agrupamentos -->
  <nav class="flex-1 mt-2 text-[13px] font-medium space-y-2">

    <!-- GRUPO: NAVEGAÇÃO PRINCIPAL -->
    <div>
      <h3 class="text-[10px] font-semibold uppercase tracking-wide text-gray-400 px-4 py-1.5">
        <span x-show="sidebarOpen">Navegação Principal</span>
      </h3>
      <ul class="space-y-0.5">
        <x-sidebar-link icon="fa-calendar" label="Agenda" route="agenda" />
        <x-sidebar-link icon="fa-user" label="Pacientes" />
        <x-sidebar-link icon="fa-stethoscope" label="Atendimentos" />
        <x-sidebar-link icon="fa-camera" label="Procedimentos" />
      </ul>
    </div>

    <!-- GRUPO: FINANCEIRO -->
    <div>
      <h3 class="text-[10px] font-semibold uppercase tracking-wide text-gray-400 px-4 py-1.5">
        <span x-show="sidebarOpen">Financeiro</span>
      </h3>
      <ul class="space-y-0.5">
        <x-sidebar-link icon="fa-briefcase" label="Pacote de atendimentos" />
        <x-sidebar-link icon="fa-file-invoice" label="Resumo" />
        <x-sidebar-link icon="fa-credit-card" label="Movimento" />
        <x-sidebar-link icon="fa-truck" label="Fornecedores" />
        <x-sidebar-link icon="fa-file-alt" label="Notas fiscais (NFS-e)" />
      </ul>
    </div>

    <!-- GRUPO: RELATÓRIOS -->
    <div>
      <h3 class="text-[10px] font-semibold uppercase tracking-wide text-gray-400 px-4 py-1.5">
        <span x-show="sidebarOpen">Relatórios</span>
      </h3>
      <ul class="space-y-0.5">
        <x-sidebar-link icon="fa-clipboard-list" label="Atendimentos" />
        <x-sidebar-link icon="fa-chart-line" label="Financeiro" />
      </ul>
    </div>

    <!-- GRUPO: CONFIGURAÇÃO GERAL -->
    <div>
      <h3 class="text-[10px] font-semibold uppercase tracking-wide text-gray-400 px-4 py-1.5">
        <span x-show="sidebarOpen">Configuração Geral</span>
      </h3>
      <ul class="space-y-0.5">
        <x-sidebar-link icon="fa-users" label="Equipe" route="employees.index" />
        <x-sidebar-link icon="fa-cog" label="Configurações" />
      </ul>
    </div>

    <!-- GRUPO: PLANO FÁCIL -->
    <div>
      <h3 class="text-[10px] font-semibold uppercase tracking-wide text-gray-400 px-4 py-1.5">
        <span x-show="sidebarOpen">Plano Fácil</span>
      </h3>
      <ul class="space-y-0.5">
        <x-sidebar-link icon="fa-star" label="Assinar um Plano" class="bg-green-700 text-white hover:bg-green-600" />
        <x-sidebar-link icon="fa-question-circle" label="Central de ajuda" />
      </ul>
    </div>

  </nav>
</aside>
