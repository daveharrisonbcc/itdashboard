<?php

namespace App\Models;

use App\Models\StudentGroup;
use Illuminate\Database\Eloquent\Model;

class EBSPeople extends Model
{
    protected $connection = 'ebslive';
    protected $table = 'dbo.PEOPLE';

    public function studentGroups()
    {
        return $this->hasMany(StudentGroup::class, 'Learner', 'PERSON_CODE');
    }        

    

}
