@extends('layouts.app')
@section('title', 'Categorias Financeiras')

@section('content')
<div class="p-6">

    <div class="flex items-center justify-between mb-4">
        <h1 class="text-xl font-bold text-gray-800">
            <i class="fa fa-tags text-gray-400 mr-1"></i> Categorias Financeiras
        </h1>
        <a href="{{ route('admin.financial.index') }}" class="text-sm text-blue-600 hover:text-blue-800">
            <i class="fa fa-arrow-left mr-1"></i> Voltar ao Financeiro
        </a>
    </div>

    @if(session('success'))
        <div class="bg-green-50 border border-green-300 text-green-700 text-sm rounded-md p-3 mb-4">
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="bg-red-50 border border-red-300 text-red-700 text-sm rounded-md p-3 mb-4">
            {{ session('error') }}
        </div>
    @endif
    @if ($errors->any())
        <div class="bg-red-50 border border-red-300 text-red-700 text-sm rounded-md p-4 mb-4">
            <ul class="list-disc list-inside">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- Formulário Nova Categoria --}}
        <div>
            <div class="bg-white rounded-lg shadow-sm border p-5">
                <h2 class="text-sm font-bold text-gray-700 mb-4">
                    <i class="fa fa-plus-circle text-gray-400 mr-1"></i> Nova Categoria
                </h2>

                <form action="{{ route('admin.financial.categories.store') }}" method="POST" class="space-y-4">
                    @csrf

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nome *</label>
                        <input type="text" name="name" value="{{ old('name') }}"
                               class="w-full border rounded-md px-3 py-2 text-sm" required
                               placeholder="Ex: Consulta, Aluguel, Material...">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Tipo *</label>
                        <div class="flex gap-3">
                            <label class="flex items-center gap-1.5 cursor-pointer">
                                <input type="radio" name="type" value="income" {{ old('type', 'income') === 'income' ? 'checked' : '' }}
                                       class="text-green-600">
                                <span class="text-sm text-green-700">Receita</span>
                            </label>
                            <label class="flex items-center gap-1.5 cursor-pointer">
                                <input type="radio" name="type" value="expense" {{ old('type') === 'expense' ? 'checked' : '' }}
                                       class="text-red-600">
                                <span class="text-sm text-red-700">Despesa</span>
                            </label>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Cor</label>
                        <input type="color" name="color" value="{{ old('color', '#6366f1') }}"
                               class="w-12 h-8 border rounded cursor-pointer">
                    </div>

                    <button type="submit" class="w-full bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-medium px-4 py-2 rounded-md transition">
                        <i class="fa fa-check mr-1"></i> Criar Categoria
                    </button>
                </form>
            </div>
        </div>

        {{-- Lista de Categorias --}}
        <div class="lg:col-span-2">
            <div class="bg-white rounded-lg shadow-sm border overflow-hidden">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 border-b">
                        <tr>
                            <th class="text-left px-4 py-3 font-medium text-gray-600">Cor</th>
                            <th class="text-left px-4 py-3 font-medium text-gray-600">Nome</th>
                            <th class="text-left px-4 py-3 font-medium text-gray-600">Tipo</th>
                            <th class="text-center px-4 py-3 font-medium text-gray-600">Lançamentos</th>
                            <th class="text-center px-4 py-3 font-medium text-gray-600">Status</th>
                            <th class="text-center px-4 py-3 font-medium text-gray-600">Ações</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        @forelse($categories as $cat)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3">
                                    <span class="inline-block w-4 h-4 rounded-full" style="background: {{ $cat->color ?? '#6b7280' }}"></span>
                                </td>
                                <td class="px-4 py-3 font-medium text-gray-800">{{ $cat->name }}</td>
                                <td class="px-4 py-3">
                                    @if($cat->type === 'income')
                                        <span class="inline-flex items-center gap-1 text-xs px-2 py-0.5 rounded-full bg-green-100 text-green-700">
                                            <i class="fa fa-arrow-up text-[10px]"></i> Receita
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 text-xs px-2 py-0.5 rounded-full bg-red-100 text-red-700">
                                            <i class="fa fa-arrow-down text-[10px]"></i> Despesa
                                        </span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-center text-gray-600">{{ $cat->entries_count }}</td>
                                <td class="px-4 py-3 text-center">
                                    @if($cat->active)
                                        <span class="text-xs px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-700">Ativa</span>
                                    @else
                                        <span class="text-xs px-2 py-0.5 rounded-full bg-gray-100 text-gray-500">Inativa</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-center">
                                    @if($cat->entries_count === 0)
                                        <form action="{{ route('admin.financial.categories.destroy', $cat->id) }}" method="POST"
                                              class="inline" onsubmit="return confirm('Excluir a categoria {{ $cat->name }}?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-500 hover:text-red-700" title="Excluir">
                                                <i class="fa fa-trash"></i>
                                            </button>
                                        </form>
                                    @else
                                        <span class="text-gray-300" title="Categoria possui lançamentos">
                                            <i class="fa fa-trash"></i>
                                        </span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-4 py-8 text-center text-gray-400">
                                    Nenhuma categoria cadastrada.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
