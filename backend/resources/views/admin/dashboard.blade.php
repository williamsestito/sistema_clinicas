@extends('layouts.app')

@section('title', 'Dashboard Admin')

@section('content')
<div class="px-4 py-3 space-y-3">

    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-xl font-bold text-gray-800">Painel Administrativo</h1>
            <p class="text-xs text-gray-500">Visão geral da clínica — {{ now()->translatedFormat('d \d\e F \d\e Y') }}</p>
        </div>
    </div>

    {{-- Cards --}}
    <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-8 gap-2">
        <x-dashboard-card
            icon="fa-user-injured"
            label="Total de Pacientes"
            :value="$totalClients"
            bg="bg-blue-50"
            text="text-blue-600" />

        <x-dashboard-card
            icon="fa-user-md"
            label="Profissionais Ativos"
            :value="$totalProfessionals"
            bg="bg-emerald-50"
            text="text-emerald-600" />

        <x-dashboard-card
            icon="fa-briefcase-medical"
            label="Serviços Ativos"
            :value="$totalServices"
            bg="bg-purple-50"
            text="text-purple-600" />

        <x-dashboard-card
            icon="fa-users-gear"
            label="Colaboradores"
            :value="$totalEmployees"
            bg="bg-orange-50"
            text="text-orange-600" />

        <x-dashboard-card
            icon="fa-calendar-day"
            label="Agendamentos Hoje"
            :value="$appointmentsToday"
            bg="bg-sky-50"
            text="text-sky-600" />

        <x-dashboard-card
            icon="fa-calendar-check"
            label="Confirmados Hoje"
            :value="$confirmedToday"
            bg="bg-green-50"
            text="text-green-600" />

        <x-dashboard-card
            icon="fa-clock"
            label="Pendentes"
            :value="$pendingAppointments"
            bg="bg-yellow-50"
            text="text-yellow-600" />

        <x-dashboard-card
            icon="fa-money-bill-trend-up"
            label="Receita do Mês"
            :value="'R$ ' . number_format($monthlyRevenue, 2, ',', '.')"
            bg="bg-emerald-50"
            text="text-emerald-600" />
    </div>

    {{-- ═══ Gráficos: 4 em grid 2x2 ═══ --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-3">

        {{-- 1. Pizza: agendamentos por status --}}
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-3">
            <h3 class="text-xs font-semibold text-gray-600 mb-1">
                <i class="fa fa-calendar-days text-blue-500 mr-1"></i>Agendamentos — Status (mês)
            </h3>
            @if(array_sum($appointmentsByStatus) > 0)
            <div class="flex justify-center" style="height:220px">
                <canvas id="chartAppointmentsPie"></canvas>
            </div>
            @else
            <div class="flex flex-col items-center justify-center py-8 text-gray-400">
                <i class="fa fa-chart-pie text-3xl mb-2"></i>
                <p class="text-xs">Sem agendamentos no mês atual.</p>
            </div>
            @endif
        </div>

        {{-- 2. Barras: agendamentos últimos 7 dias --}}
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-3">
            <h3 class="text-xs font-semibold text-gray-600 mb-1">
                <i class="fa fa-chart-column text-blue-500 mr-1"></i>Agendamentos — Últimos 7 dias
            </h3>
            @if(array_sum($appointmentsDayValues) > 0)
            <div style="height:220px">
                <canvas id="chartAppointmentsBar"></canvas>
            </div>
            @else
            <div class="flex flex-col items-center justify-center py-8 text-gray-400">
                <i class="fa fa-chart-bar text-3xl mb-2"></i>
                <p class="text-xs">Sem agendamentos nos últimos 7 dias.</p>
            </div>
            @endif
        </div>

        {{-- 3. Pizza: lançamentos por categoria --}}
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-3">
            <h3 class="text-xs font-semibold text-gray-600 mb-1">
                <i class="fa fa-wallet text-emerald-500 mr-1"></i>Financeiro — Categorias (mês)
            </h3>
            @if($financialByCategory->count() > 0)
            <div class="flex justify-center" style="height:220px">
                <canvas id="chartFinancialPie"></canvas>
            </div>
            @else
            <div class="flex flex-col items-center justify-center py-8 text-gray-400">
                <i class="fa fa-chart-pie text-3xl mb-2"></i>
                <p class="text-xs">Sem lançamentos financeiros no mês atual.</p>
            </div>
            @endif
        </div>

        {{-- 4. Barras: receitas vs despesas (6 meses) --}}
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-3">
            <h3 class="text-xs font-semibold text-gray-600 mb-1">
                <i class="fa fa-chart-column text-emerald-500 mr-1"></i>Receitas × Despesas (6 meses)
            </h3>
            @php
                $hasFinancialHistory = collect($financialMonthly)->sum('income') + collect($financialMonthly)->sum('expense') > 0;
            @endphp
            @if($hasFinancialHistory)
            <div style="height:220px">
                <canvas id="chartFinancialBar"></canvas>
            </div>
            @else
            <div class="flex flex-col items-center justify-center py-8 text-gray-400">
                <i class="fa fa-chart-bar text-3xl mb-2"></i>
                <p class="text-xs">Sem dados financeiros nos últimos 6 meses.</p>
            </div>
            @endif
        </div>

    </div>

</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {

    // ── Agendamentos: Pizza por Status ──
    @php
        $statusLabels = [
            'pending'   => 'Pendentes',
            'confirmed' => 'Confirmados',
            'done' => 'Concluídos',
            'cancelled' => 'Cancelados',
            'no_show'   => 'Não Compareceu',
        ];
        $statusColors = [
            'pending'   => '#facc15',
            'confirmed' => '#3b82f6',
            'done' => '#22c55e',
            'cancelled' => '#ef4444',
            'no_show'   => '#9ca3af',
        ];
        $pieLabels = [];
        $pieValues = [];
        $pieColors = [];
        foreach ($statusLabels as $key => $label) {
            if (($appointmentsByStatus[$key] ?? 0) > 0) {
                $pieLabels[] = $label;
                $pieValues[] = $appointmentsByStatus[$key];
                $pieColors[] = $statusColors[$key];
            }
        }
    @endphp

    @if(array_sum($appointmentsByStatus) > 0)
    new Chart(document.getElementById('chartAppointmentsPie'), {
        type: 'pie',
        data: {
            labels: @json($pieLabels),
            datasets: [{
                data: @json($pieValues),
                backgroundColor: @json($pieColors),
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'right', labels: { boxWidth: 10, padding: 6, font: { size: 11 } } }
            }
        }
    });
    @endif

    // ── Agendamentos: Barras últimos 7 dias ──
    @if(array_sum($appointmentsDayValues) > 0)
    new Chart(document.getElementById('chartAppointmentsBar'), {
        type: 'bar',
        data: {
            labels: @json($appointmentsDayLabels),
            datasets: [{
                label: 'Agendamentos',
                data: @json($appointmentsDayValues),
                backgroundColor: '#3b82f6',
                borderRadius: 4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: { beginAtZero: true, ticks: { stepSize: 1, font: { size: 10 } } },
                x: { ticks: { font: { size: 10 } } }
            },
            plugins: { legend: { display: false } }
        }
    });
    @endif

    // ── Financeiro: Pizza por Categoria ──
    @php
        $catLabels = $financialByCategory->pluck('cat_name')->toArray();
        $catValues = $financialByCategory->pluck('total')->map(fn($v) => (float) $v)->toArray();
        $catColors = $financialByCategory->pluck('cat_color')->toArray();
        // fallback colors if empty
        if (empty($catColors) || ($catColors[0] ?? null) === null) {
            $palette = ['#3b82f6','#22c55e','#ef4444','#f59e0b','#8b5cf6','#ec4899','#14b8a6','#f97316'];
            $catColors = array_slice($palette, 0, count($catLabels));
        }
    @endphp

    @if($financialByCategory->count() > 0)
    new Chart(document.getElementById('chartFinancialPie'), {
        type: 'pie',
        data: {
            labels: @json($catLabels),
            datasets: [{
                data: @json($catValues),
                backgroundColor: @json($catColors),
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'right', labels: { boxWidth: 10, padding: 6, font: { size: 10 } } },
                tooltip: {
                    callbacks: {
                        label: function(ctx) {
                            return ctx.label + ': R$ ' + ctx.parsed.toLocaleString('pt-BR', {minimumFractionDigits:2});
                        }
                    }
                }
            }
        }
    });
    @endif

    // ── Financeiro: Barras Receita x Despesa (6 meses) ──
    @php
        $fmLabels  = array_column($financialMonthly, 'label');
        $fmIncome  = array_column($financialMonthly, 'income');
        $fmExpense = array_column($financialMonthly, 'expense');
    @endphp

    @if($hasFinancialHistory)
    new Chart(document.getElementById('chartFinancialBar'), {
        type: 'bar',
        data: {
            labels: @json($fmLabels),
            datasets: [
                {
                    label: 'Receitas',
                    data: @json($fmIncome),
                    backgroundColor: '#22c55e',
                    borderRadius: 4
                },
                {
                    label: 'Despesas',
                    data: @json($fmExpense),
                    backgroundColor: '#ef4444',
                    borderRadius: 4
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(v) { return 'R$ ' + v.toLocaleString('pt-BR'); },
                        font: { size: 10 }
                    }
                },
                x: { ticks: { font: { size: 10 } } }
            },
            plugins: {
                legend: { position: 'bottom', labels: { boxWidth: 10, padding: 8, font: { size: 10 } } },
                tooltip: {
                    callbacks: {
                        label: function(ctx) {
                            return ctx.dataset.label + ': R$ ' + ctx.parsed.y.toLocaleString('pt-BR', {minimumFractionDigits:2});
                        }
                    }
                }
            }
        }
    });
    @endif});
</script>
@endpush
@endsection
