@extends('layouts.app')

@section('title', 'Pacientes Atendidos')

@section('content')
<div class="p-6 space-y-6">

  {{-- Cabeçalho --}}
  <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 mb-4">
    <h1 class="text-2xl font-semibold text-gray-800">Pacientes Atendidos</h1>
    <a href="{{ route('professional.dashboard') }}" 
       class="text-sm text-blue-600 hover:text-blue-800 transition">
      ← Voltar ao Dashboard
    </a>
  </div>

  {{-- Mensagens --}}
  @if(session('success'))
    <div class="p-3 rounded-md bg-green-50 border border-green-200 text-green-700 text-sm">
      {{ session('success') }}
    </div>
  @endif

  @if(session('error'))
    <div class="p-3 rounded-md bg-red-50 border border-red-200 text-red-700 text-sm">
      {{ session('error') }}
    </div>
  @endif

  {{-- Lista de Pacientes --}}
  @if($pacients->isEmpty())
    <p class="text-gray-500 text-sm">Nenhum paciente atendido até o momento.</p>
  @else
    {{-- Container responsivo da tabela --}}
    <div class="bg-white shadow-sm border border-gray-100 rounded-xl overflow-hidden">
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr class="text-xs font-semibold text-gray-600 uppercase tracking-wider">
              <th class="px-4 py-3 text-left">Nome</th>
              <th class="px-4 py-3 text-left hidden sm:table-cell">Telefone</th>
              <th class="px-4 py-3 text-left hidden md:table-cell">Último Atendimento</th>
              <th class="px-4 py-3 text-right">Ações</th>
            </tr>
          </thead>

          <tbody class="divide-y divide-gray-100">
            @foreach($pacients as $p)
              @php
                $lastAppointment = \App\Models\Appointment::where('client_id', $p->id)
                    ->where('professional_id', auth()->user()->professional->id)
                    ->latest('start_at')
                    ->first();
              @endphp

              <tr class="hover:bg-gray-50 transition-colors">
                {{-- Nome --}}
                <td class="px-4 py-3 text-sm text-gray-800 font-medium">
                  <div class="flex flex-col">
                    <span>{{ $p->name }}</span>
                    {{-- Exibe telefone abaixo em telas menores --}}
                    <span class="text-xs text-gray-500 sm:hidden mt-0.5">
                      {{ $p->phone ?? '—' }}
                    </span>
                    {{-- Exibe data abaixo em telas pequenas --}}
                    <span class="text-xs text-gray-400 md:hidden">
                      {{ $lastAppointment ? $lastAppointment->start_at->format('d/m/Y H:i') : '—' }}
                    </span>
                  </div>
                </td>

                {{-- Telefone --}}
                <td class="px-4 py-3 text-sm text-gray-600 hidden sm:table-cell">
                  {{ $p->phone ?? '—' }}
                </td>

                {{-- Último Atendimento --}}
                <td class="px-4 py-3 text-sm text-gray-600 hidden md:table-cell">
                  {{ $lastAppointment ? $lastAppointment->start_at->format('d/m/Y H:i') : '—' }}
                </td>

                {{-- Ações --}}
                <td class="px-4 py-3 text-right">
                  <a href="{{ route('professional.pacients.show', $p->id) }}" 
                     class="inline-flex items-center gap-1 text-blue-600 hover:text-blue-800 text-sm font-medium transition">
                    <svg xmlns="http://www.w3.org/2000/svg" 
                         class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268 2.943 9.542 7-1.274 4.057-5.065 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                    </svg>
                    Ver Detalhes
                  </a>
                </td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    </div>
  @endif
</div>
@endsection
