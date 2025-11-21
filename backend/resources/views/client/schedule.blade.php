@extends('layouts.app_client')

@section('title', 'Agendar Consulta')

@section('content')
@php
    $clientUser = auth('client')->user();
@endphp

<!-- Garantindo CSRF para fetch() -->
<meta name="csrf-token" content="{{ csrf_token() }}">

<div x-data="agendamento({
        clientName: '{{ $clientUser->name ?? '' }}',
        clientEmail: '{{ $clientUser->email ?? '' }}'
    })"
     x-init="init()"
     class="p-4 sm:p-6 space-y-6">

  <!-- Cabeçalho -->
  <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-2">
    <h1 class="text-xl sm:text-2xl font-semibold text-gray-700 flex items-center gap-2">
      🩺 <span>Agendar Consulta</span>
    </h1>
    <p class="text-sm text-gray-500 leading-snug sm:text-right text-justify sm:text-left">
      Escolha um profissional e um horário disponível.<br class="hidden sm:block">
      O agendamento ficará <b>pendente</b> até confirmação.
    </p>
  </div>

  <!-- Etapa 1: Localização -->
  <div class="bg-white p-4 sm:p-6 rounded-xl shadow border border-gray-100">
    <h2 class="font-semibold text-gray-800 mb-4 text-base sm:text-lg">1️⃣ Escolha sua localização</h2>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
      <div>
        <label class="block text-xs font-medium text-gray-500 mb-1">UF</label>
        <select x-model="uf"
                @change="changeUf"
                class="w-full border rounded-md px-3 py-2 text-sm">
          <option value="">Selecione</option>
          <template x-for="estado in ufs" :key="estado">
            <option :value="estado" x-text="estado"></option>
          </template>
        </select>
      </div>

      <div>
        <label class="block text-xs font-medium text-gray-500 mb-1">Cidade</label>
        <select x-model="cidade"
                @change="changeCidade"
                :disabled="!uf"
                class="w-full border rounded-md px-3 py-2 text-sm">
          <option value="">Selecione</option>
          <template x-for="c in cidades" :key="c">
            <option :value="c" x-text="c"></option>
          </template>
        </select>
      </div>
    </div>
  </div>

  <!-- Etapa 2 -->
  <template x-if="cidade">
    <div class="bg-white p-4 sm:p-6 rounded-xl shadow border border-gray-100">
      <h2 class="font-semibold text-gray-800 mb-4 text-base sm:text-lg">2️⃣ Selecione os filtros</h2>

      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">

        <!-- PROFISSIONAL -->
        <div>
          <label class="block text-xs font-medium text-gray-500 mb-1">Profissional</label>
          <select x-model="profissional"
                  @change="changeProfissional"
                  class="w-full border rounded-md px-3 py-2 text-sm">
            <option value="">Selecione</option>
            <template x-for="p in profissionais" :key="p.id">
              <option :value="p.id" x-text="p.nome"></option>
            </template>
          </select>
        </div>

        <!-- ESPECIALIDADE -->
        <div>
          <label class="block text-xs font-medium text-gray-500 mb-1">Especialidade</label>
          <select x-model="especialidade"
                  @change="changeEspecialidade"
                  class="w-full border rounded-md px-3 py-2 text-sm">
            <option value="">Selecione</option>
            <template x-for="e in especialidades" :key="e">
              <option :value="e" x-text="e"></option>
            </template>
          </select>
        </div>

        <!-- PROCEDIMENTO -->
        <div>
          <label class="block text-xs font-medium text-gray-500 mb-1">Procedimento</label>
          <select x-model="procedimento"
                  @change="changeProcedimento"
                  class="w-full border rounded-md px-3 py-2 text-sm">
            <option value="">Selecione</option>
            <template x-for="proc in procedimentos" :key="proc">
              <option :value="proc" x-text="proc"></option>
            </template>
          </select>
        </div>
      </div>

      <template x-if="profissionalSelecionado">
        <div class="mt-4 p-3 border border-gray-100 rounded-lg bg-gray-50 text-xs text-gray-600">
          <p class="font-semibold text-gray-700" x-text="profissionalSelecionado.nome"></p>
          <p x-text="'Especialidades: ' + (profissionalSelecionado.especialidades?.join(', ') || '-')"></p>
          <p x-text="'Endereço: ' + (profissionalSelecionado.endereco || '-')"></p>
        </div>
      </template>
    </div>
  </template>

  <!-- Etapa 3 -->
  <template x-if="profissional && procedimento">
    <div class="bg-white p-4 sm:p-6 rounded-xl shadow border border-gray-100">
      <h2 class="font-semibold text-gray-800 mb-4 text-base sm:text-lg">3️⃣ Escolha o dia desejado</h2>

      <div class="flex flex-col sm:flex-row items-start sm:items-center gap-3">
        <input type="date"
               x-model="dataSelecionada"
               :min="minDate"
               class="border rounded-md px-3 py-2 text-sm w-full sm:w-auto">

        <button @click="carregarHorarios"
                class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2 rounded-md text-sm">
          Ver horários disponíveis
        </button>
      </div>
    </div>
  </template>

  <!-- Etapa 4 -->
  <template x-if="horarios.length > 0">
    <div class="bg-white p-4 sm:p-6 rounded-xl shadow border border-gray-100">
      <h2 class="text-base sm:text-lg font-semibold text-gray-800 mb-3">
        4️⃣ Horários disponíveis para
        <span class="text-green-700 font-medium" x-text="formatarData(dataSelecionada)"></span>
      </h2>

      <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 xl:grid-cols-6 gap-3 mt-3">
        <template x-for="hora in horarios" :key="hora">
          <div @click="selecionarHorario(hora)"
               class="border rounded-lg px-3 py-2 text-center cursor-pointer transition select-none text-sm"
               :class="horarioSelecionado === hora
                 ? 'bg-green-600 text-white border-green-700 shadow-md'
                 : 'hover:bg-green-50 hover:border-green-300'">
            <span x-text="hora"></span>
          </div>
        </template>
      </div>

      <div class="flex justify-end mt-6">
        <button @click="confirmarAgendamento"
                class="bg-green-600 hover:bg-green-700 text-white px-5 py-2 rounded-md text-sm">
          Confirmar Pré-Agendamento
        </button>
      </div>
    </div>
  </template>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

{{-- Script corrigido --}}
@include('client.schedule-script')

@endsection
