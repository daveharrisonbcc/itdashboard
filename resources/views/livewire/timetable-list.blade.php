<div 
    wire:poll.keep-alive.20s="nextPage"
    wire:poll.5m="refreshData"
>

    <div>
        @if($timetables && count($timetables) > 0)
        <table class="w-full text-sm text-left rtl:text-right text-white">
            <thead class="text-lg text-white uppercase">
                <tr>
                    <th scope="col" class="px-6 py-3 w-18">Room</th>
                    <th scope="col" class="px-6 py-3 w-24">Time</th>
                    <th scope="col" class="px-6 py-3">Course</th>
                </tr>
            </thead>
            <tbody class="text-lg">
                @foreach($timetables as $row)
                @php
                $rooms = collect(explode(',', $row['ROOMS']))
                    ->map(function($room) {
                        $room = trim($room);
                        $room = preg_replace('/\s*\(.*\)$/', '', $room);
                        $room = preg_replace('/^[^-]*-/', '', $room);
                        $room = preg_replace('/-/', '.', $room, 1);
                        return $room;
                    })
                    ->implode(' / ');
                @endphp
                <tr class="border-b border-white">
                    <td scope="row" class="px-6 py-2 font-medium whitespace-nowrap text-white">{{ $rooms }}</td>
                    <td class="whitespace-nowrap px-6 py-2">
                        {{ \Carbon\Carbon::parse($row['START_TIME'])->format('H:i') }} -
                        {{ \Carbon\Carbon::parse($row['END_TIME'])->format('H:i') }}
                    </td>
                    <td class="px-6 py-2">{{ $row['DESCRIPTION'] }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @elseif($lastPage == 1 && $currentPage == 1)
        <div class="text-3xl text-white py-2">No lessons currently taking place</div>
        @endif
        @if ($lastPage > 1)
        <div class="text-center text-xl text-white py-2">
            Page {{ $currentPage }} of {{ $lastPage }}
        </div>
        @endif
    </div>
</div>
