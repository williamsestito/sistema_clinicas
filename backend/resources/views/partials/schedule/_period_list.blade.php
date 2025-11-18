@if($periods->count())
<div class="bg-white shadow p-6 rounded-lg">
    <h2 class="text-lg font-semibold text-gray-700 mb-4">📋 Períodos Cadastrados</h2>

    @php
        $weekMap = [
            0 => 'Domingo',
            1 => 'Segunda',
            2 => 'Terça',
            3 => 'Quarta',
            4 => 'Quinta',
            5 => 'Sexta',
            6 => 'Sábado',
        ];
    @endphp

    <ul class="divide-y divide-gray-200 text-sm" id="period-list">
        @foreach($periods as $period)
            <li id="period-row-{{ $period->id }}" class="py-3 flex justify-between items-center">

                <div class="text-gray-800 font-medium">
                    <strong>{{ \Carbon\Carbon::parse($period->start_date)->format('d/m/Y') }}</strong>
                    →
                    <strong>{{ \Carbon\Carbon::parse($period->end_date)->format('d/m/Y') }}</strong>

                    <span class="text-gray-500 ml-2">
                        Dias:
                        {{ implode(', ', array_map(fn($d) => $weekMap[$d], $period->active_days)) }}
                    </span>

                    @if($selectedPeriod && $selectedPeriod->id == $period->id)
                        <span class="ml-2 px-2 py-1 rounded bg-green-100 text-green-700 text-xs">
                            Selecionado
                        </span>
                    @endif
                </div>

                {{-- BOTÃO EXCLUIR PADRÃO --}}
                <button 
                    class="delete-period px-3 py-1 text-sm font-medium 
                           text-red-600 hover:text-white 
                           hover:bg-red-600 border border-red-600 
                           rounded-md transition-all duration-200"
                    data-id="{{ $period->id }}">
                    Excluir
                </button>

            </li>
        @endforeach
    </ul>
</div>
@endif
