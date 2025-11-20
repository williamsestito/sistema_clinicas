@extends('layouts.app')

@section('title', 'Configurar Agenda')

@section('content')
<div class="p-6 max-w-6xl mx-auto space-y-12">

    {{-- Selecionar Período --}}
    @include('partials.schedule._period_selector', [
        'selectedPeriod' => $selectedPeriod,
        'periods'        => $periods,
    ])

    {{-- Criar Novo Período --}}
    @include('partials.schedule._period_form')

    {{-- Lista de Períodos Existentes --}}
    @include('partials.schedule._period_list', [
        'periods' => $periods
    ])

    {{-- Configuração de Horários Semanais --}}
    @include('partials.schedule._day_form', [
        'days'            => $days,
        'weeklySchedules' => $weeklySchedules,
        'selectedPeriod'  => $selectedPeriod,
    ])

    {{-- Bloqueio de Datas + Lista --}}
    <section class="space-y-6">
        @include('partials.schedule._blocked_form')

        @include('partials.schedule._blocked_list', [
            'blocked' => $blocked
        ])
    </section>

</div>
@endsection

@push('scripts')
    {{-- Scripts Ajax do módulo de Agenda --}}
    @include('partials.schedule._scripts')
@endpush
