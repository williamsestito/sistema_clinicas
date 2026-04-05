@extends('layouts.app')
@section('title', 'Editar Banner')
@section('content')
<div class="p-6" x-data="bannerEdit()">
    <div class="flex items-center justify-between mb-4">
        <h1 class="text-xl font-semibold text-gray-700">Editar Banner</h1>
        <a href="{{ route('admin.banners.index') }}" class="text-sm text-blue-600 hover:underline">
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

    <form action="{{ route('admin.banners.update', $banner->id) }}" method="POST" enctype="multipart/form-data"
          class="bg-white rounded-lg shadow p-6">
        @csrf
        @method('PUT')

        {{-- Imagem atual --}}
        @if($banner->image_url)
            <div class="mb-4">
                <p class="text-sm font-medium text-gray-700 mb-2">Imagem atual:</p>
                <img src="{{ asset('storage/' . $banner->image_url) }}" alt=""
                     class="h-40 rounded-lg border shadow-sm object-cover">
            </div>
        @endif

        {{-- Upload de nova imagem --}}
        <div class="mb-6">
            <label class="block text-sm font-medium text-gray-700 mb-2">
                Substituir imagem <span class="text-gray-400 font-normal">(opcional - jpeg, jpg, png, webp, gif - max 5MB)</span>
            </label>
            <div class="border-2 border-dashed rounded-lg p-6 text-center transition-colors cursor-pointer"
                 :class="dragging ? 'border-blue-500 bg-blue-50' : 'border-gray-300 hover:border-gray-400'"
                 @click="$refs.fileInput.click()"
                 @dragover.prevent="dragging = true"
                 @dragleave.prevent="dragging = false"
                 @drop.prevent="handleDrop($event)">
                <i class="bi bi-cloud-arrow-up text-3xl text-gray-400"></i>
                <p class="text-gray-500 mt-1 text-sm">Arraste uma nova imagem aqui ou clique para selecionar</p>
            </div>
            <input type="file" name="image"
                   accept="image/jpeg,image/jpg,image/png,image/webp,image/gif,image/bmp,image/svg+xml"
                   class="hidden" x-ref="fileInput"
                   @change="handleFile($event)">
        </div>

        {{-- Preview da nova imagem --}}
        <div class="mb-6" x-show="preview" x-cloak>
            <p class="text-sm font-medium text-gray-700 mb-2">Nova imagem:</p>
            <div class="relative inline-block">
                <img :src="preview" class="h-40 rounded-lg border shadow-sm object-cover">
                <button type="button" @click="clearFile()"
                        class="absolute top-1 right-1 bg-red-600 text-white rounded-full w-6 h-6 flex items-center justify-center text-xs">
                    <i class="bi bi-x"></i>
                </button>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Titulo *</label>
                <input type="text" name="title" value="{{ old('title', $banner->title) }}" required
                       class="w-full border rounded-md px-3 py-2 text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Subtitulo</label>
                <input type="text" name="subtitle" value="{{ old('subtitle', $banner->subtitle) }}"
                       class="w-full border rounded-md px-3 py-2 text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">URL do Link</label>
                <input type="url" name="link_url" value="{{ old('link_url', $banner->link_url) }}"
                       class="w-full border rounded-md px-3 py-2 text-sm"
                       placeholder="https://... (link ao clicar no banner)">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Ordem de exibicao *</label>
                <input type="number" name="position" value="{{ old('position', $banner->position) }}" min="0" required
                       class="w-full border rounded-md px-3 py-2 text-sm">
                <p class="text-xs text-gray-400 mt-1">
                    <i class="bi bi-info-circle"></i>
                    Define a ordem em que o banner aparece no site. Quanto menor o numero, mais a frente ele sera exibido.
                    Ex: 0 = primeiro, 1 = segundo, etc.
                </p>
            </div>
            <div class="flex items-center pt-2">
                <label class="inline-flex items-center">
                    <input type="hidden" name="active" value="0">
                    <input type="checkbox" name="active" value="1" {{ old('active', $banner->active) ? 'checked' : '' }}
                           class="rounded border-gray-300 text-blue-600">
                    <span class="ml-2 text-sm text-gray-700">Ativo</span>
                </label>
            </div>
        </div>

        <div class="mt-6">
            <button type="submit"
                    class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-md text-sm font-medium">
                <i class="bi bi-check-lg"></i> Atualizar
            </button>
        </div>
    </form>
</div>

<script>
function bannerEdit() {
    return {
        dragging: false,
        preview: null,

        handleFile(event) {
            const file = event.target.files[0];
            if (file && file.type.startsWith('image/')) {
                if (this.preview) URL.revokeObjectURL(this.preview);
                this.preview = URL.createObjectURL(file);
            }
        },

        handleDrop(event) {
            this.dragging = false;
            const file = event.dataTransfer.files[0];
            if (file && file.type.startsWith('image/')) {
                // Atualiza o input file
                const dt = new DataTransfer();
                dt.items.add(file);
                this.$refs.fileInput.files = dt.files;

                if (this.preview) URL.revokeObjectURL(this.preview);
                this.preview = URL.createObjectURL(file);
            }
        },

        clearFile() {
            if (this.preview) URL.revokeObjectURL(this.preview);
            this.preview = null;
            this.$refs.fileInput.value = '';
        }
    };
}
</script>
@endsection
