@extends('layouts.app')
@section('title', 'Secoes do Site')
@section('content')
<div class="p-6">
    <div class="flex justify-between items-center mb-4">
        <h1 class="text-xl font-semibold text-gray-700">Secoes do Site</h1>
        <a href="{{ route('admin.sections.create') }}"
           class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-md text-sm font-medium">
            <i class="bi bi-plus-lg"></i> Nova Secao
        </a>
    </div>

    <div class="bg-white p-4 rounded-lg shadow mb-4">
        <form method="GET" action="{{ route('admin.sections.index') }}" class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label class="text-sm text-gray-600">Pesquisar</label>
                <input type="text" name="search" placeholder="Titulo ou slug"
                       value="{{ request('search') }}"
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

    <div class="bg-white rounded-lg shadow overflow-x-auto">
        <table class="min-w-full text-sm text-left">
            <thead class="bg-gray-100">
                <tr>
                    <th class="px-4 py-2 font-medium text-gray-700">Pos.</th>
                    <th class="px-4 py-2 font-medium text-gray-700">Titulo</th>
                    <th class="px-4 py-2 font-medium text-gray-700">Slug</th>
                    <th class="px-4 py-2 font-medium text-gray-700">Status</th>
                    <th class="px-4 py-2 font-medium text-gray-700 text-center">Acoes</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($sections as $section)
                    <tr class="border-b hover:bg-gray-50">
                        <td class="px-4 py-2">{{ $section->position }}</td>
                        <td class="px-4 py-2 font-medium">{{ $section->title }}</td>
                        <td class="px-4 py-2 text-gray-500">{{ $section->slug }}</td>
                        <td class="px-4 py-2">
                            @if($section->active)
                                <span class="px-2 py-1 text-xs font-semibold bg-green-100 text-green-700 rounded">Ativo</span>
                            @else
                                <span class="px-2 py-1 text-xs font-semibold bg-red-100 text-red-700 rounded">Inativo</span>
                            @endif
                        </td>
                        <td class="px-4 py-2 flex justify-center space-x-3">
                            <a href="{{ route('admin.sections.edit', $section->id) }}"
                               class="text-blue-600 hover:text-blue-800 text-sm" title="Editar">
                                <i class="bi bi-pencil-square"></i>
                            </a>
                            <form action="{{ route('admin.sections.destroy', $section->id) }}" method="POST">
                                @csrf
                                @method('DELETE')
                                <button type="button" onclick="confirmDelete(this.closest('form'), 'esta secao')"
                                        class="text-red-600 hover:text-red-800 text-sm" title="Excluir">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center text-gray-500 py-4">Nenhuma secao encontrada.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        <div class="p-4">
            {{ $sections->withQueryString()->links() }}
        </div>
    </div>
</div>
@endsection
