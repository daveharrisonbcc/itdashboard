<?php

namespace App\Livewire;

use App\Models\FloorTimetable;
use App\Models\SidTimetable;
use Carbon\Carbon;
use Livewire\Component;

class RoomSearch extends Component
{
    public $date = '';
    public $startTime = '';
    public $endTime = '';
    public $freeRooms = [];

    public $sites = [];
    public $buildings = [];
    public $floors = [];
    public $rooms = []; // Now associative: [ db_code => label ]

    public $site = '';
    public $building = '';
    public $selectedFloors = [];
    public $selectedRooms = [];

    public function mount()
    {
        $this->updateDropdowns();
    }

    public function updatedSite()
    {
        $this->building = '';
        $this->floor = '';
        $this->room = '';
        $this->updateDropdowns();
    }

    public function updatedBuilding()
    {
        $this->floor = $this->room = '';
        $this->updateDropdowns();
    }

    public function updatedFloor()
    {
        $this->room = '';
        $this->updateDropdowns();
    }

    public function updatedRoom()
    {
        // No-op (last select box)
    }

    public function updateDropdowns()
    {
        $allRooms = SidTimetable::query()
            ->distinct()
            ->whereNotNull('Room')
            ->where('Room', 'NOT LIKE', '%OFFSITE%')
            ->where('Room', 'NOT LIKE', 'DRPS-%')
            ->where('Room', 'NOT LIKE', '%NRR%')
            ->where('Room', 'NOT LIKE', 'SP-%')
            ->where('Room', 'NOT LIKE', 'UNI-%')
            ->where('Room', 'NOT LIKE', '%ONLINE%')
            ->pluck('Room')
            ->filter()
            ->unique();

        $allSites = [];
        $allBuildings = [];
        $allFloors = [];
        $rooms = [];

        foreach ($allRooms as $roomCode) {
            $parts = explode('-', $roomCode);

            if (count($parts) === 3) {
                $site = $parts[0];
                $buildingVal = substr($parts[1], 0, 1);
                $floorVal = substr($parts[1], 1);
                $roomVal = $parts[2];
                $allSites[] = $site;

                if (!$this->site || strtoupper($this->site) === strtoupper($site)) {
                    $allBuildings[] = $buildingVal;
                }
                if ((!$this->site || strtoupper($this->site) === strtoupper($site)) &&
                    (!$this->building || strtoupper($this->building) === strtoupper($buildingVal))) {
                    $allFloors[] = $floorVal;
                }
                if (
                    (!$this->site || strtoupper($this->site) === strtoupper($site)) &&
                    (!$this->building || strtoupper($this->building) === strtoupper($buildingVal)) &&
                    (!$this->floor || $this->floor === $floorVal)
                ) {
                    // Key = DB code, Value = DR: A1.03
                    $rooms[$roomCode] = $parts[1] . '.' . $roomVal;
                }
            } elseif (count($parts) === 2) {
                $site = $parts[0];
                $roomVal = $parts[1];
                $allSites[] = $site;

                if (!$this->site || strtoupper($this->site) === strtoupper($site)) {
                    // Key = DB code, Value = SITE.03
                    $rooms[$roomCode] = $site . '.' . $roomVal;
                }
            }
        }

        $this->sites     = collect($allSites)->filter()->unique()->sort()->values()->toArray();
        $this->buildings = collect($allBuildings)->filter()->unique()->sort()->values()->toArray();
        $this->floors    = collect($allFloors)->filter()->unique()->sort()->values()->toArray();
        $this->rooms     = $rooms; // Associative array: [db_code => label]
    }

    public $roomEvents = []; // Change from freeRooms

    public function searchFreeRooms()
    {
        $date = $this->date ?: Carbon::today()->toDateString();
        $start = $this->startTime ?: '08:00:00';
        $end = $this->endTime ?: '18:00:00';

        // Prepare the room code to label map (same pattern as before)
        $possibleRooms = SidTimetable::query()
            ->distinct()
            ->whereNotNull('Room')
            ->where('Room', 'NOT LIKE', '%OFFSITE%')
            ->where('Room', 'NOT LIKE', 'DRPS-%')
            ->where('Room', 'NOT LIKE', '%NRR%')
            ->where('Room', 'NOT LIKE', 'SP-%')
            ->where('Room', 'NOT LIKE', 'UNI-%')
            ->where('Room', 'NOT LIKE', '%ONLINE%')
            ->pluck('Room')
            ->filter()
            ->unique();

        $roomMap = [];
        foreach ($possibleRooms as $roomCode) {
            $parts = explode('-', $roomCode);
            if (count($parts) === 3 && strtoupper($parts[0]) === 'DR') {
                $site = $parts[0];
                $building = substr($parts[1], 0, 1);
                $floor = substr($parts[1], 1);
                $number = $parts[2];
                if ($this->site && strtoupper($this->site) !== strtoupper($site)) continue;
                if ($this->building && strtoupper($this->building) !== strtoupper($building)) continue;
                if ($this->floor && $this->floor !== $floor) continue;
                if ($this->room && $roomCode !== $this->room) continue;
                $roomMap[$roomCode] = "{$parts[1]}.{$number}";
            } elseif (count($parts) === 2) {
                $site = $parts[0];
                $number = $parts[1];
                if ($this->site && strtoupper($this->site) !== strtoupper($site)) continue;
                if ($this->room && $roomCode !== $this->room) continue;
                $roomMap[$roomCode] = "{$site}.{$number}";
            }
        }

         // Get all bookings for those rooms on this date
        $bookings = FloorTimetable::selectRaw('
                    MIN(STARTDATE) as STARTDATE,
                    MIN(START_TIME) as START_TIME,
                    MAX(END_TIME) as END_TIME,
                    MAX(ROOMS) as ROOMS,
                    MAX(DESCRIPTION) as DESCRIPTION
                ')
            ->whereDate('STARTDATE', $date)
            ->where(function($q) use ($roomMap) {
                foreach (array_keys($roomMap) as $roomCode) {
                    $q->orWhere('ROOMS', 'LIKE', $roomCode . '%');
                }
            })
            ->where(function ($q) use ($start, $end) {
                $q->whereBetween('START_TIME', [$start, $end])
                    ->orWhereBetween('END_TIME', [$start, $end])
                    ->orWhere(function ($q2) use ($start, $end) {
                        $q2->where('START_TIME', '<=', $start)
                            ->where('END_TIME', '>=', $end);
                    });
            })
                ->groupBy('ROOMS')
                ->orderBy('START_TIME', 'asc')
                ->orderBy('DESCRIPTION', 'asc')
                ->orderBy('ROOMS', 'asc');
            

                dd($bookings->toSql(), $bookings->getBindings());

                $bookings = $bookings->get();

        

        $bookingsByRoom = $bookings->groupBy(function($item){
            return preg_replace('/\s*\(.*$/', '', $item->ROOMS);
        });

        

        $result = [];
        foreach ($roomMap as $dbRoom => $label) {
            $result[$label] = $bookingsByRoom->has($dbRoom)
                ? $bookingsByRoom->get($dbRoom)
                : collect();
        }

        $this->roomEvents = $result;
    }
    public function render()
    {
        return view('livewire.room-search');
    }
}