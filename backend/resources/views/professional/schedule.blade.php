@extends('layouts.app')

@section('title', 'Minha Agenda')

@section('content')
<div class="p-6" x-data="{ loading: false }">
  <div class="flex justify-between items-center mb-6">
    <h1 class="text-xl font-semibold text-gray-700">📅 Minha Agenda</h1>
    <div class="flex items-center gap-2">
      <a href="{{ route('professional.schedule', ['date' => now()->subDay()->toDateString()]) }}"
         class="px-3 py-1 bg-gray-200 hover:bg-gray-300 rounded text-sm">← Ontem</a>
      <a href="{{ route('professional.schedule', ['date' => now()->toDateString()]) }}"
         class="px-3 py-1 bg-green-600 hover:bg-green-700 text-white rounded text-sm">Hoje</a>
      <a href="{{ route('professional.schedule', ['date' => now()->addDay()->toDateString()]) }}"
         class="px-3 py-1 bg-gray-200 hover:bg-gray-300 rounded text-sm">Amanhã →</a>
    </div>
  </div>

  <!-- Data atual -->
  <p class="text-gray-500 mb-3">
    <i class="fa-regular fa-calendar"></i> {{ $date->translatedFormat('l, d \\d\\e F \\d\\e Y') }}
  </p>

  <!-- Lista de agendamentos -->
  <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
    @forelse($appointments as $appt)
      <div class="p-4 bg-white rounded-lg shadow border border-gray-100">
        <div class="flex justify-between items-center mb-2">
          <h2 class="font-semibold text-gray-800 text-sm">
            {{ $appt->client->name ?? 'Paciente não identificado' }}
          </h2>
          <span class="text-xs px-2 py-0.5 rounded-full
            @class([
              'bg-yellow-100 text-yellow-700' => $appt->status === 'pending',
              'bg-green-100 text-green-700' => $appt->status === 'confirmed',
              'bg-gray-200 text-gray-600' => $appt->status === 'done',
              'bg-red-100 text-red-700' => $appt->status === 'cancelled',
            ])">
            {{ ucfirst($appt->status) }}
          </span>
        </div>

        <p class="text-sm text-gray-600">
          <i class="fa-regular fa-clock"></i>
          {{ \Carbon\Carbon::parse($appt->start_at)->format('H:i') }} -
          {{ \Carbon\Carbon::parse($appt->end_at)->format('H:i') }}
        </p>
        <p class="text-sm text-gray-600">
          <i class="fa-solid fa-stethoscope"></i> {{ $appt->service->name ?? '—' }}
        </p>
        @if($appt->notes)
          <p class="text-xs text-gray-500 mt-1 italic">“{{ $appt->notes }}”</p>
        @endif
      </div>
    @empty
      <div class="col-span-full text-center text-gray-500 p-6 bg-white rounded-md shadow">
        Nenhum agendamento encontrado para esta data.
      </div>
    @endforelse
  </div>

  <!-- Bloqueios -->
  @if($blocked->count())
    <div class="mt-8">
      <h3 class="font-semibold text-gray-700 mb-2"><i class="fa-solid fa-ban text-red-500"></i> Horários Bloqueados</h3>
      <ul class="space-y-1 text-sm text-gray-600">
        @foreach($blocked as $b)
          <li>🕒 {{ \Carbon\Carbon::parse($b->date)->format('d/m/Y') }} — {{ $b->reason ?? 'Bloqueado manualmente' }}</li>
        @endforeach
      </ul>
    </div>
  @endif

  <!-- Botão bloquear -->
  <div class="mt-6 text-right">
    <button class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-md text-sm">
      <i class="fa-solid fa-ban mr-1"></i> Bloquear Horário
    </button>
  </div>
</div>
@endsection
