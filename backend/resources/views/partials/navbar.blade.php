<header class="bg-white shadow flex items-center justify-between px-4 py-3 gap-4" x-data="patientSearch()">

  {{-- Busca de pacientes com autocomplete --}}
  <div class="relative flex items-center w-full md:w-1/2">
    <div class="relative w-full">
      <i class="fa fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm"></i>
      <input type="text" placeholder="Pesquisar Paciente..." x-model="query"
             @input.debounce.300ms="search()"
             @keydown.enter.prevent="goToSelected()"
             @keydown.arrow-down.prevent="moveDown()"
             @keydown.arrow-up.prevent="moveUp()"
             @keydown.escape="results = []; show = false"
             @focus="if(results.length) show = true"
             @click.away="show = false"
             class="w-full pl-9 pr-3 py-2 border rounded-lg text-sm focus:ring-2 focus:ring-green-400">

      {{-- Dropdown de resultados --}}
      <div x-show="show && results.length > 0" x-transition
           class="absolute top-full left-0 right-0 mt-1 bg-white border rounded-lg shadow-lg z-50 max-h-80 overflow-y-auto">
        <template x-for="(p, idx) in results" :key="p.id">
          <div class="border-b last:border-b-0">
            {{-- Nome do paciente --}}
            <div class="flex items-center gap-3 px-4 pt-2.5 pb-1">
              <div class="w-8 h-8 rounded-full bg-green-100 text-green-700 flex items-center justify-center text-xs font-bold"
                   x-text="p.name.charAt(0).toUpperCase()"></div>
              <div class="flex-1 min-w-0">
                <p class="text-sm font-medium text-gray-800 truncate" x-text="p.name"></p>
                <p class="text-xs text-gray-400 truncate" x-text="p.phone || p.email || ''"></p>
              </div>
            </div>
            {{-- Ações --}}
            <div class="flex items-center gap-1 px-4 pb-2.5 pt-1 ml-11">
              <a :href="editUrl(p.id)"
                 class="inline-flex items-center gap-1 px-2.5 py-1 rounded-md text-xs font-medium bg-gray-100 text-gray-600 hover:bg-gray-200 transition">
                <i class="fa fa-pen-to-square"></i> Editar
              </a>
              <a :href="agendaUrl(p.id)"
                 class="inline-flex items-center gap-1 px-2.5 py-1 rounded-md text-xs font-medium bg-blue-50 text-blue-600 hover:bg-blue-100 transition">
                <i class="fa fa-calendar-plus"></i> Agendar
              </a>
              <a :href="financialUrl(p.id)"
                 class="inline-flex items-center gap-1 px-2.5 py-1 rounded-md text-xs font-medium bg-emerald-50 text-emerald-600 hover:bg-emerald-100 transition">
                <i class="fa fa-money-bill"></i> Pagamento
              </a>
            </div>
          </div>
        </template>
      </div>

      {{-- Nenhum resultado — sugerir cadastro --}}
      <div x-show="show && results.length === 0 && query.length >= 2 && !loading" x-transition
           class="absolute top-full left-0 right-0 mt-1 bg-white border rounded-lg shadow-lg z-50 px-4 py-3">
        <p class="text-sm text-gray-400 text-center mb-2">Nenhum paciente encontrado.</p>
        <a :href="createUrl()"
           class="flex items-center justify-center gap-2 w-full px-3 py-2 rounded-lg text-sm font-medium bg-green-50 text-green-700 hover:bg-green-100 transition">
          <i class="fa fa-user-plus"></i>
          Cadastrar "<span x-text="query" class="font-semibold"></span>"
        </a>
      </div>

      {{-- Loading --}}
      <div x-show="loading"
           class="absolute right-3 top-1/2 -translate-y-1/2">
        <i class="fa fa-spinner fa-spin text-gray-400 text-sm"></i>
      </div>
    </div>
  </div>

  {{-- Lado direito: WhatsApp stats + user menu --}}
  <div class="flex items-center gap-4">

    {{-- WhatsApp stats --}}
    @if(in_array(Auth::user()->role, ['admin', 'owner']))
    <a href="{{ route('admin.whatsapp.dashboard') }}" class="flex items-center gap-3 hover:opacity-80 transition" title="WhatsApp — Envios de hoje">
      <span class="flex items-center gap-1 text-sm font-medium">
        <i class="fa-solid fa-paper-plane text-green-500"></i>
        <span class="text-green-600">{{ $waStats->sent ?? 0 }}</span>
      </span>
      <span class="flex items-center gap-1 text-sm font-medium">
        <i class="fa-solid fa-circle-exclamation text-red-500"></i>
        <span class="text-red-600">{{ $waStats->failed ?? 0 }}</span>
      </span>
    </a>
    @endif

    {{-- User menu --}}
    <div x-data="{ userMenuOpen: false }" class="relative">
      <button @click="userMenuOpen = !userMenuOpen" class="flex items-center space-x-2 focus:outline-none">
        <img src="https://ui-avatars.com/api/?name={{ urlencode(Auth::user()->name) }}"
             class="w-8 h-8 rounded-full" alt="Avatar">
        <span class="hidden md:inline text-gray-700 text-sm font-medium">{{ Auth::user()->name }}</span>
        <i class="fa fa-chevron-down text-gray-400 text-xs hidden md:inline"></i>
      </button>
      <div
        x-show="userMenuOpen"
        @click.away="userMenuOpen = false"
        class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg border border-gray-200 py-2 z-50"
        x-transition>
        @if(in_array(Auth::user()->role, ['admin', 'owner']))
        <a href="{{ route('admin.profile') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
          <i class="fa fa-user-circle mr-1 text-gray-400"></i> Meu Perfil
        </a>
        @endif
        <form method="POST" action="{{ route('logout') }}">
          @csrf
          <button type="submit" class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
            <i class="fa fa-sign-out-alt mr-1 text-gray-400"></i> Sair
          </button>
        </form>
      </div>
    </div>
  </div>
</header>

<script>
function patientSearch() {
  return {
    query: '',
    results: [],
    show: false,
    loading: false,
    selectedIdx: -1,

    async search() {
      if (this.query.length < 2) {
        this.results = [];
        this.show = false;
        return;
      }
      this.loading = true;
      try {
        const resp = await axios.get('{{ route("admin.patients.searchJson") }}', {
          params: { q: this.query }
        });
        this.results = resp.data;
        this.selectedIdx = -1;
        this.show = true;
      } catch (e) {
        this.results = [];
      }
      this.loading = false;
    },

    editUrl(id) {
      return '{{ url("admin/patients") }}/' + id + '/edit';
    },

    agendaUrl(id) {
      return '{{ url("admin/agenda") }}?client_id=' + id;
    },

    financialUrl(id) {
      return '{{ url("admin/financial/create") }}?client_id=' + id;
    },

    createUrl() {
      return '{{ url("admin/patients/create") }}?name=' + encodeURIComponent(this.query);
    },

    moveDown() {
      if (this.selectedIdx < this.results.length - 1) this.selectedIdx++;
    },
    moveUp() {
      if (this.selectedIdx > 0) this.selectedIdx--;
    },

    goToSelected() {
      if (this.selectedIdx >= 0 && this.results[this.selectedIdx]) {
        window.location.href = this.editUrl(this.results[this.selectedIdx].id);
      } else if (this.results.length === 1) {
        window.location.href = this.editUrl(this.results[0].id);
      }
    }
  }
}
</script>
