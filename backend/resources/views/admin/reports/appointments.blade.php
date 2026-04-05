@extends('layouts.app')
@section('title', 'Relatório de Atendimentos')

@section('content')
<div class="p-6 space-y-6">

    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-xl font-bold text-gray-800">
                <i class="fa fa-clipboard-list text-gray-400 mr-1"></i> Relatório de Atendimentos
            </h1>
            <p class="text-sm text-gray-500 mt-1">
                {{ \Carbon\Carbon::parse($startDate)->format('d/m/Y') }} — {{ \Carbon\Carbon::parse($endDate)->format('d/m/Y') }}
            </p>
        </div>
    </div>

    {{-- Filtros --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
        <form method="GET" action="{{ route('admin.reports.appointments') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4 items-end">
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
                <label class="block text-xs font-medium text-gray-600 mb-1">Profissional</label>
                <select name="professional_id" class="w-full border rounded-md px-3 py-2 text-sm">
                    <option value="">Todos</option>
                    @foreach($professionals as $p)
                        <option value="{{ $p->id }}" {{ request('professional_id') == $p->id ? 'selected' : '' }}>
                            {{ $p->user->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Status</label>
                <select name="status" class="w-full border rounded-md px-3 py-2 text-sm">
                    <option value="">Todos</option>
                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pendente</option>
                    <option value="confirmed" {{ request('status') == 'confirmed' ? 'selected' : '' }}>Confirmado</option>
                    <option value="done" {{ request('status') == 'done' ? 'selected' : '' }}>Concluído</option>
                    <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelado</option>
                    <option value="no_show" {{ request('status') == 'no_show' ? 'selected' : '' }}>Não Compareceu</option>
                </select>
            </div>
            <div>
                <button type="submit" class="w-full bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-medium px-4 py-2 rounded-md transition">
                    <i class="fa fa-search mr-1"></i> Filtrar
                </button>
            </div>
        </form>
    </div>

    {{-- Cards Resumo --}}
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3">
        <div class="bg-white rounded-lg shadow-sm border p-4 text-center">
            <p class="text-2xl font-bold text-gray-800">{{ $summary->total ?? 0 }}</p>
            <p class="text-xs text-gray-500 mt-1">Total</p>
        </div>
        <div class="bg-white rounded-lg shadow-sm border p-4 text-center">
            <p class="text-2xl font-bold text-green-600">{{ $summary->done ?? 0 }}</p>
            <p class="text-xs text-gray-500 mt-1">Concluídos</p>
        </div>
        <div class="bg-white rounded-lg shadow-sm border p-4 text-center">
            <p class="text-2xl font-bold text-blue-600">{{ $summary->confirmed ?? 0 }}</p>
            <p class="text-xs text-gray-500 mt-1">Confirmados</p>
        </div>
        <div class="bg-white rounded-lg shadow-sm border p-4 text-center">
            <p class="text-2xl font-bold text-yellow-600">{{ $summary->pending ?? 0 }}</p>
            <p class="text-xs text-gray-500 mt-1">Pendentes</p>
        </div>
        <div class="bg-white rounded-lg shadow-sm border p-4 text-center">
            <p class="text-2xl font-bold text-red-500">{{ $summary->cancelled ?? 0 }}</p>
            <p class="text-xs text-gray-500 mt-1">Cancelados</p>
        </div>
        <div class="bg-white rounded-lg shadow-sm border p-4 text-center">
            <p class="text-2xl font-bold text-gray-500">{{ $summary->no_show ?? 0 }}</p>
            <p class="text-xs text-gray-500 mt-1">Não Compareceu</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- Gráfico de atendimentos por dia --}}
        <div class="lg:col-span-2 bg-white rounded-xl shadow-sm border border-gray-200 p-5">
            <h2 class="text-sm font-semibold text-gray-700 mb-4">
                <i class="fa fa-chart-bar text-gray-400 mr-1"></i> Atendimentos por Dia
            </h2>
            <canvas id="chartByDay" height="120"></canvas>
        </div>

        {{-- Atendimentos por profissional --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
            <h2 class="text-sm font-semibold text-gray-700 mb-4">
                <i class="fa fa-user-md text-gray-400 mr-1"></i> Por Profissional
            </h2>
            @forelse($byProfessional as $item)
                <div class="flex items-center justify-between py-2 {{ !$loop->last ? 'border-b border-gray-100' : '' }}">
                    <span class="text-sm text-gray-700">{{ $item->prof_name }}</span>
                    <span class="text-sm font-semibold text-emerald-600 bg-emerald-50 px-2.5 py-0.5 rounded-full">
                        {{ $item->total }}
                    </span>
                </div>
            @empty
                <p class="text-xs text-gray-400">Nenhum dado no período.</p>
            @endforelse
        </div>
    </div>

    {{-- Tabela de agendamentos --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-100">
            <h2 class="text-sm font-semibold text-gray-700">
                <i class="fa fa-list text-gray-400 mr-1"></i> Detalhamento
            </h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="bg-gray-50 text-xs text-gray-500 uppercase">
                    <tr>
                        <th class="px-4 py-3">Data/Hora</th>
                        <th class="px-4 py-3">Paciente</th>
                        <th class="px-4 py-3">Profissional</th>
                        <th class="px-4 py-3">Serviço</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3">Pagamento</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($appointments as $apt)
                        @php
                            $statusColors = [
                                'pending'   => 'bg-yellow-100 text-yellow-800',
                                'confirmed' => 'bg-blue-100 text-blue-800',
                                'done'      => 'bg-green-100 text-green-800',
                                'cancelled' => 'bg-red-100 text-red-800',
                                'no_show'   => 'bg-gray-100 text-gray-800',
                            ];
                            $statusLabels = [
                                'pending'   => 'Pendente',
                                'confirmed' => 'Confirmado',
                                'done'      => 'Concluído',
                                'cancelled' => 'Cancelado',
                                'no_show'   => 'Não Compareceu',
                            ];
                            $payLabels = [
                                'pending' => 'Pendente',
                                'paid'    => 'Pago',
                                'plan'    => 'Convênio',
                            ];
                            $payColors = [
                                'pending' => 'bg-yellow-100 text-yellow-800',
                                'paid'    => 'bg-green-100 text-green-800',
                                'plan'    => 'bg-purple-100 text-purple-800',
                            ];
                        @endphp
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 whitespace-nowrap">
                                {{ \Carbon\Carbon::parse($apt->start_at)->format('d/m/Y H:i') }}
                            </td>
                            <td class="px-4 py-3">{{ $apt->client->name ?? '—' }}</td>
                            <td class="px-4 py-3">{{ $apt->professional->user->name ?? '—' }}</td>
                            <td class="px-4 py-3">{{ $apt->service->name ?? '—' }}</td>
                            <td class="px-4 py-3">
                                <span class="px-2 py-0.5 text-xs rounded-full {{ $statusColors[$apt->status] ?? '' }}">
                                    {{ $statusLabels[$apt->status] ?? $apt->status }}
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                <span class="px-2 py-0.5 text-xs rounded-full {{ $payColors[$apt->payment_status] ?? 'bg-gray-100 text-gray-600' }}">
                                    {{ $payLabels[$apt->payment_status] ?? $apt->payment_status }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-8 text-center text-gray-400">
                                Nenhum agendamento encontrado no período.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-4 py-3 border-t border-gray-100">
            {{ $appointments->links() }}
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const ctx = document.getElementById('chartByDay');
    if (!ctx) return;

    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: @json($byDay->pluck('date')->map(fn($d) => \Carbon\Carbon::parse($d)->format('d/m'))),
            datasets: [{
                label: 'Atendimentos',
                data: @json($byDay->pluck('total')),
                backgroundColor: 'rgba(16, 185, 129, 0.6)',
                borderColor: 'rgb(16, 185, 129)',
                borderWidth: 1,
                borderRadius: 4
            }]
        },
        options: {
            responsive: true,
            plugins: { legend: { display: false } },
            scales: {
                y: { beginAtZero: true, ticks: { stepSize: 1 } }
            }
        }
    });
});
</script>
@endpush
