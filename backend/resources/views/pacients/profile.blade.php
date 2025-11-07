@extends('layouts.app')

@section('title', 'Meus Dados')

@section('content')
<div class="p-6">
  <h1 class="text-xl font-semibold text-gray-700 mb-4">👤 Meus Dados</h1>

  @php
      $user = Auth::user();
      $camposObrigatorios = ['phone', 'address', 'city', 'state'];
      $faltando = collect($camposObrigatorios)->some(fn($campo) => empty($user->$campo));
  @endphp

  @if($faltando)
    <div class="bg-yellow-100 border border-yellow-300 text-yellow-800 text-sm p-3 rounded-md mb-4">
      ⚠️ Complete seus dados abaixo para continuar agendando consultas.
    </div>
  @endif

  <div class="bg-white p-6 rounded-lg shadow text-sm text-gray-600">
    <p><strong>Nome:</strong> {{ $user->name }}</p>
    <p><strong>E-mail:</strong> {{ $user->email }}</p>
    <p><strong>Telefone:</strong> {{ $user->phone ?? '—' }}</p>
    <p><strong>Endereço:</strong> {{ $user->address ?? '—' }}</p>
    <p><strong>Cidade:</strong> {{ $user->city ?? '—' }}</p>
    <p><strong>Estado:</strong> {{ $user->state ?? '—' }}</p>
  </div>
</div>
@endsection
