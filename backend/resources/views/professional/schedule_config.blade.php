@extends('layouts.app')

@section('title', 'Configurar Agenda')

@section('content')
<div class="p-6" x-data="{ diasAtivos: {{ json_encode($schedules->pluck('day_of_week')) }} }">
  <div class="flex justify-between items-center mb-4">
    <h1 class="text-xl font-semibold text-gray-700">Configuração da Agenda</h1>
    <a href="{{ route('professional.schedule') }}" class="text-sm text-blue-600 hover:underline">
      ← Voltar para Agenda
    </a>
  </div>

  <form action="{{ route('professional.schedule.config.update') }}" method="POST" class="space-y-6">
    @csrf

    <div class="bg-white shadow rounded-lg divide-y divide-gray-200">
      @foreach($diasSemana as $diaNumero => $diaNome)
        @php
            $config = $schedules->firstWhere('day_of_week', $diaNumero);
        @endphp

        <div 
          class="p-5 flex flex-col md:flex-row md:items-center md:justify-between gap-4 transition-all duration-150"
          :class="diasAtivos.includes({{ $diaNumero }}) ? 'bg-green-50' : 'bg-white'">
          
          {{-- Checkbox do dia --}}
          <div class="flex items-center space-x-3 w-full md:w-1/3">
            <input type="checkbox" 
                   name="schedules[{{ $diaNumero }}][active]" 
                   value="1"
                   @checked($config?->available)
                   @change="if($event.target.checked){ diasAtivos.push({{ $diaNumero }}) } else { diasAtivos = diasAtivos.filter(d => d !== {{ $diaNumero }}) }"
                   class="h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
            <label class="font-medium text-gray-800 flex items-center gap-2">
              {{ $diaNome }}
              @if($config)
                <span class="text-green-600 text-xs font-semibold flex items-center">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                  </svg>
                  Ativo
                </span>
              @endif
            </label>
          </div>

          {{-- Campos de configuração --}}
          <div class="grid grid-cols-2 md:grid-cols-5 gap-3 w-full md:w-2/3">
            <div>
              <label class="block text-xs text-gray-500">Início</label>
              <input type="time" 
                     name="schedules[{{ $diaNumero }}][start_time]" 
                     value="{{ $config && $config->start_hour ? substr($config->start_hour, 0, 5) : '' }}" 
                     class="input input-sm w-full border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
            </div>

            <div>
              <label class="block text-xs text-gray-500">Término</label>
              <input type="time" 
                     name="schedules[{{ $diaNumero }}][end_time]" 
                     value="{{ $config && $config->end_hour ? substr($config->end_hour, 0, 5) : '' }}" 
                     class="input input-sm w-full border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
            </div>

            <div>
              <label class="block text-xs text-gray-500">Início intervalo</label>
              <input type="time" 
                     name="schedules[{{ $diaNumero }}][break_start]" 
                     value="{{ $config && $config->break_start ? substr($config->break_start, 0, 5) : '' }}" 
                     class="input input-sm w-full border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
            </div>

            <div>
              <label class="block text-xs text-gray-500">Fim intervalo</label>
              <input type="time" 
                     name="schedules[{{ $diaNumero }}][break_end]" 
                     value="{{ $config && $config->break_end ? substr($config->break_end, 0, 5) : '' }}" 
                     class="input input-sm w-full border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
            </div>

            <div>
              <label class="block text-xs text-gray-500">Duração (min)</label>
              <input type="number" 
                     min="10" step="5"
                     name="schedules[{{ $diaNumero }}][duration_min]" 
                     value="{{ $config?->duration_min ?? 30 }}" 
                     class="input input-sm w-full border-gray-300 rounded-md text-center focus:ring-blue-500 focus:border-blue-500">
            </div>
          </div>
        </div>
      @endforeach
    </div>

    <div class="flex justify-between items-center">
      <p class="text-sm text-gray-500">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 inline text-green-600 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
        </svg>
        Indica dias já configurados anteriormente
      </p>

      <button type="submit" 
              class="bg-blue-600 hover:bg-blue-700 text-white font-medium px-5 py-2.5 rounded-md transition">
        Salvar Configuração
      </button>
    </div>
  </form>
</div>
@endsection
