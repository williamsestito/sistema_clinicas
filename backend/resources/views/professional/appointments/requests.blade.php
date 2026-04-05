@extends('layouts.app')

@section('title', 'Solicitações de Agendamento')

@section('content')
<div class="p-6 max-w-5xl mx-auto space-y-6">

    <h1 class="text-2xl font-semibold text-gray-800 flex items-center gap-2">
        📬 Solicitações de Agendamento
    </h1>

    <div id="alerts"></div>

    @if($requests->count() == 0)
        <div class="p-6 bg-white shadow rounded text-center text-gray-500">
            Nenhuma solicitação pendente no momento.
        </div>
    @endif

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4" id="cards-container">

        @foreach($requests as $req)
            <div class="bg-white shadow rounded-lg p-5 border border-gray-100"
                 id="appointment-card-{{ $req->id }}">

                <div class="flex justify-between items-center mb-3">
                    <h2 class="text-lg font-semibold text-gray-800">
                        {{ $req->client->name }}
                    </h2>

                    <span class="bg-yellow-100 text-yellow-700 text-xs px-2 py-0.5 rounded">
                        Pendente
                    </span>
                </div>

                {{-- DATA / HORÁRIO --}}
                <p class="text-sm text-gray-600 mb-1">
                    <i class="fa-regular fa-clock"></i>
                    {{ \Carbon\Carbon::parse($req->start_at)->format('d/m/Y H:i') }}
                    –
                    {{ \Carbon\Carbon::parse($req->end_at)->format('H:i') }}
                </p>

                {{-- SERVIÇO / PROCEDIMENTO --}}
                <p class="text-sm text-gray-600 flex items-center gap-1 mb-2">
                    <i class="fa-solid fa-stethoscope"></i>

                    @if($req->service)
                        {{ $req->service->name }}
                    @else
                        {{ $req->notes ? str_replace('Procedimento: ', '', $req->notes) : 'Consulta' }}
                    @endif
                </p>

                {{-- OBSERVAÇÃO / NOTES --}}
                @if($req->notes)
                    <p class="text-xs text-gray-500 italic border-l-2 pl-2 mt-2">
                        “{{ $req->notes }}”
                    </p>
                @endif

                {{-- BOTÕES --}}
                <div class="mt-4 flex flex-col sm:flex-row gap-2">

                    {{-- Aceitar --}}
                    <button type="button"
                            onclick="approveAppointment({{ $req->id }})"
                            class="flex-1 bg-green-600 hover:bg-green-700 text-white py-2 rounded text-sm">
                        ✔ Aceitar
                    </button>

                    {{-- Reagendar --}}
                    <button type="button"
                            onclick="openRescheduleModal({{ $req->id }}, '{{ $req->start_at->format('Y-m-d') }}', '{{ $req->start_at->format('H:i') }}')"
                            class="flex-1 bg-blue-600 hover:bg-blue-700 text-white py-2 rounded text-sm">
                        🔁 Reagendar
                    </button>

                    {{-- Rejeitar --}}
                    <button type="button"
                            onclick="openRejectModal({{ $req->id }})"
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
     class="fixed inset-0 bg-black/40 hidden items-center justify-center z-50">

    <form method="POST" id="rejectForm"
          class="bg-white w-full max-w-md rounded-xl shadow-lg p-6 space-y-4 relative">

        @csrf
        <button type="button"
                onclick="closeRejectModal()"
                class="absolute top-3 right-3 text-gray-400 hover:text-gray-600">
            <i class="fa-solid fa-xmark"></i>
        </button>

        <h2 class="text-lg font-semibold text-gray-800 flex items-center gap-2">
            ✖ Rejeitar Agendamento
        </h2>

        <input type="hidden" name="appointment_id" id="reject_id">

        <div>
            <label class="text-sm text-gray-600">Motivo (opcional)</label>
            <input type="text" name="reason"
                   placeholder="Ex: horário indisponível..."
                   class="mt-1 w-full border-gray-300 rounded-md px-3 py-2 text-sm focus:ring-2 focus:ring-red-200 focus:border-red-400">
        </div>

        <div class="flex justify-end gap-2 pt-2">
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

