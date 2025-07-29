<?php

namespace App\Services;
use App\Models\Room;
use App\Models\Booking;
use Illuminate\Support\Collection;

class BookingService {
    const MAX_PER_USER = 5;
    const HORIZ = 1; // minute per room
    const VERT  = 2; // minute per floor

    public function bookRooms(int $userId, int $n): Collection 
    {
        if (Booking::where('user_id', $userId)->count() + $n > self::MAX_PER_USER) {
            throw new \Exception("Booking exceeds max of " . self::MAX_PER_USER);
        }

        $available = Room::where('is_booked', false)->get();
        $userRooms = Booking::where('user_id', $userId)->with('room')->get()->pluck('room');

        $group = null;

        if ($userRooms->isNotEmpty()) {
            $group = $this->closestToExisting($available, $userRooms, $n);
            if ($group->count() < $n) {
                $group = $this->minTravelGroup($available, $n);
            }
        } else {
            $group = $this->findSameFloor($available, $n);
            if (!$group || $group->count() < $n) {
                $group = $this->minTravelGroup($available, $n);
            }
        }

        foreach ($group as $room) {
            $room->update(['is_booked' => true]);
            Booking::create(['user_id' => $userId, 'room_id' => $room->id]);
        }

        return $group;
    }


    protected function findSameFloor(Collection $rooms,int $n) {
        $group =  $rooms->groupBy('floor')->first(function($grp)use($n){
            return $grp->count() >= $n;
        });
        return $group ? $group->take($n) : null;
    }

    protected function minTravelGroup(Collection $rooms,int $n) 
    {
        $combos = $this->combinations($rooms->toArray(), $n);
        $best = null; $bestTime = INF;
        foreach ($combos as $arr) {
            $time = $this->travelTime($arr);
            if ($time < $bestTime) { $bestTime=$time; $best=$arr; }
        }

        
        $ids = array_map(fn($r) => $r['id'], $best);
        return Room::whereIn('id', $ids)->get();
    }


    protected function travelTime(array $rooms) {
        usort($rooms, fn($a,$b)=> $a['id'] <=> $b['id']);
        $first = array_shift($rooms); $last = array_pop($rooms) ?: $first;
        $hf = abs($first['floor'] - $last['floor']) * self::VERT;
        $h1 = $this->roomIndex($last['room_number']) - $this->roomIndex($first['room_number']);
        $h = abs($h1) * self::HORIZ;
        return $hf + $h;
    }

    protected function roomIndex(string $num) { return intval(substr($num, -2)); }

    protected function combinations(array $arr,int $k): array {
        if ($k === 0) return [[]];
        if (count($arr) < $k) return [];
        $result=[];
        for ($i=0; $i <= count($arr)-$k; $i++) {
            $head = $arr[$i];
            foreach ($this->combinations(array_slice($arr, $i+1), $k-1) as $comb) {
                $result[] = array_merge([$head], $comb);
            }
        }
        return $result;
    }

    protected function closestToExisting(Collection $available, Collection $userRooms, int $n)
    {
        $scored = $available->map(function ($room) use ($userRooms) {
            $totalDist = 0;
            foreach ($userRooms as $booked) {
                $floorDiff = abs($room->floor - $booked->floor) * self::VERT;
                $horizDiff = abs($this->roomIndex($room->room_number) - $this->roomIndex($booked->room_number)) * self::HORIZ;
                $totalDist += $floorDiff + $horizDiff;
            }
            return ['room' => $room, 'score' => $totalDist];
        });

        return collect($scored)
            ->sortBy('score')
            ->take($n)
            ->map(fn($r) => $r['room']);
    }

}