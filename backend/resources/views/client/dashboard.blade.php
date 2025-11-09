@extends('layouts.app_client')


@section('content')
<div class="min-h-screen bg-gray-100 flex flex-col items-center justify-center p-6">
    <div class="bg-white shadow-lg rounded-2xl w-full max-w-2xl p-8 text-center">
        <h1 class="text-3xl font-bold text-green-600 mb-4">Bem-vindo(a), {{ Auth::guard('client')->user()->name }}!</h1>
        <p class="text-gray-700 mb-6">Você está autenticado na área do paciente.</p>

        <div class="space-y-4">
            <a href="{{ route('client.appointments') }}" 
               class="block bg-green-500 hover:bg-green-600 text-white py-2 px-4 rounded-lg font-semibold transition">
               Ver meus agendamentos
            </a>

            <a href="{{ route('client.profile') }}" 
               class="block bg-blue-500 hover:bg-blue-600 text-white py-2 px-4 rounded-lg font-semibold transition">
               Meu Perfil
            </a>
        </div>

        <form method="POST" action="{{ route('client.logout') }}" class="mt-8">
            @csrf
            <button type="submit" 
                    class="bg-red-500 hover:bg-red-600 text-white py-2 px-6 rounded-lg font-semibold transition">
                Sair
            </button>
        </form>
    </div>
</div>
@endsection
