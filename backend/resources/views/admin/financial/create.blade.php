@extends('layouts.app')
@section('title', 'Novo Lançamento')

@section('content')
<div class="p-6">

    <div class="flex items-center justify-between mb-4">
        <h1 class="text-xl font-bold text-gray-800">
            <i class="fa fa-plus-circle text-gray-400 mr-1"></i> Novo Lançamento
        </h1>
        <a href="{{ route('admin.financial.index') }}" class="text-sm text-blue-600 hover:text-blue-800">
            <i class="fa fa-arrow-left mr-1"></i> Voltar
        </a>
    </div>

    @if ($errors->any())
        <div class="bg-red-50 border border-red-300 text-red-700 text-sm rounded-md p-4 mb-4">
            <ul class="list-disc list-inside">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('admin.financial.store') }}" method="POST">
        @csrf

        <div class="bg-white rounded-lg shadow-sm border p-6 space-y-5">

            {{-- Tipo --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Tipo *</label>
                <div class="flex gap-4">
                    <label class="flex items-center gap-2 cursor-pointer px-4 py-2 rounded-lg border {{ old('type', 'income') === 'income' ? 'border-green-500 bg-green-50' : 'border-gray-200' }} transition"
                           x-data x-on:click="$el.closest('form').querySelector('[name=type][value=income]').checked = true">
                        <input type="radio" name="type" value="income" {{ old('type', 'income') === 'income' ? 'checked' : '' }}
                               class="text-green-600">
                        <i class="fa fa-arrow-up text-green-600"></i>
                        <span class="text-sm font-medium text-green-700">Receita</span>
                    </label>
                    <label class="flex items-center gap-2 cursor-pointer px-4 py-2 rounded-lg border {{ old('type') === 'expense' ? 'border-red-500 bg-red-50' : 'border-gray-200' }} transition">
                        <input type="radio" name="type" value="expense" {{ old('type') === 'expense' ? 'checked' : '' }}
                               class="text-red-600">
                        <i class="fa fa-arrow-down text-red-500"></i>
                        <span class="text-sm font-medium text-red-700">Despesa</span>
                    </label>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                {{-- Descrição --}}
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Descrição *</label>
                    <input type="text" name="description" value="{{ old('description') }}"
                           class="w-full border rounded-md px-3 py-2 text-sm" required
                           placeholder="Ex: Consulta dermatológica, Aluguel da sala, Material...">
                </div>

                {{-- Valor --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Valor (R$) *</label>
                    <input type="number" name="amount" value="{{ old('amount') }}"
                           class="w-full border rounded-md px-3 py-2 text-sm" required
                           step="0.01" min="0.01" placeholder="0,00">
                </div>

                {{-- Data --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Data *</label>
                    <input type="date" name="date" value="{{ old('date', now()->format('Y-m-d')) }}"
                           class="w-full border rounded-md px-3 py-2 text-sm" required>
                </div>

                {{-- Categoria --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Categoria</label>
                    <select name="category_id" class="w-full border rounded-md px-3 py-2 text-sm">
                        <option value="">Sem categoria</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" {{ old('category_id') == $cat->id ? 'selected' : '' }}>
                                {{ $cat->name }} ({{ $cat->type === 'income' ? 'Receita' : 'Despesa' }})
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Forma de Pagamento --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Forma de Pagamento *</label>
                    <select name="payment_method" class="w-full border rounded-md px-3 py-2 text-sm" required>
                        <option value="cash" {{ old('payment_method', 'cash') === 'cash' ? 'selected' : '' }}>Dinheiro</option>
                        <option value="credit_card" {{ old('payment_method') === 'credit_card' ? 'selected' : '' }}>Cartão de Crédito</option>
                        <option value="debit_card" {{ old('payment_method') === 'debit_card' ? 'selected' : '' }}>Cartão de Débito</option>
                        <option value="pix" {{ old('payment_method') === 'pix' ? 'selected' : '' }}>PIX</option>
                        <option value="transfer" {{ old('payment_method') === 'transfer' ? 'selected' : '' }}>Transferência</option>
                        <option value="plan" {{ old('payment_method') === 'plan' ? 'selected' : '' }}>Convênio</option>
                        <option value="other" {{ old('payment_method') === 'other' ? 'selected' : '' }}>Outro</option>
                    </select>
                </div>
            </div>

            {{-- Observações --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Observações</label>
                <textarea name="notes" rows="3" class="w-full border rounded-md px-3 py-2 text-sm"
                          placeholder="Observações opcionais...">{{ old('notes') }}</textarea>
            </div>

            {{-- Botão --}}
            <div class="flex">
                <button type="submit" class="bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-medium px-6 py-2.5 rounded-md transition">
                    <i class="fa fa-check mr-1"></i> Salvar Lançamento
                </button>
            </div>
        </div>
    </form>
</div>
@endsection
