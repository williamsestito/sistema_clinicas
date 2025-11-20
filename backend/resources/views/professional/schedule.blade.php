@extends('layouts.app')

@section('title', 'Minha Agenda')

@section('content')
<div class="p-6" x-data="{ openBlockModal: false }">

    {{-- Cabeçalho --}}
    <div class="flex justify-between items-center mb-6 day-nav">
        <h1 class="text-xl font-semibold text-gray-700 flex items-center gap-2">
            📅 Minha Agenda

            @if($activePeriod)
                <span class="text-sm text-gray-500 font-normal">
                    ({{ $activePeriod->start_date->format('d/m/Y') }}
                    até {{ $activePeriod->end_date->format('d/m/Y') }})
                </span>
            @else
                <span class="text-sm text-red-500 font-normal">
                    (Nenhum período ativo)
                </span>
            @endif
        </h1>

        {{-- Navegação por datas --}}
        <div class="flex items-center gap-2 day-nav">
            <a href="{{ route('professional.schedule', ['date' => $date->copy()->subDay()->toDateString()]) }}"
               class="px-3 py-1 bg-gray-200 hover:bg-gray-300 rounded text-sm transition btn-soft">
                ← Ontem
            </a>

            <a href="{{ route('professional.schedule', ['date' => now()->format('Y-m-d')]) }}"
               class="px-3 py-1 bg-green-600 hover:bg-green-700 text-white rounded text-sm transition btn-soft">
                Hoje
            </a>

            <a href="{{ route('professional.schedule', ['date' => $date->copy()->addDay()->toDateString()]) }}"
               class="px-3 py-1 bg-gray-200 hover:bg-gray-300 rounded text-sm transition btn-soft">
                Amanhã →
            </a>
        </div>
    </div>

    {{-- Data formatada --}}
    <p class="text-gray-500 mb-4 flex items-center gap-2">
        <i class="fa-regular fa-calendar"></i>
        {{ $date->translatedFormat('l, d \\d\\e F \\d\\e Y') }}
    </p>

    {{-- Ações --}}
    <div class="flex justify-between items-center mb-4">
        <a href="{{ route('professional.schedule.period.index') }}"
           class="text-blue-600 hover:underline text-sm">
            ⚙️ Configurar Agenda
        </a>

        <button @click="openBlockModal = true"
                class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-md text-sm transition btn-soft">
            <i class="fa-solid fa-ban mr-1"></i> Bloquear Data
        </button>
    </div>

    {{-- Mensagem de erro --}}
    @isset($error)
        <div class="p-4 bg-red-100 text-red-700 border border-red-200 rounded mb-6">
            <strong>Atenção:</strong> {{ $error }}
        </div>
    @endisset

    {{-- Grade de horários --}}
    @if(isset($slots) && $slots->count())
        <div class="bg-white p-5 rounded-lg shadow-soft mb-10 card-clean">

            <h3 class="font-semibold text-gray-700 mb-4 flex items-center gap-2">
                <i class="fa-regular fa-clock text-blue-500"></i>
                Horários do dia
            </h3>

            <div class="grid slot-grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-3">
                @foreach($slots as $slot)
                    @php
                        $color = match($slot['type']) {
                            'available' => 'slot-available',
                            'occupied'  => 'slot-occupied',
                            'blocked'   => 'slot-blocked',
                            default     => 'slot-lunch'
                        };
                    @endphp

                    <div class="border rounded p-2 text-center text-sm font-medium {{ $color }}">
                        {{ $slot['start'] }} - {{ $slot['end'] }}
                    </div>
                @endforeach
            </div>

            {{-- Legenda --}}
            <div class="mt-5 grid grid-cols-2 md:grid-cols-4 gap-3 text-xs text-gray-600">

                <div class="flex items-center gap-2">
                    <span class="w-4 h-4 bg-green-50 border border-green-400 rounded"></span>
                    Disponível
                </div>

                <div class="flex items-center gap-2">
                    <span class="w-4 h-4 bg-yellow-50 border border-yellow-400 rounded"></span>
                    Ocupado
                </div>

                <div class="flex items-center gap-2">
                    <span class="w-4 h-4 bg-gray-100 border border-gray-400 rounded"></span>
                    Intervalo
                </div>

                <div class="flex items-center gap-2">
                    <span class="w-4 h-4 bg-red-50 border border-red-400 rounded"></span>
                    Bloqueado
                </div>
            </div>

        </div>
    @endif

    {{-- Lista de Agendamentos --}}
    <div class="mt-10">

        <h3 class="font-semibold text-gray-700 mb-2 flex items-center gap-2">
            <i class="fa-solid fa-user-clock text-blue-500"></i> Agendamentos do dia
        </h3>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @forelse($appointments as $appt)

                <div class="p-4 bg-white rounded-lg shadow-soft hover-soft border border-gray-100 transition">
                    <div class="flex justify-between items-center mb-2">

                        <h2 class="font-semibold text-gray-800 text-sm">
                            {{ $appt->client->name ?? 'Paciente não identificado' }}
                        </h2>

                        <span class="tag
                            @class([
                                'bg-yellow-100 text-yellow-700' => $appt->status === 'pending',
                                'bg-green-100 text-green-700' => $appt->status === 'confirmed',
                                'bg-gray-200 text-gray-600' => $appt->status === 'done',
                                'bg-red-100 text-red-700' => $appt->status === 'cancelled',
                            ])
                        ">
                            {{ ucfirst($appt->status) }}
                        </span>
                    </div>

                    <p class="text-sm text-gray-600 flex items-center gap-1">
                        <i class="fa-regular fa-clock"></i>
                        {{ $appt->start_at->format('H:i') }} – {{ $appt->end_at->format('H:i') }}
                    </p>

                    <p class="text-sm text-gray-600 flex items-center gap-1">
                        <i class="fa-solid fa-stethoscope"></i>
                        {{ $appt->service->name ?? '—' }}
                    </p>

                    @if($appt->notes)
                        <p class="text-xs text-gray-500 mt-2 italic border-l-2 border-gray-200 pl-2">
                            “{{ $appt->notes }}”
                        </p>
                    @endif
                </div>

            @empty

                <div class="col-span-full text-center text-gray-500 p-6 bg-white rounded-md shadow-soft">
                    Nenhum agendamento encontrado para esta data.
                </div>

            @endforelse
        </div>
    </div>

    {{-- Dias bloqueados --}}
    <div class="mt-10">

        <h3 class="font-semibold text-gray-700 mb-2 flex items-center gap-2">
            <i class="fa-solid fa-ban text-red-500"></i> Dias Bloqueados
        </h3>

        @if($blocked->count())

            <ul class="space-y-1 text-sm text-gray-700">

                @foreach($blocked as $b)
                    <li class="flex justify-between items-center bg-red-50 px-3 py-2 rounded-md border border-red-100">

                        <div>
                            🗓️ {{ $b->date->format('d/m/Y') }}
                            <span class="text-gray-500">— {{ $b->reason ?? 'Bloqueio manual' }}</span>
                        </div>

                        <form action="{{ route('professional.schedule.blocked.destroy', $b->id) }}"
                              method="POST"
                              onsubmit="return confirm('Desbloquear este dia?')">
                            @csrf
                            @method('DELETE')

                            <button class="text-xs text-red-600 hover:underline">Remover</button>
                        </form>

                    </li>
                @endforeach

            </ul>

        @else

            <p class="text-sm text-gray-500 italic">
                Nenhum bloqueio cadastrado neste período.
            </p>

        @endif

    </div>

    {{-- Modal Bloquear Dia --}}
    <div x-show="openBlockModal"
         class="fixed inset-0 modal-backdrop flex items-center justify-center z-50"
         x-cloak
         @keydown.escape.window="openBlockModal = false">

        <form action="{{ route('professional.schedule.blocked.store') }}"
              method="POST"
              class="modal-card shadow-soft space-y-4">
            @csrf

            <h2 class="text-lg font-semibold text-gray-800 flex items-center gap-2">
                <i class="fa-solid fa-ban text-red-600"></i> Bloquear Data
            </h2>

            <div>
                <label class="label">Data</label>
                <input type="date" name="date" required class="input-premium w-full">
            </div>

            <div>
                <label class="label">Motivo (opcional)</label>
                <input type="text" name="reason" class="input-premium w-full"
                       placeholder="Ex: Folga, manutenção, viagem...">
            </div>

            <div class="flex justify-end gap-2 pt-2">
                <button type="button"
                        @click="openBlockModal = false"
                        class="text-gray-600 hover:underline text-sm btn-soft">
                    Cancelar
                </button>

                <button type="submit"
                        class="bg-red-600 hover:bg-red-700 text-white text-sm px-4 py-2 rounded-md btn-soft">
                    Confirmar Bloqueio
                </button>
            </div>

        </form>
    </div>

</div>
@endsection
