<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProMonitorStudent extends Model
{
    protected $connection = 'promon';
    protected $table = 'Student';

    public function studentPhoto()
    {
        return $this->hasOne(ProMonitorStudentPhoto::class, 'StudentID', 'PMStudentID');
    }

    // Get the most recent student record
    public function scopeMostRecent($query)
    {
        return $query->orderByDesc('PMStudentID')->limit(1);  // Order by ID to get most recent
    }       
  
}
