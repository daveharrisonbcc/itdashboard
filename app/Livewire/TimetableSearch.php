<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\FloorTimetable;
use Carbon\Carbon;

class TimetableSearch extends Component
{
    public $roomSearch = '';
    public $date = '';
    public $onlyFree = false;

    public $results = [];
    // groups: [ ['room' => 'DR-A101', 'items' => [ {type:event,event:{...}} | {type:free,start,end} ] ], ... ]
    public $displayItems = [];

    public function updatedRoomSearch()
    {
        $this->search();
    }

    public function updatedDate()
    {
        $this->search();
    }

    public function updatedOnlyFree()
    {
        $this->search();
    }

    public function search()
    {
        $room = trim($this->roomSearch);
        $date = $this->date ?: Carbon::today()->toDateString();

        if (strlen($room) < 2) {
            $this->results = [];
            $this->displayItems = [];
            return;
        }

        $this->results = FloorTimetable::selectRaw('
            EVENT_ID,
            MIN(STARTDATE) as STARTDATE,
            MIN(START_TIME) as START_TIME,
            MAX(END_TIME) as END_TIME,
            MAX(ROOMS) as ROOMS,
            MAX(DESCRIPTION) as DESCRIPTION
        ')
        ->whereDate('STARTDATE', $date)
        ->where(function($query) use ($room) {
            $query->where('DESCRIPTION', 'LIKE', '%' . $room . '%')
                ->orWhere('ROOMS', 'LIKE', '%' . $room . '%');
        })
        ->groupBy('EVENT_ID')
        ->orderBy('STARTDATE', 'asc')
        ->orderBy('START_TIME', 'asc')
        ->get()
        ->toArray();

        // Find unique ROOMS labels that include the search term
        $matchedRows = array_values(array_filter($this->results, function($r) use ($room) {
            return !empty($r['ROOMS']) && stripos($r['ROOMS'], $room) !== false;
        }));

        // fallback: if nothing matches exactly, show a single "All results" group
        if (empty($matchedRows)) {
            $fallbackItems = array_map(function($r){
                return ['type' => 'event', 'event' => $r];
            }, $this->results);

            $this->displayItems = [
                ['room' => 'Results', 'items' => $fallbackItems]
            ];
            return;
        }

        $roomLabels = array_values(array_unique(array_map(function($r){ return $r['ROOMS']; }, $matchedRows)));

        $dayStart = '08:00:00';
        $dayEnd   = '18:00:00';
        $groups = [];

        foreach ($roomLabels as $roomLabel) {
            // Collect events that include this room label
            $eventsForRoom = array_values(array_filter($this->results, function($r) use ($roomLabel) {
                return !empty($r['ROOMS']) && stripos($r['ROOMS'], $roomLabel) !== false;
            }));

            // Sort events by start time
            usort($eventsForRoom, function($a, $b){
                return strcmp($a['START_TIME'] ?? '', $b['START_TIME'] ?? '');
            });

            $pointer = Carbon::parse("{$date} {$dayStart}");
            $endOfDay = Carbon::parse("{$date} {$dayEnd}");
            $items = [];

            foreach ($eventsForRoom as $ev) {
                if (empty($ev['START_TIME']) || empty($ev['END_TIME'])) {
                    $items[] = ['type' => 'event', 'event' => $ev];
                    continue;
                }

                $s = Carbon::parse("{$date} {$ev['START_TIME']}");
                $e = Carbon::parse("{$date} {$ev['END_TIME']}");

                if ($e <= $s) {
                    $items[] = ['type' => 'event', 'event' => $ev];
                    continue;
                }

                if ($s->gt($pointer) && $pointer->lt($endOfDay)) {
                    $freeEnd = $s->lt($endOfDay) ? $s : $endOfDay;
                    $items[] = [
                        'type' => 'free',
                        'start' => $pointer->format('H:i'),
                        'end'   => $freeEnd->format('H:i'),
                    ];
                }

                $items[] = ['type' => 'event', 'event' => $ev];
                $pointer = $e->gt($pointer) ? $e : $pointer;

                if ($pointer->gte($endOfDay)) {
                    break;
                }
            }

            if ($pointer->lt($endOfDay)) {
                $items[] = [
                    'type' => 'free',
                    'start' => $pointer->format('H:i'),
                    'end'   => $endOfDay->format('H:i'),
                ];
            }

            // Only show free slots if checkbox is enabled
            if ($this->onlyFree) {
                $freeOnly = array_values(array_filter($items, function($it){ return ($it['type'] ?? '') === 'free'; }));
                if (!empty($freeOnly)) {
                    $groups[] = ['room' => $roomLabel, 'items' => $freeOnly];
                }
            } else {
                $groups[] = ['room' => $roomLabel, 'items' => $items];
            }
        }

        $this->displayItems = $groups;
    }

    public function render()
    {
        return view('livewire.timetable-search');
    }
}