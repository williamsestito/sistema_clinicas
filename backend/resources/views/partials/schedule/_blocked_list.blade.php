<div id="blocked-list-container" class="bg-white rounded shadow px-6 py-4">

    @if(count($blocked) > 0)

        <table id="blocked-table" class="min-w-full text-sm">
            <thead>
                <tr class="border-b text-gray-600">
                    <th class="py-2 text-left">Data</th>
                    <th class="py-2 text-left">Motivo</th>
                    <th class="py-2 text-right">Ações</th>
                </tr>
            </thead>

            <tbody>
                @foreach($blocked as $item)
                <tr id="block-row-{{ $item->id }}" class="border-b">
                    <td class="py-2">
                        {{ \Carbon\Carbon::parse($item->date)->format('d/m/Y') }}
                    </td>

                    <td class="py-2">
                        {{ $item->reason ?: '-' }}
                    </td>

                    <td class="py-2 text-right">
                        <button 
                            class="delete-block text-red-600 hover:text-red-800"
                            data-id="{{ $item->id }}">
                            Excluir
                        </button>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>

    @else
        <p id="blocked-empty" class="text-gray-500">Nenhuma data bloqueada.</p>
    @endif

</div>
