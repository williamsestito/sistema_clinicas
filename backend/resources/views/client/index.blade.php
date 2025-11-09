@extends('layouts.app')

@section('content')
<div class="p-6">
  <div class="flex justify-between items-center mb-4">
    <h1 class="text-xl font-semibold text-gray-700">Pacientes</h1>
    <a href="{{ route('pacients.create') }}" 
       class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-md text-sm font-medium">
      + Novo Paciente
    </a>
  </div>

  <!-- Filtros -->
  <div class="bg-white p-4 rounded-lg shadow mb-4">
    <form method="GET" action="{{ route('pacients.index') }}" class="grid grid-cols-1 md:grid-cols-3 gap-4">
      <div>
        <label class="text-sm text-gray-600">Status</label>
        <select name="active" class="w-full border rounded-md px-3 py-2 text-sm">
          <option value="">Todos</option>
          <option value="1" {{ request('active') == '1' ? 'selected' : '' }}>Ativo</option>
          <option value="0" {{ request('active') == '0' ? 'selected' : '' }}>Inativo</option>
        </select>
      </div>

      <div>
        <label class="text-sm text-gray-600">Pesquisar</label>
        <input type="text" name="search" placeholder="Nome, e-mail ou telefone"
               value="{{ request('search') }}"
               class="w-full border rounded-md px-3 py-2 text-sm">
      </div>

      <div class="md:col-span-3 flex justify-end">
        <button type="submit" 
                class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md text-sm">
          Filtrar
        </button>
      </div>
    </form>
  </div>

  <!-- Tabela -->
  <div class="bg-white rounded-lg shadow overflow-x-auto">
    <table class="min-w-full text-sm text-left">
      <thead class="bg-gray-100">
        <tr>
          <th class="px-4 py-2 font-medium text-gray-700">Nome</th>
          <th class="px-4 py-2 font-medium text-gray-700">E-mail</th>
          <th class="px-4 py-2 font-medium text-gray-700">Telefone</th>
          <th class="px-4 py-2 font-medium text-gray-700">Status</th>
          <th class="px-4 py-2 font-medium text-gray-700 text-center">Ações</th>
        </tr>
      </thead>
      <tbody>
        @forelse ($pacients as $pacient)
          <tr class="border-b hover:bg-gray-50">
            <td class="px-4 py-2">{{ $pacient->social_name ? $pacient->social_name_text : $pacient->name }}</td>
            <td class="px-4 py-2">{{ $pacient->email }}</td>
            <td class="px-4 py-2">{{ $pacient->phone }}</td>
            <td class="px-4 py-2">
              @if($pacient->active)
                <span class="px-2 py-1 text-xs font-semibold bg-green-100 text-green-700 rounded">Ativo</span>
              @else
                <span class="px-2 py-1 text-xs font-semibold bg-red-100 text-red-700 rounded">Inativo</span>
              @endif
            </td>
            <td class="px-4 py-2 flex justify-center space-x-3">
              <a href="{{ route('pacients.edit', $pacient->id) }}" 
                 class="text-blue-600 hover:underline text-sm">Editar</a>
              
              <form action="{{ route('pacients.destroy', $pacient->id) }}" method="POST"
                    onsubmit="return confirm('Deseja realmente excluir este paciente?')">
                @csrf
                @method('DELETE')
                <button type="submit" class="text-red-600 hover:underline text-sm">Excluir</button>
              </form>
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="5" class="text-center text-gray-500 py-4">Nenhum paciente encontrado.</td>
          </tr>
        @endforelse
      </tbody>
    </table>

    <div class="p-4">
      {{ $pacients->links() }}
    </div>
  </div>
</div>
@endsection
