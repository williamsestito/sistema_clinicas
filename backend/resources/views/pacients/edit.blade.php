@extends('layouts.app')

@section('content')
<div class="p-6">
  <div class="flex justify-between items-center mb-4">
    <h1 class="text-xl font-semibold text-gray-700">Editar Paciente</h1>
    <a href="{{ route('pacients.index') }}" class="text-sm text-blue-600 hover:underline">← Voltar para lista</a>
  </div>

  <div x-data="{ aba: 'pessoal' }" class="bg-white rounded-lg shadow p-6">
    <div class="flex border-b border-gray-200 mb-4">
      <button @click="aba = 'pessoal'" 
              :class="aba === 'pessoal' ? 'border-b-2 border-green-600 text-green-700' : 'text-gray-500'"
              class="px-4 py-2 text-sm font-medium">Dados Pessoais</button>
      <button @click="aba = 'localizacao'"
              :class="aba === 'localizacao' ? 'border-b-2 border-green-600 text-green-700' : 'text-gray-500'"
              class="px-4 py-2 text-sm font-medium">Endereço</button>
    </div>

    <form method="POST" action="{{ route('pacients.update', $pacient->id) }}" class="space-y-4">
      @csrf
      @method('PUT')

      <div x-show="aba === 'pessoal'" x-transition>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">

          <!-- Nome e nome social -->
          <div x-data="{ usarNomeSocial: {{ old('social_name', $pacient->social_name ?? false) ? 'true' : 'false' }} }" class="md:col-span-3">
            <input type="hidden" name="social_name" :value="usarNomeSocial ? 1 : 0">
            <div class="flex items-end gap-3">
              <div class="flex-1">
                <label class="text-sm text-gray-600">Nome completo</label>
                <input type="text" name="name" value="{{ old('name', $pacient->name) }}" required
                       class="w-full border rounded-md px-3 py-2 text-sm mt-1">
              </div>
              <div class="shrink-0 min-w-[150px] flex items-center gap-2 mb-1">
                <input type="checkbox" x-model="usarNomeSocial" class="rounded text-green-600">
                <span class="text-sm text-gray-700 select-none">É nome social?</span>
              </div>
            </div>
            <div x-show="usarNomeSocial" x-transition class="mt-2">
              <input type="text" name="social_name_text" value="{{ old('social_name_text', $pacient->social_name_text ?? '') }}"
                     placeholder="Digite o nome social"
                     class="w-full border rounded-md px-3 py-2 text-sm">
              <p class="text-xs text-gray-500 mt-1">* Nome utilizado conforme a LGPD.</p>
            </div>
          </div>

          <div>
            <label class="text-sm text-gray-600">Data de nascimento</label>
            <input type="date" name="birth_date" value="{{ old('birth_date', optional($pacient->birth_date)->format('Y-m-d')) }}"
                   class="w-full border rounded-md px-3 py-2 text-sm">
          </div>

          <div>
            <label class="text-sm text-gray-600">CPF</label>
            <input type="text" name="document" value="{{ old('document', $pacient->document) }}"
                   class="w-full border rounded-md px-3 py-2 text-sm">
          </div>

          <div>
            <label class="text-sm text-gray-600">E-mail</label>
            <input type="email" name="email" value="{{ old('email', $pacient->email) }}"
                   class="w-full border rounded-md px-3 py-2 text-sm">
          </div>

          <div>
            <label class="text-sm text-gray-600">Celular</label>
            <input type="text" name="phone" value="{{ old('phone', $pacient->phone) }}"
                   class="w-full border rounded-md px-3 py-2 text-sm" placeholder="(00) 99999-9999">
          </div>

          <div>
            <label class="text-sm text-gray-600">Status</label>
            <select name="active" class="w-full border rounded-md px-3 py-2 text-sm">
              <option value="1" {{ $pacient->active ? 'selected' : '' }}>Ativo</option>
              <option value="0" {{ !$pacient->active ? 'selected' : '' }}>Inativo</option>
            </select>
          </div>
        </div>
      </div>

      <!-- Endereço -->
      <div x-show="aba === 'localizacao'" x-transition>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
          <div><label class="text-sm text-gray-600">CEP</label>
            <input type="text" name="cep" value="{{ old('cep', $pacient->cep) }}" class="w-full border rounded-md px-3 py-2 text-sm">
          </div>
          <div class="md:col-span-2">
            <label class="text-sm text-gray-600">Endereço</label>
            <input type="text" name="address" value="{{ old('address', $pacient->address) }}"
                   class="w-full border rounded-md px-3 py-2 text-sm">
          </div>
          <div><label class="text-sm text-gray-600">Número</label>
            <input type="text" name="number" value="{{ old('number', $pacient->number) }}"
                   class="w-full border rounded-md px-3 py-2 text-sm">
          </div>
          <div><label class="text-sm text-gray-600">Bairro</label>
            <input type="text" name="district" value="{{ old('district', $pacient->district) }}"
                   class="w-full border rounded-md px-3 py-2 text-sm">
          </div>
          <div><label class="text-sm text-gray-600">Cidade</label>
            <input type="text" name="city" value="{{ old('city', $pacient->city) }}"
                   class="w-full border rounded-md px-3 py-2 text-sm">
          </div>
          <div><label class="text-sm text-gray-600">Estado</label>
            <input type="text" name="state" value="{{ old('state', $pacient->state) }}"
                   class="w-full border rounded-md px-3 py-2 text-sm">
          </div>
        </div>
      </div>

      <div class="flex justify-end mt-4">
        <button type="submit"
                class="bg-green-600 hover:bg-green-700 text-white px-5 py-2 rounded-md text-sm font-medium">
          Atualizar Paciente
        </button>
      </div>
    </form>
  </div>
</div>
@endsection
