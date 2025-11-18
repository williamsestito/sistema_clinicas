<div class="bg-white shadow p-6 rounded-lg space-y-6">
    <h2 class="text-lg font-semibold text-gray-700">🆕 Criar Novo Período</h2>

    <form action="{{ route('professional.schedule.period.store') }}" method="POST" class="space-y-5">
        @csrf

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="text-sm text-gray-600">Data inicial</label>
                <input type="date" name="start_date" required class="input-premium w-full">
            </div>

            <div>
                <label class="text-sm text-gray-600">Data final</label>
                <input type="date" name="end_date" required class="input-premium w-full">
            </div>
        </div>

        <div>
            <label class="text-sm text-gray-600 mb-2 block">Dias da semana ativos</label>

            <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                @foreach([0=>'Dom',1=>'Seg',2=>'Ter',3=>'Qua',4=>'Qui',5=>'Sex',6=>'Sáb'] as $num => $label)
                    <label class="flex items-center space-x-2 text-gray-700 text-sm">
                        <input type="checkbox" name="active_days[]" value="{{ $num }}" class="rounded">
                        <span>{{ $label }}</span>
                    </label>
                @endforeach
            </div>
        </div>

        <div class="text-right">
            <button 
                class="px-5 py-2 text-sm font-semibold 
                       text-white bg-green-600 
                       hover:bg-green-700 
                       rounded-md shadow 
                       transition-all duration-200">
                ✔️ Salvar Período
            </button>
        </div>
    </form>
</div>
