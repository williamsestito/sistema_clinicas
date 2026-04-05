@extends('layouts.app')
@section('title', 'Profissionais')
@section('content')
<div class="p-6">
  <div class="flex justify-between items-center mb-4">
    <h1 class="text-xl font-semibold text-gray-700">Profissionais</h1>
    <a href="{{ route('admin.professionals.create') }}"
       class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-md text-sm font-medium">
      <i class="bi bi-plus-lg"></i> Novo Profissional
    </a>
  </div>

  {{-- Filtros --}}
  <div class="bg-white p-4 rounded-lg shadow mb-4">
    <form method="GET" action="{{ route('admin.professionals.index') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4">
      <div>
        <label class="text-sm text-gray-600">Pesquisar</label>
        <input type="text" name="search" placeholder="Nome ou e-mail"
               value="{{ request('search') }}"
               class="w-full border rounded-md px-3 py-2 text-sm">
      </div>
      <div>
        <label class="text-sm text-gray-600">Especialidade</label>
        <input type="text" name="specialty" placeholder="Filtrar por especialidade"
               value="{{ request('specialty') }}"
               class="w-full border rounded-md px-3 py-2 text-sm">
      </div>
      <div>
        <label class="text-sm text-gray-600">Status</label>
        <select name="active" class="w-full border rounded-md px-3 py-2 text-sm">
          <option value="">Todos</option>
          <option value="1" {{ request('active') === '1' ? 'selected' : '' }}>Ativo</option>
          <option value="0" {{ request('active') === '0' ? 'selected' : '' }}>Inativo</option>
        </select>
      </div>
      <div class="flex items-end">
        <button type="submit"
                class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md text-sm w-full">
          Filtrar
        </button>
      </div>
    </form>
  </div>

  {{-- Tabela --}}
  <div class="bg-white rounded-lg shadow overflow-x-auto">
    <table class="min-w-full text-sm text-left">
      <thead class="bg-gray-100">
        <tr>
          <th class="px-4 py-2 font-medium text-gray-700">Nome</th>
          <th class="px-4 py-2 font-medium text-gray-700">E-mail</th>
          <th class="px-4 py-2 font-medium text-gray-700">Especialidade</th>
          <th class="px-4 py-2 font-medium text-gray-700">Consulta (min)</th>
          <th class="px-4 py-2 font-medium text-gray-700">Status</th>
          <th class="px-4 py-2 font-medium text-gray-700 text-center">Ações</th>
        </tr>
      </thead>
      <tbody>
        @forelse ($profissionais as $prof)
          <tr class="border-b hover:bg-gray-50">
            <td class="px-4 py-2">{{ $prof->user->name ?? '-' }}</td>
            <td class="px-4 py-2">{{ $prof->user->email ?? '-' }}</td>
            <td class="px-4 py-2">{{ $prof->specialty ?? '-' }}</td>
            <td class="px-4 py-2">{{ $prof->default_consultation_time }} min</td>
            <td class="px-4 py-2">
              @if($prof->active)
                <span class="px-2 py-1 text-xs font-semibold bg-green-100 text-green-700 rounded">Ativo</span>
              @else
                <span class="px-2 py-1 text-xs font-semibold bg-red-100 text-red-700 rounded">Inativo</span>
              @endif
            </td>
            <td class="px-4 py-2 flex justify-center space-x-3">
              <a href="{{ route('admin.professionals.edit', $prof->id) }}"
                 class="text-blue-600 hover:text-blue-800 text-sm" title="Editar">
                <i class="bi bi-pencil-square"></i>
              </a>

              <form action="{{ route('admin.professionals.destroy', $prof->id) }}" method="POST">
                @csrf
                @method('DELETE')
                <button type="button" onclick="confirmDelete(this.closest('form'), 'este profissional')"
                        class="text-red-600 hover:text-red-800 text-sm">
                  <i class="bi bi-trash"></i>
                </button>
              </form>
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="6" class="text-center text-gray-500 py-4">Nenhum profissional encontrado.</td>
          </tr>
        @endforelse
      </tbody>
    </table>

    <div class="p-4">
      {{ $profissionais->withQueryString()->links() }}
    </div>
  </div>
</div>
@endsection
