@extends('layouts.app')

@section('title', 'Agenda | Clínica Fácil')

@section('content')
<div class="bg-white rounded-xl shadow p-6">
  <div class="flex flex-col md:flex-row md:items-center justify-between mb-4">
    <h1 class="text-2xl font-semibold text-gray-700 mb-4 md:mb-0">Agenda</h1>
    <button class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg font-medium">
      + Novo Agendamento
    </button>
  </div>

  <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-4">
    <input type="text" placeholder="Filtrar por status" class="border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-green-400">
    <input type="text" placeholder="Filtrar por paciente" class="border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-green-400">
    <input type="text" placeholder="Sala/Convênio" class="border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-green-400">
    <input type="text" placeholder="Profissional" class="border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-green-400">
  </div>

  <div class="overflow-x-auto">
    <table class="min-w-full text-sm text-left border border-gray-200">
      <thead class="bg-gray-50">
        <tr>
          <th class="px-4 py-2 border-b">Hora</th>
          <th class="px-4 py-2 border-b">Seg</th>
          <th class="px-4 py-2 border-b">Ter</th>
          <th class="px-4 py-2 border-b">Qua</th>
          <th class="px-4 py-2 border-b">Qui</th>
          <th class="px-4 py-2 border-b">Sex</th>
        </tr>
      </thead>
      <tbody>
        @for ($h = 7; $h <= 18; $h++)
          <tr class="hover:bg-gray-50">
            <td class="px-4 py-2 border-b font-medium text-gray-600">{{ sprintf('%02d:00', $h) }}</td>
            <td class="px-4 py-2 border-b"></td>
            <td class="px-4 py-2 border-b"></td>
            <td class="px-4 py-2 border-b"></td>
            <td class="px-4 py-2 border-b"></td>
            <td class="px-4 py-2 border-b"></td>
          </tr>
        @endfor
      </tbody>
    </table>
  </div>

  <p class="text-xs text-gray-500 mt-4">
    Por padrão, a agenda exibe horários das 07h às 18h. Você pode ajustar seus horários em 
    <a href="#" class="text-green-600 hover:underline">Configurações da Agenda</a>.
  </p>
</div>
@endsection
