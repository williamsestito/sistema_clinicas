@extends('layouts.app')

@section('content')
<div class="p-6">
  <div class="flex justify-between items-center mb-4">
    <h1 class="text-xl font-semibold text-gray-700">Colaboradores</h1>
    <a href="{{ route('employees.create') }}" 
       class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-md text-sm font-medium">
      + Adicionar Colaborador
    </a>
  </div>

  <!-- Filtros -->
  <div class="bg-white p-4 rounded-lg shadow mb-4">
    <form method="GET" action="{{ route('employees.index') }}" class="grid grid-cols-1 md:grid-cols-3 gap-4">
      <div>
        <label class="text-sm text-gray-600">Função</label>
        <select name="role" class="w-full border rounded-md px-3 py-2 text-sm">
          <option value="">Todas</option>
          <option value="owner" {{ request('role') == 'owner' ? 'selected' : '' }}>Proprietário</option>
          <option value="admin" {{ request('role') == 'admin' ? 'selected' : '' }}>Administrador</option>
          <option value="professional" {{ request('role') == 'professional' ? 'selected' : '' }}>Profissional</option>
          <option value="frontdesk" {{ request('role') == 'frontdesk' ? 'selected' : '' }}>Recepção</option>
        </select>
      </div>
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
        <input type="text" name="search" placeholder="Digite o nome ou e-mail" 
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
          <th class="px-4 py-2 font-medium text-gray-700">Função</th>
          <th class="px-4 py-2 font-medium text-gray-700">Status</th>
          <th class="px-4 py-2 font-medium text-gray-700 text-center">Ações</th>
        </tr>
      </thead>
      <tbody>
        @forelse ($usuarios as $usuario)
          <tr class="border-b hover:bg-gray-50">
            <td class="px-4 py-2">{{ $usuario->name }}</td>
            <td class="px-4 py-2">{{ $usuario->email }}</td>
            <td class="px-4 py-2 capitalize">
              @switch($usuario->role)
                @case('owner') Proprietário @break
                @case('admin') Administrador @break
                @case('professional') Profissional @break
                @case('frontdesk') Recepção @break
                @default {{ $usuario->role }}
              @endswitch
            </td>
            <td class="px-4 py-2">
              @if($usuario->active)
                <span class="px-2 py-1 text-xs font-semibold bg-green-100 text-green-700 rounded">Ativo</span>
              @else
                <span class="px-2 py-1 text-xs font-semibold bg-red-100 text-red-700 rounded">Inativo</span>
              @endif
            </td>
            <td class="px-4 py-2 flex justify-center space-x-3">
              <a href="{{ route('employees.edit', $usuario->id) }}" 
                 class="text-blue-600 hover:underline text-sm">Editar</a>
              
              <form action="{{ route('employees.destroy', $usuario->id) }}" method="POST" 
                    onsubmit="return confirm('Tem certeza que deseja excluir este colaborador?')">
                @csrf
                @method('DELETE')
                <button type="submit" class="text-red-600 hover:underline text-sm">Excluir</button>
              </form>
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="5" class="text-center text-gray-500 py-4">Nenhum colaborador encontrado.</td>
          </tr>
        @endforelse
      </tbody>
    </table>

    <div class="p-4">
      {{ $usuarios->links() }}
    </div>
  </div>
</div>
@endsection
