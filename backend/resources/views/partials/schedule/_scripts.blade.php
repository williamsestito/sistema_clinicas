<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
/* ============================================================================
   CONFIGURAÇÕES GLOBAIS
   ============================================================================ */

// Token CSRF global vindo do <meta> em app.blade.php
const CSRF = document.querySelector('meta[name="csrf-token"]').content;

// Função segura para formatar datas sem sofrer timezone
function formatDateBR(dateString) {
    return dateString.split("-").reverse().join("/");
}


/* ============================================================================
   INSERIR BLOQUEIO (AJAX)
   ============================================================================ */
document.getElementById('block-date-form')?.addEventListener('submit', async function (e) {
    e.preventDefault();

    const form = e.target;
    const btn  = document.getElementById('block-submit-btn');
    const formData = new FormData(form);

    if (btn) btn.disabled = true;

    try {
        const response = await fetch("{{ route('professional.schedule.blocked.store') }}", {
            method: "POST",
            headers: {
                "X-Requested-With": "XMLHttpRequest",
                "X-CSRF-TOKEN": CSRF
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
            if (btn) btn.disabled = false;
            return;
        }

        Swal.fire({
            icon: "success",
            title: "Sucesso!",
            text: json.message,
            timer: 1500,
            showConfirmButton: false
        });

        // Garante HTML da tabela se ela não existir
        let table = document.getElementById("blocked-table");

        if (!table) {
            document.getElementById("blocked-list-container").innerHTML = `
                <table id="blocked-table" class="min-w-full text-sm bg-white rounded shadow">
                    <thead>
                        <tr class="border-b text-gray-600 bg-gray-50">
                            <th class="py-2 px-2 text-left">Data</th>
                            <th class="py-2 px-2 text-left">Motivo</th>
                            <th class="py-2 px-2 text-right">Ações</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            `;
            table = document.getElementById("blocked-table");
        }

        const tbody = table.querySelector("tbody");

        // INSERIR nova linha (com data corretamente formatada)
        tbody.insertAdjacentHTML("afterbegin", `
            <tr id="block-row-${json.item.id}" class="border-b bg-green-50 hover:bg-gray-50 transition">
                <td class="py-2 px-2 font-medium">
                    ${formatDateBR(json.item.date)}
                </td>
                <td class="py-2 px-2">
                    ${json.item.reason ? json.item.reason : "-"}
                </td>
                <td class="py-2 px-2 text-right">
                    <button
                        class="delete-block text-red-600 hover:text-red-800 font-semibold"
                        data-id="${json.item.id}">
                        Excluir
                    </button>
                </td>
            </tr>
        `);

        form.reset();
        if (btn) btn.disabled = false;

    } catch (error) {
        console.error("Erro ao processar bloqueio:", error);

        Swal.fire({
            icon: "error",
            title: "Erro",
            text: "Falha ao comunicar com o servidor."
        });

        if (btn) btn.disabled = false;
    }
});


/* ============================================================================
   EXCLUIR BLOQUEIO — SWEETALERT + AJAX
   ============================================================================ */
document.addEventListener('click', async function (e) {
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

    try {
        const response = await fetch(`/professional/schedule/blocked/${id}`, {
            method: "DELETE",
            headers: {
                "X-Requested-With": "XMLHttpRequest",
                "X-CSRF-TOKEN": CSRF
            }
        });

        const json = await response.json();

        if (!json.success) return;

        const row = document.getElementById(`block-row-${id}`);

        if (row) {
            row.classList.add("bg-red-100");
            setTimeout(() => row.remove(), 250);
        }

        Swal.fire({
            icon: "success",
            title: "Removido!",
            text: json.message,
            timer: 1500,
            showConfirmButton: false
        });

        // Se tabela ficar vazia → exibir texto
        const table = document.getElementById("blocked-table");
        const tbody = table?.querySelector("tbody");

        if (tbody && tbody.children.length === 0) {
            document.getElementById("blocked-list-container").innerHTML = `
                <p id="blocked-empty" class="text-gray-500 text-sm py-2 text-center">
                    Nenhuma data bloqueada.
                </p>
            `;
        }

    } catch (error) {
        console.error("Erro ao excluir:", error);

        Swal.fire({
            icon: "error",
            title: "Erro",
            text: "Falha ao comunicar com o servidor."
        });
    }
});


/* ============================================================================
   EXCLUIR PERÍODO — SWEETALERT + AJAX
   ============================================================================ */
document.addEventListener('click', async function (e) {
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

    try {
        const response = await fetch(`/professional/schedule/period/${id}`, {
            method: "DELETE",
            headers: {
                "X-Requested-With": "XMLHttpRequest",
                "X-CSRF-TOKEN": CSRF
            }
        });

        const json = await response.json();

        if (!json.success) return;

        const row = document.getElementById(`period-row-${id}`);
        if (row) {
            row.classList.add("bg-red-100");
            setTimeout(() => row.remove(), 250);
        }

        Swal.fire({
            icon: "success",
            title: "Período removido",
            text: json.message,
            timer: 1500,
            showConfirmButton: false
        });

    } catch (error) {
        console.error("Erro ao excluir período:", error);

        Swal.fire({
            icon: "error",
            title: "Erro",
            text: "Falha ao comunicar com o servidor."
        });
    }
});
</script>
