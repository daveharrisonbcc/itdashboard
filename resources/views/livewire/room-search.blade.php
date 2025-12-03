<div>
    <div class="mb-4 font-bold text-lg">Search for Free Rooms</div>

    <div class="flex flex-wrap gap-2 items-center mb-4">

        <select id="site" name="site" wire:model.live="site">
            <option value="">All Sites</option>
            @foreach($sites as $site)
                <option value="{{ $site }}">{{ $site }}</option>
            @endforeach
        </select>

        <select id="building" name="building" wire:model.live="building" @if(trim($site ?? '') !== 'DR') disabled @endif>
            <option value="">All Buildings</option>
            @foreach($buildings as $building)
                <option value="{{ $building }}">{{ $building }}</option>
            @endforeach
        </select>

        <!-- Floors as checkboxes -->
        <div class="flex flex-col" @if($site !== 'DR') style="opacity: 0.5; pointer-events: none;" @endif>
            <div class="font-semibold text-sm">Floors</div>
            <div class="flex flex-wrap gap-2">
                @foreach($floors as $floor)
                    <label class="inline-flex items-center">
                        <input type="checkbox" name="floor[]" wire:model.live="selectedFloors" value="{{ $floor }}" />
                        <span class="ml-1">{{ $floor }}</span>
                    </label>
                @endforeach
            </div>
        </div>

        <!-- Rooms as checkboxes -->
        <div class="flex flex-col">
            <div class="font-semibold text-sm">Rooms</div>
            <div class="flex flex-wrap gap-2">
                @foreach($rooms as $db_code => $label)
                    <label class="inline-flex items-center">
                        <input type="checkbox" name="room[]" wire:model.live="selectedRooms" value="{{ $db_code }}" />
                        <span class="ml-1">{{ $label }}</span>
                    </label>
                @endforeach
            </div>
        </div>
        <input wire:model.live="date" type="date" class="p-2 border rounded w-40">
        <input wire:model.live="startTime" type="time" class="p-2 border rounded w-32">
        <input wire:model.live="endTime" type="time" class="p-2 border rounded w-32">
        <div>
            <button wire:click="searchFreeRooms" class="bg-blue-600 text-white rounded px-4 py-2">Find</button>
        </div>
    </div>

    {{-- @if($roomEvents && count($roomEvents) > 0 )
        <div class="mt-4">
            <b>Free slots:</b>
            <ul> --}}
            {{-- @foreach($roomEvents as $room => $slots)
                @dump($roomEvents) --}}
           
                {{-- <li>
                    <b>{{ $room }}</b>
                    <ul class="ml-4 text-sm">
                        @foreach($slots as $slot)
                            <li>{{ $slot[0] }} &ndash; {{ $slot[1] }}</li>
                        @endforeach
                    </ul>
                </li> --}}
            {{-- @endforeach --}}
            {{-- </ul>
        </div>
    @else
        <div class="mt-4 text-red-600 font-semibold">No free slots found for your criteria.</div>
    @endif --}}

    @if($roomEvents && count($roomEvents))
    <div class="mt-4 p-3 border rounded bg-gray-50 max-h-96 overflow-x-auto">
        <b>Room Timetables:</b>
        @foreach($roomEvents as $roomLabel => $events)
            <div class="mb-3">
                <h4 class="font-bold mb-1">{{ $roomLabel }}</h4>
                <table class="table-auto border text-xs w-full bg-white">
                    <thead>
                        <tr class="bg-gray-200">
                            <th class="px-2 py-1 border">Start</th>
                            <th class="px-2 py-1 border">End</th>   
                            <th class="px-2 py-1 border">Description</th>

                        </tr>
                    </thead>
                    <tbody>
                        @forelse($events as $event)
                        <tr>
                            <td class="px-2 py-1 border">{{ \Carbon\Carbon::parse($row['START_TIME'])->format('H:i') }}</td>
                            <td class="px-2 py-1 border">{{ \Carbon\Carbon::parse($row['END_TIME'])->format('H:i') }}</td>
                            <td class="px-2 py-1 border">{{ $event['DESCRIPTION'] }}</td>
       
                        </tr>
                        @empty
                        <tr><td colspan="5" class="text-gray-500 italic px-2 py-1 border">No lessons found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        @endforeach
    </div>
@else
    <div class="mt-4 text-red-600 font-semibold">No lessons found for your criteria.</div>
@endif
</div>