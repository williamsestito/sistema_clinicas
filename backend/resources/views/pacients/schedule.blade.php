@extends('layouts.app')

@section('title', 'Agendar Consulta')

@section('content')
<div x-data="agendamento()" class="p-6 space-y-6">

  <!-- Cabeçalho -->
  <div class="flex flex-col md:flex-row md:items-center justify-between mb-2">
    <h1 class="text-xl font-semibold text-gray-700">🩺 Agendar Consulta</h1>
    <p class="text-sm text-gray-500 mt-2 md:mt-0">
      Escolha um profissional e um horário disponível. O agendamento ficará <b>pendente</b> até confirmação.
    </p>
  </div>

  <!-- Etapa 1: Localização -->
  <div class="bg-white p-6 rounded-lg shadow border border-gray-100">
    <h2 class="font-semibold text-gray-800 mb-3">1️⃣ Escolha sua localização</h2>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
      <div>
        <label class="block text-xs font-medium text-gray-500 mb-1">UF</label>
        <select x-model="uf" @change="carregarCidades()" class="w-full border rounded-md px-3 py-2 text-sm">
          <option value="">Selecione</option>
          <template x-for="estado in ufs" :key="estado.sigla">
            <option :value="estado.sigla" x-text="estado.nome"></option>
          </template>
        </select>
      </div>

      <div>
        <label class="block text-xs font-medium text-gray-500 mb-1">Cidade</label>
        <select x-model="cidade" :disabled="!uf" class="w-full border rounded-md px-3 py-2 text-sm">
          <option value="">Selecione</option>
          <template x-for="c in cidades" :key="c">
            <option x-text="c"></option>
          </template>
        </select>
      </div>
    </div>
  </div>

  <!-- Etapa 2: Profissional / Especialidade / Procedimento -->
  <template x-if="cidade">
    <div class="bg-white p-6 rounded-lg shadow border border-gray-100">
      <h2 class="font-semibold text-gray-800 mb-3">2️⃣ Escolha o profissional, especialidade e procedimento</h2>
      <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div>
          <label class="block text-xs font-medium text-gray-500 mb-1">Profissional</label>
          <select x-model="profissional" class="w-full border rounded-md px-3 py-2 text-sm">
            <option value="">Selecione</option>
            <template x-for="p in profissionais" :key="p.id">
              <option :value="p.id" x-text="p.nome"></option>
            </template>
          </select>
        </div>

        <div>
          <label class="block text-xs font-medium text-gray-500 mb-1">Especialidade</label>
          <select x-model="especialidade" class="w-full border rounded-md px-3 py-2 text-sm">
            <option value="">Selecione</option>
            <template x-for="e in especialidades" :key="e">
              <option x-text="e"></option>
            </template>
          </select>
        </div>

        <div>
          <label class="block text-xs font-medium text-gray-500 mb-1">Procedimento</label>
          <select x-model="procedimento" class="w-full border rounded-md px-3 py-2 text-sm">
            <option value="">Selecione</option>
            <template x-for="proc in procedimentos" :key="proc">
              <option x-text="proc"></option>
            </template>
          </select>
        </div>
      </div>
    </div>
  </template>

  <!-- Etapa 3: Escolher data -->
  <template x-if="profissional && procedimento">
    <div class="bg-white p-6 rounded-lg shadow border border-gray-100">
      <h2 class="font-semibold text-gray-800 mb-3">3️⃣ Escolha o dia desejado</h2>
      <div class="flex items-center gap-3">
        <input type="date" x-model="dataSelecionada" class="border rounded-md px-3 py-2 text-sm">
        <button @click="carregarHorarios()" 
                class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md text-sm font-medium">
          Ver horários disponíveis
        </button>
      </div>
    </div>
  </template>

  <!-- Etapa 4: Agenda -->
  <template x-if="horarios.length > 0">
    <div class="bg-white p-6 rounded-lg shadow border border-gray-100">
      <h2 class="font-semibold text-gray-800 mb-3">
        4️⃣ Horários disponíveis para <span x-text="dataSelecionada"></span>
      </h2>

      <div class="grid grid-cols-2 md:grid-cols-4 gap-3 mt-3">
        <template x-for="hora in horarios" :key="hora">
          <div @click="selecionarHorario(hora)"
               class="border rounded-lg px-4 py-2 text-center cursor-pointer transition"
               :class="horarioSelecionado === hora ? 'bg-green-600 text-white border-green-700' : 'hover:bg-green-50'">
            <span x-text="hora"></span>
          </div>
        </template>
      </div>

      <div class="flex justify-end mt-6">
        <button @click="confirmarAgendamento"
                class="bg-green-600 hover:bg-green-700 text-white px-5 py-2 rounded-md text-sm font-medium">
          Confirmar Pré-Agendamento
        </button>
      </div>

      <p class="text-xs text-gray-500 mt-3">
        O agendamento será enviado para o profissional e ficará com status <b>pendente</b> até confirmação.
      </p>
    </div>
  </template>
</div>

