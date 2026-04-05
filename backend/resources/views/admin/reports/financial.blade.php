@extends('layouts.app')
@section('title', 'Relatório Financeiro')

@section('content')
<div class="p-6 space-y-6">

    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-xl font-bold text-gray-800">
                <i class="fa fa-money-bill-trend-up text-gray-400 mr-1"></i> Relatório Financeiro
            </h1>
            <p class="text-sm text-gray-500 mt-1">
                {{ \Carbon\Carbon::parse($startDate)->format('d/m/Y') }} — {{ \Carbon\Carbon::parse($endDate)->format('d/m/Y') }}
            </p>
        </div>
    </div>

    {{-- Filtros --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
        <form method="GET" action="{{ route('admin.reports.financial') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4 items-end">
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
                <label class="block text-xs font-medium text-gray-600 mb-1">Pagamento</label>
                <select name="payment_status" class="w-full border rounded-md px-3 py-2 text-sm">
                    <option value="">Todos</option>
                    <option value="pending" {{ request('payment_status') == 'pending' ? 'selected' : '' }}>Pendente</option>
                    <option value="paid" {{ request('payment_status') == 'paid' ? 'selected' : '' }}>Pago</option>
                    <option value="plan" {{ request('payment_status') == 'plan' ? 'selected' : '' }}>Convênio</option>
                </select>
            </div>
            <div>
                <button type="submit" class="w-full bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-medium px-4 py-2 rounded-md transition">
                    <i class="fa fa-search mr-1"></i> Filtrar
                </button>
            </div>
        </form>
    </div>

    {{-- Cards Financeiros --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white rounded-lg shadow-sm border p-5">
            <p class="text-xs text-gray-500 uppercase tracking-wide">Receita Bruta (Agenda)</p>
            <p class="text-2xl font-bold text-gray-800 mt-1">
                R$ {{ number_format($financial->total_bruto ?? 0, 2, ',', '.') }}
            </p>
            <p class="text-xs text-gray-400 mt-1">{{ $financial->total_agendamentos ?? 0 }} agendamentos</p>
        </div>
        <div class="bg-white rounded-lg shadow-sm border p-5">
            <p class="text-xs text-gray-500 uppercase tracking-wide">Receita Realizada</p>
            <p class="text-2xl font-bold text-emerald-600 mt-1">
                R$ {{ number_format($financial->receita_realizada ?? 0, 2, ',', '.') }}
            </p>
            <p class="text-xs text-gray-400 mt-1">{{ $financial->total_realizados ?? 0 }} concluídos</p>
        </div>
        <div class="bg-white rounded-lg shadow-sm border p-5">
            <p class="text-xs text-gray-500 uppercase tracking-wide">Total Pago</p>
            <p class="text-2xl font-bold text-green-600 mt-1">
                R$ {{ number_format($financial->total_pago ?? 0, 2, ',', '.') }}
            </p>
        </div>
        <div class="bg-white rounded-lg shadow-sm border p-5">
            <p class="text-xs text-gray-500 uppercase tracking-wide">Pendente</p>
            <p class="text-2xl font-bold text-yellow-600 mt-1">
                R$ {{ number_format($financial->total_pendente ?? 0, 2, ',', '.') }}
            </p>
        </div>
    </div>

    {{-- Cards Lançamentos Avulsos --}}
    <div class="grid grid-cols-2 lg:grid-cols-3 gap-4">
        <div class="bg-white rounded-lg shadow-sm border p-5">
            <p class="text-xs text-gray-500 uppercase tracking-wide">Receitas Avulsas</p>
            <p class="text-2xl font-bold text-emerald-600 mt-1">
                R$ {{ number_format($financial->entries_income ?? 0, 2, ',', '.') }}
            </p>
            <p class="text-xs text-gray-400 mt-1">Lançamentos manuais</p>
        </div>
        <div class="bg-white rounded-lg shadow-sm border p-5">
            <p class="text-xs text-gray-500 uppercase tracking-wide">Despesas</p>
            <p class="text-2xl font-bold text-red-600 mt-1">
                R$ {{ number_format($financial->entries_expense ?? 0, 2, ',', '.') }}
            </p>
            <p class="text-xs text-gray-400 mt-1">Lançamentos manuais</p>
        </div>
        <div class="bg-white rounded-lg shadow-sm border p-5 border-l-4 {{ ($financial->saldo ?? 0) >= 0 ? 'border-l-emerald-500' : 'border-l-red-500' }}">
            <p class="text-xs text-gray-500 uppercase tracking-wide">Saldo Consolidado</p>
            <p class="text-2xl font-bold {{ ($financial->saldo ?? 0) >= 0 ? 'text-emerald-600' : 'text-red-600' }} mt-1">
                R$ {{ number_format($financial->saldo ?? 0, 2, ',', '.') }}
            </p>
            <p class="text-xs text-gray-400 mt-1">Receita realizada + avulsas - despesas</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- Gráfico de receita por dia --}}
        <div class="lg:col-span-2 bg-white rounded-xl shadow-sm border border-gray-200 p-5">
            <h2 class="text-sm font-semibold text-gray-700 mb-4">
                <i class="fa fa-chart-line text-gray-400 mr-1"></i> Receita por Dia
            </h2>
            <canvas id="chartRevenueByDay" height="120"></canvas>
        </div>

        {{-- Receita por profissional --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
            <h2 class="text-sm font-semibold text-gray-700 mb-4">
                <i class="fa fa-user-md text-gray-400 mr-1"></i> Por Profissional
            </h2>
            @forelse($byProfessional as $item)
                <div class="flex items-center justify-between py-2 {{ !$loop->last ? 'border-b border-gray-100' : '' }}">
                    <div>
                        <span class="text-sm text-gray-700">{{ $item->prof_name }}</span>
                        <span class="text-xs text-gray-400 ml-1">({{ $item->qty }})</span>
                    </div>
                    <span class="text-sm font-semibold text-emerald-600">
                        R$ {{ number_format($item->total, 2, ',', '.') }}
                    </span>
                </div>
            @empty
                <p class="text-xs text-gray-400">Nenhum dado no período.</p>
            @endforelse
        </div>
    </div>

    {{-- Receita por serviço --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
        <h2 class="text-sm font-semibold text-gray-700 mb-4">
            <i class="fa fa-briefcase-medical text-gray-400 mr-1"></i> Receita por Serviço
        </h2>
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="bg-gray-50 text-xs text-gray-500 uppercase">
                    <tr>
                        <th class="px-4 py-3">Serviço</th>
                        <th class="px-4 py-3 text-center">Atendimentos</th>
                        <th class="px-4 py-3 text-right">Receita</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($byService as $item)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 font-medium text-gray-700">{{ $item->service_name }}</td>
                            <td class="px-4 py-3 text-center">{{ $item->qty }}</td>
                            <td class="px-4 py-3 text-right font-semibold text-emerald-600">
                                R$ {{ number_format($item->total, 2, ',', '.') }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="px-4 py-6 text-center text-gray-400">Nenhum dado no período.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
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
                        <th class="px-4 py-3 text-right">Valor</th>
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
                            $payLabels = ['pending' => 'Pendente', 'paid' => 'Pago', 'plan' => 'Convênio'];
                            $payColors = ['pending' => 'bg-yellow-100 text-yellow-800', 'paid' => 'bg-green-100 text-green-800', 'plan' => 'bg-purple-100 text-purple-800'];
                        @endphp
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 whitespace-nowrap">{{ \Carbon\Carbon::parse($apt->start_at)->format('d/m/Y H:i') }}</td>
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
                            <td class="px-4 py-3 text-right font-medium">
                                R$ {{ number_format($apt->charged_amount ?? $apt->service->price ?? 0, 2, ',', '.') }}
                                @if($apt->charged_amount && $apt->charged_amount != $apt->service->price)
                                    <br><span class="text-xs text-gray-400 line-through">R$ {{ number_format($apt->service->price ?? 0, 2, ',', '.') }}</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-8 text-center text-gray-400">
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
    const ctx = document.getElementById('chartRevenueByDay');
    if (!ctx) return;

    new Chart(ctx, {
        type: 'line',
        data: {
            labels: @json($byDay->pluck('date')->map(fn($d) => \Carbon\Carbon::parse($d)->format('d/m'))),
            datasets: [{
                label: 'Receita (R$)',
                data: @json($byDay->pluck('total')),
                borderColor: 'rgb(16, 185, 129)',
                backgroundColor: 'rgba(16, 185, 129, 0.1)',
                fill: true,
                tension: 0.3,
                pointRadius: 4,
                pointBackgroundColor: 'rgb(16, 185, 129)'
            }]
        },
        options: {
            responsive: true,
            plugins: { legend: { display: false } },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(v) { return 'R$ ' + v.toLocaleString('pt-BR'); }
                    }
                }
            }
        }
    });
});
</script>
@endpush
