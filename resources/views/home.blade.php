
@extends('layouts.app')

@section('content')

    
    <div class="w-full text-2xl font-roboto relative h-full">

        <div class="flex justify-between text-white text-4xl py-8">
            @livewire('floor-name')
            @livewire('clock')
        </div>

        <div class="w-full">
            @livewire('floor-map')
        </div>

        <div class="text-white py-4">
            @livewire('timetable-list')
        </div>
        
        <div class="text-white py-4 absolute bottom-0 left-0 w-full">
            @livewire('q-r-code')
        </div>
        
   
    </div>



@endsection
