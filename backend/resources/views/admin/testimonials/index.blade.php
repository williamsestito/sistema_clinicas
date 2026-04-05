@extends('layouts.app')
@section('title', 'Depoimentos')
@section('content')
<div class="p-6">
    <div class="flex justify-between items-center mb-4">
        <h1 class="text-xl font-semibold text-gray-700">Depoimentos</h1>
        <a href="{{ route('admin.testimonials.create') }}"
           class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-md text-sm font-medium">
            <i class="bi bi-plus-lg"></i> Novo Depoimento
        </a>
    </div>

    <div class="bg-white p-4 rounded-lg shadow mb-4">
        <form method="GET" action="{{ route('admin.testimonials.index') }}" class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label class="text-sm text-gray-600">Pesquisar</label>
                <input type="text" name="search" placeholder="Nome do cliente"
                       value="{{ request('search') }}"
                       class="w-full border rounded-md px-3 py-2 text-sm">
            </div>
            <div>
                <label class="text-sm text-gray-600">Visibilidade</label>
                <select name="visible" class="w-full border rounded-md px-3 py-2 text-sm">
                    <option value="">Todos</option>
                    <option value="1" {{ request('visible') === '1' ? 'selected' : '' }}>Visivel</option>
                    <option value="0" {{ request('visible') === '0' ? 'selected' : '' }}>Oculto</option>
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
                    <th class="px-4 py-2 font-medium text-gray-700">Cliente</th>
                    <th class="px-4 py-2 font-medium text-gray-700">Avaliacao</th>
                    <th class="px-4 py-2 font-medium text-gray-700">Comentario</th>
                    <th class="px-4 py-2 font-medium text-gray-700">Visibilidade</th>
                    <th class="px-4 py-2 font-medium text-gray-700 text-center">Acoes</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($testimonials as $testimonial)
                    <tr class="border-b hover:bg-gray-50">
                        <td class="px-4 py-2 font-medium">
                            <div class="flex items-center space-x-2">
                                @if($testimonial->photo_url)
                                    <img src="{{ $testimonial->photo_url }}" alt="" class="h-8 w-8 rounded-full object-cover">
                                @else
                                    <span class="h-8 w-8 rounded-full bg-gray-200 flex items-center justify-center text-gray-500 text-xs">
                                        <i class="bi bi-person"></i>
                                    </span>
                                @endif
                                <span>{{ $testimonial->client_name }}</span>
                            </div>
                        </td>
                        <td class="px-4 py-2">
                            @for($i = 1; $i <= 5; $i++)
                                <i class="bi bi-star{{ $i <= $testimonial->rating ? '-fill' : '' }} text-yellow-400 text-xs"></i>
                            @endfor
                        </td>
                        <td class="px-4 py-2 text-gray-500">{{ Str::limit($testimonial->comment, 50) }}</td>
                        <td class="px-4 py-2">
                            @if($testimonial->visible)
                                <span class="px-2 py-1 text-xs font-semibold bg-green-100 text-green-700 rounded">Visivel</span>
                            @else
                                <span class="px-2 py-1 text-xs font-semibold bg-gray-100 text-gray-600 rounded">Oculto</span>
                            @endif
                        </td>
                        <td class="px-4 py-2 flex justify-center space-x-3">
                            <a href="{{ route('admin.testimonials.edit', $testimonial->id) }}"
                               class="text-blue-600 hover:text-blue-800 text-sm" title="Editar">
                                <i class="bi bi-pencil-square"></i>
                            </a>
                            <form action="{{ route('admin.testimonials.destroy', $testimonial->id) }}" method="POST">
                                @csrf
                                @method('DELETE')
                                <button type="button" onclick="confirmDelete(this.closest('form'), 'este depoimento')"
                                        class="text-red-600 hover:text-red-800 text-sm" title="Excluir">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center text-gray-500 py-4">Nenhum depoimento encontrado.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        <div class="p-4">
            {{ $testimonials->withQueryString()->links() }}
        </div>
    </div>
</div>
@endsection
