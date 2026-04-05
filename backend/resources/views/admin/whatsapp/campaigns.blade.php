@extends('layouts.app')
@section('title', 'WhatsApp — Campanhas')

@section('content')
<div class="p-6 space-y-4">

    <div class="flex items-center justify-between">
        <h1 class="text-xl font-bold text-gray-800">
            <i class="fa-brands fa-whatsapp text-green-500 mr-1"></i> Campanhas
        </h1>
        <div class="flex gap-2">
            <a href="{{ route('admin.whatsapp.dashboard') }}" class="text-sm text-blue-600 hover:text-blue-800">
                <i class="fa fa-arrow-left mr-1"></i> Dashboard
            </a>
            <a href="{{ route('admin.whatsapp.campaigns.create') }}"
               class="bg-emerald-600 hover:bg-emerald-700 text-white text-sm px-4 py-2 rounded-md">
                <i class="fa fa-plus mr-1"></i> Nova Campanha
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="bg-green-50 border border-green-300 text-green-700 text-sm rounded-md p-3">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="bg-red-50 border border-red-300 text-red-700 text-sm rounded-md p-3">{{ session('error') }}</div>
    @endif

    <div class="bg-white rounded-lg shadow-sm border overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b">
                    <tr>
                        <th class="text-left px-4 py-3 font-medium text-gray-600">Nome</th>
                        <th class="text-left px-4 py-3 font-medium text-gray-600">Segmento</th>
                        <th class="text-center px-4 py-3 font-medium text-gray-600">Status</th>
                        <th class="text-center px-4 py-3 font-medium text-gray-600">Destinatários</th>
                        <th class="text-center px-4 py-3 font-medium text-gray-600">Progresso</th>
                        <th class="text-left px-4 py-3 font-medium text-gray-600">Agendada em</th>
                        <th class="text-center px-4 py-3 font-medium text-gray-600">Ações</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    @php
                        $segmentLabels = [
                            'all'      => 'Todos',
                            'active'   => 'Ativos',
                            'inactive' => 'Inativos',
                            'birthday' => 'Aniversariantes',
                            'custom'   => 'Personalizado',
                        ];
                        $statusConfig = [
                            'draft'     => ['Rascunho', 'bg-gray-100 text-gray-600'],
                            'scheduled' => ['Agendada', 'bg-yellow-100 text-yellow-700'],
                            'sending'   => ['Enviando', 'bg-blue-100 text-blue-700'],
                            'completed' => ['Concluída', 'bg-green-100 text-green-700'],
                            'cancelled' => ['Cancelada', 'bg-red-100 text-red-700'],
                        ];
                    @endphp
                    @forelse($campaigns as $campaign)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3">
                                <p class="font-medium text-gray-800">{{ $campaign->name }}</p>
                                <p class="text-xs text-gray-400">Criada {{ $campaign->created_at->format('d/m/Y') }}</p>
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-700">
                                {{ $segmentLabels[$campaign->segment] ?? $campaign->segment }}
                            </td>
                            <td class="px-4 py-3 text-center">
                                @php $sc = $statusConfig[$campaign->status] ?? [$campaign->status, 'bg-gray-100 text-gray-700']; @endphp
                                <span class="text-xs px-2 py-0.5 rounded-full {{ $sc[1] }}">{{ $sc[0] }}</span>
                            </td>
                            <td class="px-4 py-3 text-center text-gray-700">
                                {{ $campaign->total_recipients }}
                            </td>
                            <td class="px-4 py-3 text-center">
                                @if(in_array($campaign->status, ['sending', 'completed']))
                                    @php
                                        $pct = $campaign->total_recipients > 0
                                            ? round(($campaign->sent_count / $campaign->total_recipients) * 100)
                                            : 0;
                                    @endphp
                                    <div class="w-24 mx-auto bg-gray-200 rounded-full h-2">
                                        <div class="bg-emerald-500 h-2 rounded-full" style="width:{{ $pct }}%"></div>
                                    </div>
                                    <span class="text-xs text-gray-500 mt-0.5 block">
                                        {{ $campaign->sent_count }}/{{ $campaign->total_recipients }}
                                        ({{ $pct }}%)
                                    </span>
                                @else
                                    <span class="text-xs text-gray-400">—</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-600">
                                @if($campaign->scheduled_at)
                                    {{ $campaign->scheduled_at->format('d/m/Y H:i') }}
                                @else
                                    <span class="text-gray-400">—</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-center">
                                <div class="flex items-center justify-center gap-1">
                                    @if($campaign->status === 'draft')
                                        <form method="POST" action="{{ route('admin.whatsapp.campaigns.launch', $campaign) }}">
                                            @csrf
                                            <button class="text-emerald-600 hover:text-emerald-800 text-xs px-2 py-1 border rounded-md"
                                                    title="Enviar agora" onclick="return confirm('Enviar esta campanha agora?')">
                                                <i class="fa fa-play"></i> Enviar
                                            </button>
                                        </form>
                                    @endif

                                    @if(in_array($campaign->status, ['draft', 'scheduled', 'sending']))
                                        <form method="POST" action="{{ route('admin.whatsapp.campaigns.cancel', $campaign) }}">
                                            @csrf
                                            <button class="text-yellow-600 hover:text-yellow-800 text-xs px-2 py-1 border rounded-md"
                                                    title="Cancelar" onclick="return confirm('Cancelar esta campanha?')">
                                                <i class="fa fa-ban"></i>
                                            </button>
                                        </form>
                                    @endif

                                    @if(in_array($campaign->status, ['draft', 'cancelled', 'completed']))
                                        <form method="POST" action="{{ route('admin.whatsapp.campaigns.destroy', $campaign) }}">
                                            @csrf @method('DELETE')
                                            <button class="text-red-600 hover:text-red-800 text-xs px-2 py-1 border rounded-md"
                                                    title="Excluir" onclick="return confirm('Excluir esta campanha permanentemente?')">
                                                <i class="fa fa-trash"></i>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-8 text-center text-gray-400">Nenhuma campanha encontrada.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-4 py-3 border-t border-gray-100">
            {{ $campaigns->links() }}
        </div>
    </div>
</div>
@endsection
