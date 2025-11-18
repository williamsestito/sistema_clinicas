@extends('layouts.app')

@section('title', 'Definir Período da Agenda')

@section('content')
<div class="p-6 max-w-3xl mx-auto space-y-8">

    {{-- Cabeçalho --}}
    <h1 class="text-2xl font-semibold text-gray-800 flex items-center gap-2">
        🗓️ Definir Período da Agenda
    </h1>

    {{-- Mensagens de retorno --}}
    @if(session('success'))
        <div class="alert-success">
            {{ session('success') }}
        </div>
    @endif

    @if($errors->any())
        <div class="alert-error">
            <ul class="list-disc list-inside text-sm">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif


    {{-- FORMULÁRIO PRINCIPAL --}}
    <form action="{{ route('professional.schedule.period.save') }}"
          method="POST"
          class="card-clean shadow-soft p-6 space-y-6">

        @csrf

        {{-- 1. Datas --}}
        <h2 class="section-title mb-2">
            📅 Período de Atendimentos
        </h2>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

            <div>
                <label class="form-label">Data inicial</label>
                <input type="date"
                       name="start_date"
                       value="{{ old('start_date') }}"
                       required
                       class="input-premium w-full">
            </div>

            <div>
                <label class="form-label">Data final</label>
                <input type="date"
                       name="end_date"
                       value="{{ old('end_date') }}"
                       required
                       class="input-premium w-full">
            </div>

        </div>


        {{-- 2. Dias ativos --}}
        <div>
            <label class="form-label">Dias da semana com atendimento</label>

            <div class="grid grid-cols-3 md:grid-cols-7 gap-2 mt-3">

                @foreach(['Dom','Seg','Ter','Qua','Qui','Sex','Sáb'] as $i => $dia)
                    <label class="checkbox-day">
                        <input type="checkbox"
                               name="active_days[]"
                               value="{{ $i }}"
                               class="checkbox-blue">
                        {{ $dia }}
                    </label>
                @endforeach

            </div>

            <p class="text-xs text-gray-500 mt-1">
                Marque os dias em que você atenderá durante esse período.
            </p>
        </div>


        {{-- Ações --}}
        <div class="flex justify-between items-center pt-4">

            <a href="{{ route('professional.schedule.config') }}"
               class="btn-link flex items-center gap-1">
                ← Voltar para Configuração
            </a>

            <button type="submit"
                    class="btn-primary px-5 py-2">
                Salvar Período
            </button>
        </div>

    </form>

</div>
@endsection
