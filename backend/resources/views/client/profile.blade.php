@extends('layouts.app_client')

@section('title', 'Meus Dados')

@section('content')
@php
    use Illuminate\Support\Facades\Auth;
    $user = Auth::guard('client')->user();
@endphp

<div class="p-6"
     x-data="perfilUsuario()"
     x-init="init()">

  <div class="flex justify-between items-center mb-4">
    <h1 class="text-xl font-semibold text-gray-700">👤 Meus Dados</h1>
  </div>

  <div class="bg-white rounded-lg shadow p-6 border border-gray-100">

    <!-- Abas -->
    <div class="flex border-b border-gray-200 mb-4">
      <button @click="aba = 'dados'"
              :class="aba === 'dados' ? 'border-b-2 border-green-600 text-green-700' : 'text-gray-500'"
              class="px-4 py-2 text-sm font-medium focus:outline-none">
        Dados Pessoais
      </button>
      <button @click="aba = 'localizacao'"
              :class="aba === 'localizacao' ? 'border-b-2 border-green-600 text-green-700' : 'text-gray-500'"
              class="px-4 py-2 text-sm font-medium focus:outline-none">
        Localização
      </button>
    </div>

    <form @submit.prevent="salvarAlteracoes" class="space-y-6">

      <!-- ABA DADOS PESSOAIS -->
      <div x-show="aba === 'dados'" x-transition>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

          <!-- Nome + Nome Social -->
          <div class="md:col-span-2">
            <div class="flex items-end gap-3">
              <div class="flex-1">
                <label class="text-sm text-gray-600">Nome completo</label>
                <input type="text" x-model="form.name" required
                       class="w-full border rounded-md px-3 py-2 text-sm mt-1">
              </div>
              <div class="shrink-0 min-w-[150px] flex items-center gap-2 mb-1">
                <input type="checkbox" x-model="form.usarNomeSocial" class="rounded text-green-600">
                <span class="text-sm text-gray-700 select-none">É nome social?</span>
              </div>
            </div>

            <div x-show="form.usarNomeSocial" x-transition class="mt-2">
              <input type="text" x-model="form.social_name_text"
                     placeholder="Digite o nome social"
                     class="w-full border rounded-md px-3 py-2 text-sm">
              <p class="text-xs text-gray-500 mt-1">* Utilizado conforme a LGPD.</p>
            </div>
          </div>

          <!-- CPF -->
          <div x-data="{ cpfValido: true }">
            <label class="text-sm text-gray-600">CPF</label>
            <input type="text" x-model="form.document"
                   x-mask="999.999.999-99"
                   @blur="cpfValido = validarCPF(form.document)"
                   :class="cpfValido ? 'border-gray-300' : 'border-red-500 focus:ring-red-500'"
                   class="w-full border rounded-md px-3 py-2 text-sm mt-1"
                   placeholder="000.000.000-00">
            <p x-show="!cpfValido" class="text-red-600 text-xs mt-1">CPF inválido</p>
          </div>

          <!-- Data de nascimento -->
          <div>
            <label class="text-sm text-gray-600">Data de nascimento</label>
            <input type="date" x-model="form.birth_date"
                   class="w-full border rounded-md px-3 py-2 text-sm mt-1">
          </div>

          <!-- E-mail -->
          <div>
            <label class="text-sm text-gray-600">E-mail</label>
            <input type="email" x-model="form.email" readonly
                   class="w-full border rounded-md px-3 py-2 text-sm bg-gray-100 cursor-not-allowed mt-1">
          </div>

          <!-- Telefone -->
          <div>
            <label class="text-sm text-gray-600">Celular</label>
            <input type="text" x-model="form.phone"
                   x-mask="(99) 9 9999-9999"
                   class="w-full border rounded-md px-3 py-2 text-sm mt-1"
                   placeholder="(00) 9 9999-9999">
          </div>
        </div>
      </div>

      <!-- ABA LOCALIZAÇÃO -->
      <div x-show="aba === 'localizacao'" x-transition>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
          <div>
            <label class="text-sm text-gray-600">CEP</label>
            <input type="text" x-model="form.cep" maxlength="10"
                   x-mask="99.999-999"
                   class="w-full border rounded-md px-3 py-2 text-sm mt-1"
                   placeholder="00.000-000" @blur="buscarEndereco">
          </div>
          <div class="md:col-span-2">
            <label class="text-sm text-gray-600">Endereço</label>
            <input type="text" x-model="form.address"
                   class="w-full border rounded-md px-3 py-2 text-sm mt-1">
          </div>
          <div>
            <label class="text-sm text-gray-600">Número</label>
            <input type="text" x-model="form.number"
                   class="w-full border rounded-md px-3 py-2 text-sm mt-1">
          </div>
          <div>
            <label class="text-sm text-gray-600">Bairro</label>
            <input type="text" x-model="form.district"
                   class="w-full border rounded-md px-3 py-2 text-sm mt-1">
          </div>
          <div>
            <label class="text-sm text-gray-600">Cidade</label>
            <input type="text" x-model="form.city"
                   class="w-full border rounded-md px-3 py-2 text-sm mt-1">
          </div>
          <div>
            <label class="text-sm text-gray-600">Estado</label>
            <input type="text" x-model="form.state" maxlength="2"
                   class="w-full border rounded-md px-3 py-2 text-sm mt-1 uppercase">
          </div>
        </div>
      </div>

      <!-- Botão -->
      <div class="flex justify-end mt-6">
        <button type="submit"
                class="bg-green-600 hover:bg-green-700 text-white px-5 py-2 rounded-md text-sm font-medium flex items-center gap-2">
          <i class="fa-solid fa-floppy-disk"></i> Salvar Alterações
        </button>
      </div>
    </form>
  </div>
