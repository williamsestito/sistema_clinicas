@extends('layouts.app')
@section('title', 'WhatsApp — Dashboard')

@section('content')
<div class="p-6 space-y-6">

    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-xl font-bold text-gray-800">
                <i class="fa-brands fa-whatsapp text-green-500 mr-1"></i> WhatsApp — Dashboard
            </h1>
            <p class="text-sm text-gray-500 mt-0.5">Últimos 30 dias</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('admin.whatsapp.messages') }}" class="text-sm text-blue-600 hover:text-blue-800">
                <i class="fa fa-list mr-1"></i> Mensagens
            </a>
            <a href="{{ route('admin.whatsapp.settings') }}" class="text-sm text-gray-600 hover:text-gray-800 ml-3">
                <i class="fa fa-cog mr-1"></i> Configurações
            </a>
        </div>
    </div>

    {{-- Status da Conexão --}}
    @if(!$connection || !$connection->active)
        <div class="bg-yellow-50 border border-yellow-300 text-yellow-800 text-sm rounded-md p-4">
            <i class="fa fa-exclamation-triangle mr-1"></i>
            <strong>WhatsApp não configurado.</strong>
            <a href="{{ route('admin.whatsapp.settings') }}" class="underline ml-1">Configure agora</a>
        </div>
    @else
        <div class="bg-green-50 border border-green-300 text-green-700 text-sm rounded-md p-3">
            <i class="fa fa-check-circle mr-1"></i>
            Conectado via <strong>{{ strtoupper($connection->provider) }}</strong>
            @if($connection->connected_at)
                — desde {{ $connection->connected_at->format('d/m/Y H:i') }}
            @endif
            @if($automation && $automation->active)
                <span class="ml-2 px-2 py-0.5 bg-green-200 text-green-800 text-xs rounded-full">Automações ativas</span>
            @endif
        </div>
    @endif

    {{-- Cards de Estatísticas --}}
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-4">
        <div class="bg-white rounded-lg shadow-sm border p-4 text-center">
            <p class="text-xs text-gray-500 uppercase">Enviadas</p>
            <p class="text-2xl font-bold text-gray-800 mt-1">{{ number_format($stats->sent ?? 0) }}</p>
        </div>
        <div class="bg-white rounded-lg shadow-sm border p-4 text-center">
            <p class="text-xs text-gray-500 uppercase">Entregues</p>
            <p class="text-2xl font-bold text-blue-600 mt-1">{{ number_format($stats->delivered ?? 0) }}</p>
        </div>
        <div class="bg-white rounded-lg shadow-sm border p-4 text-center">
            <p class="text-xs text-gray-500 uppercase">Lidas</p>
            <p class="text-2xl font-bold text-green-600 mt-1">{{ number_format($stats->read ?? 0) }}</p>
        </div>
        <div class="bg-white rounded-lg shadow-sm border p-4 text-center">
            <p class="text-xs text-gray-500 uppercase">Falharam</p>
            <p class="text-2xl font-bold text-red-600 mt-1">{{ number_format($stats->failed ?? 0) }}</p>
        </div>
        <div class="bg-white rounded-lg shadow-sm border p-4 text-center">
            <p class="text-xs text-gray-500 uppercase">Recebidas</p>
            <p class="text-2xl font-bold text-purple-600 mt-1">{{ number_format($stats->received ?? 0) }}</p>
        </div>
        <div class="bg-white rounded-lg shadow-sm border p-4 text-center">
            <p class="text-xs text-gray-500 uppercase">Taxa Entrega</p>
            <p class="text-2xl font-bold text-emerald-600 mt-1">
                {{ ($stats->sent ?? 0) > 0 ? number_format((($stats->delivered ?? 0) / $stats->sent) * 100, 1) : '0' }}%
            </p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- Gráfico: Envios por dia --}}
        <div class="lg:col-span-2 bg-white rounded-xl shadow-sm border p-5">
            <h2 class="text-sm font-semibold text-gray-700 mb-4">
                <i class="fa fa-chart-bar text-gray-400 mr-1"></i> Envios por Dia
            </h2>
            <canvas id="chartByDay" height="120"></canvas>
        </div>

        {{-- Mensagens por tipo --}}
        <div class="bg-white rounded-xl shadow-sm border p-5">
            <h2 class="text-sm font-semibold text-gray-700 mb-4">
                <i class="fa fa-pie-chart text-gray-400 mr-1"></i> Por Tipo
            </h2>
            @php
                $typeLabels = [
                    'confirmation' => 'Confirmação',
                    'reminder'     => 'Lembrete',
                    'cancellation' => 'Cancelamento',
                    'reschedule'   => 'Remarcação',
                    'campaign'     => 'Campanha',
                    'manual'       => 'Manual',
                    'reply'        => 'Resposta',
                ];
            @endphp
            @forelse($byType as $item)
                <div class="flex items-center justify-between py-2 {{ !$loop->last ? 'border-b border-gray-100' : '' }}">
                    <span class="text-sm text-gray-700">{{ $typeLabels[$item->type] ?? $item->type }}</span>
                    <span class="text-sm font-semibold text-gray-800">{{ $item->total }}</span>
                </div>
            @empty
                <p class="text-xs text-gray-400">Nenhuma mensagem no período.</p>
            @endforelse
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        {{-- Campanhas ativas --}}
        <div class="bg-white rounded-xl shadow-sm border p-5">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-sm font-semibold text-gray-700">
                    <i class="fa fa-bullhorn text-gray-400 mr-1"></i> Campanhas Ativas
                </h2>
                <a href="{{ route('admin.whatsapp.campaigns') }}" class="text-xs text-blue-600 hover:text-blue-800">Ver todas</a>
            </div>
            @forelse($activeCampaigns as $cp)
                <div class="flex items-center justify-between py-2 {{ !$loop->last ? 'border-b border-gray-100' : '' }}">
                    <div>
                        <p class="text-sm font-medium text-gray-800">{{ $cp->name }}</p>
                        <p class="text-xs text-gray-400">
                            {{ $cp->total_recipients }} destinatários
                            @if($cp->scheduled_at) — {{ $cp->scheduled_at->format('d/m H:i') }} @endif
                        </p>
                    </div>
                    <span class="text-xs px-2 py-0.5 rounded-full {{ $cp->status === 'sending' ? 'bg-blue-100 text-blue-700' : 'bg-yellow-100 text-yellow-700' }}">
                        {{ $cp->status === 'sending' ? 'Enviando' : 'Agendada' }}
                    </span>
                </div>
            @empty
                <p class="text-xs text-gray-400">Nenhuma campanha ativa.</p>
            @endforelse
        </div>

        {{-- Últimas mensagens --}}
        <div class="bg-white rounded-xl shadow-sm border p-5">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-sm font-semibold text-gray-700">
                    <i class="fa fa-comment text-gray-400 mr-1"></i> Últimas Mensagens
                </h2>
                <a href="{{ route('admin.whatsapp.messages') }}" class="text-xs text-blue-600 hover:text-blue-800">Ver todas</a>
            </div>
            @forelse($recentMessages as $msg)
                <div class="flex items-start gap-3 py-2 {{ !$loop->last ? 'border-b border-gray-100' : '' }}">
                    <i class="fa {{ $msg->direction === 'outbound' ? 'fa-paper-plane text-blue-400' : 'fa-reply text-green-400' }} mt-0.5"></i>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm text-gray-800 truncate">{{ $msg->to_name ?? $msg->to_phone }}</p>
                        <p class="text-xs text-gray-400 truncate">{{ \Illuminate\Support\Str::limit($msg->content, 60) }}</p>
                    </div>
                    <div class="text-right flex-shrink-0">
                        @php
                            $statusColors = [
                                'queued'    => 'text-gray-400',
                                'sent'      => 'text-blue-500',
                                'delivered' => 'text-blue-600',
                                'read'      => 'text-green-500',
                                'failed'    => 'text-red-500',
                                'received'  => 'text-purple-500',
                            ];
                        @endphp
                        <i class="fa fa-circle text-[8px] {{ $statusColors[$msg->status] ?? 'text-gray-300' }}" title="{{ $msg->status }}"></i>
                        <p class="text-[10px] text-gray-400">{{ $msg->created_at->format('d/m H:i') }}</p>
                    </div>
                </div>
            @empty
                <p class="text-xs text-gray-400">Nenhuma mensagem ainda.</p>
            @endforelse
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const ctx = document.getElementById('chartByDay');
    if (!ctx) return;

    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: @json($byDay->pluck('date')->map(fn($d) => \Carbon\Carbon::parse($d)->format('d/m'))->unique()->values()),
            datasets: [
                {
                    label: 'Enviadas',
                    data: @json($byDay->pluck('total')),
                    backgroundColor: 'rgba(59, 130, 246, 0.7)',
                },
                {
                    label: 'Entregues',
                    data: @json($byDay->pluck('delivered')),
                    backgroundColor: 'rgba(16, 185, 129, 0.7)',
                }
            ]
        },
        options: {
            responsive: true,
            plugins: { legend: { position: 'bottom' } },
            scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } }
        }
    });
});
</script>
@endpush
