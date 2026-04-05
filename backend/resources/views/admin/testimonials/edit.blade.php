@extends('layouts.app')
@section('title', 'Editar Depoimento')
@section('content')
<div class="p-6">
    <div class="flex items-center justify-between mb-4">
        <h1 class="text-xl font-semibold text-gray-700">Editar Depoimento</h1>
        <a href="{{ route('admin.testimonials.index') }}" class="text-sm text-blue-600 hover:underline">
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

    <form action="{{ route('admin.testimonials.update', $testimonial->id) }}" method="POST" class="bg-white rounded-lg shadow p-6">
        @csrf
        @method('PUT')
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Nome do Cliente *</label>
                <input type="text" name="client_name" value="{{ old('client_name', $testimonial->client_name) }}" required
                       class="w-full border rounded-md px-3 py-2 text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Avaliacao *</label>
                <select name="rating" required class="w-full border rounded-md px-3 py-2 text-sm">
                    @for($i = 5; $i >= 1; $i--)
                        <option value="{{ $i }}" {{ old('rating', $testimonial->rating) == $i ? 'selected' : '' }}>
                            {{ $i }} {{ str_repeat('★', $i) }}{{ str_repeat('☆', 5 - $i) }}
                        </option>
                    @endfor
                </select>
            </div>
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-1">URL da Foto</label>
                <input type="url" name="photo_url" value="{{ old('photo_url', $testimonial->photo_url) }}"
                       class="w-full border rounded-md px-3 py-2 text-sm" placeholder="https://...">
            </div>
        </div>
        <div class="mt-4">
            <label class="block text-sm font-medium text-gray-700 mb-1">Comentario *</label>
            <textarea name="comment" id="comment" rows="4" required
                      class="wysiwyg w-full border rounded-md px-3 py-2 text-sm">{{ old('comment', $testimonial->comment) }}</textarea>
        </div>
        <div class="mt-4">
            <label class="inline-flex items-center">
                <input type="hidden" name="visible" value="0">
                <input type="checkbox" name="visible" value="1" {{ old('visible', $testimonial->visible) ? 'checked' : '' }}
                       class="rounded border-gray-300 text-blue-600">
                <span class="ml-2 text-sm text-gray-700">Visivel no site</span>
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
      height: 200,
      plugins: 'lists link code wordcount',
      toolbar: 'undo redo | bold italic underline | bullist numlist | link | code',
      content_style: 'body { font-family: sans-serif; font-size: 14px; }',
      branding: false,
      promotion: false
    });
  });
</script>
@endpush
