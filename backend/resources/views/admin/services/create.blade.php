@extends('layouts.app')
@section('title', 'Novo Serviço')
@section('content')
<div class="p-6">
  <div class="flex justify-between items-center mb-4">
    <h1 class="text-xl font-semibold text-gray-700">Novo Serviço</h1>
    <a href="{{ route('admin.services.index') }}" class="text-sm text-blue-600 hover:underline">← Voltar para lista</a>
  </div>

  {{-- Erros --}}
  @if($errors->any())
    <div class="bg-red-100 border border-red-300 text-red-800 px-4 py-2 rounded mb-4 text-sm">
      <ul class="list-disc list-inside">
        @foreach($errors->all() as $error)
          <li>{{ $error }}</li>
        @endforeach
      </ul>
    </div>
  @endif

  <div class="bg-white rounded-lg shadow p-6">
    <form method="POST" action="{{ route('admin.services.store') }}" class="space-y-4">
      @csrf

      <div class="grid grid-cols-1 md:grid-cols-3 gap-4">

        <div class="md:col-span-2">
          <label class="text-sm text-gray-600">Nome do Serviço</label>
          <input type="text" name="name" value="{{ old('name') }}" required
                 class="w-full border rounded-md px-3 py-2 text-sm"
                 placeholder="Ex: Consulta de Dermatologia">
        </div>

        <div>
          <label class="text-sm text-gray-600">Profissional</label>
          <select name="professional_id" required class="w-full border rounded-md px-3 py-2 text-sm">
            <option value="">Selecione</option>
            @foreach($profissionais as $prof)
              <option value="{{ $prof->id }}" {{ old('professional_id') == $prof->id ? 'selected' : '' }}>
                {{ $prof->user->name ?? 'Profissional #' . $prof->id }}
                @if($prof->specialty) — {{ $prof->specialty }} @endif
              </option>
            @endforeach
          </select>
        </div>

        <div>
          <label class="text-sm text-gray-600">Duração (minutos)</label>
          <input type="number" name="duration_min" value="{{ old('duration_min', 30) }}"
                 min="5" max="480" required
                 class="w-full border rounded-md px-3 py-2 text-sm">
        </div>

        <div>
          <label class="text-sm text-gray-600">Preço (R$)</label>
          <input type="number" name="price" value="{{ old('price', '0.00') }}"
                 min="0" step="0.01" required
                 class="w-full border rounded-md px-3 py-2 text-sm">
        </div>

        <div class="flex items-center gap-2 mt-6">
          <input type="checkbox" name="active" value="1"
                 {{ old('active', true) ? 'checked' : '' }}
                 class="rounded text-green-600">
          <span class="text-sm text-gray-700">Ativo</span>
        </div>

        <div class="md:col-span-3">
          <label class="text-sm text-gray-600">Descrição</label>
          <textarea name="description" rows="3"
                    class="w-full border rounded-md px-3 py-2 text-sm"
                    placeholder="Descrição do serviço...">{{ old('description') }}</textarea>
        </div>
      </div>

      <div class="flex justify-end mt-4">
        <button type="submit"
                class="bg-green-600 hover:bg-green-700 text-white px-5 py-2 rounded-md text-sm font-medium">
          Salvar Serviço
        </button>
      </div>
    </form>
  </div>
</div>
@endsection