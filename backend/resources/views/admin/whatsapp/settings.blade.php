@extends('layouts.app')
@section('title', 'WhatsApp — Configurações')

@section('content')
<div class="p-6 space-y-6">

    <div class="flex items-center justify-between">
        <h1 class="text-xl font-bold text-gray-800">
            <i class="fa-brands fa-whatsapp text-green-500 mr-1"></i> Configurações
        </h1>
        <a href="{{ route('admin.whatsapp.dashboard') }}" class="text-sm text-blue-600 hover:text-blue-800">
            <i class="fa fa-arrow-left mr-1"></i> Dashboard
        </a>
    </div>

    @if(session('success'))
        <div class="bg-green-50 border border-green-300 text-green-700 text-sm rounded-md p-3">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="bg-red-50 border border-red-300 text-red-700 text-sm rounded-md p-3">{{ session('error') }}</div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        {{-- Conexão da API --}}
        <form method="POST" action="{{ route('admin.whatsapp.connection.update') }}" class="bg-white rounded-lg shadow-sm border p-6 space-y-4">
            @csrf
            <h2 class="text-lg font-semibold text-gray-800 border-b pb-2">
                <i class="fa fa-plug mr-1 text-blue-500"></i> Conexão da API
            </h2>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Provedor</label>
                <select name="provider" class="w-full border rounded-md px-3 py-2 text-sm">
                    <option value="meta" {{ $connection->provider === 'meta' ? 'selected' : '' }}>Meta Cloud API (Oficial)</option>
                    <option value="z-api" {{ $connection->provider === 'z-api' ? 'selected' : '' }}>Z-API</option>
                    <option value="ultramsg" {{ $connection->provider === 'ultramsg' ? 'selected' : '' }}>UltraMsg</option>
                    <option value="twilio" {{ $connection->provider === 'twilio' ? 'selected' : '' }}>Twilio</option>
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Phone Number ID</label>
                <input type="text" name="phone_number_id" value="{{ $connection->phone_number_id }}"
                       class="w-full border rounded-md px-3 py-2 text-sm" placeholder="Ex: 123456789012345">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Business Account ID</label>
                <input type="text" name="business_account_id" value="{{ $connection->business_account_id }}"
                       class="w-full border rounded-md px-3 py-2 text-sm" placeholder="Ex: 123456789012345">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Token da API</label>
                <input type="password" name="api_token" value="{{ $connection->api_token }}"
                       class="w-full border rounded-md px-3 py-2 text-sm" placeholder="EAAx...">
                <p class="text-xs text-gray-400 mt-1">Token permanente gerado no painel Meta for Developers.</p>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Webhook Verify Token</label>
                <div class="flex gap-2">
                    <input type="text" name="webhook_verify_token" value="{{ $connection->webhook_verify_token }}"
                           class="flex-1 border rounded-md px-3 py-2 text-sm" placeholder="seu_token_aleatorio">
                </div>
                <p class="text-xs text-gray-400 mt-1">Token usado para verificar o webhook no Meta.</p>
            </div>

            <div class="bg-gray-50 border rounded-md p-3">
                <p class="text-xs font-medium text-gray-600 mb-1">URL do Webhook (cole no Meta):</p>
                <code class="text-xs text-blue-700 block break-all select-all">{{ url('/api/whatsapp/webhook') }}</code>
            </div>

            <div class="flex items-center gap-2">
                <input type="hidden" name="active" value="0">
                <input type="checkbox" name="active" value="1" {{ $connection->active ? 'checked' : '' }}
                       class="rounded" id="conn-active">
                <label for="conn-active" class="text-sm text-gray-700">Conexão ativa</label>
            </div>

            <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white text-sm px-4 py-2 rounded-md">
                <i class="fa fa-save mr-1"></i> Salvar Conexão
            </button>
        </form>

        {{-- Automações --}}
        <form method="POST" action="{{ route('admin.whatsapp.automation.update') }}" class="bg-white rounded-lg shadow-sm border p-6 space-y-4" x-data="automationForm()">
            @csrf
            <h2 class="text-lg font-semibold text-gray-800 border-b pb-2">
                <i class="fa fa-robot mr-1 text-purple-500"></i> Automações
            </h2>

            <div class="flex items-center gap-2 bg-purple-50 rounded-md p-3 border border-purple-200">
                <input type="hidden" name="active" value="0">
                <input type="checkbox" name="active" value="1" {{ $automation->active ? 'checked' : '' }}
                       class="rounded" id="auto-active">
                <label for="auto-active" class="text-sm font-medium text-purple-700">Automações ativas</label>
            </div>

            {{-- Confirmação --}}
            <div class="border rounded-md p-3 space-y-2">
                <div class="flex items-center gap-2">
                    <input type="hidden" name="send_confirmation" value="0">
                    <input type="checkbox" name="send_confirmation" value="1" x-model="confirmation"
                           {{ $automation->send_confirmation ? 'checked' : '' }} class="rounded">
                    <span class="text-sm font-medium text-gray-700">Confirmação de agendamento</span>
                </div>
                <div x-show="confirmation" x-transition>
                    <textarea name="confirmation_template" rows="3"
                              class="w-full border rounded-md px-3 py-2 text-sm font-mono">{{ $automation->confirmation_template }}</textarea>
                </div>
            </div>

            {{-- Lembrete --}}
            <div class="border rounded-md p-3 space-y-2">
                <div class="flex items-center gap-2">
                    <input type="hidden" name="send_reminder" value="0">
                    <input type="checkbox" name="send_reminder" value="1" x-model="reminder"
                           {{ $automation->send_reminder ? 'checked' : '' }} class="rounded">
                    <span class="text-sm font-medium text-gray-700">Lembrete antes da consulta</span>
                </div>
                <div x-show="reminder" x-transition class="space-y-2">
                    <div>
                        <label class="block text-xs text-gray-500 mb-1">Enviar lembrete X horas antes:</label>
                        <input type="number" name="reminder_hours_before" value="{{ $automation->reminder_hours_before }}"
                               min="1" max="72" class="w-24 border rounded-md px-3 py-2 text-sm">
                    </div>
                    <textarea name="reminder_template" rows="3"
                              class="w-full border rounded-md px-3 py-2 text-sm font-mono">{{ $automation->reminder_template }}</textarea>
                </div>
            </div>

            {{-- Cancelamento --}}
            <div class="border rounded-md p-3 space-y-2">
                <div class="flex items-center gap-2">
                    <input type="hidden" name="send_cancellation" value="0">
                    <input type="checkbox" name="send_cancellation" value="1" x-model="cancellation"
                           {{ $automation->send_cancellation ? 'checked' : '' }} class="rounded">
                    <span class="text-sm font-medium text-gray-700">Notificação de cancelamento</span>
                </div>
                <div x-show="cancellation" x-transition>
                    <textarea name="cancellation_template" rows="3"
                              class="w-full border rounded-md px-3 py-2 text-sm font-mono">{{ $automation->cancellation_template }}</textarea>
                </div>
            </div>

            {{-- Remarcação --}}
            <div class="border rounded-md p-3 space-y-2">
                <div class="flex items-center gap-2">
                    <input type="hidden" name="send_reschedule" value="0">
                    <input type="checkbox" name="send_reschedule" value="1" x-model="reschedule"
                           {{ $automation->send_reschedule ? 'checked' : '' }} class="rounded">
                    <span class="text-sm font-medium text-gray-700">Notificação de remarcação</span>
                </div>
                <div x-show="reschedule" x-transition>
                    <textarea name="reschedule_template" rows="3"
                              class="w-full border rounded-md px-3 py-2 text-sm font-mono">{{ $automation->reschedule_template }}</textarea>
                </div>
            </div>

            <button type="submit" class="w-full bg-purple-600 hover:bg-purple-700 text-white text-sm px-4 py-2 rounded-md">
                <i class="fa fa-save mr-1"></i> Salvar Automações
            </button>
        </form>
    </div>
</div>

<script>
function automationForm() {
    return {
        confirmation: {{ $automation->send_confirmation ? 'true' : 'false' }},
        reminder: {{ $automation->send_reminder ? 'true' : 'false' }},
        cancellation: {{ $automation->send_cancellation ? 'true' : 'false' }},
        reschedule: {{ $automation->send_reschedule ? 'true' : 'false' }},
    }
}
</script>
@endsection
