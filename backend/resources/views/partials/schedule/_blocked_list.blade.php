<div id="blocked-list-container" class="bg-white rounded shadow px-6 py-4">

    @if($blocked->count() > 0)

        <table id="blocked-table" class="min-w-full text-sm">
            <thead>
                <tr class="border-b text-gray-600 bg-gray-50">
                    <th class="py-2 px-2 text-left">Data</th>
                    <th class="py-2 px-2 text-left">Motivo</th>
                    <th class="py-2 px-2 text-right">Ações</th>
                </tr>
            </thead>

            <tbody>
                @foreach($blocked as $item)
                <tr id="block-row-{{ $item->id }}" class="border-b hover:bg-gray-50 transition">
                    <td class="py-2 px-2 font-medium text-gray-800">
                        {{ $item->date_formatted }}
                    </td>

                    <td class="py-2 px-2 text-gray-700">
                        {{ $item->reason_clean }}
                    </td>

                    <td class="py-2 px-2 text-right">
                        <button 
                            type="button"
                            class="delete-block text-red-600 hover:text-red-800 font-semibold transition"
                            data-id="{{ $item->id }}">
                            Excluir
                        </button>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>

    @else

        <p id="blocked-empty" class="text-gray-500 text-sm py-2 text-center">
            Nenhuma data bloqueada.
        </p>

    @endif

</div>
