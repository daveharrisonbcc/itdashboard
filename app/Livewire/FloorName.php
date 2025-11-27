<?php

namespace App\Livewire;

use Livewire\Component;

class FloorName extends Component
{

    public $floorCode = '';

    protected $queryString = ['floorCode'];

    public function render()
    {
        $floorName = match ($this->floorCode) {
            'DR-AG' => 'Main Building Ground Floor (A Block)',
            'DR-A1' => 'Main Building First Floor (A Block)',
            'DR-A2' => 'Main Building Second Floor (A Block)',
            'DR-A3' => 'Main Building Third Floor (A Block)',
            'DR-BG' => 'Construction & Art Ground Floor (B Block)',
            'DR-B1' => 'Construction & Art First Floor (B Block)',
            'DR-CG' => 'STEM Centre Ground Floor (C Block)',
            'DR-C1' => 'STEM Centre First Floor (C Block)',
            'DR-DG' => 'Digital Ground Floor (D Block)',
            'DR-D1' => 'Digital First Floor (D Block)',
            'DR-D2' => 'Digital Second Floor (D Block)',
            default => 'Unknown Floor',
        };

        return view('livewire.floor-name', ['floorName' => $floorName]);
    }
}
