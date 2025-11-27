<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FloorTimetable extends Model
{
    protected $connection = 'ebslive';
    protected $table = 'bcc_isu.BCC_LEARNER_EVENTS_V';

    public function getStartTimeHmAttribute()
    {
        return \Carbon\Carbon::parse($this->START_TIME)->format('H:i');
    }
    
    public function getEndTimeHmAttribute()
    {
        return \Carbon\Carbon::parse($this->END_TIME)->format('H:i');
    }

}
