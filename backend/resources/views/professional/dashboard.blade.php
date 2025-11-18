@extends('layouts.app')

@section('title', 'Dashboard do Profissional')

@section('content')
<div class="w-full flex justify-center px-3 sm:px-4 md:px-6 lg:px-8">

    <div class="w-full max-w-5xl py-6">

        {{-- TÍTULO --}}
        <h1 class="text-xl md:text-2xl font-semibold text-gray-800 mb-6 flex items-center gap-2">
            📊 Dashboard Profissional
        </h1>

        {{-- ========================================================= --}}
        {{-- CARDS DE RESUMO --}}
        {{-- ========================================================= --}}
        <div class="grid grid-cols-2 sm:grid-cols-2 md:grid-cols-4 gap-4 mb-8">

            <x-dashboard-card icon="fa-calendar-check"
                bg="bg-green-50"
                text="text-green-700"
                label="Atendimentos Hoje"
                :value="$totalConfirmadosHoje" />

            <x-dashboard-card icon="fa-hourglass-half"
                bg="bg-yellow-50"
                text="text-yellow-700"
                label="Pendentes"
                :value="$totalPendentes" />

            <x-dashboard-card icon="fa-ban"
                bg="bg-red-50"
                text="text-red-700"
                label="Dias Bloqueados"
                :value="$bloqueiosAtivos" />

            <x-dashboard-card icon="fa-users"
                bg="bg-blue-50"
                text="text-blue-700"
                label="Pacientes no Mês"
                :value="$pacientesMes" />
        </div>


        {{-- ========================================================= --}}
        {{-- PRÓXIMOS ATENDIMENTOS --}}
        {{-- ========================================================= --}}
        <div class="bg-white shadow-sm border border-gray-200 rounded-xl p-5 mb-8">
            <h2 class="text-lg font-semibold text-gray-800 mb-4 flex items-center gap-2">
                📅 Próximos Atendimentos
            </h2>

            @if($proximosAtendimentos->isEmpty())
                <p class="text-sm text-gray-500">Nenhum atendimento agendado.</p>

            @else
                <div class="space-y-4">

                    @foreach($proximosAtendimentos as $a)
                        <div class="border border-gray-200 rounded-lg p-4 flex flex-col sm:flex-row sm:justify-between sm:items-center gap-3">

                            <div>
                                <p class="text-gray-900 font-medium text-sm">
                                    {{ $a->client->name ?? 'Paciente removido' }}
                                </p>
                                <p class="text-xs text-gray-500 mt-1">
                                    {{ \Carbon\Carbon::parse($a->date)->format('d/m/Y H:i') }} —
                                    {{ ucfirst($a->status) }}
                                </p>
                            </div>

                            <div>
                                @if($a->status === 'pending')
                                    <span class="px-3 py-1 rounded-full text-xs bg-yellow-100 text-yellow-700">Pendente</span>

                                @elseif($a->status === 'confirmed')
                                    <span class="px-3 py-1 rounded-full text-xs bg-green-100 text-green-700">Confirmado</span>

                                @else
                                    <span class="px-3 py-1 rounded-full text-xs bg-gray-100 text-gray-700">Finalizado</span>
                                @endif
                            </div>

                        </div>
                    @endforeach

                </div>
            @endif
        </div>


        {{-- ========================================================= --}}
        {{-- ÚLTIMOS PACIENTES ATENDIDOS --}}
        {{-- ========================================================= --}}
        <div class="bg-white shadow-sm border border-gray-200 rounded-xl p-5">

            <h2 class="text-lg font-semibold text-gray-800 mb-4 flex items-center gap-2">
                🩺 Últimos Pacientes Atendidos
            </h2>

            @if($ultimosPacientes->isEmpty())
                <p class="text-sm text-gray-500">Nenhum atendimento finalizado recentemente.</p>

            @else

                {{-- Tabela Desktop --}}
                <div class="hidden md:block">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="text-left text-gray-600 border-b">
                                <th class="py-2">Paciente</th>
                                <th>Data</th>
                                <th>Status</th>
                            </tr>
                        </thead>

                        <tbody>
                            @foreach($ultimosPacientes as $a)
                                <tr class="border-b">
                                    <td class="py-3">{{ $a->client->name ?? 'Paciente removido' }}</td>
                                    <td>{{ \Carbon\Carbon::parse($a->date)->format('d/m/Y H:i') }}</td>
                                    <td>
                                        <span class="px-3 py-1 rounded-full text-xs bg-green-100 text-green-700">
                                            Finalizado
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Cards Mobile --}}
                <div class="md:hidden space-y-3">
                    @foreach($ultimosPacientes as $a)
                        <div class="border border-gray-200 rounded-lg p-4">
                            <p class="font-medium text-gray-800 text-sm">
                                {{ $a->client->name ?? 'Paciente removido' }}
                            </p>
                            <p class="text-xs text-gray-500 mt-1">
                                {{ \Carbon\Carbon::parse($a->date)->format('d/m/Y H:i') }}
                            </p>
                            <span class="px-3 py-1 rounded-full text-xs bg-green-100 text-green-700 mt-2 inline-block">
                                Finalizado
                            </span>
                        </div>
                    @endforeach
                </div>

            @endif

        </div>
    </div>
</div>
@endsection
