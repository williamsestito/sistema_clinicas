<aside 
    x-data="{ sidebarOpen: true }"
    class="bg-[#0B111B] text-gray-100 flex flex-col transition-all duration-300 min-h-screen"
    :class="sidebarOpen ? 'w-64' : 'w-20'">

    {{-- 🔹 Logo + botão colapsar --}}
    <div class="flex items-center justify-between px-4 py-3 border-b border-gray-700">
        <img 
            src="{{ asset('assets/images/logo_branca.png') }}" 
            alt="Logo Clínica Fácil" 
            class="h-7 transition-all duration-300"
            :class="sidebarOpen ? 'opacity-100 w-auto' : 'opacity-0 w-0'">

        <button @click="sidebarOpen = !sidebarOpen" 
                class="text-gray-300 focus:outline-none">
            <i class="fa-solid fa-bars text-lg"></i>
        </button>
    </div>

    @php
        $user = Auth::user();
    @endphp

    <nav class="flex-1 mt-2 text-[13px] font-medium space-y-2">

        {{-- ========================================================= --}}
        {{-- 🧍 CLIENTE --}}
        {{-- ========================================================= --}}
        @if($user->role === 'client')

            <ul class="space-y-0.5">

                <x-sidebar-link icon="fa-calendar-check" 
                                label="Meus Agendamentos"
                                route="client.appointments"
                                :is-open="'sidebarOpen'" />

                <x-sidebar-link icon="fa-stethoscope"
                                label="Agendar Consulta"
                                route="client.schedule"
                                :is-open="'sidebarOpen'" />

                <x-sidebar-link icon="fa-user"
                                label="Meus Dados"
                                route="client.profile"
                                :is-open="'sidebarOpen'" />
            </ul>

            @php
                $fields = ['phone', 'address', 'city', 'state'];
                $incomplete = collect($fields)->some(fn($f) => empty($user->$f));
            @endphp

            @if($incomplete)
                <div 
                    x-show="sidebarOpen"
                    class="bg-yellow-100 text-yellow-800 text-xs mx-4 mt-3 p-2 rounded-md border border-yellow-300">
                    ⚠️ Complete seus dados em <strong>“Meus Dados”</strong> para continuar agendando.
                </div>
            @endif


        {{-- ========================================================= --}}
        {{-- 👩‍⚕️ PROFISSIONAL --}}
        {{-- ========================================================= --}}
        @elseif($user->role === 'professional')

            <h3 x-show="sidebarOpen" 
                class="text-[10px] font-semibold uppercase tracking-wide text-gray-400 px-4 py-1.5">
                Profissional
            </h3>

            <ul class="space-y-0.5">

                <x-sidebar-link icon="fa-chart-line" 
                                label="Dashboard"
                                route="professional.dashboard"
                                :is-open="'sidebarOpen'" />

                <x-sidebar-link icon="fa-inbox"
                                label="Solicitações"
                                route="professional.appointments.requests"
                                :is-open="'sidebarOpen'" />

                <x-sidebar-link icon="fa-calendar-days"
                                label="Minha Agenda"
                                route="professional.schedule"
                                :is-open="'sidebarOpen'" />

                <x-sidebar-link icon="fa-clock"
                                label="Configurar Agenda"
                                route="professional.schedule.config"
                                :is-open="'sidebarOpen'" />

                <x-sidebar-link icon="fa-user-injured"
                                label="Pacientes"
                                route="professional.pacients"
                                :is-open="'sidebarOpen'" />

                {{-- 🔥 ROTA AJUSTADA --}}
                <x-sidebar-link icon="fa-stethoscope"
                                label="Procedimentos"
                                route="professional.procedures.index"
                                :is-open="'sidebarOpen'" />
            </ul>

            <h3 x-show="sidebarOpen" 
                class="text-[10px] font-semibold uppercase tracking-wide text-gray-400 px-4 py-1.5 mt-3">
                Relatórios
            </h3>

            <ul class="space-y-0.5">
                <x-sidebar-link icon="fa-clipboard-list"
                                label="Atendimentos"
                                route="professional.reports.appointments"
                                :is-open="'sidebarOpen'" />

                <x-sidebar-link icon="fa-money-bill-wave"
                                label="Financeiro"
                                route="professional.reports.finance"
                                :is-open="'sidebarOpen'" />
            </ul>


        {{-- ========================================================= --}}
        {{-- 🧑‍💼 ADMIN / OWNER / FRONTDESK --}}
        {{-- ========================================================= --}}
        @elseif(in_array($user->role, ['admin', 'owner', 'frontdesk']))

            <h3 x-show="sidebarOpen"
                class="text-[10px] font-semibold uppercase tracking-wide text-gray-400 px-4 py-1.5">
                Painel
            </h3>

            <ul class="space-y-0.5">
                <x-sidebar-link icon="fa-chart-line"
                                label="Dashboard"
                                route="admin.dashboard"
                                :is-open="'sidebarOpen'" />

                <x-sidebar-link icon="fa-calendar"
                                label="Agenda"
                                route="admin.agenda"
                                :is-open="'sidebarOpen'" />
            </ul>

            <h3 x-show="sidebarOpen"
                class="text-[10px] font-semibold uppercase tracking-wide text-gray-400 px-4 py-1.5 mt-3">
                Cadastros
            </h3>

            <ul class="space-y-0.5">
                <x-sidebar-link icon="fa-user-injured"
                                label="Pacientes"
                                route="admin.patients.index"
                                :is-open="'sidebarOpen'" />

                <x-sidebar-link icon="fa-user-md"
                                label="Profissionais"
                                route="admin.professionals.index"
                                :is-open="'sidebarOpen'" />

                <x-sidebar-link icon="fa-briefcase-medical"
                                label="Serviços"
                                route="admin.services.index"
                                :is-open="'sidebarOpen'" />
            </ul>

            <h3 x-show="sidebarOpen"
                class="text-[10px] font-semibold uppercase tracking-wide text-gray-400 px-4 py-1.5 mt-3">
                Site / CMS
            </h3>

            <ul class="space-y-0.5">
                <x-sidebar-link icon="fa-image"
                                label="Banners"
                                route="admin.banners.index"
                                :is-open="'sidebarOpen'" />

                <x-sidebar-link icon="fa-layer-group"
                                label="Seções"
                                route="admin.sections.index"
                                :is-open="'sidebarOpen'" />

                <x-sidebar-link icon="fa-star"
                                label="Depoimentos"
                                route="admin.testimonials.index"
                                :is-open="'sidebarOpen'" />
            </ul>

            <h3 x-show="sidebarOpen"
                class="text-[10px] font-semibold uppercase tracking-wide text-gray-400 px-4 py-1.5 mt-3">
                Administração
            </h3>

            <ul class="space-y-0.5">
                <x-sidebar-link icon="fa-users-gear"
                                label="Colaboradores"
                                route="employees.index"
                                :is-open="'sidebarOpen'" />

                <x-sidebar-link icon="fa-building"
                                label="Dados da Clínica"
                                route="admin.settings"
                                :is-open="'sidebarOpen'" />
            </ul>

            {{-- Link externo para o site --}}
            <div class="border-t border-gray-700 mt-4 pt-3 px-4">
                <a href="http://localhost:5174" target="_blank" rel="noopener"
                   class="flex items-center gap-3 text-gray-300 hover:text-white hover:bg-gray-700/50 rounded-md px-3 py-2 transition-colors">
                    <i class="fa-solid fa-arrow-up-right-from-square text-sm"></i>
                    <span x-show="sidebarOpen" class="text-[13px]">Ver Site</span>
                </a>
            </div>

        @endif

    </nav>

</aside>
