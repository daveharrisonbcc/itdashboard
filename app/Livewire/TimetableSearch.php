<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\FloorTimetable;
use Carbon\Carbon;

class TimetableSearch extends Component
{
    public $roomSearch = '';
    public $date = '';
    public $startDate = '';
    public $endDate = '';
    public $onlyFree = false;

    public $results = [];
    // groups: [ ['room' => 'DR-A101', 'items' => [ {type:event,event:{...}} | {type:free,start,end} ] ], ... ]
    public $displayItems = [];

    public function updatedRoomSearch()  { $this->search(); }
    public function updatedDate()        { $this->search(); }

    public function updatedStartDate()
    {
        // If the new start date is after the current end date, set end date to start date
        if ($this->startDate && $this->endDate && $this->startDate > $this->endDate) {
            $this->endDate = $this->startDate;
        }

        if($this->roomSearch != '') {
            $this->search();
        }
       
    }

    public function updatedEndDate()
    {
        // If the new end date is before the current start date, set start date to end date
        if ($this->endDate && $this->startDate && $this->endDate < $this->startDate) {
            $this->startDate = $this->endDate;
        }
       if($this->roomSearch != '') {
            $this->search();
        }
    }
    public function updatedOnlyFree()    { $this->search(); }

    public function mount()
    {
        $today = Carbon::today()->toDateString();
        $this->startDate = $today;
        $this->endDate = $today;
    }

    public function search()
    {
        $room = trim($this->roomSearch);

        $from = $this->startDate ?: $this->date ?: Carbon::today()->toDateString();
        $to   = $this->endDate ?: $this->startDate ?: $this->date ?: Carbon::today()->toDateString();

        if (empty($from)) $from = Carbon::today()->toDateString();
        if (empty($to))   $to   = $from;

        if (strlen($room) < 2) {
            $this->results = [];
            $this->displayItems = [];
            return;
        }

        $rawResults = FloorTimetable::selectRaw('
            EVENT_ID,
            MIN(STARTDATE) as STARTDATE,
            MIN(START_TIME) as START_TIME,
            MAX(END_TIME) as END_TIME,
            MAX(ROOMS) as ROOMS,
            MAX(TUTORS) as TUTORS,
            MAX(DESCRIPTION) as DESCRIPTION
        ')
        ->whereBetween('STARTDATE', [$from, $to])
        ->where(function($query) use ($room) {
            $query->where('DESCRIPTION', 'LIKE', '%' . $room . '%')
                ->orWhere('ROOMS', 'LIKE', '%' . $room . '%');
        })
        ->groupBy('EVENT_ID')
        ->orderBy('ROOMS', 'asc')
        ->orderBy('STARTDATE', 'asc')
        ->orderBy('START_TIME', 'asc')
        ->get()
        ->toArray();

        // NEW: Build a map of roomCode => event rows, splitting ROOMS by comma
        $roomMap = []; // 'DR-A2-60 (Capacity 48)' => [eventInfo1, eventInfo2, ...]
        foreach ($rawResults as $row) {
            // Split on comma, clean spaces, ignore empty
            $roomTokens = array_filter(array_map('trim', explode(',', $row['ROOMS'] ?? '')));

            foreach ($roomTokens as $token) {
                if (stripos($token, $room) !== false) {
                    $roomMap[$token][] = $row;
                }
            }
        }

        // fallback: if no individual room matches search term, show all as single group
        if (empty($roomMap)) {
            $fallbackItems = array_map(function($r){
                return ['type' => 'event', 'event' => $r];
            }, $rawResults);

            $this->displayItems = [
                ['room' => 'Results', 'items' => $fallbackItems]
            ];
            return;
        }

        $groups = [];

        foreach ($roomMap as $roomLabel => $eventsForRoom) {
            // Sort by date then time
            usort($eventsForRoom, function($a, $b){
                $dateCompare = strcmp($a['STARTDATE'] ?? '', $b['STARTDATE'] ?? '');
                if ($dateCompare !== 0) return $dateCompare;
                return strcmp($a['START_TIME'] ?? '', $b['START_TIME'] ?? '');
            });

            $items = [];

            $groupedByDay = collect($eventsForRoom)->groupBy('STARTDATE');
            foreach ($groupedByDay as $day => $events) {
                $dayStart = '08:00:00';
                $dayEnd   = '18:00:00';
                $pointer = Carbon::parse("{$day} {$dayStart}");
                $endOfDay = Carbon::parse("{$day} {$dayEnd}");

                $dayEvents = $events->all();

                // Sort by start time in the day
                usort($dayEvents, function($a, $b){
                    return strcmp($a['START_TIME'] ?? '', $b['START_TIME'] ?? '');
                });

                foreach ($dayEvents as $ev) {
                    if (empty($ev['START_TIME']) || empty($ev['END_TIME'])) {
                        $ev['GROUP_DATE'] = $day;
                        $items[] = ['type' => 'event', 'event' => $ev];
                        continue;
                    }

                    $s = Carbon::parse("{$day} {$ev['START_TIME']}");
                    $e = Carbon::parse("{$day} {$ev['END_TIME']}");

                    if ($e <= $s) {
                        $ev['GROUP_DATE'] = $day;
                        $items[] = ['type' => 'event', 'event' => $ev];
                        continue;
                    }

                    if ($s->gt($pointer) && $pointer->lt($endOfDay)) {
                        $freeEnd = $s->lt($endOfDay) ? $s : $endOfDay;
                        $items[] = [
                            'type' => 'free',
                            'start' => $pointer->format('H:i'),
                            'end'   => $freeEnd->format('H:i'),
                            'date'  => $day,
                        ];
                    }

                    $ev['GROUP_DATE'] = $day;
                    $items[] = ['type' => 'event', 'event' => $ev];
                    $pointer = $e->gt($pointer) ? $e : $pointer;

                    if ($pointer->gte($endOfDay)) {
                        break;
                    }
                }

                // Slot after last event of day?
                if ($pointer->lt($endOfDay)) {
                    $items[] = [
                        'type'  => 'free',
                        'start' => $pointer->format('H:i'),
                        'end'   => $endOfDay->format('H:i'),
                        'date'  => $day,
                    ];
                }
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