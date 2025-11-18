<div class="bg-white shadow p-6 rounded-lg mt-6">
    <h2 class="text-lg font-semibold mb-4">📅 Bloquear Data</h2>

    <form id="block-date-form">
        @csrf

        <div class="flex gap-4">
            <input type="date" name="date" class="border rounded p-2" required>

            <input type="text" name="reason"
                   placeholder="Motivo (opcional)"
                   class="border rounded p-2 w-64">

            <button type="submit"
                class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded shadow">
                Bloquear Dia
            </button>
        </div>
    </form>

    <div id="block-feedback" class="mt-3 text-sm"></div>
</div>
