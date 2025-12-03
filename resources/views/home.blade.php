
@extends('layouts.app')

@section('content')

    
    <div class="w-full text-2xl font-roboto relative h-full">

        <div class="flex justify-between py-8">
           
            {{-- @livewire('room-search') --}}

            <livewire:timetable-search />

        </div>
        
   
    </div>



@endsection
