@extends('layouts.app')
@section('title', 'WhatsApp — Mensagens')

@section('content')
<div class="p-6 space-y-4">

    <div class="flex items-center justify-between">
        <h1 class="text-xl font-bold text-gray-800">
            <i class="fa-brands fa-whatsapp text-green-500 mr-1"></i> Mensagens
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

    {{-- Filtros --}}
    <div class="bg-white rounded-lg shadow-sm border p-4">
        <form method="GET" class="grid grid-cols-2 md:grid-cols-5 gap-3 items-end">
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Buscar</label>
                <input type="text" name="search" value="{{ request('search') }}"
                       class="w-full border rounded-md px-3 py-2 text-sm" placeholder="Nome, telefone, conteúdo...">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Tipo</label>
                <select name="type" class="w-full border rounded-md px-3 py-2 text-sm">
                    <option value="">Todos</option>
                    <option value="confirmation" {{ request('type') == 'confirmation' ? 'selected' : '' }}>Confirmação</option>
                    <option value="reminder" {{ request('type') == 'reminder' ? 'selected' : '' }}>Lembrete</option>
                    <option value="cancellation" {{ request('type') == 'cancellation' ? 'selected' : '' }}>Cancelamento</option>
                    <option value="reschedule" {{ request('type') == 'reschedule' ? 'selected' : '' }}>Remarcação</option>
                    <option value="campaign" {{ request('type') == 'campaign' ? 'selected' : '' }}>Campanha</option>
                    <option value="manual" {{ request('type') == 'manual' ? 'selected' : '' }}>Manual</option>
                    <option value="reply" {{ request('type') == 'reply' ? 'selected' : '' }}>Resposta</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Status</label>
                <select name="status" class="w-full border rounded-md px-3 py-2 text-sm">
                    <option value="">Todos</option>
                    <option value="queued" {{ request('status') == 'queued' ? 'selected' : '' }}>Na fila</option>
                    <option value="sent" {{ request('status') == 'sent' ? 'selected' : '' }}>Enviada</option>
                    <option value="delivered" {{ request('status') == 'delivered' ? 'selected' : '' }}>Entregue</option>
                    <option value="read" {{ request('status') == 'read' ? 'selected' : '' }}>Lida</option>
                    <option value="failed" {{ request('status') == 'failed' ? 'selected' : '' }}>Falhou</option>
                    <option value="received" {{ request('status') == 'received' ? 'selected' : '' }}>Recebida</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Direção</label>
                <select name="direction" class="w-full border rounded-md px-3 py-2 text-sm">
                    <option value="">Todas</option>
                    <option value="outbound" {{ request('direction') == 'outbound' ? 'selected' : '' }}>Enviada</option>
                    <option value="inbound" {{ request('direction') == 'inbound' ? 'selected' : '' }}>Recebida</option>
                </select>
            </div>
            <div>
                <button type="submit" class="w-full bg-emerald-600 hover:bg-emerald-700 text-white text-sm px-4 py-2 rounded-md">
                    <i class="fa fa-search mr-1"></i> Filtrar
                </button>
            </div>
        </form>
    </div>

    {{-- Tabela --}}
    <div class="bg-white rounded-lg shadow-sm border overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b">
                    <tr>
                        <th class="text-left px-4 py-3 font-medium text-gray-600">Direção</th>
                        <th class="text-left px-4 py-3 font-medium text-gray-600">Destinatário</th>
                        <th class="text-left px-4 py-3 font-medium text-gray-600">Tipo</th>
                        <th class="text-left px-4 py-3 font-medium text-gray-600">Mensagem</th>
                        <th class="text-center px-4 py-3 font-medium text-gray-600">Status</th>
                        <th class="text-left px-4 py-3 font-medium text-gray-600">Data</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    @php
                        $typeLabels = [
                            'confirmation' => ['Confirmação', 'bg-blue-100 text-blue-700'],
                            'reminder'     => ['Lembrete', 'bg-yellow-100 text-yellow-700'],
                            'cancellation' => ['Cancelamento', 'bg-red-100 text-red-700'],
                            'reschedule'   => ['Remarcação', 'bg-purple-100 text-purple-700'],
                            'campaign'     => ['Campanha', 'bg-indigo-100 text-indigo-700'],
                            'manual'       => ['Manual', 'bg-gray-100 text-gray-700'],
                            'reply'        => ['Resposta', 'bg-green-100 text-green-700'],
                        ];
                        $statusLabels = [
                            'queued'    => ['Na fila', 'bg-gray-100 text-gray-600'],
                            'sent'      => ['Enviada', 'bg-blue-100 text-blue-700'],
                            'delivered' => ['Entregue', 'bg-blue-100 text-blue-700'],
                            'read'      => ['Lida', 'bg-green-100 text-green-700'],
                            'failed'    => ['Falhou', 'bg-red-100 text-red-700'],
                            'received'  => ['Recebida', 'bg-purple-100 text-purple-700'],
                        ];
                    @endphp
                    @forelse($messages as $msg)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3">
                                @if($msg->direction === 'outbound')
                                    <i class="fa fa-paper-plane text-blue-500" title="Enviada"></i>
                                @else
                                    <i class="fa fa-reply text-green-500" title="Recebida"></i>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                <p class="font-medium text-gray-800">{{ $msg->to_name ?? '—' }}</p>
                                <p class="text-xs text-gray-400">{{ $msg->to_phone }}</p>
                            </td>
                            <td class="px-4 py-3">
                                @php $tl = $typeLabels[$msg->type] ?? [$msg->type, 'bg-gray-100 text-gray-700']; @endphp
                                <span class="text-xs px-2 py-0.5 rounded-full {{ $tl[1] }}">{{ $tl[0] }}</span>
                            </td>
                            <td class="px-4 py-3 max-w-xs">
                                <p class="text-sm text-gray-700 truncate" title="{{ $msg->content }}">{{ \Illuminate\Support\Str::limit($msg->content, 80) }}</p>
                                @if($msg->error_message)
                                    <p class="text-xs text-red-500 mt-0.5">{{ \Illuminate\Support\Str::limit($msg->error_message, 50) }}</p>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-center">
                                @php $sl = $statusLabels[$msg->status] ?? [$msg->status, 'bg-gray-100 text-gray-700']; @endphp
                                <span class="text-xs px-2 py-0.5 rounded-full {{ $sl[1] }}">{{ $sl[0] }}</span>
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-gray-600 text-xs">
                                {{ $msg->created_at->format('d/m/Y H:i') }}
                                @if($msg->read_at)
                                    <br><span class="text-green-500">Lida {{ $msg->read_at->format('H:i') }}</span>
                                @elseif($msg->delivered_at)
                                    <br><span class="text-blue-500">Entregue {{ $msg->delivered_at->format('H:i') }}</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-8 text-center text-gray-400">Nenhuma mensagem encontrada.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-4 py-3 border-t border-gray-100">
            {{ $messages->links() }}
        </div>
    </div>
</div>
@endsection
