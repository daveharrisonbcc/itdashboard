<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SidTimetable extends Model
{
    protected $connection = 'mysql';
    protected $table = 'ext_a_learner_timetable';

    public function getStartTimeHmAttribute()
    {
        return \Carbon\Carbon::parse($this->START_TIME)->format('H:i');
    }
    
    public function getEndTimeHmAttribute()
    {
        return \Carbon\Carbon::parse($this->END_TIME)->format('H:i');
    }
}