</div>

<!-- Dependências -->
<script src="https://kit.fontawesome.com/a2d9d6c8b8.js" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/@alpinejs/mask@3.x.x/dist/cdn.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
document.addEventListener('alpine:init', () => Alpine.plugin(window.Mask));

function perfilUsuario() {
  return {
    aba: 'dados',
    form: {
      name: @json($user->name),
      usarNomeSocial: @json((bool) $user->social_name),
      social_name_text: @json($user->social_name_text),
      email: @json($user->email),
      document: @json($user->document),
      birth_date: @json($user->birth_date),
      phone: @json($user->phone),
      cep: @json($user->cep),
      address: @json($user->address),
      number: @json($user->number),
      district: @json($user->district),
      city: @json($user->city),
      state: @json($user->state),
    },

    init() {
      // Garantir que estado inicial estável
      this.aba = 'dados';
    },

    validarCPF(valor) {
      const cpf = valor?.replace(/[^\d]+/g, '');
      if (!cpf || cpf.length !== 11 || /^(\d)\1+$/.test(cpf)) return false;
      let soma = 0, resto;
      for (let i = 1; i <= 9; i++) soma += parseInt(cpf.substring(i - 1, i)) * (11 - i);
      resto = (soma * 10) % 11;
      if (resto === 10 || resto === 11) resto = 0;
      if (resto !== parseInt(cpf.substring(9, 10))) return false;
      soma = 0;
      for (let i = 1; i <= 10; i++) soma += parseInt(cpf.substring(i - 1, i)) * (12 - i);
      resto = (soma * 10) % 11;
      if (resto === 10 || resto === 11) resto = 0;
      return resto === parseInt(cpf.substring(10, 11));
    },

    async buscarEndereco() {
      const cep = this.form.cep?.replace(/\D/g, '');
      if (cep?.length !== 8) return;
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
            this.form.address = data.address || data.logradouro || data.street || '';
            this.form.district = data.district || data.bairro || '';
            this.form.city = data.city || data.localidade || '';
            this.form.state = data.state || data.uf || '';
            break;
          }
        } catch (_) { continue; }
      }
    },

    async salvarAlteracoes() {
      try {
        const response = await fetch('/api/client/profile/update', {
          method: 'PATCH',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
          },
          body: JSON.stringify(this.form),
        });
        if (!response.ok) throw new Error(await response.text());
        Swal.fire({
          icon: 'success',
          title: 'Dados atualizados com sucesso!',
          showConfirmButton: false,
          timer: 1800,
          background: '#fff',
          color: '#374151',
        });
      } catch (error) {
        console.error('Erro ao salvar perfil:', error);
        Swal.fire({
          icon: 'error',
          title: 'Erro ao salvar',
          text: 'Não foi possível salvar as alterações.',
          confirmButtonColor: '#16a34a'
        });
      }
    }
  }
}
</script>
@endsection
