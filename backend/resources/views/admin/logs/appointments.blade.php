@extends('layouts.app')
@section('title', 'Logs de Agendamentos')

@section('content')
<div class="p-6 space-y-6">

    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-xl font-bold text-gray-800">
                <i class="fa fa-clock-rotate-left text-gray-400 mr-1"></i> Logs de Agendamentos
            </h1>
            <p class="text-sm text-gray-500 mt-1">Histórico de alterações de status dos agendamentos</p>
        </div>
    </div>

    {{-- Filtros --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
        <form method="GET" action="{{ route('admin.logs.appointments') }}" class="grid grid-cols-1 sm:grid-cols-3 gap-4 items-end">
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Data Início</label>
                <input type="date" name="start_date" value="{{ request('start_date', \Carbon\Carbon::parse($startDate)->format('Y-m-d')) }}"
                       class="w-full border rounded-md px-3 py-2 text-sm">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Data Fim</label>
                <input type="date" name="end_date" value="{{ request('end_date', \Carbon\Carbon::parse($endDate)->format('Y-m-d')) }}"
                       class="w-full border rounded-md px-3 py-2 text-sm">
            </div>
            <div>
                <button type="submit" class="w-full bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-medium px-4 py-2 rounded-md transition">
                    <i class="fa fa-search mr-1"></i> Filtrar
                </button>
            </div>
        </form>
    </div>

    {{-- Timeline de logs --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="bg-gray-50 text-xs text-gray-500 uppercase">
                    <tr>
                        <th class="px-4 py-3">Data/Hora</th>
                        <th class="px-4 py-3">Paciente</th>
                        <th class="px-4 py-3">Profissional</th>
                        <th class="px-4 py-3">Serviço</th>
                        <th class="px-4 py-3">Alteração</th>
                        <th class="px-4 py-3">Alterado por</th>
                        <th class="px-4 py-3">Observação</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @php
                        $statusLabels = [
                            'pending'   => 'Pendente',
                            'confirmed' => 'Confirmado',
                            'done'      => 'Concluído',
                            'cancelled' => 'Cancelado',
                            'no_show'   => 'Não Compareceu',
                        ];
                        $statusColors = [
                            'pending'   => 'bg-yellow-100 text-yellow-800',
                            'confirmed' => 'bg-blue-100 text-blue-800',
                            'done'      => 'bg-green-100 text-green-800',
                            'cancelled' => 'bg-red-100 text-red-800',
                            'no_show'   => 'bg-gray-200 text-gray-700',
                        ];
                    @endphp
                    @forelse($logs as $log)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 whitespace-nowrap text-gray-600">
                                {{ \Carbon\Carbon::parse($log->changed_at)->format('d/m/Y H:i') }}
                            </td>
                            <td class="px-4 py-3">
                                {{ $log->appointment->client->name ?? '—' }}
                            </td>
                            <td class="px-4 py-3">
                                {{ $log->appointment->professional->user->name ?? '—' }}
                            </td>
                            <td class="px-4 py-3">
                                {{ $log->appointment->service->name ?? '—' }}
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap">
                                @if($log->from_status)
                                    <span class="px-2 py-0.5 text-xs rounded-full {{ $statusColors[$log->from_status] ?? 'bg-gray-100' }}">
                                        {{ $statusLabels[$log->from_status] ?? $log->from_status }}
                                    </span>
                                @else
                                    <span class="text-xs text-gray-400">—</span>
                                @endif
                                <i class="fa fa-arrow-right text-xs text-gray-400 mx-1"></i>
                                <span class="px-2 py-0.5 text-xs rounded-full {{ $statusColors[$log->to_status] ?? 'bg-gray-100' }}">
                                    {{ $statusLabels[$log->to_status] ?? $log->to_status }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-gray-600">
                                {{ $log->changedBy->name ?? 'Sistema' }}
                            </td>
                            <td class="px-4 py-3 text-gray-500 text-xs max-w-[200px] truncate" title="{{ $log->note }}">
                                {{ $log->note ?: '—' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-8 text-center text-gray-400">
                                Nenhum log encontrado no período.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-4 py-3 border-t border-gray-100">
            {{ $logs->links() }}
        </div>
    </div>
</div>
@endsection
