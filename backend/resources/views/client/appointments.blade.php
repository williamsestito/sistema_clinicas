@extends('layouts.app_client')

@section('title', 'Meus Agendamentos')

@section('content')
<div x-data="agendamentosClient()" class="p-4 sm:p-6">

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
            class="px-4 py-2 text-sm font-medium focus:outline-none w-1/2 sm:w-auto text-center">
      Ativos
    </button>
    <button @click="aba = 'historico'"
            :class="aba === 'historico' ? 'border-b-2 border-green-600 text-green-700' : 'text-gray-500'"
            class="px-4 py-2 text-sm font-medium focus:outline-none w-1/2 sm:w-auto text-center">
      Histórico
    </button>
  </div>

  <!-- ABA: AGENDAMENTOS ATIVOS -->
  <div x-show="aba === 'ativos'" x-transition>
    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-4">

      @foreach (range(1, 3) as $a)
      <div class="bg-white rounded-xl shadow p-4 border border-gray-100 flex flex-col justify-between hover:shadow-md transition">
        <div>
          <div class="flex items-center justify-between mb-2">
            <h2 class="font-semibold text-gray-800 text-sm sm:text-base">
              Consulta com <span class="text-green-700">Dra. Juliana</span>
            </h2>
            <span class="text-xs bg-yellow-100 text-yellow-700 px-2 py-0.5 rounded-md whitespace-nowrap">Pendente</span>
          </div>
          <p class="text-sm text-gray-600"><i class="fa-regular fa-calendar"></i> 10/11/2025 às 14:00</p>
          <p class="text-sm text-gray-600"><i class="fa-solid fa-user-md"></i> Dermatologia</p>
        </div>

        <div class="flex justify-end mt-3">
          <button @click="abrirDetalhes({
              profissional: 'Dra. Juliana',
              especialidade: 'Dermatologia',
              data: '2025-11-10',
              hora: '14:00',
              status: 'Pendente',
              endereco: 'Av. Paulista, 1000 - São Paulo/SP'
            })"
            class="text-sm text-blue-600 hover:underline flex items-center gap-1">
            <i class="fa-regular fa-eye"></i> Detalhes
          </button>
        </div>
      </div>
      @endforeach

    </div>
  </div>

  <!-- ABA: HISTÓRICO -->
  <div x-show="aba === 'historico'" x-transition>
    <div class="bg-white rounded-xl shadow p-4 mb-4 border border-gray-100 space-y-3 sm:space-y-0 sm:flex sm:flex-wrap sm:items-end sm:gap-3">
      <div class="w-full sm:w-auto">
        <label class="text-xs text-gray-500">Data</label>
        <input type="date" x-model="filtro.data" class="border rounded-md px-3 py-1.5 text-sm w-full sm:w-auto">
      </div>

      <div class="w-full sm:w-auto">
        <label class="text-xs text-gray-500">Profissional</label>
        <select x-model="filtro.profissional" class="border rounded-md px-3 py-1.5 text-sm w-full sm:w-auto">
          <option value="">Todos</option>
          <option value="juliana">Dra. Juliana</option>
          <option value="marcos">Dr. Marcos</option>
        </select>
      </div>

      <div class="w-full sm:w-auto">
        <label class="text-xs text-gray-500">Status</label>
        <select x-model="filtro.status" class="border rounded-md px-3 py-1.5 text-sm w-full sm:w-auto">
          <option value="">Todos</option>
          <option value="concluido">Concluído</option>
          <option value="cancelado">Cancelado</option>
          <option value="faltou">Faltou</option>
        </select>
      </div>

      <div class="w-full sm:w-auto sm:ml-auto">
        <button class="bg-gray-800 hover:bg-gray-900 text-white px-4 py-1.5 rounded-md text-sm w-full sm:w-auto">
          <i class="fa-solid fa-filter mr-1"></i> Filtrar
        </button>
      </div>
    </div>

    <div class="bg-white rounded-xl shadow overflow-x-auto border border-gray-100">
      <table class="min-w-full text-sm text-gray-700">
        <thead class="bg-gray-50 border-b text-gray-600 uppercase text-xs">
          <tr>
            <th class="px-4 py-2 text-left whitespace-nowrap">Data</th>
            <th class="px-4 py-2 text-left whitespace-nowrap">Profissional</th>
            <th class="px-4 py-2 text-left whitespace-nowrap">Especialidade</th>
            <th class="px-4 py-2 text-left whitespace-nowrap">Status</th>
            <th class="px-4 py-2 text-right whitespace-nowrap">Ações</th>
          </tr>
        </thead>
        <tbody>
          @foreach (range(1,5) as $h)
          <tr class="border-b hover:bg-gray-50 transition">
            <td class="px-4 py-2">05/11/2025</td>
            <td class="px-4 py-2">Dr. Marcos</td>
            <td class="px-4 py-2">Ortopedia</td>
            <td class="px-4 py-2">
              <span class="text-xs bg-gray-100 text-gray-700 px-2 py-0.5 rounded-md">Concluído</span>
            </td>
            <td class="px-4 py-2 text-right">
              <button class="text-blue-600 hover:underline text-xs">Ver detalhes</button>
            </td>
          </tr>
          @endforeach
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

      <h2 class="text-lg font-semibold text-gray-700 mb-4">📋 Detalhes da Consulta</h2>

      <div class="space-y-2 text-sm text-gray-600">
        <p><strong>Profissional:</strong> <span x-text="detalhes.profissional"></span></p>
        <p><strong>Especialidade:</strong> <span x-text="detalhes.especialidade"></span></p>
        <p><strong>Data:</strong> <span x-text="formatarData(detalhes.data)"></span></p>
        <p><strong>Horário:</strong> <span x-text="detalhes.hora"></span></p>
        <p><strong>Status:</strong> <span x-text="detalhes.status"></span></p>
        <p><strong>Endereço da clínica:</strong> <span x-text="detalhes.endereco"></span></p>
      </div>

      <!-- Timeline -->
      <div class="mt-6">
        <div class="flex items-center justify-between text-xs text-gray-500">
          <template x-for="(etapa, index) in 3" :key="index">
            <div class="flex flex-col items-center flex-1">
              <div :class="['w-3 h-3 rounded-full', statusEtapa(index + 1)]"></div>
              <span class="mt-1 text-[10px] sm:text-xs" x-text="['Enviado', 'Aguardando aceite', 'Confirmado'][index]"></span>
            </div>
          </template>
        </div>
      </div>

      <!-- Botões -->
      <div class="flex flex-col sm:flex-row justify-end gap-3 mt-6">
        <button @click="cancelarConsulta"
                class="bg-red-600 hover:bg-red-700 text-white text-sm px-4 py-2 rounded-md flex items-center justify-center gap-1">
          <i class="fa-solid fa-ban"></i> Cancelar
        </button>
        <a href="{{ route('client.schedule') }}"
           class="bg-green-600 hover:bg-green-700 text-white text-sm px-4 py-2 rounded-md flex items-center justify-center gap-1">
          <i class="fa-solid fa-rotate-right"></i> Reagendar
        </a>
      </div>
    </div>
  </div>
