<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
/* ============================================================
   INSERIR BLOQUEIO (AJAX)
   ============================================================ */
document.getElementById('block-date-form')?.addEventListener('submit', async function(e) {
    e.preventDefault();

    const form     = e.target;
    const formData = new FormData(form);

    const response = await fetch("{{ route('professional.schedule.blocked.store') }}", {
        method: "POST",
        headers: {
            "X-Requested-With": "XMLHttpRequest",
            "X-CSRF-TOKEN": form.querySelector('input[name=_token]').value
        },
        body: formData
    });

    const json = await response.json();

    if (!json.success) {
        Swal.fire({
            icon: "warning",
            title: "Atenção",
            text: json.message ?? "Erro ao bloquear data."
        });
        return;
    }

    Swal.fire({
        icon: "success",
        title: "Sucesso!",
        text: json.message,
        timer: 1500,
        showConfirmButton: false
    });

    // ================================
    // GARANTIR TABELA E TBODY EXISTEM
    // ================================
    let table = document.getElementById("blocked-table");

    if (!table) {
        document.getElementById("blocked-list-container").innerHTML = `
            <table id="blocked-table" class="min-w-full text-sm bg-white rounded shadow">
                <thead>
                    <tr class="border-b text-gray-600">
                        <th class="py-2">Data</th>
                        <th class="py-2">Motivo</th>
                        <th class="py-2 text-right">Ações</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        `;
        table = document.getElementById("blocked-table");
    }

    const tbody = table.querySelector("tbody");

    // ================================
    // INSERIR NOVA LINHA
    // ================================
    const reason = json.item.reason ? json.item.reason : "-";

    tbody.insertAdjacentHTML("afterbegin", `
        <tr id="block-row-${json.item.id}" class="border-b bg-green-50 transition">
            <td class="py-2">${new Date(json.item.date).toLocaleDateString("pt-BR")}</td>
            <td class="py-2">${reason}</td>
            <td class="py-2 text-right">
                <button class="delete-block text-red-600 hover:text-red-800"
                        data-id="${json.item.id}">
                    Excluir
                </button>
            </td>
        </tr>
    `);

    form.reset();
});


/* ============================================================
   EXCLUIR BLOQUEIO — SWEETALERT + AJAX
   ============================================================ */
document.addEventListener('click', async function(e) {
    if (!e.target.classList.contains('delete-block')) return;

    e.preventDefault();

    const id = e.target.dataset.id;

    const confirm = await Swal.fire({
        title: "Excluir?",
        text: "Deseja remover este dia bloqueado?",
        icon: "warning",
        showCancelButton: true,
        confirmButtonText: "Sim, excluir",
        cancelButtonText: "Cancelar",
        confirmButtonColor: "#d33",
        cancelButtonColor: "#3085d6"
    });

    if (!confirm.isConfirmed) return;

    const response = await fetch(`/professional/schedule/blocked/${id}`, {
        method: "DELETE",
        headers: {
            "X-Requested-With": "XMLHttpRequest",
            "X-CSRF-TOKEN": document.querySelector('input[name=_token]').value
        }
    });

    const json = await response.json();

    if (!json.success) return;

    const row = document.getElementById(`block-row-${id}`);
    row.classList.add("bg-red-100");

    setTimeout(() => row.remove(), 250);

    Swal.fire({
        icon: "success",
        title: "Removido!",
        text: json.message,
        timer: 1500,
        showConfirmButton: false
    });
});


/* ============================================================
   EXCLUIR PERÍODO — SWEETALERT + AJAX
   (NOVO BLOCO — sem alterar nada do que já funcionava)
   ============================================================ */
document.addEventListener('click', async function(e) {
    if (!e.target.classList.contains('delete-period')) return;

    e.preventDefault();

    const id = e.target.dataset.id;

    const confirm = await Swal.fire({
        title: "Excluir Período?",
        text: "Deseja realmente remover este período?",
        icon: "warning",
        showCancelButton: true,
        confirmButtonText: "Sim, excluir",
        cancelButtonText: "Cancelar",
        confirmButtonColor: "#d33",
        cancelButtonColor: "#3085d6"
    });

    if (!confirm.isConfirmed) return;

    const response = await fetch(`/professional/schedule/period/${id}`, {
        method: "DELETE",
        headers: {
            "X-Requested-With": "XMLHttpRequest",
            "X-CSRF-TOKEN": document.querySelector('input[name=_token]').value
        }
    });

    const json = await response.json();

    if (!json.success) return;

    const row = document.getElementById(`period-row-${id}`);
    row.classList.add("bg-red-100");

    setTimeout(() => row.remove(), 250);

    Swal.fire({
        icon: "success",
        title: "Período removido",
        text: json.message,
        timer: 1500,
        showConfirmButton: false
    });

});
</script>
