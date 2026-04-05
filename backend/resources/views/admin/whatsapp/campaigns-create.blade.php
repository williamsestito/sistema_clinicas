@extends('layouts.app')
@section('title', 'WhatsApp — Nova Campanha')

@section('content')
<div class="p-6 space-y-4" x-data="campaignForm()">

    <div class="flex items-center justify-between">
        <h1 class="text-xl font-bold text-gray-800">
            <i class="fa-brands fa-whatsapp text-green-500 mr-1"></i> Nova Campanha
        </h1>
        <a href="{{ route('admin.whatsapp.campaigns') }}" class="text-sm text-blue-600 hover:text-blue-800">
            <i class="fa fa-arrow-left mr-1"></i> Campanhas
        </a>
    </div>

    @if($errors->any())
        <div class="bg-red-50 border border-red-300 text-red-700 text-sm rounded-md p-3">
            <ul class="list-disc list-inside">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('admin.whatsapp.campaigns.store') }}" class="bg-white rounded-lg shadow-sm border p-6 space-y-5">
        @csrf

        {{-- Nome --}}
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Nome da Campanha <span class="text-red-500">*</span></label>
            <input type="text" name="name" value="{{ old('name') }}" required
                   class="w-full border rounded-md px-3 py-2 text-sm" placeholder="Ex: Promoção Semana da Saúde">
        </div>

        {{-- Segmento --}}
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Segmento de Destinatários <span class="text-red-500">*</span></label>
            <select name="segment" x-model="segment" required
                    class="w-full border rounded-md px-3 py-2 text-sm">
                <option value="all">Todos os pacientes</option>
                <option value="active">Pacientes ativos (com consulta recente)</option>
                <option value="inactive">Pacientes inativos</option>
                <option value="birthday">Aniversariantes do mês</option>
            </select>
        </div>

        {{-- Dias de inatividade (condicional) --}}
        <div x-show="segment === 'inactive'" x-transition>
            <label class="block text-sm font-medium text-gray-700 mb-1">Dias sem consulta</label>
            <input type="number" name="inactive_days" value="{{ old('inactive_days', 90) }}" min="7" max="365"
                   class="w-48 border rounded-md px-3 py-2 text-sm" placeholder="90">
            <p class="text-xs text-gray-400 mt-1">Pacientes sem consulta há pelo menos esta quantidade de dias.</p>
        </div>

        {{-- Template de mensagem --}}
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Mensagem <span class="text-red-500">*</span></label>
            <textarea name="message_template" rows="6" required
                      class="w-full border rounded-md px-3 py-2 text-sm font-mono"
                      placeholder="Olá {{nome}}, temos uma novidade...">{{ old('message_template') }}</textarea>
            <div class="mt-2 flex flex-wrap gap-1.5">
                <span class="text-xs text-gray-500">Variáveis disponíveis:</span>
                <button type="button" @click="insertVar('{{nome}}')"
                        class="text-xs bg-gray-100 hover:bg-gray-200 text-gray-700 px-2 py-0.5 rounded cursor-pointer">@{{nome}}</button>
                <button type="button" @click="insertVar('{{clinica}}')"
                        class="text-xs bg-gray-100 hover:bg-gray-200 text-gray-700 px-2 py-0.5 rounded cursor-pointer">@{{clinica}}</button>
            </div>
        </div>

        {{-- Agendamento --}}
        <div class="border-t pt-4">
            <label class="flex items-center gap-2 text-sm font-medium text-gray-700 mb-2">
                <input type="checkbox" x-model="scheduleEnabled" class="rounded">
                Agendar envio para data/hora específica
            </label>
            <div x-show="scheduleEnabled" x-transition>
                <input type="datetime-local" name="scheduled_at" value="{{ old('scheduled_at') }}"
                       class="w-64 border rounded-md px-3 py-2 text-sm">
                <p class="text-xs text-gray-400 mt-1">A campanha será enviada automaticamente no horário definido.</p>
            </div>
        </div>

        {{-- Botões --}}
        <div class="flex gap-3 pt-3 border-t">
            <button type="submit" name="action" value="draft"
                    class="bg-gray-600 hover:bg-gray-700 text-white text-sm px-6 py-2 rounded-md">
                <i class="fa fa-save mr-1"></i> Salvar Rascunho
            </button>
            <button type="submit" name="action" value="send"
                    class="bg-emerald-600 hover:bg-emerald-700 text-white text-sm px-6 py-2 rounded-md"
                    onclick="return confirm('Enviar campanha agora para todos os destinatários do segmento selecionado?')">
                <i class="fa fa-paper-plane mr-1"></i> Enviar Agora
            </button>
        </div>
    </form>
</div>

<script>
function campaignForm() {
    return {
        segment: '{{ old('segment', 'all') }}',
        scheduleEnabled: {{ old('scheduled_at') ? 'true' : 'false' }},
        insertVar(v) {
            const textarea = document.querySelector('textarea[name="message_template"]');
            if (!textarea) return;
            const start = textarea.selectionStart;
            const end = textarea.selectionEnd;
            const text = textarea.value;
            textarea.value = text.substring(0, start) + v + text.substring(end);
            textarea.selectionStart = textarea.selectionEnd = start + v.length;
            textarea.focus();
        }
    }
}
</script>
@endsection
