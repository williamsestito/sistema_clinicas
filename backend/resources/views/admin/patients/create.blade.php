@extends('layouts.app')
@section('title', 'Novo Paciente')
@section('content')
<div class="p-6">
  <div class="flex justify-between items-center mb-4">
    <h1 class="text-xl font-semibold text-gray-700">Novo Paciente</h1>
    <a href="{{ route('admin.patients.index') }}" class="text-sm text-blue-600 hover:underline">← Voltar para lista</a>
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

  <div x-data="{ aba: 'pessoal' }" class="bg-white rounded-lg shadow p-6">

    {{-- Abas --}}
    <div class="flex border-b border-gray-200 mb-4">
      <button @click="aba = 'pessoal'"
              :class="aba === 'pessoal' ? 'border-b-2 border-green-600 text-green-700' : 'text-gray-500'"
              class="px-4 py-2 text-sm font-medium focus:outline-none" type="button">
        Dados Pessoais
      </button>
      <button @click="aba = 'localizacao'"
              :class="aba === 'localizacao' ? 'border-b-2 border-green-600 text-green-700' : 'text-gray-500'"
              class="px-4 py-2 text-sm font-medium focus:outline-none" type="button">
        Dados de Localização
      </button>
    </div>

    <form method="POST" action="{{ route('admin.patients.store') }}" class="space-y-4">
      @csrf

      {{-- Aba: Dados Pessoais --}}
      <div x-show="aba === 'pessoal'" x-transition>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">

          {{-- Nome + Nome Social --}}
          <div x-data="{ usarNomeSocial: false }" class="md:col-span-3">
            <input type="hidden" name="use_social_name" :value="usarNomeSocial ? 1 : 0">
            <div class="flex items-end gap-3">
              <div class="flex-1">
                <label class="text-sm text-gray-600">Nome</label>
                <input type="text" name="name" value="{{ old('name') }}" required
                       class="w-full border rounded-md px-3 py-2 text-sm mt-1">
              </div>
              <div class="shrink-0 min-w-[150px] flex items-center gap-2 mb-1">
                <input type="checkbox" x-model="usarNomeSocial" class="rounded text-green-600">
                <span class="text-sm text-gray-700 select-none">É nome social?</span>
              </div>
            </div>
            <div x-show="usarNomeSocial" x-transition class="mt-2">
              <input type="text" name="social_name" value="{{ old('social_name') }}"
                     placeholder="Digite o nome social"
                     class="w-full border rounded-md px-3 py-2 text-sm">
              <p class="text-xs text-gray-500 mt-1">* Utilizado em comunicações internas conforme a LGPD.</p>
            </div>
          </div>

          <div>
            <label class="text-sm text-gray-600">Data de nascimento</label>
            <input type="date" name="birthdate" value="{{ old('birthdate') }}"
                   class="w-full border rounded-md px-3 py-2 text-sm">
          </div>

          {{-- CPF --}}
          <div x-data="{ cpfValido: true }">
            <label class="text-sm text-gray-600">CPF</label>
            <input type="text" name="document" value="{{ old('document') }}" maxlength="14"
                   x-mask="999.999.999-99"
                   @blur="cpfValido = validarCPF($event.target.value)"
                   :class="cpfValido ? 'border-gray-300' : 'border-red-500 focus:ring-red-500'"
                   class="w-full border rounded-md px-3 py-2 text-sm"
                   placeholder="000.000.000-00">
            <p x-show="!cpfValido" class="text-red-600 text-xs mt-1">CPF inválido</p>
          </div>

          <div>
            <label class="text-sm text-gray-600">RG</label>
            <input type="text" name="rg" value="{{ old('rg') }}"
                   class="w-full border rounded-md px-3 py-2 text-sm">
          </div>

          <div>
            <label class="text-sm text-gray-600">Estado civil</label>
            <select name="civil_status" class="w-full border rounded-md px-3 py-2 text-sm">
              <option value="">Selecione</option>
              <option value="solteiro" {{ old('civil_status') == 'solteiro' ? 'selected' : '' }}>Solteiro(a)</option>
              <option value="casado" {{ old('civil_status') == 'casado' ? 'selected' : '' }}>Casado(a)</option>
              <option value="divorciado" {{ old('civil_status') == 'divorciado' ? 'selected' : '' }}>Divorciado(a)</option>
              <option value="viuvo" {{ old('civil_status') == 'viuvo' ? 'selected' : '' }}>Viúvo(a)</option>
            </select>
          </div>

          <div>
            <label class="text-sm text-gray-600">Sexo / Gênero</label>
            <select name="gender" class="w-full border rounded-md px-3 py-2 text-sm">
              <option value="">Selecione</option>
              <option value="masculino" {{ old('gender') == 'masculino' ? 'selected' : '' }}>Masculino</option>
              <option value="feminino" {{ old('gender') == 'feminino' ? 'selected' : '' }}>Feminino</option>
              <option value="outro" {{ old('gender') == 'outro' ? 'selected' : '' }}>Outro</option>
            </select>
          </div>

          <div>
            <label class="text-sm text-gray-600">E-mail</label>
            <input type="email" name="email" value="{{ old('email') }}" required
                   class="w-full border rounded-md px-3 py-2 text-sm">
          </div>

          <div>
            <label class="text-sm text-gray-600">Celular</label>
            <input type="text" name="phone" value="{{ old('phone') }}" maxlength="16"
                   x-mask="(99) 9 9999-9999"
                   class="w-full border rounded-md px-3 py-2 text-sm"
                   placeholder="(00) 9 9999-9999">
          </div>

          {{-- Senha --}}
          <div x-data="{ show: false }">
            <label class="text-sm text-gray-600">Senha (acesso do paciente)</label>
            <div class="relative">
              <input :type="show ? 'text' : 'password'" name="password"
                     class="w-full border rounded-md px-3 py-2 text-sm pr-10">
              <button type="button" @click="show = !show"
                      class="absolute right-2 top-2 text-gray-500 hover:text-gray-700">
                <i :class="show ? 'bi bi-eye-slash' : 'bi bi-eye'"></i>
              </button>
            </div>
          </div>

          <div x-data="{ show: false }">
            <label class="text-sm text-gray-600">Confirmar senha</label>
            <div class="relative">
              <input :type="show ? 'text' : 'password'" name="password_confirmation"
                     class="w-full border rounded-md px-3 py-2 text-sm pr-10">
              <button type="button" @click="show = !show"
                      class="absolute right-2 top-2 text-gray-500 hover:text-gray-700">
                <i :class="show ? 'bi bi-eye-slash' : 'bi bi-eye'"></i>
              </button>
            </div>
          </div>

          {{-- Marketing --}}
          <div class="flex items-center gap-2 mt-2">
            <input type="checkbox" name="consent_marketing" value="1"
                   {{ old('consent_marketing') ? 'checked' : '' }}
                   class="rounded text-green-600">
            <span class="text-sm text-gray-700">Aceita receber comunicações de marketing</span>
          </div>

          {{-- Observações --}}
          <div class="md:col-span-3">
            <label class="text-sm text-gray-600">Observações</label>
            <textarea name="notes" rows="3"
                      class="w-full border rounded-md px-3 py-2 text-sm">{{ old('notes') }}</textarea>
          </div>
        </div>
      </div>

      {{-- Aba: Localização --}}
      <div x-show="aba === 'localizacao'" x-transition>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
          <div>
            <label class="text-sm text-gray-600">CEP</label>
            <input type="text" name="cep" id="cep" value="{{ old('cep') }}" maxlength="10"
                   x-mask="99.999-999"
                   class="w-full border rounded-md px-3 py-2 text-sm"
                   placeholder="00.000-000" @blur="buscarEndereco()">
          </div>
          <div class="md:col-span-2">
            <label class="text-sm text-gray-600">Endereço</label>
            <input type="text" name="address" id="address" value="{{ old('address') }}"
                   class="w-full border rounded-md px-3 py-2 text-sm">
          </div>
          <div>
            <label class="text-sm text-gray-600">Número</label>
            <input type="text" name="number" id="number" value="{{ old('number') }}"
                   class="w-full border rounded-md px-3 py-2 text-sm">
          </div>
          <div>
            <label class="text-sm text-gray-600">Complemento</label>
            <input type="text" name="complement" value="{{ old('complement') }}"
                   class="w-full border rounded-md px-3 py-2 text-sm">
          </div>
          <div>
            <label class="text-sm text-gray-600">Bairro</label>
            <input type="text" name="district" id="district" value="{{ old('district') }}"
                   class="w-full border rounded-md px-3 py-2 text-sm">
          </div>
          <div>
            <label class="text-sm text-gray-600">Cidade</label>
            <input type="text" name="city" id="city" value="{{ old('city') }}"
                   class="w-full border rounded-md px-3 py-2 text-sm">
          </div>
          <div>
            <label class="text-sm text-gray-600">Estado</label>
            <input type="text" name="state" id="state" value="{{ old('state') }}"
                   class="w-full border rounded-md px-3 py-2 text-sm" maxlength="2">
          </div>
        </div>
      </div>

      <div class="flex justify-end mt-4">
        <button type="submit"
                class="bg-green-600 hover:bg-green-700 text-white px-5 py-2 rounded-md text-sm font-medium">
          Salvar Paciente
        </button>
      </div>
    </form>
  </div>
