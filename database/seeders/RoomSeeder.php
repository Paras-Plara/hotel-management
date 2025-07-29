<?php

namespace Database\Seeders;

use App\Models\Room;
use Illuminate\Database\Seeder;

class RoomSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
   public function run() {
        // Floors 1-9: 10 rooms
        for ($f = 1; $f <= 9; $f++) {
            for ($i = 1; $i <= 10; $i++) {
                Room::create([
                    'room_number' => $f . str_pad($i, 2, '0', STR_PAD_LEFT),
                    'floor'       => $f,
                ]);
            }
        }
        // Floor 10: 7 rooms
        for ($i = 1; $i <= 7; $i++) {
            Room::create([
                'room_number' => '10' . str_pad($i, 2, '0', STR_PAD_LEFT),
                'floor'       => 10,
            ]);
        }
    }

}
