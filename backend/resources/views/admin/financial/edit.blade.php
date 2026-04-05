@extends('layouts.app')
@section('title', 'Editar Lançamento')

@section('content')
<div class="p-6">

    <div class="flex items-center justify-between mb-4">
        <h1 class="text-xl font-bold text-gray-800">
            <i class="fa fa-edit text-gray-400 mr-1"></i> Editar Lançamento
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

    <form action="{{ route('admin.financial.update', $entry->id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="bg-white rounded-lg shadow-sm border p-6 space-y-5">

            {{-- Tipo --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Tipo *</label>
                <div class="flex gap-4">
                    <label class="flex items-center gap-2 cursor-pointer px-4 py-2 rounded-lg border {{ old('type', $entry->type) === 'income' ? 'border-green-500 bg-green-50' : 'border-gray-200' }} transition">
                        <input type="radio" name="type" value="income" {{ old('type', $entry->type) === 'income' ? 'checked' : '' }}
                               class="text-green-600">
                        <i class="fa fa-arrow-up text-green-600"></i>
                        <span class="text-sm font-medium text-green-700">Receita</span>
                    </label>
                    <label class="flex items-center gap-2 cursor-pointer px-4 py-2 rounded-lg border {{ old('type', $entry->type) === 'expense' ? 'border-red-500 bg-red-50' : 'border-gray-200' }} transition">
                        <input type="radio" name="type" value="expense" {{ old('type', $entry->type) === 'expense' ? 'checked' : '' }}
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
                    <input type="text" name="description" value="{{ old('description', $entry->description) }}"
                           class="w-full border rounded-md px-3 py-2 text-sm" required
                           placeholder="Ex: Consulta dermatológica, Aluguel da sala...">
                </div>

                {{-- Valor --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Valor (R$) *</label>
                    <input type="number" name="amount" value="{{ old('amount', $entry->amount) }}"
                           class="w-full border rounded-md px-3 py-2 text-sm" required
                           step="0.01" min="0.01" placeholder="0,00">
                </div>

                {{-- Data --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Data *</label>
                    <input type="date" name="date" value="{{ old('date', $entry->date->format('Y-m-d')) }}"
                           class="w-full border rounded-md px-3 py-2 text-sm" required>
                </div>

                {{-- Categoria --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Categoria</label>
                    <select name="category_id" class="w-full border rounded-md px-3 py-2 text-sm">
                        <option value="">Sem categoria</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" {{ old('category_id', $entry->category_id) == $cat->id ? 'selected' : '' }}>
                                {{ $cat->name }} ({{ $cat->type === 'income' ? 'Receita' : 'Despesa' }})
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Forma de Pagamento --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Forma de Pagamento *</label>
                    <select name="payment_method" class="w-full border rounded-md px-3 py-2 text-sm" required>
                        @php
                            $pm = old('payment_method', $entry->payment_method);
                        @endphp
                        <option value="cash" {{ $pm === 'cash' ? 'selected' : '' }}>Dinheiro</option>
                        <option value="credit_card" {{ $pm === 'credit_card' ? 'selected' : '' }}>Cartão de Crédito</option>
                        <option value="debit_card" {{ $pm === 'debit_card' ? 'selected' : '' }}>Cartão de Débito</option>
                        <option value="pix" {{ $pm === 'pix' ? 'selected' : '' }}>PIX</option>
                        <option value="transfer" {{ $pm === 'transfer' ? 'selected' : '' }}>Transferência</option>
                        <option value="plan" {{ $pm === 'plan' ? 'selected' : '' }}>Convênio</option>
                        <option value="other" {{ $pm === 'other' ? 'selected' : '' }}>Outro</option>
                    </select>
                </div>
            </div>

            {{-- Observações --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Observações</label>
                <textarea name="notes" rows="3" class="w-full border rounded-md px-3 py-2 text-sm"
                          placeholder="Observações opcionais...">{{ old('notes', $entry->notes) }}</textarea>
            </div>

            @if($entry->appointment_id)
                <div class="bg-blue-50 border border-blue-200 text-blue-700 text-sm rounded-md p-3">
                    <i class="fa fa-link mr-1"></i>
                    Vinculado ao agendamento #{{ $entry->appointment_id }}
                </div>
            @endif

            {{-- Botão --}}
            <div class="flex">
                <button type="submit" class="bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-medium px-6 py-2.5 rounded-md transition">
                    <i class="fa fa-check mr-1"></i> Atualizar Lançamento
                </button>
            </div>
        </div>
    </form>
</div>
@endsection
