<aside 
    x-data="{ 
        sidebarOpen: true,
        menu: '' 
    }"
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

    <nav class="flex-1 mt-2 text-[13px] font-medium space-y-1 overflow-y-auto sidebar-scroll">

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

            {{-- Profissional --}}
            <div>
                <button @click="menu = menu === 'prof' ? '' : 'prof'" x-show="sidebarOpen"
                        class="w-full flex items-center justify-between px-4 py-2 text-[10px] font-semibold uppercase tracking-wide text-gray-400 hover:text-gray-200 transition-colors">
                    <span>Profissional</span>
                    <i class="fa fa-chevron-down text-[9px] transition-transform duration-200" :class="menu === 'prof' ? 'rotate-180' : ''"></i>
                </button>
                <ul x-show="!sidebarOpen || menu === 'prof'" x-collapse class="space-y-0.5">
                    <x-sidebar-link icon="fa-chart-line"    label="Dashboard"        route="professional.dashboard"              :is-open="'sidebarOpen'" />
                    <x-sidebar-link icon="fa-inbox"         label="Solicitações"     route="professional.appointments.requests"  :is-open="'sidebarOpen'" />
                    <x-sidebar-link icon="fa-calendar-days" label="Minha Agenda"     route="professional.schedule"               :is-open="'sidebarOpen'" />
                    <x-sidebar-link icon="fa-clock"         label="Configurar Agenda" route="professional.schedule.config"       :is-open="'sidebarOpen'" />
                    <x-sidebar-link icon="fa-user-injured"  label="Pacientes"        route="professional.pacients"               :is-open="'sidebarOpen'" />
                    <x-sidebar-link icon="fa-stethoscope"   label="Procedimentos"    route="professional.procedures.index"       :is-open="'sidebarOpen'" />
                </ul>
            </div>

            {{-- Relatórios --}}
            <div>
                <button @click="menu = menu === 'profRel' ? '' : 'profRel'" x-show="sidebarOpen"
                        class="w-full flex items-center justify-between px-4 py-2 text-[10px] font-semibold uppercase tracking-wide text-gray-400 hover:text-gray-200 transition-colors">
                    <span>Relatórios</span>
                    <i class="fa fa-chevron-down text-[9px] transition-transform duration-200" :class="menu === 'profRel' ? 'rotate-180' : ''"></i>
                </button>
                <ul x-show="!sidebarOpen || menu === 'profRel'" x-collapse class="space-y-0.5">
                    <x-sidebar-link icon="fa-clipboard-list"  label="Atendimentos" route="professional.reports.appointments" :is-open="'sidebarOpen'" />
                    <x-sidebar-link icon="fa-money-bill-wave" label="Financeiro"    route="professional.reports.finance"      :is-open="'sidebarOpen'" />
                </ul>
            </div>


        {{-- ========================================================= --}}
        {{-- 🧑‍💼 ADMIN / OWNER / FRONTDESK --}}
        {{-- ========================================================= --}}
        @elseif(in_array($user->role, ['admin', 'owner', 'frontdesk']))

            {{-- Painel (sempre visível, sem toggle) --}}
            <ul class="space-y-0.5 pb-1">
                <x-sidebar-link icon="fa-chart-line" label="Dashboard" route="admin.dashboard" :is-open="'sidebarOpen'" />
                <x-sidebar-link icon="fa-calendar"   label="Agenda"    route="admin.agenda"    :is-open="'sidebarOpen'" />
            </ul>

            {{-- Cadastros --}}
            <div>
                <button @click="menu = menu === 'cad' ? '' : 'cad'" x-show="sidebarOpen"
                        class="w-full flex items-center justify-between px-4 py-2 text-[10px] font-semibold uppercase tracking-wide text-gray-400 hover:text-gray-200 transition-colors">
                    <span>Cadastros</span>
                    <i class="fa fa-chevron-down text-[9px] transition-transform duration-200" :class="menu === 'cad' ? 'rotate-180' : ''"></i>
                </button>
                <ul x-show="!sidebarOpen || menu === 'cad'" x-collapse class="space-y-0.5">
                    <x-sidebar-link icon="fa-user-injured"      label="Pacientes"    route="admin.patients.index"      :is-open="'sidebarOpen'" />
                    <x-sidebar-link icon="fa-user-md"           label="Profissionais" route="admin.professionals.index" :is-open="'sidebarOpen'" />
                    <x-sidebar-link icon="fa-briefcase-medical" label="Serviços"     route="admin.services.index"      :is-open="'sidebarOpen'" />
                </ul>
            </div>

            {{-- Site / CMS --}}
            <div>
                <button @click="menu = menu === 'cms' ? '' : 'cms'" x-show="sidebarOpen"
                        class="w-full flex items-center justify-between px-4 py-2 text-[10px] font-semibold uppercase tracking-wide text-gray-400 hover:text-gray-200 transition-colors">
                    <span>Site / CMS</span>
                    <i class="fa fa-chevron-down text-[9px] transition-transform duration-200" :class="menu === 'cms' ? 'rotate-180' : ''"></i>
                </button>
                <ul x-show="!sidebarOpen || menu === 'cms'" x-collapse class="space-y-0.5">
                    <x-sidebar-link icon="fa-image"       label="Banners"     route="admin.banners.index"      :is-open="'sidebarOpen'" />
                    <x-sidebar-link icon="fa-layer-group"  label="Seções"      route="admin.sections.index"     :is-open="'sidebarOpen'" />
                    <x-sidebar-link icon="fa-star"         label="Depoimentos" route="admin.testimonials.index" :is-open="'sidebarOpen'" />
                </ul>
            </div>

            {{-- Financeiro --}}
            <div>
                <button @click="menu = menu === 'fin' ? '' : 'fin'" x-show="sidebarOpen"
                        class="w-full flex items-center justify-between px-4 py-2 text-[10px] font-semibold uppercase tracking-wide text-gray-400 hover:text-gray-200 transition-colors">
                    <span>Financeiro</span>
                    <i class="fa fa-chevron-down text-[9px] transition-transform duration-200" :class="menu === 'fin' ? 'rotate-180' : ''"></i>
                </button>
                <ul x-show="!sidebarOpen || menu === 'fin'" x-collapse class="space-y-0.5">
                    <x-sidebar-link icon="fa-dollar-sign" label="Lançamentos" route="admin.financial.index"     :is-open="'sidebarOpen'" />
                    <x-sidebar-link icon="fa-tags"        label="Categorias"  route="admin.financial.categories" :is-open="'sidebarOpen'" />
                </ul>
            </div>

            {{-- Relatórios --}}
            <div>
                <button @click="menu = menu === 'rel' ? '' : 'rel'" x-show="sidebarOpen"
                        class="w-full flex items-center justify-between px-4 py-2 text-[10px] font-semibold uppercase tracking-wide text-gray-400 hover:text-gray-200 transition-colors">
                    <span>Relatórios</span>
                    <i class="fa fa-chevron-down text-[9px] transition-transform duration-200" :class="menu === 'rel' ? 'rotate-180' : ''"></i>
                </button>
                <ul x-show="!sidebarOpen || menu === 'rel'" x-collapse class="space-y-0.5">
                    <x-sidebar-link icon="fa-clipboard-list"  label="Atendimentos"   route="admin.reports.appointments" :is-open="'sidebarOpen'" />
                    <x-sidebar-link icon="fa-money-bill-wave" label="Rel. Financeiro" route="admin.reports.financial"    :is-open="'sidebarOpen'" />
                </ul>
            </div>

            {{-- Administração --}}
            <div>
                <button @click="menu = menu === 'adm' ? '' : 'adm'" x-show="sidebarOpen"
                        class="w-full flex items-center justify-between px-4 py-2 text-[10px] font-semibold uppercase tracking-wide text-gray-400 hover:text-gray-200 transition-colors">
                    <span>Administração</span>
                    <i class="fa fa-chevron-down text-[9px] transition-transform duration-200" :class="menu === 'adm' ? 'rotate-180' : ''"></i>
                </button>
                <ul x-show="!sidebarOpen || menu === 'adm'" x-collapse class="space-y-0.5">
                    <x-sidebar-link icon="fa-users-gear" label="Colaboradores"    route="employees.index" :is-open="'sidebarOpen'" />
                    <x-sidebar-link icon="fa-hospital"   label="Dados da Clínica" route="admin.clinic"    :is-open="'sidebarOpen'" />
                    <x-sidebar-link icon="fa-building"   label="Config. do Site"  route="admin.settings"  :is-open="'sidebarOpen'" />
                </ul>
            </div>

            {{-- WhatsApp --}}
            <div>
                <button @click="menu = menu === 'wpp' ? '' : 'wpp'" x-show="sidebarOpen"
                        class="w-full flex items-center justify-between px-4 py-2 text-[10px] font-semibold uppercase tracking-wide text-gray-400 hover:text-gray-200 transition-colors">
                    <span>WhatsApp</span>
                    <i class="fa fa-chevron-down text-[9px] transition-transform duration-200" :class="menu === 'wpp' ? 'rotate-180' : ''"></i>
                </button>
                <ul x-show="!sidebarOpen || menu === 'wpp'" x-collapse class="space-y-0.5">
                    <x-sidebar-link icon="fa-brands fa-whatsapp" label="Dashboard"    route="admin.whatsapp.dashboard" :is-open="'sidebarOpen'" />
                    <x-sidebar-link icon="fa-envelope"           label="Mensagens"     route="admin.whatsapp.messages"  :is-open="'sidebarOpen'" />
                    <x-sidebar-link icon="fa-bullhorn"           label="Campanhas"     route="admin.whatsapp.campaigns" :is-open="'sidebarOpen'" />
                    <x-sidebar-link icon="fa-gear"               label="Configurações" route="admin.whatsapp.settings"  :is-open="'sidebarOpen'" />
                </ul>
            </div>

            {{-- Logs --}}
            <div>
                <button @click="menu = menu === 'logs' ? '' : 'logs'" x-show="sidebarOpen"
                        class="w-full flex items-center justify-between px-4 py-2 text-[10px] font-semibold uppercase tracking-wide text-gray-400 hover:text-gray-200 transition-colors">
                    <span>Logs</span>
                    <i class="fa fa-chevron-down text-[9px] transition-transform duration-200" :class="menu === 'logs' ? 'rotate-180' : ''"></i>
                </button>
                <ul x-show="!sidebarOpen || menu === 'logs'" x-collapse class="space-y-0.5">
                    <x-sidebar-link icon="fa-clock-rotate-left" label="Logs Agendamentos" route="admin.logs.appointments"  :is-open="'sidebarOpen'" />
                    <x-sidebar-link icon="fa-bell"              label="Logs Notificações" route="admin.logs.notifications" :is-open="'sidebarOpen'" />
                </ul>
            </div>

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

<style>
.sidebar-scroll::-webkit-scrollbar { width: 4px; }
.sidebar-scroll::-webkit-scrollbar-track { background: transparent; }
.sidebar-scroll::-webkit-scrollbar-thumb { background: #374151; border-radius: 4px; }
.sidebar-scroll::-webkit-scrollbar-thumb:hover { background: #4B5563; }
</style>
