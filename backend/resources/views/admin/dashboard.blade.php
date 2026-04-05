@extends('layouts.app')

@section('title', 'Dashboard Admin')

@section('content')
<div class="p-6 space-y-6">

    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Painel Administrativo</h1>
            <p class="text-sm text-gray-500 mt-1">Visão geral da clínica — {{ now()->translatedFormat('d \d\e F \d\e Y') }}</p>
        </div>
    </div>

    {{-- Cards Principais --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
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
    </div>

    {{-- Cards Agendamentos + Receita --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
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

    {{-- Linha 2: Gráfico de Status + Resumo --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- Status dos Agendamentos do Mês --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
            <h2 class="text-sm font-semibold text-gray-700 mb-4">
                <i class="fa fa-chart-pie text-gray-400 mr-1"></i>
                Agendamentos do Mês
            </h2>
            <div class="space-y-3">
                @php
                    $statusLabels = [
                        'pending' => ['label' => 'Pendentes', 'color' => 'bg-yellow-400'],
                        'confirmed' => ['label' => 'Confirmados', 'color' => 'bg-blue-500'],
                        'completed' => ['label' => 'Concluídos', 'color' => 'bg-green-500'],
                        'cancelled' => ['label' => 'Cancelados', 'color' => 'bg-red-400'],
                        'no_show' => ['label' => 'Não Compareceu', 'color' => 'bg-gray-400'],
                    ];
                    $totalMonth = array_sum($appointmentsByStatus);
                @endphp

                @forelse($statusLabels as $key => $status)
                    @php
                        $count = $appointmentsByStatus[$key] ?? 0;
                        $percent = $totalMonth > 0 ? round(($count / $totalMonth) * 100) : 0;
                    @endphp
                    <div>
                        <div class="flex justify-between text-xs text-gray-600 mb-1">
                            <span>{{ $status['label'] }}</span>
                            <span class="font-medium">{{ $count }} ({{ $percent }}%)</span>
                        </div>
                        <div class="w-full bg-gray-100 rounded-full h-2">
                            <div class="{{ $status['color'] }} h-2 rounded-full transition-all duration-500"
                                 style="width: {{ $percent }}%"></div>
                        </div>
                    </div>
                @empty
                    <p class="text-xs text-gray-400">Nenhum agendamento este mês.</p>
                @endforelse

                <div class="pt-2 border-t border-gray-100 mt-2">
                    <p class="text-xs text-gray-500">
                        Total do mês: <span class="font-semibold text-gray-700">{{ $appointmentsMonth }}</span>
                    </p>
                </div>
            </div>
        </div>

        {{-- Resumo Rápido --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
            <h2 class="text-sm font-semibold text-gray-700 mb-4">
                <i class="fa fa-bolt text-gray-400 mr-1"></i>
                Resumo Rápido
            </h2>
            <div class="space-y-4">
                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-600">Novos pacientes (mês)</span>
                    <span class="text-sm font-semibold text-blue-600 bg-blue-50 px-2.5 py-0.5 rounded-full">
                        {{ $newClientsMonth }}
                    </span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-600">Agendamentos hoje</span>
                    <span class="text-sm font-semibold text-sky-600 bg-sky-50 px-2.5 py-0.5 rounded-full">
                        {{ $appointmentsToday }}
                    </span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-600">Agendamentos no mês</span>
                    <span class="text-sm font-semibold text-purple-600 bg-purple-50 px-2.5 py-0.5 rounded-full">
                        {{ $appointmentsMonth }}
                    </span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-600">Receita do mês</span>
                    <span class="text-sm font-semibold text-emerald-600 bg-emerald-50 px-2.5 py-0.5 rounded-full">
                        R$ {{ number_format($monthlyRevenue, 2, ',', '.') }}
                    </span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-600">Taxa de presença</span>
                    @php
                        $completed = $appointmentsByStatus['completed'] ?? 0;
                        $noShow = $appointmentsByStatus['no_show'] ?? 0;
                        $attendanceTotal = $completed + $noShow;
                        $attendanceRate = $attendanceTotal > 0 ? round(($completed / $attendanceTotal) * 100) : 0;
                    @endphp
                    <span class="text-sm font-semibold {{ $attendanceRate >= 80 ? 'text-green-600 bg-green-50' : 'text-red-600 bg-red-50' }} px-2.5 py-0.5 rounded-full">
                        {{ $attendanceRate }}%
                    </span>
                </div>
            </div>
        </div>

        {{-- Ações Rápidas --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
            <h2 class="text-sm font-semibold text-gray-700 mb-4">
                <i class="fa fa-rocket text-gray-400 mr-1"></i>
                Ações Rápidas
            </h2>
            <div class="space-y-2">
                <a href="{{ route('admin.agenda') }}"
                   class="flex items-center gap-3 p-3 rounded-lg hover:bg-gray-50 transition group">
                    <div class="w-9 h-9 rounded-lg bg-blue-100 text-blue-600 flex items-center justify-center">
                        <i class="fa fa-calendar text-sm"></i>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-700 group-hover:text-blue-600">Ver Agenda</p>
                        <p class="text-xs text-gray-400">Consultar agendamentos do dia</p>
                    </div>
                </a>
                <a href="{{ route('employees.index') }}"
                   class="flex items-center gap-3 p-3 rounded-lg hover:bg-gray-50 transition group">
                    <div class="w-9 h-9 rounded-lg bg-orange-100 text-orange-600 flex items-center justify-center">
                        <i class="fa fa-users-gear text-sm"></i>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-700 group-hover:text-orange-600">Colaboradores</p>
                        <p class="text-xs text-gray-400">Gerenciar equipe</p>
                    </div>
                </a>
                <a href="{{ route('employees.create') }}"
                   class="flex items-center gap-3 p-3 rounded-lg hover:bg-gray-50 transition group">
                    <div class="w-9 h-9 rounded-lg bg-green-100 text-green-600 flex items-center justify-center">
                        <i class="fa fa-user-plus text-sm"></i>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-700 group-hover:text-green-600">Novo Colaborador</p>
                        <p class="text-xs text-gray-400">Cadastrar funcionário</p>
                    </div>
                </a>
            </div>
        </div>
    </div>

    {{-- Últimos Agendamentos --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200">
        <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
            <h2 class="text-sm font-semibold text-gray-700">
                <i class="fa fa-list text-gray-400 mr-1"></i>
                Últimos Agendamentos
            </h2>
            <a href="{{ route('admin.agenda') }}" class="text-xs text-blue-500 hover:text-blue-700 font-medium">
                Ver todos →
            </a>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-gray-500 text-xs uppercase">
                    <tr>
                        <th class="px-5 py-3 text-left font-medium">Paciente</th>
                        <th class="px-5 py-3 text-left font-medium">Profissional</th>
                        <th class="px-5 py-3 text-left font-medium">Serviço</th>
                        <th class="px-5 py-3 text-left font-medium">Data/Hora</th>
                        <th class="px-5 py-3 text-center font-medium">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($latestAppointments as $apt)
                        @php
                            $statusColors = [
                                'pending'   => 'bg-yellow-100 text-yellow-700',
                                'confirmed' => 'bg-blue-100 text-blue-700',
                                'completed' => 'bg-green-100 text-green-700',
                                'cancelled' => 'bg-red-100 text-red-700',
                                'no_show'   => 'bg-gray-100 text-gray-600',
                            ];
                            $statusNames = [
                                'pending'   => 'Pendente',
                                'confirmed' => 'Confirmado',
                                'completed' => 'Concluído',
                                'cancelled' => 'Cancelado',
                                'no_show'   => 'Não Compareceu',
                            ];
                        @endphp
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-5 py-3 text-gray-700">
                                {{ $apt->client?->name ?? '—' }}
                            </td>
                            <td class="px-5 py-3 text-gray-700">
                                {{ $apt->professional?->user?->name ?? '—' }}
                            </td>
                            <td class="px-5 py-3 text-gray-500">
                                {{ $apt->service?->name ?? '—' }}
                            </td>
                            <td class="px-5 py-3 text-gray-500">
                                {{ \Carbon\Carbon::parse($apt->start_at)->format('d/m/Y H:i') }}
                            </td>
                            <td class="px-5 py-3 text-center">
                                <span class="inline-block px-2.5 py-0.5 rounded-full text-xs font-medium {{ $statusColors[$apt->status] ?? 'bg-gray-100 text-gray-600' }}">
                                    {{ $statusNames[$apt->status] ?? $apt->status }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-5 py-8 text-center text-gray-400 text-sm">
                                <i class="fa fa-calendar-xmark text-2xl mb-2 block"></i>
                                Nenhum agendamento encontrado.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
