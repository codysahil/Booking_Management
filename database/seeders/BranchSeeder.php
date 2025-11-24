<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\Room;
use App\Models\Bed;
use Illuminate\Database\Seeder;

class BranchSeeder extends Seeder
{
    public function run(): void
    {
        // Branch 1 - Downtown Haven (15 rooms)
        $branch1 = Branch::create([
            'name' => 'Downtown Haven',
            'address' => '123 Main Street, Downtown, City 12345',
            'google_map_url' => 'https://maps.google.com',
        ]);

        // Create 15 rooms for Branch 1 with mix of 2/3/4 sharing
        $branch1Rooms = [
            // 2 Sharing Rooms (5 rooms)
            ['name' => 'Room 101', 'type' => 'AC', 'beds' => 2, 'price' => 1800],
            ['name' => 'Room 102', 'type' => 'AC', 'beds' => 2, 'price' => 1800],
            ['name' => 'Room 103', 'type' => 'AC', 'beds' => 2, 'price' => 2000],
            ['name' => 'Room 104', 'type' => 'AC', 'beds' => 2, 'price' => 1800],
            ['name' => 'Room 105', 'type' => 'AC', 'beds' => 2, 'price' => 2000],
            
            // 3 Sharing Rooms (5 rooms)
            ['name' => 'Room 106', 'type' => 'Non-AC', 'beds' => 3, 'price' => 1400],
            ['name' => 'Room 107', 'type' => 'Non-AC', 'beds' => 3, 'price' => 1400],
            ['name' => 'Room 108', 'type' => 'AC', 'beds' => 3, 'price' => 1600],
            ['name' => 'Room 109', 'type' => 'Non-AC', 'beds' => 3, 'price' => 1400],
            ['name' => 'Room 110', 'type' => 'AC', 'beds' => 3, 'price' => 1600],
            
            // 4 Sharing Rooms (5 rooms)
            ['name' => 'Room 111', 'type' => 'Non-AC', 'beds' => 4, 'price' => 1200],
            ['name' => 'Room 112', 'type' => 'Non-AC', 'beds' => 4, 'price' => 1200],
            ['name' => 'Room 113', 'type' => 'Non-AC', 'beds' => 4, 'price' => 1200],
            ['name' => 'Room 114', 'type' => 'AC', 'beds' => 4, 'price' => 1400],
            ['name' => 'Room 115', 'type' => 'Non-AC', 'beds' => 4, 'price' => 1200],
        ];

        foreach ($branch1Rooms as $roomData) {
            $room = Room::create([
                'branch_id' => $branch1->id,
                'room_number' => $roomData['name'],
                'type' => $roomData['type'],
                'capacity' => $roomData['beds'],
                'gender_allowed' => 'Female',
            ]);

            // Create beds for each room
            for ($i = 1; $i <= $roomData['beds']; $i++) {
                Bed::create([
                    'room_id' => $room->id,
                    'bed_number' => $i,
                    'monthly_rent' => $roomData['price'],
                    'status' => 'vacant',
                ]);
            }
        }

        // Branch 2 - Riverside Retreat (4 rooms)
        $branch2 = Branch::create([
            'name' => 'Riverside Retreat',
            'address' => '456 River Road, Riverside District, City 12346',
            'google_map_url' => 'https://maps.google.com',
        ]);

        // Create 4 rooms for Branch 2 with mix of 2/3/4 sharing
        $branch2Rooms = [
            ['name' => 'Room 201', 'type' => 'AC', 'beds' => 2, 'price' => 2100],
            ['name' => 'Room 202', 'type' => 'AC', 'beds' => 3, 'price' => 1700],
            ['name' => 'Room 203', 'type' => 'Non-AC', 'beds' => 4, 'price' => 1300],
            ['name' => 'Room 204', 'type' => 'AC', 'beds' => 2, 'price' => 1900],
        ];

        foreach ($branch2Rooms as $roomData) {
            $room = Room::create([
                'branch_id' => $branch2->id,
                'room_number' => $roomData['name'],
                'type' => $roomData['type'],
                'capacity' => $roomData['beds'],
                'gender_allowed' => 'Female',
            ]);

            // Create beds for each room
            for ($i = 1; $i <= $roomData['beds']; $i++) {
                Bed::create([
                    'room_id' => $room->id,
                    'bed_number' => $i,
                    'monthly_rent' => $roomData['price'],
                    'status' => 'vacant',
                ]);
            }
        }

        $this->command->info('✅ Branch 1: 15 rooms created (5x 2-sharing, 5x 3-sharing, 5x 4-sharing)');
        $this->command->info('✅ Branch 2: 4 rooms created (2x 2-sharing, 1x 3-sharing, 1x 4-sharing)');
        $this->command->info('✅ Total beds created: ' . Bed::count());
    }
}