</div>

<script>
function validarCPF(valor) {
  const cpf = valor.replace(/[^\d]+/g, '');
  if (cpf.length !== 11 || /^(\d)\1+$/.test(cpf)) return false;
  let soma = 0, resto;
  for (let i = 1; i <= 9; i++) soma += parseInt(cpf.substring(i-1, i)) * (11 - i);
  resto = (soma * 10) % 11;
  if (resto === 10 || resto === 11) resto = 0;
  if (resto !== parseInt(cpf.substring(9, 10))) return false;
  soma = 0;
  for (let i = 1; i <= 10; i++) soma += parseInt(cpf.substring(i-1, i)) * (12 - i);
  resto = (soma * 10) % 11;
  if (resto === 10 || resto === 11) resto = 0;
  return resto === parseInt(cpf.substring(10, 11));
}

async function buscarEndereco() {
  const cepInput = document.getElementById('cep');
  if (!cepInput) return;
  const cep = cepInput.value.replace(/\D/g, '');
  if (cep.length !== 8) return;
  const apis = [
    `https://cep.awesomeapi.com.br/json/${cep}`,
    `https://viacep.com.br/ws/${cep}/json/`,
    `https://cdn.apicep.com/file/apicep/${cep}.json`
  ];
  for (const api of apis) {
    try {
      const res = await fetch(api);
      if (!res.ok) continue;
      const data = await res.json();
      if (data && (data.address || data.logradouro || data.street)) {
        document.getElementById('address').value = data.address || data.logradouro || data.street || '';
        document.getElementById('district').value = data.district || data.bairro || '';
        document.getElementById('city').value = data.city || data.localidade || '';
        document.getElementById('state').value = data.state || data.uf || '';
        break;
      }
    } catch (e) { continue }
  }
}
</script>
@endsection