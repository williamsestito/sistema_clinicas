@extends('layouts.app_client')

@section('title', 'Meus Agendamentos')

@section('content')
<div x-data="agendamentosClient()" x-init="init()" class="p-4 sm:p-6">

  <!-- Cabeçalho -->
  <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4 mb-6">
    <h1 class="text-xl sm:text-2xl font-semibold text-gray-700 flex items-center gap-2">
      📅 <span>Meus Agendamentos</span>
    </h1>

    <a href="{{ route('client.schedule') }}"
       class="w-full sm:w-auto text-center bg-green-600 hover:bg-green-700 text-white text-sm sm:text-base px-4 py-2 rounded-md flex items-center justify-center gap-2 transition">
      <i class="fa-solid fa-calendar-plus"></i> Novo Agendamento
    </a>
  </div>

  <!-- Abas -->
  <div class="flex flex-wrap border-b border-gray-200 mb-4">
    <button @click="aba = 'ativos'"
            :class="aba === 'ativos' ? 'border-b-2 border-green-600 text-green-700' : 'text-gray-500'"
            class="px-4 py-2 text-sm font-medium w-1/2 sm:w-auto text-center">
      Ativos
    </button>

    <button @click="aba = 'historico'"
            :class="aba === 'historico' ? 'border-b-2 border-green-600 text-green-700' : 'text-gray-500'"
            class="px-4 py-2 text-sm font-medium w-1/2 sm:w-auto text-center">
      Histórico
    </button>
  </div>


  <!-- ABA: ATIVOS -->
  <div x-show="aba === 'ativos'" x-transition>
    <template x-if="ativos.length === 0">
      <p class="text-gray-500 text-sm">Você não possui agendamentos futuros.</p>
    </template>

    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-4">
      <template x-for="item in ativos" :key="item.id">
        <div class="bg-white rounded-xl shadow p-4 border border-gray-100 flex flex-col justify-between hover:shadow-md transition">

          <div>
            <div class="flex items-center justify-between mb-2">
              <h2 class="font-semibold text-gray-800 text-sm sm:text-base">
                Consulta com <span class="text-green-700" x-text="item.professional"></span>
              </h2>

              <span class="text-xs px-2 py-0.5 rounded-md"
                    :class="statusClass(item.status)"
                    x-text="item.status_text"></span>
            </div>

            <p class="text-sm text-gray-600">
              <i class="fa-regular fa-calendar"></i>
              <span x-text="formatarData(item.start_at)"></span>
              às <span x-text="formatarHora(item.start_at)"></span>
            </p>

            <p class="text-sm text-gray-600">
              <i class="fa-solid fa-user-md"></i>
              <span x-text="item.service"></span>
            </p>
          </div>

          <div class="flex justify-end mt-3">
            <button @click="abrirDetalhes(item)"
                    class="text-sm text-blue-600 hover:underline flex items-center gap-1">
              <i class="fa-regular fa-eye"></i> Detalhes
            </button>
          </div>

        </div>
      </template>
    </div>
  </div>


  <!-- ABA: HISTÓRICO -->
  <div x-show="aba === 'historico'" x-transition>
    <template x-if="historico.length === 0">
      <p class="text-gray-500 text-sm">Nenhum agendamento encontrado no histórico.</p>
    </template>

    <div class="bg-white rounded-xl shadow overflow-x-auto border border-gray-100 mt-3">
      <table class="min-w-full text-sm text-gray-700">
        <thead class="bg-gray-50 border-b text-gray-600 uppercase text-xs">
          <tr>
            <th class="px-4 py-2 text-left">Data</th>
            <th class="px-4 py-2 text-left">Profissional</th>
            <th class="px-4 py-2 text-left">Serviço</th>
            <th class="px-4 py-2 text-left">Status</th>
            <th class="px-4 py-2 text-right">Ações</th>
          </tr>
        </thead>

        <tbody>
          <template x-for="item in historico" :key="item.id">
            <tr class="border-b hover:bg-gray-50 transition">
              <td class="px-4 py-2" x-text="formatarData(item.start_at)"></td>
              <td class="px-4 py-2" x-text="item.professional"></td>
              <td class="px-4 py-2" x-text="item.service"></td>
              <td class="px-4 py-2">
                <span class="text-xs px-2 py-0.5 rounded-md"
                      :class="statusClass(item.status)"
                      x-text="item.status_text"></span>
              </td>

              <td class="px-4 py-2 text-right">
                <button @click="abrirDetalhes(item)"
                        class="text-blue-600 hover:underline text-xs">
                  Ver detalhes
                </button>
              </td>
            </tr>
          </template>
        </tbody>
      </table>
    </div>
  </div>


  <!-- MODAL DETALHES -->
  <div x-show="mostrarModal" x-transition
       class="fixed inset-0 bg-black/40 flex items-center justify-center z-50 p-4 sm:p-0">

    <div class="bg-white rounded-lg shadow-lg w-full max-w-md p-6 relative">
      <button @click="mostrarModal = false"
              class="absolute top-3 right-3 text-gray-500 hover:text-gray-700">
        <i class="fa-solid fa-xmark"></i>
      </button>

      <h2 class="text-lg font-semibold text-gray-700 mb-4">
        📋 Detalhes da Consulta
      </h2>

      <div class="space-y-2 text-sm text-gray-600">
        <p><strong>Profissional:</strong> <span x-text="detalhes.professional"></span></p>
        <p><strong>Serviço:</strong> <span x-text="detalhes.service"></span></p>
        <p><strong>Data:</strong> <span x-text="formatarData(detalhes.start_at)"></span></p>
        <p><strong>Horário:</strong> <span x-text="formatarHora(detalhes.start_at)"></span></p>
        <p><strong>Status:</strong> <span x-text="detalhes.status_text"></span></p>
        <p><strong>Notas:</strong> <span x-text="detalhes.notes || '-'"></span></p>
      </div>

      <!-- BOTÃO CANCELAR -->
      <template x-if="detalhes.status === 'pending' || detalhes.status === 'confirmed'">
        <div class="flex justify-end mt-6">
          <button @click="cancelarConsulta(detalhes.id)"
                  class="bg-red-600 hover:bg-red-700 text-white text-sm px-4 py-2 rounded-md">
            Cancelar
          </button>
        </div>
      </template>

    </div>
  </div>

