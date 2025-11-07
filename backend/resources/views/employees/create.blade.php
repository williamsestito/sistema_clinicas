@extends('layouts.app')

@section('content')
<div class="p-6">
  <div class="flex justify-between items-center mb-4">
    <h1 class="text-xl font-semibold text-gray-700">Novo Colaborador</h1>
    <a href="{{ route('employees.index') }}" 
       class="text-sm text-blue-600 hover:underline">← Voltar para lista</a>
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
        Dados de Localização
      </button>
    </div>

    <form method="POST" action="{{ route('employees.store') }}" class="space-y-4">
      @csrf

      <!-- Aba: Dados Pessoais -->
      <div x-show="aba === 'pessoal'" x-transition>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">

          <div>
            <label class="text-sm text-gray-600">Nome</label>
            <input type="text" name="name" required
                   class="w-full border rounded-md px-3 py-2 text-sm">
          </div>

          <div>
            <label class="text-sm text-gray-600">Data de nascimento</label>
            <input type="date" name="birth_date"
                   class="w-full border rounded-md px-3 py-2 text-sm">
          </div>

          <div class="flex items-center space-x-2 mt-6">
            <input type="checkbox" name="social_name" class="rounded text-green-600">
            <span class="text-sm text-gray-700">É nome social?</span>
          </div>

          <!-- CPF -->
          <div x-data="{ cpfValido: true }">
            <label class="text-sm text-gray-600">Documento (CPF)</label>
            <input type="text" name="document" maxlength="14"
                   x-mask="999.999.999-99"
                   @blur="cpfValido = validarCPF($event.target.value)"
                   :class="cpfValido ? 'border-gray-300' : 'border-red-500 focus:ring-red-500'"
                   class="w-full border rounded-md px-3 py-2 text-sm"
                   placeholder="000.000.000-00">
            <p x-show="!cpfValido" class="text-red-600 text-xs mt-1">CPF inválido</p>

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
            </script>
          </div>

          <div>
            <label class="text-sm text-gray-600">RG</label>
            <input type="text" name="rg"
                   class="w-full border rounded-md px-3 py-2 text-sm">
          </div>

          <div>
            <label class="text-sm text-gray-600">Estado civil</label>
            <select name="civil_status" class="w-full border rounded-md px-3 py-2 text-sm">
              <option value="">Selecione</option>
              <option value="solteiro">Solteiro(a)</option>
              <option value="casado">Casado(a)</option>
              <option value="divorciado">Divorciado(a)</option>
              <option value="viuvo">Viúvo(a)</option>
            </select>
          </div>

          <div>
            <label class="text-sm text-gray-600">Sexo / Gênero</label>
            <select name="gender" class="w-full border rounded-md px-3 py-2 text-sm">
              <option value="">Selecione</option>
              <option value="masculino">Masculino</option>
              <option value="feminino">Feminino</option>
              <option value="outro">Outro</option>
            </select>
          </div>

          <div>
            <label class="text-sm text-gray-600">E-mail</label>
            <input type="email" name="email" required
                   class="w-full border rounded-md px-3 py-2 text-sm">
          </div>

          <div>
            <label class="text-sm text-gray-600">Celular</label>
            <input type="text" name="phone" maxlength="16"
                   x-mask="(99) 9 9999-9999"
                   class="w-full border rounded-md px-3 py-2 text-sm"
                   placeholder="(00) 9 9999-9999">
          </div>

          <div>
            <label class="text-sm text-gray-600">Função</label>
            <select name="role" required class="w-full border rounded-md px-3 py-2 text-sm">
              <option value="">Selecione</option>
              <option value="admin">Administrador</option>
              <option value="professional">Profissional</option>
              <option value="frontdesk">Recepção</option>
            </select>
          </div>

          <!-- Senha com ícone -->
          <div x-data="{ show: false }">
            <label class="text-sm text-gray-600">Senha</label>
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

      <!-- Aba: Localização -->
      <div x-show="aba === 'localizacao'" x-transition>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
          <div>
            <label class="text-sm text-gray-600">CEP</label>
            <input type="text" name="cep" id="cep" maxlength="10"
                   x-mask="99.999-999"
                   class="w-full border rounded-md px-3 py-2 text-sm"
                   placeholder="00.000-000" @blur="buscarEndereco()">
          </div>
          <div class="md:col-span-2">
            <label class="text-sm text-gray-600">Endereço</label>
            <input type="text" name="address" id="address"
                   class="w-full border rounded-md px-3 py-2 text-sm">
          </div>
          <div>
            <label class="text-sm text-gray-600">Número</label>
            <input type="text" name="number" id="number" required
                   class="w-full border rounded-md px-3 py-2 text-sm">
          </div>
          <div>
            <label class="text-sm text-gray-600">Bairro</label>
            <input type="text" name="district" id="district"
                   class="w-full border rounded-md px-3 py-2 text-sm">
          </div>
          <div>
            <label class="text-sm text-gray-600">Cidade</label>
            <input type="text" name="city" id="city"
                   class="w-full border rounded-md px-3 py-2 text-sm">
          </div>
          <div>
            <label class="text-sm text-gray-600">Estado</label>
            <input type="text" name="state" id="state"
                   class="w-full border rounded-md px-3 py-2 text-sm">
          </div>
        </div>
      </div>

      <div class="flex justify-end">
        <button type="submit"
                class="bg-green-600 hover:bg-green-700 text-white px-5 py-2 rounded-md text-sm font-medium">
          Salvar Colaborador
        </button>
      </div>
    </form>
  </div>
</div>

<!-- Font Awesome + Alpine.js + Máscara -->
<script src="https://kit.fontawesome.com/a2d9d6c8b8.js" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/@alpinejs/mask@3.x.x/dist/cdn.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>

<script>
document.addEventListener('alpine:init', () => Alpine.plugin(window.Mask));

// Fallbacks de CEP
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
