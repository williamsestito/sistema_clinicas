@extends('layouts.app')
@section('title', 'Dados da Clinica')
@section('content')
<div class="p-6">
    <div class="flex items-center justify-between mb-4">
        <h1 class="text-xl font-semibold text-gray-700">Dados da Clinica</h1>
    </div>

    @if ($errors->any())
        <div class="bg-red-50 border border-red-300 text-red-700 text-sm rounded-md p-4 mb-4">
            <ul class="list-disc list-inside">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('admin.settings.update') }}" method="POST" class="space-y-6">
        @csrf
        @method('PUT')

        {{-- Identidade --}}
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-lg font-semibold text-gray-700 mb-4">
                <i class="bi bi-building"></i> Identidade
            </h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nome do Site</label>
                    <input type="text" name="site_title" value="{{ old('site_title', $settings->site_title) }}"
                           class="w-full border rounded-md px-3 py-2 text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Slogan</label>
                    <input type="text" name="tagline" value="{{ old('tagline', $settings->tagline) }}"
                           class="w-full border rounded-md px-3 py-2 text-sm">
                </div>
            </div>
        </div>

        {{-- Sobre --}}
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-lg font-semibold text-gray-700 mb-4">
                <i class="bi bi-info-circle"></i> Sobre
            </h2>
            <div class="grid grid-cols-1 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Titulo da Secao Sobre</label>
                    <input type="text" name="about_title" value="{{ old('about_title', $settings->about_title) }}"
                           class="w-full border rounded-md px-3 py-2 text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Texto Sobre</label>
                    <textarea name="about_text" id="about_text" rows="5"
                              class="wysiwyg w-full border rounded-md px-3 py-2 text-sm">{{ old('about_text', $settings->about_text) }}</textarea>
                </div>
            </div>
        </div>

        {{-- Contato --}}
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-lg font-semibold text-gray-700 mb-4">
                <i class="bi bi-telephone"></i> Contato
            </h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Telefone</label>
                    <input type="text" name="contact_phone" value="{{ old('contact_phone', $settings->contact_phone) }}"
                           class="w-full border rounded-md px-3 py-2 text-sm" placeholder="(00) 00000-0000">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">E-mail</label>
                    <input type="email" name="contact_email" value="{{ old('contact_email', $settings->contact_email) }}"
                           class="w-full border rounded-md px-3 py-2 text-sm">
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Endereco</label>
                    <input type="text" name="address" value="{{ old('address', $settings->address) }}"
                           class="w-full border rounded-md px-3 py-2 text-sm">
                </div>
            </div>
        </div>

        {{-- Redes Sociais --}}
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-lg font-semibold text-gray-700 mb-4">
                <i class="bi bi-share"></i> Redes Sociais
            </h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Instagram</label>
                    <input type="url" name="instagram_url" value="{{ old('instagram_url', $settings->instagram_url) }}"
                           class="w-full border rounded-md px-3 py-2 text-sm" placeholder="https://instagram.com/...">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Facebook</label>
                    <input type="url" name="facebook_url" value="{{ old('facebook_url', $settings->facebook_url) }}"
                           class="w-full border rounded-md px-3 py-2 text-sm" placeholder="https://facebook.com/...">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">WhatsApp</label>
                    <input type="url" name="whatsapp_url" value="{{ old('whatsapp_url', $settings->whatsapp_url) }}"
                           class="w-full border rounded-md px-3 py-2 text-sm" placeholder="https://wa.me/55...">
                </div>
            </div>
        </div>

        <div>
            <button type="submit"
                    class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-md text-sm font-medium">
                Salvar Configuracoes
            </button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
  document.addEventListener('DOMContentLoaded', function () {
    tinymce.init({
      selector: '.wysiwyg',
      language: 'pt_BR',
      menubar: false,
      height: 300,
      plugins: 'lists link image code table wordcount',
      toolbar: 'undo redo | blocks | bold italic underline strikethrough | alignleft aligncenter alignright | bullist numlist | link image | code',
      content_style: 'body { font-family: sans-serif; font-size: 14px; }',
      branding: false,
      promotion: false
    });
  });
</script>
@endpush