</div>



<!-- JS Alpine -->
<script>
function agendamentosClient() {
  return {

    aba: 'ativos',

    ativos: [],
    historico: [],
    mostrarModal: false,
    detalhes: {},

    async init() {
      await this.carregar();
    },

    async carregar() {
      const resp = await fetch('{{ route('client.appointments.json') }}');
      const json = await resp.json();

      if (json.success) {
        this.ativos    = json.ativos;
        this.historico = json.historico;
      }
    },

    abrirDetalhes(item) {
      this.detalhes = item;
      this.mostrarModal = true;
    },

    async cancelarConsulta(id) {
      const confirm = await Swal.fire({
        title: 'Cancelar agendamento?',
        text: 'Deseja realmente cancelar esta consulta?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc2626',
        confirmButtonText: 'Sim, cancelar',
        cancelButtonText: 'Voltar'
      });

      if (!confirm.isConfirmed) return;

      const resp = await fetch(`/client/appointments/${id}/cancel`, {
        method: "POST",
        headers: { "X-CSRF-TOKEN": '{{ csrf_token() }}' }
      });

      const json = await resp.json();

      if (json.success) {
        Swal.fire({
          icon: 'success',
          title: 'Consulta cancelada!',
          text: json.message,
        });

        this.mostrarModal = false;
        this.carregar();
      } else {
        Swal.fire({
          icon: 'error',
          title: 'Atenção',
          text: json.message || 'Não foi possível cancelar.',
        });
      }
    },

    formatarData(dt) {
      const d = new Date(dt);
      return d.toLocaleDateString('pt-BR');
    },

    formatarHora(dt) {
      const d = new Date(dt);
      return d.toLocaleTimeString('pt-BR', { hour: '2-digit', minute: '2-digit' });
    },

    statusClass(status) {
      const s = status.toLowerCase();

      return {
        'bg-yellow-100 text-yellow-700': s === 'pending',
        'bg-blue-100 text-blue-700':    s === 'confirmed',
        'bg-green-100 text-green-700':  s === 'done',
        'bg-red-100 text-red-700':      s === 'cancelled',
        'bg-gray-200 text-gray-700':    true
      };
    }

  }
}
</script>

@endsection
