@extends('layouts.app')
@section('title', 'Novo Banner')
@section('content')
<div class="p-6" x-data="bannerUpload()">
    <div class="flex items-center justify-between mb-4">
        <h1 class="text-xl font-semibold text-gray-700">Novo Banner</h1>
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

    <form action="{{ route('admin.banners.store') }}" method="POST" enctype="multipart/form-data"
          class="bg-white rounded-lg shadow p-6">
        @csrf

        {{-- Zona de upload --}}
        <div class="mb-6">
            <label class="block text-sm font-medium text-gray-700 mb-2">
                Imagens * <span class="text-gray-400 font-normal">(jpeg, jpg, png, webp, gif - max 5MB cada)</span>
            </label>
            <div class="border-2 border-dashed rounded-lg p-8 text-center transition-colors cursor-pointer"
                 :class="dragging ? 'border-blue-500 bg-blue-50' : 'border-gray-300 hover:border-gray-400'"
                 @click="$refs.fileInput.click()"
                 @dragover.prevent="dragging = true"
                 @dragleave.prevent="dragging = false"
                 @drop.prevent="handleDrop($event)">
                <i class="bi bi-cloud-arrow-up text-4xl text-gray-400"></i>
                <p class="text-gray-500 mt-2">Arraste imagens aqui ou clique para selecionar</p>
                <p class="text-gray-400 text-xs mt-1">Voce pode selecionar varias imagens de uma vez</p>
            </div>
            <input type="file" name="images[]" multiple
                   accept="image/jpeg,image/jpg,image/png,image/webp,image/gif,image/bmp,image/svg+xml"
                   class="hidden" x-ref="fileInput"
                   @change="handleFiles($event)">
        </div>

        {{-- Preview das imagens --}}
        <div class="mb-6" x-show="previews.length > 0" x-cloak>
            <div class="flex items-center justify-between mb-2">
                <p class="text-sm font-medium text-gray-700">
                    <i class="bi bi-images"></i>
                    <span x-text="previews.length"></span> imagem(ns) selecionada(s)
                </p>
                <button type="button" @click="clearAll()" class="text-xs text-red-500 hover:underline">
                    <i class="bi bi-x-circle"></i> Limpar tudo
                </button>
            </div>
            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-3">
                <template x-for="(p, idx) in previews" :key="idx">
                    <div class="relative group rounded-lg overflow-hidden border shadow-sm">
                        <img :src="p.url" class="w-full h-32 object-cover">
                        <button type="button" @click="removeFile(idx)"
                                class="absolute top-1 right-1 bg-red-600 text-white rounded-full w-6 h-6 flex items-center justify-center text-xs opacity-0 group-hover:opacity-100 transition-opacity">
                            <i class="bi bi-x"></i>
                        </button>
                        <p class="text-xs text-gray-500 p-1 truncate" x-text="p.name"></p>
                    </div>
                </template>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Titulo</label>
                <input type="text" name="title" value="{{ old('title') }}"
                       class="w-full border rounded-md px-3 py-2 text-sm"
                       placeholder="Deixe vazio para gerar automaticamente">
                <p class="text-xs text-gray-400 mt-1">No envio em massa, as imagens extras recebem titulo numerado.</p>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Subtitulo</label>
                <input type="text" name="subtitle" value="{{ old('subtitle') }}"
                       class="w-full border rounded-md px-3 py-2 text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">URL do Link</label>
                <input type="url" name="link_url" value="{{ old('link_url') }}"
                       class="w-full border rounded-md px-3 py-2 text-sm"
                       placeholder="https://... (link ao clicar no banner)">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Ordem de exibicao *</label>
                <input type="number" name="position" value="{{ old('position', 0) }}" min="0" required
                       class="w-full border rounded-md px-3 py-2 text-sm">
                <p class="text-xs text-gray-400 mt-1">
                    <i class="bi bi-info-circle"></i>
                    Define a ordem em que o banner aparece no site. Quanto menor o numero, mais a frente ele sera exibido.
                    Ex: 0 = primeiro, 1 = segundo, etc. No envio em massa, cada imagem recebe a posicao seguinte automaticamente.
                </p>
            </div>
            <div class="flex items-center pt-2">
                <label class="inline-flex items-center">
                    <input type="hidden" name="active" value="0">
                    <input type="checkbox" name="active" value="1" {{ old('active', '1') == '1' ? 'checked' : '' }}
                           class="rounded border-gray-300 text-blue-600">
                    <span class="ml-2 text-sm text-gray-700">Ativo</span>
                </label>
            </div>
        </div>
        <div class="mt-6">
            <button type="submit"
                    class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-md text-sm font-medium">
                <i class="bi bi-check-lg"></i> Salvar
            </button>
        </div>
    </form>
</div>

<script>
function bannerUpload() {
    return {
        dragging: false,
        previews: [],
        dataTransfer: new DataTransfer(),

        handleFiles(event) {
            const files = event.target.files;
            for (let i = 0; i < files.length; i++) {
                this.addFile(files[i]);
            }
            this.syncInput();
        },

        handleDrop(event) {
            this.dragging = false;
            const files = event.dataTransfer.files;
            for (let i = 0; i < files.length; i++) {
                if (files[i].type.startsWith('image/')) {
                    this.addFile(files[i]);
                }
            }
            this.syncInput();
        },

        addFile(file) {
            this.previews.push({
                url: URL.createObjectURL(file),
                name: file.name,
                file: file
            });
            this.dataTransfer.items.add(file);
        },

        removeFile(idx) {
            URL.revokeObjectURL(this.previews[idx].url);
            this.previews.splice(idx, 1);
            this.rebuildDataTransfer();
        },

        clearAll() {
            this.previews.forEach(p => URL.revokeObjectURL(p.url));
            this.previews = [];
            this.dataTransfer = new DataTransfer();
            this.syncInput();
        },

        rebuildDataTransfer() {
            this.dataTransfer = new DataTransfer();
            this.previews.forEach(p => this.dataTransfer.items.add(p.file));
            this.syncInput();
        },

        syncInput() {
            this.$refs.fileInput.files = this.dataTransfer.files;
        }
    };
}
</script>
@endsection