{{-- MODAL REAGENDAR --}}
<div id="rescheduleModal"
     class="fixed inset-0 bg-black/40 hidden items-center justify-center z-50">

    <form method="POST" id="rescheduleForm"
          class="bg-white w-full max-w-md rounded-xl shadow-lg p-6 space-y-4 relative">

        @csrf
        <button type="button"
                onclick="closeRescheduleModal()"
                class="absolute top-3 right-3 text-gray-400 hover:text-gray-600">
            <i class="fa-solid fa-xmark"></i>
        </button>

        <h2 class="text-lg font-semibold text-gray-800 flex items-center gap-2">
            🔁 Reagendar Agendamento
        </h2>

        <input type="hidden" name="appointment_id" id="reschedule_id">

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            <div>
                <label class="text-sm text-gray-600">Nova data</label>
                <input type="date" name="date" id="reschedule_date"
                       class="mt-1 w-full border-gray-300 rounded-md px-3 py-2 text-sm focus:ring-2 focus:ring-blue-200 focus:border-blue-400">
            </div>

            <div>
                <label class="text-sm text-gray-600">Novo horário</label>
                <input type="time" name="time" id="reschedule_time"
                       class="mt-1 w-full border-gray-300 rounded-md px-3 py-2 text-sm focus:ring-2 focus:ring-blue-200 focus:border-blue-400">
            </div>
        </div>

        <p class="text-xs text-gray-500">
            O horário precisa estar livre na sua agenda. Em caso de conflito, o sistema avisará.
        </p>

        <div class="flex justify-end gap-2 pt-2">
            <button type="button"
                    onclick="closeRescheduleModal()"
                    class="text-gray-600 hover:underline text-sm">
                Cancelar
            </button>

            <button type="submit"
                    class="bg-blue-600 hover:bg-blue-700 text-white text-sm px-4 py-2 rounded-md">
                Confirmar
            </button>
        </div>
    </form>
</div>

<script>
const csrfToken = '{{ csrf_token() }}';

function showAlert(message, type = 'success') {
    const alerts = document.getElementById('alerts');
    const color = type === 'success'
        ? 'bg-green-100 border-green-200 text-green-800'
        : 'bg-red-100 border-red-200 text-red-800';

    alerts.innerHTML = `
        <div class="mb-4 p-3 border ${color} rounded text-sm flex justify-between items-center">
            <span>${message}</span>
            <button onclick="this.parentElement.remove()" class="text-xs text-gray-500 hover:text-gray-700">x</button>
        </div>
    `;
}

/* ------------ APROVAR (AJAX) ------------ */
async function approveAppointment(id) {
    try {
        const res = await fetch(`/professional/appointments/${id}/approve`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            }
        });

        const data = await res.json();

        if (!data.success) {
            showAlert(data.message || 'Erro ao aprovar.', 'error');
            return;
        }

        // Remove card da tela
        const card = document.getElementById(`appointment-card-${id}`);
        if (card) card.remove();

        showAlert(data.message || 'Agendamento aprovado!');

    } catch (e) {
        console.error(e);
        showAlert('Erro inesperado ao aprovar.', 'error');
    }
}

/* ------------ REJEITAR (MODAL + AJAX) ------------ */
function openRejectModal(id) {
    document.getElementById('reject_id').value = id;
    const modal = document.getElementById('rejectModal');
    modal.classList.remove('hidden');
    modal.classList.add('flex');
}

function closeRejectModal() {
    const modal = document.getElementById('rejectModal');
    modal.classList.remove('flex');
    modal.classList.add('hidden');
}

document.getElementById('rejectForm').addEventListener('submit', async function (e) {
    e.preventDefault();

    const id = document.getElementById('reject_id').value;
    const formData = new FormData(this);

    try {
        const res = await fetch(`/professional/appointments/${id}/reject`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            },
            body: formData
        });

        const data = await res.json();

        if (!data.success) {
            showAlert(data.message || 'Erro ao rejeitar.', 'error');
            return;
        }

        const card = document.getElementById(`appointment-card-${id}`);
        if (card) card.remove();

        closeRejectModal();
        showAlert(data.message || 'Agendamento rejeitado.');

    } catch (e) {
        console.error(e);
        showAlert('Erro inesperado ao rejeitar.', 'error');
    }
});

/* ------------ REAGENDAR (MODAL + AJAX) ------------ */
function openRescheduleModal(id, currentDate, currentTime) {
    document.getElementById('reschedule_id').value = id;
    document.getElementById('reschedule_date').value = currentDate;
    document.getElementById('reschedule_time').value = currentTime;

    const modal = document.getElementById('rescheduleModal');
    modal.classList.remove('hidden');
    modal.classList.add('flex');
}

function closeRescheduleModal() {
    const modal = document.getElementById('rescheduleModal');
    modal.classList.remove('flex');
    modal.classList.add('hidden');
}

document.getElementById('rescheduleForm').addEventListener('submit', async function (e) {
    e.preventDefault();

    const id = document.getElementById('reschedule_id').value;
    const formData = new FormData(this);

    try {
        const res = await fetch(`/professional/appointments/${id}/reschedule`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            },
            body: formData
        });

        const data = await res.json();

        if (!data.success) {
            showAlert(data.message || 'Erro ao reagendar.', 'error');
            return;
        }

        // apenas remove da lista de pendentes (profissional já tratou)
        const card = document.getElementById(`appointment-card-${id}`);
        if (card) card.remove();

        closeRescheduleModal();
        showAlert(data.message || 'Agendamento reagendado com sucesso!');

    } catch (e) {
        console.error(e);
        showAlert('Erro inesperado ao reagendar.', 'error');
    }
});
</script>
@endsection
