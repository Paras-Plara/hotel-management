<?php

use App\Http\Controllers\BookingController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

// Route::get('/', function () {
//     return view('welcome');
// });

Route::middleware('auth')->group(function(){
    Route::get('/home', function () {
        $userRooms = auth()->user()->bookedRooms()->pluck('room_number');
        return view('index', compact('userRooms'));
    });
    Route::post('/book',[BookingController::class,'store']);
    Route::post('/reset', [BookingController::class,'reset']);
    Route::post('/reset-all', [BookingController::class,'reset_all']);
    Route::post('/generate-random', [BookingController::class,'generate_random']);
});

Auth::routes();
