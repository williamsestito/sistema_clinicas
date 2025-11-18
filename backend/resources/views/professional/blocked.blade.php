@extends('layouts.app')

@section('title', 'Dias Bloqueados')

@section('content')
<div class="p-6 space-y-6">
  <div class="flex justify-between items-center mb-4">
    <h1 class="text-xl font-semibold text-gray-700">Dias Bloqueados</h1>
    <a href="{{ route('professional.schedule.config') }}" class="text-sm text-blue-600 hover:underline">
      ← Voltar para Agenda
    </a>
  </div>

  {{-- Mensagem de sucesso --}}
  @if(session('success'))
    <div class="bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-lg text-sm">
      {{ session('success') }}
    </div>
  @endif

  {{-- Formulário para adicionar novo bloqueio --}}
  <form action="{{ route('professional.blocked.store') }}" method="POST" class="bg-white shadow rounded-lg p-5 flex flex-col md:flex-row md:items-end gap-4">
    @csrf
    <div class="flex-1">
      <label class="block text-xs text-gray-500 mb-1">Data a bloquear</label>
      <input type="date" 
             name="date" 
             required 
             min="{{ date('Y-m-d') }}" 
             class="input input-sm w-full border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
    </div>

    <div class="flex-1">
      <label class="block text-xs text-gray-500 mb-1">Motivo (opcional)</label>
      <input type="text" 
             name="reason" 
             placeholder="Ex: Feriado, viagem, manutenção..." 
             class="input input-sm w-full border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500">
    </div>

    <button type="submit" 
            class="bg-red-600 hover:bg-red-700 text-white font-medium px-5 py-2.5 rounded-md transition">
      Bloquear Dia
    </button>
  </form>

  {{-- Lista de bloqueios existentes --}}
  <div class="bg-white shadow rounded-lg p-5">
    <h2 class="font-semibold text-gray-700 mb-3">Dias já bloqueados</h2>

    @if($blocked->isEmpty())
      <p class="text-gray-500 text-sm">Nenhum bloqueio cadastrado até o momento.</p>
    @else
      <table class="min-w-full divide-y divide-gray-200">
        <thead>
          <tr class="bg-gray-50 text-xs text-gray-500 uppercase">
            <th class="px-3 py-2 text-left">Data</th>
            <th class="px-3 py-2 text-left">Motivo</th>
            <th class="px-3 py-2 text-right">Ações</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
          @foreach($blocked as $blocked)
          <tr>
            <td class="px-3 py-2 text-sm text-gray-800">
              {{ \Carbon\Carbon::parse($blocked->date)->format('d/m/Y') }}
            </td>
            <td class="px-3 py-2 text-sm text-gray-600">
              {{ $blocked->reason ?? '—' }}
            </td>
            <td class="px-3 py-2 text-right">
              <form action="{{ route('professional.blocked.destroy', $blocked->id) }}" method="POST" onsubmit="return confirm('Remover bloqueio de {{ \Carbon\Carbon::parse($blocked->date)->format('d/m/Y') }}?')">
                @csrf
                @method('DELETE')
                <button class="text-red-600 hover:text-red-800 text-sm font-medium">Remover</button>
              </form>
            </td>
          </tr>
          @endforeach
        </tbody>
      </table>
    @endif
  </div>
</div>
@endsection
