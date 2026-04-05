@extends('layouts.app')
@section('title', 'Editar Secao')
@section('content')
<div class="p-6">
    <div class="flex items-center justify-between mb-4">
        <h1 class="text-xl font-semibold text-gray-700">Editar Secao</h1>
        <a href="{{ route('admin.sections.index') }}" class="text-sm text-blue-600 hover:underline">
            <i class="bi bi-arrow-left"></i> Voltar
        </a>
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

    <form action="{{ route('admin.sections.update', $section->id) }}" method="POST" class="bg-white rounded-lg shadow p-6">
        @csrf
        @method('PUT')
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Titulo *</label>
                <input type="text" name="title" value="{{ old('title', $section->title) }}" required
                       class="w-full border rounded-md px-3 py-2 text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Slug</label>
                <input type="text" name="slug" value="{{ old('slug', $section->slug) }}"
                       class="w-full border rounded-md px-3 py-2 text-sm" placeholder="Gerado automaticamente se vazio">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">URL da Imagem</label>
                <input type="url" name="image_url" value="{{ old('image_url', $section->image_url) }}"
                       class="w-full border rounded-md px-3 py-2 text-sm" placeholder="https://...">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Posicao *</label>
                <input type="number" name="position" value="{{ old('position', $section->position) }}" min="0" required
                       class="w-full border rounded-md px-3 py-2 text-sm">
            </div>
        </div>
        <div class="mt-4">
            <label class="block text-sm font-medium text-gray-700 mb-1">Conteudo *</label>
            <textarea name="content" id="content" rows="6" required
                      class="wysiwyg w-full border rounded-md px-3 py-2 text-sm">{{ old('content', $section->content) }}</textarea>
        </div>
        <div class="mt-4">
            <label class="inline-flex items-center">
                <input type="hidden" name="active" value="0">
                <input type="checkbox" name="active" value="1" {{ old('active', $section->active) ? 'checked' : '' }}
                       class="rounded border-gray-300 text-blue-600">
                <span class="ml-2 text-sm text-gray-700">Ativo</span>
            </label>
        </div>
        <div class="mt-6">
            <button type="submit"
                    class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-md text-sm font-medium">
                Atualizar
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
