<?php

use App\Livewire\Counter;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\ConversationController;
use App\Http\Controllers\PageController;
use App\Services\EBSRestService;
use Livewire\Livewire;
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/
Route::get('/', [HomeController::class, 'index'])->name('home');

if (config('app.env') === 'production') {
    Livewire::setUpdateRoute(function ($handle) {
        return Route::post('/pub/digitalsignage/timetable-screen/public/livewire/update', $handle)->name('app.livewire.update');
    });

    Livewire::setScriptRoute(static function ($handle) {
        return Route::get('/pub/digitalsignage/timetable-screen/public/livewire/livewire.js', $handle)->name('get-route');
    });

}

Route::get('/test-user-details/{username}', function ($username) {
    $userService = app(\App\Services\UserService::class);
    $result = $userService->userDetails($username);
    
    return view('test-user-details', [
        'username' => $username,
        'details' => $result
    ]);
});