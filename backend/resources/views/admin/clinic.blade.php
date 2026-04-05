@extends('layouts.app')
@section('title', 'Dados da Clínica')

@section('content')
<div class="p-6 space-y-6">

    <div class="flex items-center justify-between">
        <h1 class="text-xl font-bold text-gray-800">
            <i class="fa fa-hospital text-gray-400 mr-1"></i> Dados da Clínica
        </h1>
    </div>

    @if ($errors->any())
        <div class="bg-red-50 border border-red-300 text-red-700 text-sm rounded-md p-4">
            <ul class="list-disc list-inside">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('admin.clinic.update') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
        @csrf
        @method('PUT')

        {{-- Logo --}}
        <div class="bg-white rounded-lg shadow-sm border p-6">
            <h2 class="text-lg font-semibold text-gray-700 mb-4">
                <i class="bi bi-image"></i> Logo da Clínica
            </h2>
            <div class="flex items-center gap-6">
                <div class="flex-shrink-0">
                    @if($tenant->logo_url)
                        <img src="{{ asset('storage/' . $tenant->logo_url) }}" alt="Logo"
                             class="w-24 h-24 object-contain rounded-lg border border-gray-200 bg-gray-50 p-2">
                    @else
                        <div class="w-24 h-24 rounded-lg border-2 border-dashed border-gray-300 flex items-center justify-center bg-gray-50">
                            <i class="fa fa-image text-2xl text-gray-300"></i>
                        </div>
                    @endif
                </div>
                <div class="flex-1">
                    <input type="file" name="logo" accept="image/*"
                           class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-medium file:bg-emerald-50 file:text-emerald-700 hover:file:bg-emerald-100">
                    <p class="text-xs text-gray-400 mt-1">JPEG, PNG, WebP ou SVG — máx. 2MB</p>
                    @if($tenant->logo_url)
                        <label class="inline-flex items-center gap-2 mt-2 text-sm text-red-500 cursor-pointer">
                            <input type="checkbox" name="remove_logo" value="1" class="rounded border-gray-300">
                            Remover logo atual
                        </label>
                    @endif
                </div>
            </div>
        </div>

        {{-- Dados Cadastrais --}}
        <div class="bg-white rounded-lg shadow-sm border p-6">
            <h2 class="text-lg font-semibold text-gray-700 mb-4">
                <i class="bi bi-building"></i> Dados Cadastrais
            </h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nome da Clínica *</label>
                    <input type="text" name="name" value="{{ old('name', $tenant->name) }}"
                           class="w-full border rounded-md px-3 py-2 text-sm" required>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">CNPJ</label>
                    <input type="text" name="cnpj" value="{{ old('cnpj', $tenant->cnpj) }}"
                           class="w-full border rounded-md px-3 py-2 text-sm"
                           x-data x-mask="99.999.999/9999-99" placeholder="00.000.000/0000-00">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Inscrição Municipal</label>
                    <input type="text" name="im" value="{{ old('im', $tenant->im) }}"
                           class="w-full border rounded-md px-3 py-2 text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">E-mail da Clínica</label>
                    <input type="email" name="email" value="{{ old('email', $tenant->email) }}"
                           class="w-full border rounded-md px-3 py-2 text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Telefone da Clínica</label>
                    <input type="text" name="phone" value="{{ old('phone', $tenant->phone) }}"
                           class="w-full border rounded-md px-3 py-2 text-sm"
                           x-data x-mask="(99) 99999-9999" placeholder="(00) 00000-0000">
                </div>
            </div>
        </div>

        {{-- Cores do Tema --}}
        <div class="bg-white rounded-lg shadow-sm border p-6">
            <h2 class="text-lg font-semibold text-gray-700 mb-4">
                <i class="bi bi-palette"></i> Cores do Tema
            </h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Cor Primária</label>
                    <div class="flex items-center gap-3">
                        <input type="color" name="primary_color" value="{{ old('primary_color', $tenant->primary_color ?? '#004d40') }}"
                               class="w-12 h-10 border rounded cursor-pointer">
                        <input type="text" value="{{ old('primary_color', $tenant->primary_color ?? '#004d40') }}"
                               class="w-28 border rounded-md px-3 py-2 text-sm text-gray-500" readonly
                               x-data x-ref="pc"
                               x-init="$el.previousElementSibling.addEventListener('input', e => $el.value = e.target.value)">
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Cor Secundária</label>
                    <div class="flex items-center gap-3">
                        <input type="color" name="secondary_color" value="{{ old('secondary_color', $tenant->secondary_color ?? '#009688') }}"
                               class="w-12 h-10 border rounded cursor-pointer">
                        <input type="text" value="{{ old('secondary_color', $tenant->secondary_color ?? '#009688') }}"
                               class="w-28 border rounded-md px-3 py-2 text-sm text-gray-500" readonly
                               x-data x-ref="sc"
                               x-init="$el.previousElementSibling.addEventListener('input', e => $el.value = e.target.value)">
                    </div>
                </div>
            </div>
            <p class="text-xs text-gray-400 mt-3">
                <i class="fa fa-info-circle mr-1"></i>
                Essas cores podem ser usadas no tema do site da clínica.
            </p>
        </div>

        {{-- Salvar --}}
        <div class="flex">
            <button type="submit" class="bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-medium px-6 py-2.5 rounded-md transition">
                <i class="fa fa-check mr-1"></i> Salvar Alterações
            </button>
        </div>
    </form>
</div>
@endsection
