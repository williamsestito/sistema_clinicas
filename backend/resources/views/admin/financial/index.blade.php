@extends('layouts.app')
@section('title', 'Financeiro')

@section('content')
<div class="p-6 space-y-6">

    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-xl font-bold text-gray-800">
                <i class="fa fa-wallet text-gray-400 mr-1"></i> Financeiro
            </h1>
            <p class="text-sm text-gray-500 mt-1">
                {{ \Carbon\Carbon::parse($startDate)->format('d/m/Y') }} — {{ \Carbon\Carbon::parse($endDate)->format('d/m/Y') }}
            </p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('admin.financial.categories') }}"
               class="bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-medium px-4 py-2 rounded-md transition">
                <i class="fa fa-tags mr-1"></i> Categorias
            </a>
            <a href="{{ route('admin.financial.create') }}"
               class="bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-medium px-4 py-2 rounded-md transition">
                <i class="fa fa-plus mr-1"></i> Novo Lançamento
            </a>
        </div>
    </div>

    {{-- Filtros --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
        <form method="GET" action="{{ route('admin.financial.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-4 items-end">
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Data Início</label>
                <input type="date" name="start_date" value="{{ request('start_date', \Carbon\Carbon::parse($startDate)->format('Y-m-d')) }}"
                       class="w-full border rounded-md px-3 py-2 text-sm">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Data Fim</label>
                <input type="date" name="end_date" value="{{ request('end_date', \Carbon\Carbon::parse($endDate)->format('Y-m-d')) }}"
                       class="w-full border rounded-md px-3 py-2 text-sm">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Tipo</label>
                <select name="type" class="w-full border rounded-md px-3 py-2 text-sm">
                    <option value="">Todos</option>
                    <option value="income" {{ request('type') == 'income' ? 'selected' : '' }}>Receita</option>
                    <option value="expense" {{ request('type') == 'expense' ? 'selected' : '' }}>Despesa</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Categoria</label>
                <select name="category_id" class="w-full border rounded-md px-3 py-2 text-sm">
                    <option value="">Todas</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>
                            {{ $cat->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Forma de Pgto.</label>
                <select name="payment_method" class="w-full border rounded-md px-3 py-2 text-sm">
                    <option value="">Todas</option>
                    <option value="cash" {{ request('payment_method') == 'cash' ? 'selected' : '' }}>Dinheiro</option>
                    <option value="credit_card" {{ request('payment_method') == 'credit_card' ? 'selected' : '' }}>Cartão Crédito</option>
                    <option value="debit_card" {{ request('payment_method') == 'debit_card' ? 'selected' : '' }}>Cartão Débito</option>
                    <option value="pix" {{ request('payment_method') == 'pix' ? 'selected' : '' }}>PIX</option>
                    <option value="transfer" {{ request('payment_method') == 'transfer' ? 'selected' : '' }}>Transferência</option>
                    <option value="plan" {{ request('payment_method') == 'plan' ? 'selected' : '' }}>Convênio</option>
                    <option value="other" {{ request('payment_method') == 'other' ? 'selected' : '' }}>Outro</option>
                </select>
            </div>
            <div>
                <button type="submit" class="w-full bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-medium px-4 py-2 rounded-md transition">
                    <i class="fa fa-search mr-1"></i> Filtrar
                </button>
            </div>
        </form>
    </div>

    {{-- Cards Resumo --}}
    @php
        $income  = $summary->total_income ?? 0;
        $expense = $summary->total_expense ?? 0;
        $balance = $income - $expense;
    @endphp
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-white rounded-lg shadow-sm border p-5">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs text-gray-500 uppercase tracking-wide">Receitas</p>
                    <p class="text-2xl font-bold text-green-600 mt-1">R$ {{ number_format($income, 2, ',', '.') }}</p>
                </div>
                <div class="w-12 h-12 rounded-full bg-green-100 flex items-center justify-center">
                    <i class="fa fa-arrow-up text-green-600"></i>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-lg shadow-sm border p-5">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs text-gray-500 uppercase tracking-wide">Despesas</p>
                    <p class="text-2xl font-bold text-red-500 mt-1">R$ {{ number_format($expense, 2, ',', '.') }}</p>
                </div>
                <div class="w-12 h-12 rounded-full bg-red-100 flex items-center justify-center">
                    <i class="fa fa-arrow-down text-red-500"></i>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-lg shadow-sm border p-5">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs text-gray-500 uppercase tracking-wide">Saldo</p>
                    <p class="text-2xl font-bold {{ $balance >= 0 ? 'text-emerald-600' : 'text-red-600' }} mt-1">
                        R$ {{ number_format($balance, 2, ',', '.') }}
                    </p>
                </div>
                <div class="w-12 h-12 rounded-full {{ $balance >= 0 ? 'bg-emerald-100' : 'bg-red-100' }} flex items-center justify-center">
                    <i class="fa fa-scale-balanced {{ $balance >= 0 ? 'text-emerald-600' : 'text-red-600' }}"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- Gráfico fluxo diário --}}
        <div class="lg:col-span-2 bg-white rounded-xl shadow-sm border border-gray-200 p-5">
            <h2 class="text-sm font-semibold text-gray-700 mb-4">
                <i class="fa fa-chart-area text-gray-400 mr-1"></i> Fluxo de Caixa Diário
            </h2>
            <canvas id="chartCashFlow" height="120"></canvas>
        </div>

        {{-- Por categoria --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
            <h2 class="text-sm font-semibold text-gray-700 mb-4">
                <i class="fa fa-tags text-gray-400 mr-1"></i> Por Categoria
            </h2>
            @forelse($byCategory as $item)
                <div class="flex items-center justify-between py-2 {{ !$loop->last ? 'border-b border-gray-100' : '' }}">
                    <div class="flex items-center gap-2">
                        <span class="w-3 h-3 rounded-full" style="background-color: {{ $item->cat_color ?? '#6B7280' }}"></span>
                        <span class="text-sm text-gray-700">{{ $item->cat_name }}</span>
                        <span class="text-xs px-1.5 py-0.5 rounded {{ $item->type === 'income' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                            {{ $item->type === 'income' ? 'R' : 'D' }}
                        </span>
                    </div>
                    <span class="text-sm font-semibold {{ $item->type === 'income' ? 'text-green-600' : 'text-red-500' }}">
                        R$ {{ number_format($item->total, 2, ',', '.') }}
                    </span>
                </div>
            @empty
                <p class="text-xs text-gray-400">Nenhum dado no período.</p>
            @endforelse
        </div>
    </div>

    {{-- Tabela de lançamentos --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
            <h2 class="text-sm font-semibold text-gray-700">
                <i class="fa fa-list text-gray-400 mr-1"></i> Lançamentos
            </h2>
            <span class="text-xs text-gray-400">{{ $summary->total_entries ?? 0 }} registros</span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="bg-gray-50 text-xs text-gray-500 uppercase">
                    <tr>
                        <th class="px-4 py-3">Data</th>
                        <th class="px-4 py-3">Tipo</th>
                        <th class="px-4 py-3">Descrição</th>
                        <th class="px-4 py-3">Categoria</th>
                        <th class="px-4 py-3">Forma Pgto.</th>
                        <th class="px-4 py-3 text-right">Valor</th>
                        <th class="px-4 py-3 text-center">Ações</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @php
                        $methodLabels = [
                            'cash'        => 'Dinheiro',
                            'credit_card' => 'Crédito',
                            'debit_card'  => 'Débito',
                            'pix'         => 'PIX',
                            'transfer'    => 'Transferência',
                            'plan'        => 'Convênio',
                            'other'       => 'Outro',
                        ];
                    @endphp
                    @forelse($entries as $entry)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 whitespace-nowrap">{{ $entry->date->format('d/m/Y') }}</td>
                            <td class="px-4 py-3">
                                @if($entry->type === 'income')
                                    <span class="px-2 py-0.5 text-xs rounded-full bg-green-100 text-green-800">
                                        <i class="fa fa-arrow-up mr-0.5"></i> Receita
                                    </span>
                                @else
                                    <span class="px-2 py-0.5 text-xs rounded-full bg-red-100 text-red-800">
                                        <i class="fa fa-arrow-down mr-0.5"></i> Despesa
                                    </span>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                <span class="font-medium text-gray-700">{{ $entry->description }}</span>
                                @if($entry->appointment_id)
                                    <span class="text-xs text-blue-500 ml-1" title="Vinculado a agendamento">
                                        <i class="fa fa-calendar-check"></i>
                                    </span>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                @if($entry->category)
                                    <span class="inline-flex items-center gap-1 text-xs">
                                        <span class="w-2 h-2 rounded-full" style="background-color: {{ $entry->category->color }}"></span>
                                        {{ $entry->category->name }}
                                    </span>
                                @else
                                    <span class="text-xs text-gray-400">—</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-xs text-gray-600">
                                {{ $methodLabels[$entry->payment_method] ?? $entry->payment_method }}
                            </td>
                            <td class="px-4 py-3 text-right font-semibold {{ $entry->type === 'income' ? 'text-green-600' : 'text-red-500' }}">
                                {{ $entry->type === 'expense' ? '- ' : '' }}R$ {{ number_format($entry->amount, 2, ',', '.') }}
                            </td>
                            <td class="px-4 py-3 text-center">
                                <div class="flex items-center justify-center gap-1">
                                    <a href="{{ route('admin.financial.edit', $entry->id) }}"
                                       class="p-1.5 text-blue-600 hover:bg-blue-50 rounded" title="Editar">
                                        <i class="fa fa-pen-to-square text-sm"></i>
                                    </a>
                                    <form method="POST" action="{{ route('admin.financial.destroy', $entry->id) }}" class="inline"
                                          onsubmit="return confirmDelete(event, 'Excluir este lançamento?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="p-1.5 text-red-600 hover:bg-red-50 rounded" title="Excluir">
                                            <i class="fa fa-trash-can text-sm"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-8 text-center text-gray-400">
                                Nenhum lançamento encontrado no período.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-4 py-3 border-t border-gray-100">
            {{ $entries->links() }}
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const ctx = document.getElementById('chartCashFlow');
    if (!ctx) return;

    // Agrupar por data
    const raw = @json($byDay);
    const dates = [...new Set(raw.map(r => r.date))].sort();
    const incomeData = dates.map(d => {
        const found = raw.find(r => r.date === d && r.type === 'income');
        return found ? parseFloat(found.total) : 0;
    });
    const expenseData = dates.map(d => {
        const found = raw.find(r => r.date === d && r.type === 'expense');
        return found ? parseFloat(found.total) : 0;
    });
    const labels = dates.map(d => {
        const parts = d.split('-');
        return parts[2] + '/' + parts[1];
    });

    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [
                {
                    label: 'Receitas',
                    data: incomeData,
                    backgroundColor: 'rgba(34, 197, 94, 0.6)',
                    borderColor: 'rgb(34, 197, 94)',
                    borderWidth: 1,
                    borderRadius: 4,
                },
                {
                    label: 'Despesas',
                    data: expenseData,
                    backgroundColor: 'rgba(239, 68, 68, 0.6)',
                    borderColor: 'rgb(239, 68, 68)',
                    borderWidth: 1,
                    borderRadius: 4,
                }
            ]
        },
        options: {
            responsive: true,
            plugins: { legend: { position: 'top' } },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: { callback: v => 'R$ ' + v.toLocaleString('pt-BR') }
                }
            }
        }
    });
});
</script>
@endpush