</div>

<!-- Scripts -->
<script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
function agendamentosClient() {
  return {
    aba: 'ativos',
    mostrarModal: false,
    detalhes: {},
    filtro: { data: '', profissional: '', status: '' },

    abrirDetalhes(agendamento) {
      this.detalhes = agendamento;
      this.mostrarModal = true;
    },

    cancelarConsulta() {
      Swal.fire({
        title: 'Cancelar agendamento?',
        text: 'Deseja realmente cancelar esta consulta?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc2626',
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'Sim, cancelar',
        cancelButtonText: 'Voltar'
      }).then((result) => {
        if (result.isConfirmed) {
          this.mostrarModal = false;
          Swal.fire({
            icon: 'success',
            title: 'Consulta cancelada!',
            text: 'Seu agendamento foi cancelado com sucesso.',
            confirmButtonColor: '#16a34a'
          });
        }
      });
    },

    formatarData(data) {
      const d = new Date(data);
      return d.toLocaleDateString('pt-BR');
    },

    statusEtapa(etapa) {
      const status = this.detalhes.status?.toLowerCase();
      if (status === 'pendente') return etapa === 1 ? 'bg-green-600' : 'bg-gray-300';
      if (status === 'aguardando') return etapa <= 2 ? 'bg-green-600' : 'bg-gray-300';
      if (status === 'confirmado') return 'bg-green-600';
      return 'bg-gray-300';
    }
  }
}
</script>
@endsection
