@extends('layouts.app')

@section('title', 'Procedimentos')

@section('content')
<div class="p-6">
  <div class="flex justify-between items-center mb-4">
    <h1 class="text-xl font-semibold text-gray-700">Procedimentos</h1>
    <a href="{{ route('professional.dashboard') }}" class="text-sm text-blue-600 hover:underline">
      ← Voltar ao Dashboard
    </a>
  </div>

  <div class="bg-white rounded-lg shadow p-6 space-y-6">

    {{-- Mensagens --}}
    @if(session('success'))
      <div class="p-3 rounded-md bg-green-50 border border-green-200 text-green-700 text-sm">
        {{ session('success') }}
      </div>
    @endif
    @if($errors->any())
      <div class="p-3 rounded-md bg-red-50 border border-red-200 text-red-700 text-sm">
        <ul class="list-disc list-inside space-y-1">
          @foreach($errors->all() as $e)
            <li>{{ $e }}</li>
          @endforeach
        </ul>
      </div>
    @endif

    {{-- Formulário --}}
    <form action="{{ route('professional.procedures.store') }}" method="POST" class="space-y-4">
      @csrf
      <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div>
          <label class="text-sm text-gray-600">Nome do Procedimento</label>
          <input type="text" name="name" required
                 value="{{ old('name') }}"
                 class="w-full border rounded-md px-3 py-2 text-sm mt-1 focus:ring-green-500 focus:border-green-500">
        </div>

        <div>
          <label class="text-sm text-gray-600">Valor (R$)</label>
          <input type="number" name="price" step="0.01" min="0"
                 value="{{ old('price') }}"
                 class="w-full border rounded-md px-3 py-2 text-sm mt-1 focus:ring-green-500 focus:border-green-500">
        </div>

        <div>
          <label class="text-sm text-gray-600">Duração (minutos)</label>
          <input type="number" name="duration_min" min="5" max="600" step="5" required
                 value="{{ old('duration_min', 30) }}"
                 class="w-full border rounded-md px-3 py-2 text-sm mt-1 text-center focus:ring-green-500 focus:border-green-500">
        </div>

        <div class="md:col-span-2">
          <label class="text-sm text-gray-600">Descrição</label>
          <input type="text" name="description"
                 value="{{ old('description') }}"
                 class="w-full border rounded-md px-3 py-2 text-sm mt-1 focus:ring-green-500 focus:border-green-500">
        </div>

        <div class="flex items-center gap-2 mt-6">
          <input id="active" type="checkbox" name="active" value="1" checked
                 class="rounded text-green-600 focus:ring-green-500">
          <label for="active" class="text-sm text-gray-700">Ativo</label>
        </div>
      </div>

      <div class="flex justify-end">
        <button type="submit"
                class="bg-green-600 hover:bg-green-700 text-white px-5 py-2 rounded-md text-sm font-medium">
          Salvar Procedimento
        </button>
      </div>
    </form>

    {{-- Lista de procedimentos --}}
    <div class="border-t pt-4 mt-6">
      @if($procedures->isEmpty())
        <p class="text-gray-500 text-sm">Nenhum procedimento cadastrado.</p>
      @else
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200 mt-3">
            <thead class="bg-gray-50">
              <tr class="text-xs text-gray-600 uppercase">
                <th class="px-4 py-3 text-left">Nome</th>
                <th class="px-4 py-3 text-left">Valor</th>
                <th class="px-4 py-3 text-left">Duração</th>
                <th class="px-4 py-3 text-left hidden md:table-cell">Descrição</th>
                <th class="px-4 py-3 text-left">Status</th>
                <th class="px-4 py-3 text-right">Ações</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
              @foreach($procedures as $proc)
                <tr class="hover:bg-gray-50 transition">
                  <td class="px-4 py-3 text-sm text-gray-800 font-medium">{{ $proc->name }}</td>
                  <td class="px-4 py-3 text-sm text-gray-700">
                    R$ {{ number_format($proc->price, 2, ',', '.') }}
                  </td>
                  <td class="px-4 py-3 text-sm text-gray-700">{{ $proc->duration_min }} min</td>
                  <td class="px-4 py-3 text-sm text-gray-600 hidden md:table-cell">
                    {{ $proc->description ?? '—' }}
                  </td>
                  <td class="px-4 py-3 text-sm">
                    <span class="px-2 py-1 rounded text-xs font-medium
                      {{ $proc->active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600' }}">
                      {{ $proc->active ? 'Ativo' : 'Inativo' }}
                    </span>
                  </td>
                  <td class="px-4 py-3 text-right">
                    <form action="{{ route('professional.procedures.destroy', $proc->id) }}" method="POST"
                          onsubmit="return confirm('Remover este procedimento?');" class="inline">
                      @csrf
                      @method('DELETE')
                      <button type="submit"
                              class="text-red-600 hover:text-red-800 text-sm font-medium">
                        Remover
                      </button>
                    </form>
                  </td>
                </tr>
              @endforeach
            </tbody>
          </table>
        </div>
      @endif
    </div>
  </div>
</div>
@endsection
