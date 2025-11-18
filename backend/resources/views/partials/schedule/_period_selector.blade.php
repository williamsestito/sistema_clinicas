@if($periods->count())
<div class="bg-white shadow p-6 rounded-lg">
    <h2 class="text-lg font-semibold text-gray-700 mb-4">📅 Selecionar Período</h2>

    <form method="GET"
          action="{{ route('professional.schedule.config') }}"
          class="grid grid-cols-1 md:grid-cols-3 gap-4">

        <div class="md:col-span-2">
            <select name="period_id"
                class="input-premium w-full"
                onchange="this.form.submit()">

                @foreach($periods as $period)
                    <option value="{{ $period->id }}"
                        {{ $selectedPeriod && $selectedPeriod->id === $period->id ? 'selected' : '' }}>

                        {{ \Carbon\Carbon::parse($period->start_date)->format('d/m/Y') }}
                        →
                        {{ \Carbon\Carbon::parse($period->end_date)->format('d/m/Y') }}

                    </option>
                @endforeach

            </select>
        </div>

        <div>
            <button type="submit" class="btn-primary w-full py-2">
                Aplicar
            </button>
        </div>

    </form>
</div>
@endif
