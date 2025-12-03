<div>
    <div class="mb-2 font-bold text-lg">Room Search</div>
    <div class="flex flex-wrap items-center gap-4">
       <div>
            <label for="roomSearch" class="block mb-1 font-medium">Search room:</label>
            <input
                wire:model.live.debounce.500ms="roomSearch"
                name="roomSearch"
                type="text"
                class="border rounded p-2 mr-2"
                placeholder="Search..."
            >
       </div>
       <div>
        <label for="startDate" class="block mb-1 font-medium">Date From:</label>
            <input
                wire:model.live="startDate"
                name="startDate"
                type="date"
                class="border rounded p-2"
                placeholder="From"
                value
            >
        </div>
        <div>
            <label for="endDate" class="block mb-1 font-medium">Date To:</label>
            <input
                wire:model.live="endDate"
                name="endDate"
                type="date"
                class="border rounded p-2"
                placeholder="To"
            >
        </div>
        <div>
            <label class="inline-flex items-center ml-4">
                <input type="checkbox" wire:model.live="onlyFree" class="mr-2">
                <span>Only show free slots</span>
            </label>
        </div>  
    </div>
    <div class="mt-4 space-y-6">

    {{-- Loading spinner --}}
    <div wire:loading.flex class="justify-center items-center h-24">
        <svg class="animate-spin h-8 w-8 text-gray-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
        </svg>
        <span class="ml-4 text-gray-600 font-medium">Loading...</span>
    </div>

    <div wire:loading.remove>
        @if(!empty($displayItems))
            @foreach($displayItems as $group)
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <div class="font-semibold">{{ $group['room'] }}</div>
                    </div>

                    <table class="table-auto w-full border bg-white">
                        <thead>
                            <tr class="bg-gray-100">
                                <th class="border px-2 py-1">Date</th>
                                <th class="border px-2 py-1">Start</th>
                                <th class="border px-2 py-1">End</th>
                                <th class="border px-2 py-1">Description</th>
                                <th class="border px-2 py-1">Room(s)</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($group['items'] as $item)
                                @if($item['type'] === 'event')
                                    @php $row = $item['event']; @endphp
                                    <tr>
                                        <td class="px-2 py-1 border">{{ \Carbon\Carbon::parse($row['STARTDATE'])->format('d/m/Y') }}</td>
                                        <td class="px-2 py-1 border">{{ \Carbon\Carbon::parse($row['START_TIME'])->format('H:i') }}</td>
                                        <td class="px-2 py-1 border">{{ \Carbon\Carbon::parse($row['END_TIME'])->format('H:i') }}</td>
                                        <td class="border px-2 py-1">{{ $row['DESCRIPTION'] }}</td>
                                        <td class="border px-2 py-1">{{ $row['ROOMS'] }}</td>
                                    </tr>
                                @else
                                    <tr class="bg-green-50">
                                        <td class="px-2 py-1 border text-green-800 font-medium">
                                            {{ \Carbon\Carbon::parse($item['date'])->format('d/m/Y') }}
                                        </td>
                                        <td class="px-2 py-1 border text-green-800 font-medium">{{ $item['start'] }}</td>
                                        <td class="px-2 py-1 border text-green-800 font-medium">{{ $item['end'] }}</td>
                                        <td class="border px-2 py-1 text-green-800">Free</td>
                                        <td class="border px-2 py-1 text-green-800">{{ $group['room'] }}</td>
                                    </tr>
                                @endif
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endforeach
        @elseif(strlen($roomSearch) > 1)
            <div class="text-red-600 font-semibold mt-2">No lessons found for that room and date.</div>
        @endif
    </div>

</div>