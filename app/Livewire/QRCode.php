<?php

namespace App\Livewire;

use Livewire\Component;
use SimpleSoftwareIO\QrCode\Facades\QrCode as QrCodeFacade;

class QRCode extends Component
{
    public function render()
    {

        $qrCode = QrCodeFacade::size(150)
        ->backgroundColor(255, 255, 255, 0)
        ->color(255,255,255)
        
        ->generate('https://applications.boltoncollege.ac.uk/?ReturnUrl=%2FPage%2FLearnerTimetable');
        return view('livewire.q-r-code', ['qrCode' => $qrCode]);
        
    }
}
