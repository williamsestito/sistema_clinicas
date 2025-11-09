@extends('layouts.app')

@section('title', 'Dashboard do Profissional')

@section('content')
<div class="p-6">
  <h1 class="text-xl font-semibold text-gray-700 mb-4">📊 Dashboard Profissional</h1>

  <!-- Cards de Resumo -->
  <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
    <x-dashboard-card icon="fa-calendar-check" color="bg-green-100 text-green-800" 
      label="Atendimentos Hoje" :value="$totalConfirmadosHoje" />

    <x-dashboard-card icon="fa-hourglass-half" color="bg-yellow-100 text-yellow-800" 
      label="Pendentes" :value="$totalPendentes" />

    <x-dashboard-card icon="fa-ban" color="bg-red-100 text-red-800" 
      label="Dias Bloqueados" :value="$bloqueiosAtivos" />

    <x-dashboard-card icon="fa-users" color="bg-blue-100 text-blue-800" 
      label="Pacientes no Mês" :value="$pacientesMes" />
  </div>

  <!-- Próximos Atendimentos -->
  <div class="bg-white shadow rounded-lg p-5 mb-6">
    <h2 class="text-lg font-semibold text-gray-700 mb-3">📅 Próximos Atendimentos</h2>

    @if($proximosAtendimentos->isEmpty())
      <p class="text-sm text-gray-500">Nenhum atendimento agendado.</p>
    @else
      <ul class="divide-y divide-gray-200 text-sm">
        @foreach($proximosAtendimentos as $a)
          <li class="py-3 flex justify-between items-center">
            <div>
              <p class="font-medium text-gray-800">
                {{ $a->client->name ?? 'Paciente removido' }}
              </p>
              <p class="text-xs text-gray-500">
                {{ \Carbon\Carbon::parse($a->date)->format('d/m/Y H:i') }} — {{ ucfirst($a->status) }}
              </p>
            </div>

            <div class="flex space-x-2">
              @if($a->status === 'pending')
                <span class="text-yellow-700 bg-yellow-100 px-2 py-1 rounded text-xs">Pendente</span>
              @elseif($a->status === 'confirmed')
                <span class="text-green-700 bg-green-100 px-2 py-1 rounded text-xs">Confirmado</span>
              @else
                <span class="text-gray-600 bg-gray-100 px-2 py-1 rounded text-xs">Finalizado</span>
              @endif
            </div>
          </li>
        @endforeach
      </ul>
    @endif
  </div>

  <!-- Últimos Pacientes Atendidos -->
  <div class="bg-white shadow rounded-lg p-5">
    <h2 class="text-lg font-semibold text-gray-700 mb-3">🩺 Últimos Pacientes Atendidos</h2>

    @if($ultimosPacientes->isEmpty())
      <p class="text-sm text-gray-500">Nenhum atendimento finalizado recentemente.</p>
    @else
      <table class="w-full text-sm border-t border-gray-200">
        <thead>
          <tr class="text-left text-gray-600">
            <th class="py-2">Paciente</th>
            <th>Data</th>
            <th>Status</th>
          </tr>
        </thead>
        <tbody>
          @foreach($ultimosPacientes as $a)
            <tr class="border-t">
              <td class="py-2">{{ $a->client->name ?? 'Paciente removido' }}</td>
              <td>{{ \Carbon\Carbon::parse($a->date)->format('d/m/Y H:i') }}</td>
              <td>
                <span class="text-green-700 bg-green-100 px-2 py-1 rounded text-xs">
                  Finalizado
                </span>
              </td>
            </tr>
          @endforeach
        </tbody>
      </table>
    @endif
  </div>
</div>
@endsection
