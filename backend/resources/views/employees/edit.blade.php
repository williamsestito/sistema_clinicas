@extends('layouts.app')

@section('content')
<div class="p-6">
  <div class="flex justify-between items-center mb-4">
    <h1 class="text-xl font-semibold text-gray-700">Editar Colaborador</h1>
    <a href="{{ route('employees.index') }}" class="text-sm text-blue-600 hover:underline">← Voltar para lista</a>
  </div>

  <div x-data="{ aba: 'pessoal' }" class="bg-white rounded-lg shadow p-6">
    <div class="flex border-b border-gray-200 mb-4">
      <button @click="aba = 'pessoal'"
              :class="aba === 'pessoal' ? 'border-b-2 border-green-600 text-green-700' : 'text-gray-500'"
              class="px-4 py-2 text-sm font-medium focus:outline-none">
        Dados Pessoais
      </button>
      <button @click="aba = 'localizacao'"
              :class="aba === 'localizacao' ? 'border-b-2 border-green-600 text-green-700' : 'text-gray-500'"
              class="px-4 py-2 text-sm font-medium focus:outline-none">
        Dados de Localização
      </button>
    </div>

    <form method="POST" action="{{ route('employees.update', $usuario->id) }}" class="space-y-4">
      @csrf
      @method('PUT')

      <div x-show="aba === 'pessoal'" x-transition>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">

          <!-- Nome (90%) + checkbox (10%) na mesma linha -->
          <div x-data="{ usarNomeSocial: {{ old('social_name', $usuario->social_name ?? false) ? 'true' : 'false' }} }"
               class="md:col-span-3">
            <!-- sempre enviar 0/1 para o backend -->
            <input type="hidden" name="social_name" :value="usarNomeSocial ? 1 : 0">

            <div class="flex items-end gap-3">
              <!-- bloco do NOME ~90% -->
              <div class="flex-1">
                <label class="text-sm text-gray-600">Nome</label>
                <input type="text" name="name" value="{{ old('name', $usuario->name) }}" required
                       class="w-full border rounded-md px-3 py-2 text-sm mt-1">
              </div>

              <!-- bloco do checkbox ~10% (largura mínima p/ não quebrar) -->
              <div class="shrink-0 min-w-[150px] flex items-center gap-2 mb-1">
                <input type="checkbox" x-model="usarNomeSocial" class="rounded text-green-600">
                <span class="text-sm text-gray-700 select-none">É nome social?</span>
              </div>
            </div>

            <!-- input do Nome Social 100% abaixo do Nome -->
            <div x-show="usarNomeSocial" x-transition class="mt-2">
              <input type="text" name="social_name_text"
                     value="{{ old('social_name_text', $usuario->social_name_text ?? '') }}"
                     placeholder="Digite o nome social"
                     class="w-full border rounded-md px-3 py-2 text-sm">
              <p class="text-xs text-gray-500 mt-1">
                * Utilizado em comunicações internas conforme a LGPD.
              </p>
            </div>
          </div>

          <div>
            <label class="text-sm text-gray-600">Data de nascimento</label>
            <input type="date" name="birth_date"
                   value="{{ old('birth_date', optional($usuario->birth_date)->format('Y-m-d')) }}"
                   class="w-full border rounded-md px-3 py-2 text-sm">
          </div>

          <div>
            <label class="text-sm text-gray-600">Documento (CPF)</label>
            <input type="text" name="document" value="{{ old('document', $usuario->document ?? '') }}"
                   class="w-full border rounded-md px-3 py-2 text-sm">
          </div>

          <div>
            <label class="text-sm text-gray-600">RG</label>
            <input type="text" name="rg" value="{{ old('rg', $usuario->rg ?? '') }}"
                   class="w-full border rounded-md px-3 py-2 text-sm">
          </div>

          <div>
            <label class="text-sm text-gray-600">Estado civil</label>
            <select name="civil_status" class="w-full border rounded-md px-3 py-2 text-sm">
              <option value="">Selecione</option>
              <option value="solteiro"   {{ old('civil_status', $usuario->civil_status ?? '') == 'solteiro'   ? 'selected' : '' }}>Solteiro(a)</option>
              <option value="casado"     {{ old('civil_status', $usuario->civil_status ?? '') == 'casado'     ? 'selected' : '' }}>Casado(a)</option>
              <option value="divorciado" {{ old('civil_status', $usuario->civil_status ?? '') == 'divorciado' ? 'selected' : '' }}>Divorciado(a)</option>
              <option value="viuvo"      {{ old('civil_status', $usuario->civil_status ?? '') == 'viuvo'      ? 'selected' : '' }}>Viúvo(a)</option>
            </select>
          </div>

          <div>
            <label class="text-sm text-gray-600">Sexo / Gênero</label>
            <select name="gender" class="w-full border rounded-md px-3 py-2 text-sm">
              <option value="">Selecione</option>
              <option value="masculino" {{ old('gender', $usuario->gender ?? '') == 'masculino' ? 'selected' : '' }}>Masculino</option>
              <option value="feminino"  {{ old('gender', $usuario->gender ?? '') == 'feminino'  ? 'selected' : '' }}>Feminino</option>
              <option value="outro"     {{ old('gender', $usuario->gender ?? '') == 'outro'     ? 'selected' : '' }}>Outro</option>
            </select>
          </div>

          <div>
            <label class="text-sm text-gray-600">E-mail</label>
            <input type="email" name="email" value="{{ old('email', $usuario->email) }}" required
                   class="w-full border rounded-md px-3 py-2 text-sm">
          </div>

          <div>
            <label class="text-sm text-gray-600">Celular</label>
            <input type="text" name="phone" value="{{ old('phone', $usuario->phone ?? '') }}"
                   class="w-full border rounded-md px-3 py-2 text-sm" placeholder="(00) 99999-9999">
          </div>

          <div>
            <label class="text-sm text-gray-600">Função</label>
            <select name="role" required class="w-full border rounded-md px-3 py-2 text-sm">
              <option value="admin"        {{ $usuario->role == 'admin'        ? 'selected' : '' }}>Administrador</option>
              <option value="professional" {{ $usuario->role == 'professional' ? 'selected' : '' }}>Profissional</option>
              <option value="frontdesk"    {{ $usuario->role == 'frontdesk'    ? 'selected' : '' }}>Recepção</option>
            </select>
          </div>

          <div>
            <label class="text-sm text-gray-600">Nova senha (opcional)</label>
            <input type="password" name="password" class="w-full border rounded-md px-3 py-2 text-sm">
          </div>

          <div>
            <label class="text-sm text-gray-600">Confirmar nova senha</label>
            <input type="password" name="password_confirmation" class="w-full border rounded-md px-3 py-2 text-sm">
          </div>

          <div>
            <label class="text-sm text-gray-600">Status</label>
            <select name="active" class="w-full border rounded-md px-3 py-2 text-sm">
              <option value="1" {{ $usuario->active ? 'selected' : '' }}>Ativo</option>
              <option value="0" {{ !$usuario->active ? 'selected' : '' }}>Inativo</option>
            </select>
          </div>

        </div>
      </div>

      <div x-show="aba === 'localizacao'" x-transition>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
          <div>
            <label class="text-sm text-gray-600">CEP</label>
            <input type="text" name="cep" value="{{ old('cep', $usuario->cep ?? '') }}"
                   class="w-full border rounded-md px-3 py-2 text-sm" placeholder="00000-000">
          </div>
          <div class="md:col-span-2">
            <label class="text-sm text-gray-600">Endereço</label>
            <input type="text" name="address" value="{{ old('address', $usuario->address ?? '') }}"
                   class="w-full border rounded-md px-3 py-2 text-sm">
          </div>
          <div>
            <label class="text-sm text-gray-600">Número</label>
            <input type="text" name="number" value="{{ old('number', $usuario->number ?? '') }}"
                   class="w-full border rounded-md px-3 py-2 text-sm">
          </div>
          <div>
            <label class="text-sm text-gray-600">Bairro</label>
            <input type="text" name="district" value="{{ old('district', $usuario->district ?? '') }}"
                   class="w-full border rounded-md px-3 py-2 text-sm">
          </div>
          <div>
            <label class="text-sm text-gray-600">Cidade</label>
            <input type="text" name="city" value="{{ old('city', $usuario->city ?? '') }}"
                   class="w-full border rounded-md px-3 py-2 text-sm">
          </div>
          <div>
            <label class="text-sm text-gray-600">Estado</label>
            <input type="text" name="state" value="{{ old('state', $usuario->state ?? '') }}"
                   class="w-full border rounded-md px-3 py-2 text-sm">
          </div>
        </div>
      </div>

      <div class="flex justify-end mt-6">
        <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-5 py-2 rounded-md text-sm font-medium">
          Atualizar Colaborador
        </button>
      </div>
    </form>
  </div>
</div>
@endsection
