@extends('layouts.app')

@section('content')
<div class="p-6">
  <div class="flex justify-between items-center mb-4">
    <h1 class="text-xl font-semibold text-gray-700">Novo Paciente</h1>
    <a href="{{ route('pacients.index') }}" class="text-sm text-blue-600 hover:underline">← Voltar para lista</a>
  </div>

  <div x-data="{ aba: 'pessoal' }" class="bg-white rounded-lg shadow p-6">
    <!-- Abas -->
    <div class="flex border-b border-gray-200 mb-4">
      <button @click="aba = 'pessoal'"
              :class="aba === 'pessoal' ? 'border-b-2 border-green-600 text-green-700' : 'text-gray-500'"
              class="px-4 py-2 text-sm font-medium focus:outline-none">
        Dados Pessoais
      </button>
      <button @click="aba = 'localizacao'"
              :class="aba === 'localizacao' ? 'border-b-2 border-green-600 text-green-700' : 'text-gray-500'"
              class="px-4 py-2 text-sm font-medium focus:outline-none">
        Endereço
      </button>
    </div>

    <form method="POST" action="{{ route('pacients.store') }}" class="space-y-4">
      @csrf

      <!-- Aba: Dados pessoais -->
      <div x-show="aba === 'pessoal'" x-transition>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">

          <!-- Nome e nome social -->
          <div x-data="{ usarNomeSocial: false }" class="md:col-span-3">
            <input type="hidden" name="social_name" :value="usarNomeSocial ? 1 : 0">
            <div class="flex items-end gap-3">
              <div class="flex-1">
                <label class="text-sm text-gray-600">Nome completo</label>
                <input type="text" name="name" required class="w-full border rounded-md px-3 py-2 text-sm mt-1">
              </div>
              <div class="shrink-0 min-w-[150px] flex items-center gap-2 mb-1">
                <input type="checkbox" x-model="usarNomeSocial" class="rounded text-green-600">
                <span class="text-sm text-gray-700 select-none">É nome social?</span>
              </div>
            </div>
            <div x-show="usarNomeSocial" x-transition class="mt-2">
              <input type="text" name="social_name_text" placeholder="Digite o nome social"
                     class="w-full border rounded-md px-3 py-2 text-sm">
              <p class="text-xs text-gray-500 mt-1">* Nome utilizado conforme a LGPD.</p>
            </div>
          </div>

          <div>
            <label class="text-sm text-gray-600">Data de nascimento</label>
            <input type="date" name="birth_date" class="w-full border rounded-md px-3 py-2 text-sm">
          </div>

          <div>
            <label class="text-sm text-gray-600">CPF</label>
            <input type="text" name="document" maxlength="14" x-mask="999.999.999-99"
                   class="w-full border rounded-md px-3 py-2 text-sm" placeholder="000.000.000-00">
          </div>

          <div>
            <label class="text-sm text-gray-600">E-mail</label>
            <input type="email" name="email" class="w-full border rounded-md px-3 py-2 text-sm">
          </div>

          <div>
            <label class="text-sm text-gray-600">Celular</label>
            <input type="text" name="phone" maxlength="16" x-mask="(99) 9 9999-9999"
                   class="w-full border rounded-md px-3 py-2 text-sm" placeholder="(00) 9 9999-9999">
          </div>

          <!-- Senha -->
          <div x-data="{ show: false }">
            <label class="text-sm text-gray-600">Senha de acesso</label>
            <div class="relative">
              <input :type="show ? 'text' : 'password'" name="password" required
                     class="w-full border rounded-md px-3 py-2 text-sm pr-10">
              <button type="button" @click="show = !show"
                      class="absolute right-2 top-2 text-gray-500 hover:text-gray-700">
                <i :class="show ? 'fas fa-eye-slash' : 'fas fa-eye'"></i>
              </button>
            </div>
          </div>

          <div x-data="{ show: false }">
            <label class="text-sm text-gray-600">Confirmar senha</label>
            <div class="relative">
              <input :type="show ? 'text' : 'password'" name="password_confirmation" required
                     class="w-full border rounded-md px-3 py-2 text-sm pr-10">
              <button type="button" @click="show = !show"
                      class="absolute right-2 top-2 text-gray-500 hover:text-gray-700">
                <i :class="show ? 'fas fa-eye-slash' : 'fas fa-eye'"></i>
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Aba: Endereço -->
      <div x-show="aba === 'localizacao'" x-transition>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
          <div>
            <label class="text-sm text-gray-600">CEP</label>
            <input type="text" name="cep" id="cep" maxlength="10" x-mask="99.999-999"
                   class="w-full border rounded-md px-3 py-2 text-sm" placeholder="00.000-000"
                   @blur="buscarEndereco()">
          </div>
          <div class="md:col-span-2">
            <label class="text-sm text-gray-600">Endereço</label>
            <input type="text" name="address" id="address" class="w-full border rounded-md px-3 py-2 text-sm">
          </div>
          <div>
            <label class="text-sm text-gray-600">Número</label>
            <input type="text" name="number" id="number" class="w-full border rounded-md px-3 py-2 text-sm">
          </div>
          <div>
            <label class="text-sm text-gray-600">Bairro</label>
            <input type="text" name="district" id="district" class="w-full border rounded-md px-3 py-2 text-sm">
          </div>
          <div>
            <label class="text-sm text-gray-600">Cidade</label>
            <input type="text" name="city" id="city" class="w-full border rounded-md px-3 py-2 text-sm">
          </div>
          <div>
            <label class="text-sm text-gray-600">Estado</label>
            <input type="text" name="state" id="state" class="w-full border rounded-md px-3 py-2 text-sm">
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

<!-- Scripts -->
<script src="https://kit.fontawesome.com/a2d9d6c8b8.js" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/@alpinejs/mask@3.x.x/dist/cdn.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>

<script>
document.addEventListener('alpine:init', () => Alpine.plugin(window.Mask));

async function buscarEndereco() {
  const cepInput = document.getElementById('cep');
  if (!cepInput) return;
  const cep = cepInput.value.replace(/\D/g, '');
  if (cep.length !== 8) return;

  // APIs fallback
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
      if (data.erro || data.status === 404) continue;

      // Mapeamento flexível entre diferentes formatos de retorno
      document.getElementById('address').value = data.address || data.logradouro || data.street || '';
      document.getElementById('district').value = data.district || data.bairro || '';
      document.getElementById('city').value = data.city || data.localidade || '';
      document.getElementById('state').value = data.state || data.uf || '';
      break;
    } catch (e) { continue; }
  }
}
</script>
@endsection
