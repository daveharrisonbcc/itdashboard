<?php

namespace App\Livewire;

use Carbon\Carbon;
use Livewire\Component;
use Livewire\WithPagination;
use App\Models\FloorTimetable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class TimetableList extends Component
{
    use WithPagination;

    protected $lastRefreshedAt = null;
    public $floorCode = '';
    public $perPage = 23;
    public $pageNumber = 1;

    // Store all timetable rows for the current period in memory for local paging
    public $allTimetables = [];

    protected $queryString = ['floorCode'];

    // On component mount or refresh, fetch all results
    public function mount()
    {
        $this->allTimetables = collect();
        $this->refreshData();
    }

    // Poll every 5 minutes to refresh all timetable data
    public function refreshData()
    {

        $now = Carbon::now();
        $today = $now->format('Y-m-d');
        $currentTime = $now->format('H:i:s');
        $fifteenMinutesAgo = $now->copy()->subMinutes(60)->format('H:i:s');
        $twoHoursLater = $now->copy()->addHours(2)->format('H:i:s');

        try {
            $now = Carbon::now();
            $today = $now->format('Y-m-d');
            $currentTime = $now->format('H:i:s');
            $fifteenMinutesAgo = $now->copy()->subMinutes(30)->format('H:i:s');
            $twoHoursLater = $now->copy()->addHours(2)->format('H:i:s');

            
    
            $this->allTimetables = cache()->remember("timetable_{$this->floorCode}_{$today}", 60, function() use ($today, $currentTime, $fifteenMinutesAgo, $twoHoursLater) {
                return FloorTimetable::selectRaw('
                    EVENT_ID,
                    MIN(STARTDATE) as STARTDATE,
                    MIN(START_TIME) as START_TIME,
                    MAX(END_TIME) as END_TIME,
                    MAX(ROOMS) as ROOMS,
                    MAX(DESCRIPTION) as DESCRIPTION
                ')
                ->where('ROOMS', 'like', $this->floorCode . '%')
                ->whereDate('STARTDATE', $today)
                ->where(function($query) use ($currentTime, $fifteenMinutesAgo, $twoHoursLater) {
                    $query->whereBetween('START_TIME', [$currentTime, $twoHoursLater])
                          ->orWhereBetween('START_TIME', [$fifteenMinutesAgo, $currentTime]);
                })
                ->groupBy('EVENT_ID')
                ->orderBy('START_TIME', 'asc')
                ->orderBy('DESCRIPTION', 'asc')
                ->orderBy('ROOMS', 'asc')
                ->get()
                ->toArray();
            });
        } catch (\Exception $e) {
            Log::error('Timetable DB query failed: ' . $e->getMessage());
            // Fallback to last good result, or empty array
            // $this->allTimetables = $this->allTimetables ?? [];
            // Optionally set a flag here to display an error in the view if critical
        }

        $this->lastRefreshedAt = now()->timestamp;
        if ($this->pageNumber > $this->lastPage) {
            $this->pageNumber = 1;
        }
    }

    // Local pagination: slice the data for the current page
    public function getPagedTimetablesProperty()
    {
        $offset = ($this->pageNumber - 1) * $this->perPage;
        return collect($this->allTimetables)->slice($offset, $this->perPage);
    }

    public function getLastPageProperty()
    {
        if (empty($this->allTimetables)) {
            return 1;
        }
        return ceil(count($this->allTimetables) / $this->perPage);
    }

    // Called by polling every 15s
    public function nextPage()
    {
        // Allow paging only if X seconds after last refresh
        if ($this->lastRefreshedAt && (now()->timestamp - $this->lastRefreshedAt) < 2) {
            // Too soon after refresh, skip paging
            return;
        }
        if ($this->pageNumber < $this->lastPage) {
            $this->pageNumber++;
        } else {
            $this->pageNumber = 1;
        }
    }

    public function render()
    {
        // Note: No DB hit here, just slicing memory!
        return view('livewire.timetable-list', [
            'timetables' => $this->pagedTimetables,
            'currentPage' => $this->pageNumber,
            'lastPage' => $this->lastPage,
        ]);
    }
}