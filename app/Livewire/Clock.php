<?php

namespace App\Livewire;

use Livewire\Component;
use Carbon\Carbon;

class Clock extends Component
{
    
    public function render()
    {   
        $now = Carbon::now();
        $currentTime = $now->format('H:i');

        return view('livewire.clock', ['currentTime' => $currentTime ]);

    }
}
