@extends('layouts.app')

@section('title', 'Meus Dados')

@section('content')
<div x-data="perfilUsuario()" class="p-6 space-y-6">

  <div class="flex items-center justify-between mb-4">
    <h1 class="text-xl font-semibold text-gray-700">👤 Meus Dados</h1>
  </div>

  @php
      use Carbon\Carbon;
      $user = Auth::user();

      // Campos esperados no perfil
      $camposObrigatorios = [
          'name', 'email', 'phone', 'document', 'birth_date',
          'address', 'number', 'district', 'city', 'state', 'cep'
      ];

      // Verifica se há algum vazio
      $faltando = collect($camposObrigatorios)->some(fn($campo) => empty($user->$campo));

      // Corrige formato da data para input date
      $dataNascimento = $user->birth_date ? Carbon::parse($user->birth_date)->format('Y-m-d') : '';
  @endphp

  @if($faltando)
    <div class="bg-yellow-100 border border-yellow-300 text-yellow-800 text-sm p-3 rounded-md mb-4 flex items-center gap-2">
      ⚠️ <span>Algumas informações do seu perfil estão incompletas. Atualize seus dados abaixo para continuar utilizando todos os recursos.</span>
    </div>
  @endif

  <!-- Card principal -->
  <div class="bg-white rounded-lg shadow p-6 border border-gray-100 max-w-4xl">
    <form @submit.prevent="salvarAlteracoes" class="space-y-5">

      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

        <!-- Nome e nome social -->
        <div x-data="{ usarNomeSocial: {{ $user->social_name ? 'true' : 'false' }} }" class="md:col-span-2">
          <input type="hidden" name="social_name" :value="usarNomeSocial ? 1 : 0">

          <div class="flex items-end gap-3">
            <div class="flex-1">
              <label class="text-sm text-gray-600 font-medium">Nome completo</label>
              <input type="text" x-model="form.name" class="mt-1 w-full border rounded-md px-3 py-2 text-sm" required>
            </div>
            <div class="shrink-0 min-w-[160px] flex items-center gap-2 mb-1">
              <input type="checkbox" x-model="usarNomeSocial" class="rounded text-green-600">
              <span class="text-sm text-gray-700 select-none">É nome social?</span>
            </div>
          </div>

          <div x-show="usarNomeSocial" x-transition class="mt-2">
            <input type="text" x-model="form.social_name_text"
                   placeholder="Digite o nome social"
                   class="w-full border rounded-md px-3 py-2 text-sm">
            <p class="text-xs text-gray-500 mt-1">* Nome utilizado conforme a LGPD.</p>
          </div>
        </div>

        <!-- CPF -->
        <div>
          <label class="text-sm text-gray-600 font-medium">CPF</label>
          <input type="text" x-mask="999.999.999-99" x-model="form.document" placeholder="000.000.000-00"
                 class="mt-1 w-full border rounded-md px-3 py-2 text-sm">
        </div>

        <!-- Data de nascimento -->
        <div>
          <label class="text-sm text-gray-600 font-medium">Data de nascimento</label>
          <input type="date" x-model="form.birth_date" class="mt-1 w-full border rounded-md px-3 py-2 text-sm">
        </div>

        <!-- E-mail -->
        <div>
          <label class="text-sm text-gray-600 font-medium">E-mail</label>
          <input type="email" x-model="form.email" class="mt-1 w-full border rounded-md px-3 py-2 text-sm bg-gray-100 cursor-not-allowed" readonly>
        </div>

        <!-- Celular -->
        <div>
          <label class="text-sm text-gray-600 font-medium">Celular</label>
          <input type="text" x-mask="(99) 9 9999-9999" x-model="form.phone" placeholder="(00) 9 9999-9999"
                 class="mt-1 w-full border rounded-md px-3 py-2 text-sm">
        </div>

        <!-- CEP -->
        <div>
          <label class="text-sm text-gray-600 font-medium">CEP</label>
          <input type="text" x-mask="99.999-999" x-model="form.cep" placeholder="00.000-000"
                 class="mt-1 w-full border rounded-md px-3 py-2 text-sm">
        </div>

        <!-- Endereço -->
        <div class="md:col-span-2">
          <label class="text-sm text-gray-600 font-medium">Endereço</label>
          <input type="text" x-model="form.address" placeholder="Rua, número e complemento"
                 class="mt-1 w-full border rounded-md px-3 py-2 text-sm">
        </div>

        <!-- Número -->
        <div>
          <label class="text-sm text-gray-600 font-medium">Número</label>
          <input type="text" x-model="form.number" placeholder="—"
                 class="mt-1 w-full border rounded-md px-3 py-2 text-sm">
        </div>

        <!-- Bairro -->
        <div>
          <label class="text-sm text-gray-600 font-medium">Bairro</label>
          <input type="text" x-model="form.district" placeholder="—"
                 class="mt-1 w-full border rounded-md px-3 py-2 text-sm">
        </div>

        <!-- Cidade -->
        <div>
          <label class="text-sm text-gray-600 font-medium">Cidade</label>
          <input type="text" x-model="form.city" placeholder="—"
                 class="mt-1 w-full border rounded-md px-3 py-2 text-sm">
        </div>

        <!-- Estado -->
        <div>
          <label class="text-sm text-gray-600 font-medium">Estado (UF)</label>
          <input type="text" maxlength="2" x-model="form.state" placeholder="SC"
                 class="mt-1 w-full border rounded-md px-3 py-2 text-sm uppercase">
        </div>
      </div>

      <!-- Botão -->
      <div class="flex justify-end pt-4">
        <button type="submit"
                class="bg-green-600 hover:bg-green-700 text-white px-6 py-2 rounded-md text-sm font-medium flex items-center gap-2">
          <i class="fa-solid fa-floppy-disk"></i> Salvar Alterações
        </button>
      </div>
    </form>
  </div>
</div>

<!-- Dependências -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
<script src="https://cdn.jsdelivr.net/npm/@alpinejs/mask@3.x.x/dist/cdn.min.js" defer></script>
<script src="https://kit.fontawesome.com/a2d9d6c8b8.js" crossorigin="anonymous"></script>

<script>
function perfilUsuario() {
  return {
    form: {
      name: @json($user->name),
      social_name_text: @json($user->social_name_text),
      email: @json($user->email),
      document: @json($user->document),
      birth_date: @json($dataNascimento),
      phone: @json($user->phone),
      cep: @json($user->cep),
      address: @json($user->address),
      number: @json($user->number),
      district: @json($user->district),
      city: @json($user->city),
      state: @json($user->state),
    },

    async salvarAlteracoes() {
      try {
        const response = await fetch('/api/pacient/profile/update', {
          method: 'PATCH',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
          },
          body: JSON.stringify(this.form),
        });

        if (!response.ok) throw new Error('Erro ao salvar');

        Swal.fire({
          icon: 'success',
          title: '✅ Dados atualizados com sucesso!',
          showConfirmButton: false,
          timer: 1800,
          background: '#fff',
          color: '#374151',
          width: 400
        });

      } catch (e) {
        Swal.fire({
          icon: 'error',
          title: 'Erro ao atualizar',
          text: 'Não foi possível salvar as alterações. Tente novamente.',
          confirmButtonColor: '#16a34a'
        });
      }
    }
  }
}
</script>
@endsection
