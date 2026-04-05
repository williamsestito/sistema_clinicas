@extends('layouts.app')
@section('title', 'Logs de Notificações')

@section('content')
<div class="p-6 space-y-6">

    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-xl font-bold text-gray-800">
                <i class="fa fa-bell text-gray-400 mr-1"></i> Logs de Notificações
            </h1>
            <p class="text-sm text-gray-500 mt-1">Histórico de notificações enviadas aos pacientes</p>
        </div>
    </div>

    {{-- Filtros --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
        <form method="GET" action="{{ route('admin.logs.notifications') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4 items-end">
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
                <label class="block text-xs font-medium text-gray-600 mb-1">Canal</label>
                <select name="channel" class="w-full border rounded-md px-3 py-2 text-sm">
                    <option value="">Todos</option>
                    <option value="email" {{ request('channel') == 'email' ? 'selected' : '' }}>E-mail</option>
                    <option value="whatsapp" {{ request('channel') == 'whatsapp' ? 'selected' : '' }}>WhatsApp</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Status</label>
                <select name="status" class="w-full border rounded-md px-3 py-2 text-sm">
                    <option value="">Todos</option>
                    <option value="success" {{ request('status') == 'success' ? 'selected' : '' }}>Sucesso</option>
                    <option value="failed" {{ request('status') == 'failed' ? 'selected' : '' }}>Falha</option>
                </select>
            </div>
            <div>
                <button type="submit" class="w-full bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-medium px-4 py-2 rounded-md transition">
                    <i class="fa fa-search mr-1"></i> Filtrar
                </button>
            </div>
        </form>
    </div>

    {{-- Cards resumo --}}
    <div class="grid grid-cols-2 sm:grid-cols-5 gap-3">
        <div class="bg-white rounded-lg shadow-sm border p-4 text-center">
            <p class="text-2xl font-bold text-gray-800">{{ $summary->total ?? 0 }}</p>
            <p class="text-xs text-gray-500 mt-1">Total</p>
        </div>
        <div class="bg-white rounded-lg shadow-sm border p-4 text-center">
            <p class="text-2xl font-bold text-green-600">{{ $summary->success ?? 0 }}</p>
            <p class="text-xs text-gray-500 mt-1">Sucesso</p>
        </div>
        <div class="bg-white rounded-lg shadow-sm border p-4 text-center">
            <p class="text-2xl font-bold text-red-500">{{ $summary->failed ?? 0 }}</p>
            <p class="text-xs text-gray-500 mt-1">Falhas</p>
        </div>
        <div class="bg-white rounded-lg shadow-sm border p-4 text-center">
            <p class="text-2xl font-bold text-blue-600">{{ $summary->email ?? 0 }}</p>
            <p class="text-xs text-gray-500 mt-1">E-mail</p>
        </div>
        <div class="bg-white rounded-lg shadow-sm border p-4 text-center">
            <p class="text-2xl font-bold text-emerald-600">{{ $summary->whatsapp ?? 0 }}</p>
            <p class="text-xs text-gray-500 mt-1">WhatsApp</p>
        </div>
    </div>

    {{-- Tabela --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="bg-gray-50 text-xs text-gray-500 uppercase">
                    <tr>
                        <th class="px-4 py-3">Enviado em</th>
                        <th class="px-4 py-3">Canal</th>
                        <th class="px-4 py-3">Tipo</th>
                        <th class="px-4 py-3">Destinatário</th>
                        <th class="px-4 py-3">Paciente</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3">Erro</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @php
                        $typeLabels = [
                            'new'           => 'Novo Agendamento',
                            'reminder_24h'  => 'Lembrete 24h',
                            'reminder_2h'   => 'Lembrete 2h',
                            'status_update' => 'Atualização',
                        ];
                        $channelIcons = [
                            'email'    => 'fa-envelope text-blue-500',
                            'whatsapp' => 'fa-brands fa-whatsapp text-green-500',
                        ];
                    @endphp
                    @forelse($logs as $log)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 whitespace-nowrap text-gray-600">
                                {{ \Carbon\Carbon::parse($log->sent_at)->format('d/m/Y H:i') }}
                            </td>
                            <td class="px-4 py-3">
                                <i class="fa-solid {{ $channelIcons[$log->channel] ?? 'fa-circle text-gray-400' }} text-lg"></i>
                                <span class="ml-1 text-xs text-gray-500">{{ ucfirst($log->channel) }}</span>
                            </td>
                            <td class="px-4 py-3">
                                <span class="text-xs text-gray-600">{{ $typeLabels[$log->type] ?? $log->type }}</span>
                            </td>
                            <td class="px-4 py-3 text-gray-600">{{ $log->recipient ?? '—' }}</td>
                            <td class="px-4 py-3">{{ $log->appointment->client->name ?? '—' }}</td>
                            <td class="px-4 py-3">
                                @if($log->status === 'success')
                                    <span class="px-2 py-0.5 text-xs rounded-full bg-green-100 text-green-800">
                                        <i class="fa fa-check mr-0.5"></i> Sucesso
                                    </span>
                                @else
                                    <span class="px-2 py-0.5 text-xs rounded-full bg-red-100 text-red-800">
                                        <i class="fa fa-times mr-0.5"></i> Falha
                                    </span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-xs text-red-500 max-w-[200px] truncate" title="{{ $log->error_message }}">
                                {{ $log->error_message ?: '—' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-8 text-center text-gray-400">
                                Nenhuma notificação encontrada no período.
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