<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
function agendamento() {
  return {
    uf: '',
    cidade: '',
    profissional: '',
    especialidade: '',
    procedimento: '',
    dataSelecionada: '',
    horarioSelecionado: '',
    horarios: [],
    diasBloqueados: ['2025-11-12'], // Exemplo de dia sem atendimento

    ufs: [
      { nome: 'Santa Catarina', sigla: 'SC' },
      { nome: 'Paraná', sigla: 'PR' },
      { nome: 'São Paulo', sigla: 'SP' },
    ],
    cidades: [],
    profissionais: [
      { id: 1, nome: 'Dra. Juliana Souza' },
      { id: 2, nome: 'Dr. Marcos Lima' },
    ],
    especialidades: ['Dermatologia', 'Fisioterapia', 'Nutrição'],
    procedimentos: ['Consulta inicial', 'Retorno', 'Avaliação corporal'],

    carregarCidades() {
      const cidadesPorUF = {
        SC: ['Joinville', 'Florianópolis', 'Blumenau'],
        PR: ['Curitiba', 'Londrina', 'Maringá'],
        SP: ['São Paulo', 'Campinas', 'Santos'],
      };
      this.cidades = cidadesPorUF[this.uf] || [];
      this.cidade = '';
    },

    carregarHorarios() {
      if (!this.dataSelecionada) {
        Swal.fire({
          icon: 'warning',
          title: 'Selecione a data',
          text: 'Escolha o dia para visualizar os horários disponíveis.',
          confirmButtonColor: '#16a34a'
        });
        return;
      }

      // Caso profissional nao libere o dia
      if (this.diasBloqueados.includes(this.dataSelecionada)) {
        this.horarios = [];
        Swal.fire({
          icon: 'info',
          title: 'Profissional indisponível',
          html: `
            <p class="text-gray-700 text-sm">A profissional <b>Dra. Juliana Souza</b> não realizará atendimentos em 
            <b>${new Date(this.dataSelecionada).toLocaleDateString('pt-BR')}</b>.</p>
            <p class="text-gray-500 text-xs mt-2">Por favor, selecione outra data disponível.</p>
          `,
          confirmButtonText: 'Entendido',
          confirmButtonColor: '#16a34a'
        });
        return;
      }

      // Caso normal - ajustaremos com dados do banco 
      this.horarios = ['08:00', '09:00', '10:30', '11:30', '13:00', '14:30', '16:00', '17:30'];
    },

    selecionarHorario(hora) {
      this.horarioSelecionado = hora;
    },

    confirmarAgendamento() {
      if (!this.uf || !this.cidade || !this.profissional || !this.procedimento || !this.dataSelecionada || !this.horarioSelecionado) {
        Swal.fire({
          icon: 'error',
          title: 'Campos obrigatórios',
          text: 'Preencha todas as informações antes de confirmar o pré-agendamento.',
          confirmButtonColor: '#16a34a'
        });
        return;
      }

      const profissionalSelecionado = this.profissionais.find(p => p.id == this.profissional)?.nome || 'Profissional não identificado';
      const dataFormatada = new Date(this.dataSelecionada).toLocaleDateString('pt-BR', { timeZone: 'UTC' });

      Swal.fire({
        icon: 'success',
        title: 'Pré-agendamento enviado!',
        html: `
          <div class="text-left text-gray-700 text-sm space-y-2 leading-relaxed">
            <p><b>Profissional:</b> ${profissionalSelecionado}</p>
            <p><b>Data:</b> ${dataFormatada}</p>
            <p><b>Horário:</b> ${this.horarioSelecionado}</p>
            <p><b>Status:</b> <span class="text-yellow-600 font-semibold">Pendente</span></p>
            <p><b>Endereço da clínica:</b> <i>Definido nas configurações</i></p>
          </div>

          <div class="mt-6 flex items-center justify-between relative px-2">
            <div class="flex flex-col items-center">
              <div class="w-5 h-5 rounded-full bg-green-600 flex items-center justify-center text-white text-xs shadow">✓</div>
              <span class="text-xs mt-1 text-green-700 font-semibold">Enviado</span>
            </div>
            <div class="flex-1 h-0.5 bg-green-500 mx-2 animate-pulse"></div>
            <div class="flex flex-col items-center">
              <div class="w-5 h-5 rounded-full bg-yellow-400 flex items-center justify-center text-white text-xs shadow">⏳</div>
              <span class="text-xs mt-1 text-yellow-600 font-semibold">Aguardando</span>
            </div>
            <div class="flex-1 h-0.5 bg-gray-300 mx-2"></div>
            <div class="flex flex-col items-center">
              <div class="w-5 h-5 rounded-full bg-gray-300 flex items-center justify-center text-white text-xs shadow">✔</div>
              <span class="text-xs mt-1 text-gray-400 font-semibold">Confirmado</span>
            </div>
          </div>
        `,
        confirmButtonText: 'Fechar',
        confirmButtonColor: '#16a34a',
        background: '#fff',
        color: '#374151',
        width: 440,
      });

      this.horarioSelecionado = '';
      this.horarios = [];
    }
  }
}
</script>
@endsection
