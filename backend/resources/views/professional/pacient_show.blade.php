@extends('layouts.app')

@section('title', 'Histórico do Paciente')

@section('content')
<div class="p-6 space-y-6">
  
  {{-- Cabeçalho --}}
  <div class="flex justify-between items-center mb-4">
    <div>
      <h1 class="text-xl font-semibold text-gray-700">
        Histórico de {{ $pacient->name }}
      </h1>
      <p class="text-sm text-gray-500">
        {{ $pacient->email }} @if($pacient->phone) | {{ $pacient->phone }} @endif
      </p>
    </div>

    <a href="{{ route('professional.pacients') }}" 
       class="text-sm text-blue-600 hover:underline">
      ← Voltar para Pacientes
    </a>
  </div>

  {{-- Histórico de atendimentos --}}
  @if($appointments->isEmpty())
    <p class="text-gray-500 text-sm">Nenhum atendimento registrado para este paciente.</p>
  @else
    <div class="bg-white shadow rounded-lg overflow-hidden">
      <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
          <tr class="text-xs font-medium text-gray-500 uppercase tracking-wider">
            <th class="px-4 py-2 text-left">Data</th>
            <th class="px-4 py-2 text-left">Serviço</th>
            <th class="px-4 py-2 text-left">Status</th>
            <th class="px-4 py-2 text-left">Anotações</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
          @foreach($appointments as $a)
          <tr class="hover:bg-gray-50 transition">
            <td class="px-4 py-2 text-sm text-gray-800">
              {{ $a->start_at->format('d/m/Y H:i') }}
            </td>
            <td class="px-4 py-2 text-sm text-gray-700">
              {{ $a->service->name ?? '—' }}
            </td>
            <td class="px-4 py-2 text-sm">
              <span class="px-2 py-1 rounded text-xs font-medium
                @switch($a->status)
                    @case('confirmed') bg-green-100 text-green-700 @break
                    @case('pending') bg-yellow-100 text-yellow-700 @break
                    @case('cancelled') bg-red-100 text-red-700 @break
                    @case('done') bg-blue-100 text-blue-700 @break
                    @default bg-gray-100 text-gray-700
                @endswitch">
                {{ ucfirst($a->status) }}
              </span>
            </td>
            <td class="px-4 py-2 text-sm text-gray-600">
              {{ $a->notes ?? '—' }}
            </td>
          </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  @endif
</div>
@endsection
