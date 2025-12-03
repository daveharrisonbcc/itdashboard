<div>
    <div class="mb-2 font-bold text-lg">Room Timetable Search</div>
    <input
        wire:model.live.debounce.500ms="roomSearch"
        type="text"
        class="border rounded p-2 mr-2"
        placeholder="Search for a room (e.g. DR-A103)"
    >
    <input
        wire:model.live.debounce.500ms="date"
        type="date"
        class="border rounded p-2"
    >

    <label class="inline-flex items-center ml-4">
        <input type="checkbox" wire:model.live="onlyFree" class="mr-2">
        <span>Only show freeslots</span>
    </label>
    <div class="mt-4 space-y-6">
        @if(!empty($displayItems))
            @foreach($displayItems as $group)
                <div class="p-3 border rounded bg-white">
                    <div class="flex items-center justify-between mb-2">
                        <div class="font-semibold">{{ $group['room'] }}</div>
                        <div class="text-sm text-gray-600">{{ $date ?? \Carbon\Carbon::today()->toDateString() }}</div>
                    </div>

                    <table class="table-auto w-full border">
                        <thead>
                            <tr class="bg-gray-100">
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
                                        <td class="px-2 py-1 border">{{ \Carbon\Carbon::parse($row['START_TIME'])->format('H:i') }}</td>
                                        <td class="px-2 py-1 border">{{ \Carbon\Carbon::parse($row['END_TIME'])->format('H:i') }}</td>
                                        <td class="border px-2 py-1">{{ $row['DESCRIPTION'] }}</td>
                                        <td class="border px-2 py-1">{{ $row['ROOMS'] }}</td>
                                    </tr>
                                @else
                                    <tr class="bg-green-50">
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