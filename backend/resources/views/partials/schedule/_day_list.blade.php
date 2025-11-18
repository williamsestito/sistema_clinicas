@if(isset($weeklySchedules) && $weeklySchedules->count())
<div class="bg-white shadow p-6 rounded-lg">
    <h2 class="text-lg font-semibold text-gray-700 mb-4">
        📅 Horários Configurados
    </h2>

    <div class="overflow-x-auto">
        <table class="min-w-full text-sm divide-y divide-gray-200">
            <thead>
                <tr class="bg-gray-50 text-gray-600 text-xs uppercase">
                    <th class="px-3 py-2 text-left">Dia</th>
                    <th class="px-3 py-2 text-left">Início</th>
                    <th class="px-3 py-2 text-left">Fim</th>
                    <th class="px-3 py-2 text-left">Pausa (Início)</th>
                    <th class="px-3 py-2 text-left">Pausa (Fim)</th>
                    <th class="px-3 py-2 text-center">Duração</th>
                    <th class="px-3 py-2 text-right">Ações</th>
                </tr>
            </thead>

            <tbody class="divide-y divide-gray-100">
                @foreach($weeklySchedules as $schedule)
                    <tr>
                        <td class="px-3 py-2 text-gray-800">{{ $days[$schedule->weekday] }}</td>

                        <td class="px-3 py-2 text-gray-700">
                            {{ $schedule->start_time ? \Carbon\Carbon::parse($schedule->start_time)->format('H:i') : '—' }}
                        </td>

                        <td class="px-3 py-2 text-gray-700">
                            {{ $schedule->end_time ? \Carbon\Carbon::parse($schedule->end_time)->format('H:i') : '—' }}
                        </td>

                        <td class="px-3 py-2 text-gray-600">
                            {{ $schedule->break_start ? \Carbon\Carbon::parse($schedule->break_start)->format('H:i') : '—' }}
                        </td>

                        <td class="px-3 py-2 text-gray-600">
                            {{ $schedule->break_end ? \Carbon\Carbon::parse($schedule->break_end)->format('H:i') : '—' }}
                        </td>

                        <td class="px-3 py-2 text-center text-gray-800 font-medium">
                            {{ $schedule->slot_min }} min
                        </td>

                        <td class="px-3 py-2 text-right">
                            <form action="{{ route('professional.schedule.day.destroy', $schedule->id) }}"
                                  method="POST"
                                  onsubmit="return confirm('Excluir horário de {{ $days[$schedule->weekday] }}?')">
                                @csrf
                                @method('DELETE')
                                <button class="text-red-600 hover:text-red-800 text-sm font-medium">
                                    Remover
                                </button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>

        </table>
    </div>
</div>
@endif
