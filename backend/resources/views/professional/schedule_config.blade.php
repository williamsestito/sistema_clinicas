@extends('layouts.app')

@section('title', 'Configurar Agenda')

@section('content')
<div class="p-6 max-w-6xl mx-auto space-y-10">

    {{-- Seletor de Períodos --}}
    @include('partials.schedule._period_selector', [
        'selectedPeriod' => $selectedPeriod,
        'periods'        => $periods,
    ])

    {{-- Formulário Criar Período --}}
    @include('partials.schedule._period_form')

    {{-- Lista de Períodos --}}
    @include('partials.schedule._period_list', [
        'periods' => $periods
    ])

    {{-- Formulário de Horários Semanais --}}
    @include('partials.schedule._day_form', [
        'days'            => $days,
        'weeklySchedules' => $weeklySchedules,
        'selectedPeriod'  => $selectedPeriod,
    ])

    {{-- Formulário de Bloqueio --}}
    @include('partials.schedule._blocked_form', [
        'selectedPeriod' => $selectedPeriod
    ])

    {{-- Lista de Bloqueios --}}
    @include('partials.schedule._blocked_list', [
        'blocked' => $blocked
    ])

</div>

@include('partials.schedule._scripts')
@endsection
