<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Room;
use App\Services\BookingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BookingController extends Controller {


    public function store(Request $req, BookingService $svc) {
        $req->validate(['rooms'=>'required|integer|min:1|max:5']);
        try {
            $group = $svc->bookRooms(auth()->id(), $req->rooms);
            return back()->with('success', 'Booked: '.\implode(',', $group->pluck('room_number')->toArray()));
        } catch (\Exception $e) {
            return back()->withErrors(['rooms' => $e->getMessage()]);
        }
    }

    public function reset(Request $req) {
        $user = Auth::user();
        $roomIds = Booking::where('user_id', $user->id)->pluck('room_id');
        Room::whereIn('id', $roomIds)->update(['is_booked' => false]);
        Booking::where('user_id', $user->id)->delete();

        return back()->with('info', 'Your bookings have been reset.');
    }

    public function reset_all(Request $req) {
        Booking::truncate();
        Room::query()->update(['is_booked' => false]);
        return back()->with('info', 'All bookings have been reset.');
    }

    public function generate_random(Request $req) 
    {
        $totalToBook = (int) 10; // default 10
        $available = \App\Models\Room::where('is_booked', false)->inRandomOrder()->limit($totalToBook)->get();

        foreach ($available as $room) {
            $room->update(['is_booked' => true]);
        }

        return back()->with('info', "{$available->count()} rooms randomly marked as booked.");

    }
    
}
