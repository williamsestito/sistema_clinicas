<div class="bg-white shadow p-6 rounded-lg mt-6">
    <h2 class="text-lg font-semibold mb-4 flex items-center gap-2">
        📅 Bloquear Data
    </h2>

    <form id="block-date-form" class="space-y-4" autocomplete="off">
        @csrf

        <div class="flex flex-col sm:flex-row gap-4 items-start sm:items-center">

            {{-- DATA --}}
            <div class="flex flex-col">
                <label for="block-date" class="text-sm font-medium text-gray-700">
                    Data
                </label>
                <input
                    id="block-date"
                    type="date"
                    name="date"
                    class="border rounded p-2 focus:ring-blue-500 focus:border-blue-500"
                    required>
            </div>

            {{-- MOTIVO --}}
            <div class="flex flex-col flex-1">
                <label for="block-reason" class="text-sm font-medium text-gray-700">
                    Motivo (opcional)
                </label>
                <input
                    id="block-reason"
                    type="text"
                    name="reason"
                    placeholder="Ex: Médico ausente, manutenção, viagem..."
                    class="border rounded p-2 w-full focus:ring-blue-500 focus:border-blue-500">
            </div>

            {{-- BOTÃO --}}
            <button
                id="block-submit-btn"
                type="submit"
                class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded shadow transition disabled:opacity-60 disabled:cursor-not-allowed"
            >
                Bloquear Dia
            </button>
        </div>
    </form>

    {{-- FEEDBACK --}}
    <div id="block-feedback" class="mt-3 text-sm"></div>
</div>
