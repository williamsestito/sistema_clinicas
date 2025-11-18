@extends('layouts.app')

@section('title', 'Solicitações de Agendamento')

@section('content')
<div class="p-6 max-w-5xl mx-auto space-y-6">

    <h1 class="text-2xl font-semibold text-gray-800 flex items-center gap-2">
        📬 Solicitações de Agendamento
    </h1>

    @if(session('success'))
        <div class="p-3 bg-green-100 border border-green-200 text-green-700 rounded">
            {{ session('success') }}
        </div>
    @endif

    @if($requests->count() == 0)
        <div class="p-6 bg-white shadow rounded text-center text-gray-500">
            Nenhuma solicitação pendente no momento.
        </div>
    @endif

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

        @foreach($requests as $req)
            <div class="bg-white shadow rounded-lg p-5 border border-gray-100">

                <div class="flex justify-between items-center mb-3">
                    <h2 class="text-lg font-semibold text-gray-800">
                        {{ $req->client->name }}
                    </h2>

                    <span class="bg-yellow-100 text-yellow-700 text-xs px-2 py-0.5 rounded">
                        Pendente
                    </span>
                </div>

                <p class="text-sm text-gray-600 mb-1">
                    <i class="fa-regular fa-clock"></i>
                    {{ \Carbon\Carbon::parse($req->start_at)->format('d/m/Y H:i') }}
                    –
                    {{ \Carbon\Carbon::parse($req->end_at)->format('H:i') }}
                </p>

                <p class="text-sm text-gray-600 flex items-center gap-1 mb-2">
                    <i class="fa-solid fa-stethoscope"></i>
                    {{ $req->service->name }}
                </p>

                @if($req->notes)
                    <p class="text-xs text-gray-500 italic border-l-2 pl-2 mt-2">
                        “{{ $req->notes }}”
                    </p>
                @endif

                <div class="mt-4 flex gap-2">

                    {{-- Aceitar --}}
                    <form action="{{ route('professional.appointments.approve', $req->id) }}"
                          method="POST" class="flex-1">
                        @csrf
                        <button class="w-full bg-green-600 hover:bg-green-700 text-white py-2 rounded text-sm">
                            ✔ Aceitar
                        </button>
                    </form>

                    {{-- Rejeitar --}}
                    <button onclick="openRejectModal({{ $req->id }})"
                            class="flex-1 bg-red-600 hover:bg-red-700 text-white py-2 rounded text-sm">
                        ✖ Rejeitar
                    </button>
                </div>

            </div>
        @endforeach

    </div>
</div>

{{-- MODAL REJEIÇÃO --}}
<div id="rejectModal"
     class="fixed inset-0 bg-black/50 hidden items-center justify-center z-50">

    <form method="POST" id="rejectForm"
          class="bg-white w-96 rounded shadow-lg p-6 space-y-4">

        @csrf
        <h2 class="text-lg font-semibold text-gray-800 flex items-center gap-2">
            ✖ Rejeitar Agendamento
        </h2>

        <input type="hidden" name="appointment_id" id="reject_id">

        <div>
            <label class="text-sm text-gray-600">Motivo (opcional)</label>
            <input type="text" name="reason"
                   placeholder="Ex: horário indisponível..."
                   class="w-full border-gray-300 rounded-md">
        </div>

        <div class="flex justify-end gap-2">
            <button type="button"
                    onclick="closeRejectModal()"
                    class="text-gray-600 hover:underline text-sm">
                Cancelar
            </button>

            <button type="submit"
                    class="bg-red-600 hover:bg-red-700 text-white text-sm px-4 py-2 rounded-md">
                Confirmar
            </button>
        </div>
    </form>

</div>

<script>
function openRejectModal(id) {
    document.getElementById('reject_id').value = id;
    const form = document.getElementById('rejectForm');
    form.action = '/professional/appointments/' + id + '/reject';
    document.getElementById('rejectModal').classList.remove('hidden');
    document.getElementById('rejectModal').classList.add('flex');
}

function closeRejectModal() {
    document.getElementById('rejectModal').classList.remove('flex');
    document.getElementById('rejectModal').classList.add('hidden');
}
</script>

@endsection
