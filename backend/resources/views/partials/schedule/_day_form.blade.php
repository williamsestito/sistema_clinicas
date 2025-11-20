@if($selectedPeriod)
<div class="bg-white shadow p-6 rounded-lg space-y-6">

    {{-- Cabeçalho --}}
    <div class="flex justify-between items-center mb-3">
        <h2 class="text-lg font-semibold text-gray-700 flex items-center gap-2">
            🕒 Horários do Período
        </h2>

        <button id="copy-schedule-btn"
            type="button"
            class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md text-sm shadow">
            Copiar horários para todos os dias
        </button>
    </div>

    @php
        $activeDays = $selectedPeriod->active_days ?? [];
        $lastSaved  = session('saved_days') ?? [];
    @endphp

    <form action="{{ route('professional.schedule.weekly.store') }}" method="POST" id="schedule-form">
        @csrf
        <input type="hidden" name="period_id" value="{{ $selectedPeriod->id }}">

        <table class="min-w-full text-sm">
            <thead>
                <tr class="border-b text-gray-500">
                    <th class="py-2 text-left">Dia</th>
                    <th class="py-2 text-center">Início</th>
                    <th class="py-2 text-center">Fim</th>
                    <th class="py-2 text-center">Pausa Início</th>
                    <th class="py-2 text-center">Pausa Fim</th>
                    <th class="py-2 text-center">Duração</th>
                </tr>
            </thead>

            <tbody>
                @foreach($days as $weekday => $label)
                    @php
                        $schedule = $weeklySchedules[$weekday] ?? null;
                        $enabled  = in_array($weekday, $activeDays);
                        $saved    = in_array($weekday, $lastSaved);
                    @endphp

                    <tr class="schedule-row border-b {{ $saved ? 'bg-green-50' : '' }}">
                        <td class="py-3 font-semibold text-gray-800">
                            {{ $label }}
                            @if(!$enabled)
                                <span class="text-red-500 text-xs italic ml-2">(desativado no período)</span>
                            @endif
                        </td>

                        {{-- Início --}}
                        <td class="py-3 text-center">
                            <input type="time"
                                   name="schedules[{{ $weekday }}][start_time]"
                                   class="start-time input-premium editable-field"
                                   value="{{ $schedule->start_time ?? '' }}"
                                   {{ $enabled ? '' : 'disabled' }}>
                        </td>

                        {{-- Fim --}}
                        <td class="py-3 text-center">
                            <input type="time"
                                   name="schedules[{{ $weekday }}][end_time]"
                                   class="end-time input-premium editable-field"
                                   value="{{ $schedule->end_time ?? '' }}"
                                   {{ $enabled ? '' : 'disabled' }}>
                        </td>

                        {{-- Pausa Início --}}
                        <td class="py-3 text-center">
                            <input type="time"
                                   name="schedules[{{ $weekday }}][break_start]"
                                   class="break-start input-premium editable-field"
                                   value="{{ $schedule->break_start ?? '' }}"
                                   {{ $enabled ? '' : 'disabled' }}>
                        </td>

                        {{-- Pausa Fim --}}
                        <td class="py-3 text-center">
                            <input type="time"
                                   name="schedules[{{ $weekday }}][break_end]"
                                   class="break-end input-premium editable-field"
                                   value="{{ $schedule->break_end ?? '' }}"
                                   {{ $enabled ? '' : 'disabled' }}>
                        </td>

                        {{-- Duração (CORRETO — LÊ duration e envia como slot_min) --}}
                        <td class="py-3 text-center">
                            <input type="number"
                                   name="schedules[{{ $weekday }}][slot_min]"
                                   class="duration input-premium editable-field w-20 text-center"
                                   value="{{ $schedule->duration ?? 30 }}"
                                   min="5" step="5"
                                   {{ $enabled ? '' : 'disabled' }}>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="text-right mt-5">
            <button class="bg-green-600 hover:bg-green-700 text-white px-6 py-2 rounded-md shadow font-medium">
                ✔️ Salvar Horários
            </button>
        </div>
    </form>
</div>

{{-- Scripts --}}
<script>
/* Marca campos editados */
document.querySelectorAll(".editable-field").forEach(el => {
    el.addEventListener("input", () => {
        el.classList.add("bg-yellow-100");
    });
});

/* Cópia de horários */
document.getElementById('copy-schedule-btn')?.addEventListener('click', function () {

    const first = document.querySelector('.schedule-row input:not([disabled])');
    if (!first) return alert("Nenhum dia ativo para copiar.");

    const row = first.closest('.schedule-row');

    const get = cls => row.querySelector(cls)?.value || '';

    const ref = {
        start:  get('.start-time'),
        end:    get('.end-time'),
        bStart: get('.break-start'),
        bEnd:   get('.break-end'),
        dur:    get('.duration'),
    };

    document.querySelectorAll('.schedule-row').forEach(r => {
        if (r.querySelector('.start-time:disabled')) return;

        r.querySelector('.start-time').value   = ref.start;
        r.querySelector('.end-time').value     = ref.end;
        r.querySelector('.break-start').value  = ref.bStart;
        r.querySelector('.break-end').value    = ref.bEnd;
        r.querySelector('.duration').value     = ref.dur;

        r.classList.add("bg-yellow-100");
    });

    alert("Horários copiados para dias ativos!");
});
</script>

@endif
