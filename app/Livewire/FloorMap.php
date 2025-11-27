<?php

namespace App\Livewire;

use Livewire\Component;

class FloorMap extends Component
{
    public $floorCode = '';
    protected $queryString = ['floorCode'];
    public function render()
    {
        $this->floorCode = strtolower($this->floorCode);

        return view('livewire.floor-map',['floorCode' => $this->floorCode]);
    }
}
