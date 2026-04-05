@extends('layouts.app')
@section('title', 'Meu Perfil')

@section('content')
<div class="p-6 space-y-6">

    <div class="flex items-center justify-between">
        <h1 class="text-xl font-bold text-gray-800">
            <i class="fa fa-user-circle text-gray-400 mr-1"></i> Meu Perfil
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

    <form action="{{ route('admin.profile.update') }}" method="POST" class="space-y-6">
        @csrf
        @method('PUT')

        {{-- Avatar + Info --}}
        <div class="bg-white rounded-lg shadow-sm border p-6">
            <div class="flex items-center gap-5 mb-6">
                <img src="https://ui-avatars.com/api/?name={{ urlencode($user->name) }}&size=80&background=059669&color=fff"
                     class="w-16 h-16 rounded-full" alt="Avatar">
                <div>
                    <h2 class="text-lg font-semibold text-gray-800">{{ $user->name }}</h2>
                    <p class="text-sm text-gray-500">{{ ucfirst($user->role) }} — Desde {{ $user->created_at->format('d/m/Y') }}</p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nome *</label>
                    <input type="text" name="name" value="{{ old('name', $user->name) }}"
                           class="w-full border rounded-md px-3 py-2 text-sm" required>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">E-mail *</label>
                    <input type="email" name="email" value="{{ old('email', $user->email) }}"
                           class="w-full border rounded-md px-3 py-2 text-sm" required>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Telefone</label>
                    <input type="text" name="phone" value="{{ old('phone', $user->phone) }}"
                           class="w-full border rounded-md px-3 py-2 text-sm"
                           x-data x-mask="(99) 99999-9999" placeholder="(00) 00000-0000">
                </div>
            </div>
        </div>

        {{-- Alterar Senha --}}
        <div class="bg-white rounded-lg shadow-sm border p-6">
            <h2 class="text-lg font-semibold text-gray-700 mb-4">
                <i class="bi bi-shield-lock"></i> Alterar Senha
            </h2>
            <p class="text-xs text-gray-400 mb-4">Deixe em branco para manter a senha atual.</p>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nova Senha</label>
                    <input type="password" name="password"
                           class="w-full border rounded-md px-3 py-2 text-sm" placeholder="Mínimo 6 caracteres">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Confirmar Nova Senha</label>
                    <input type="password" name="password_confirmation"
                           class="w-full border rounded-md px-3 py-2 text-sm" placeholder="Repita a nova senha">
                </div>
            </div>
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
