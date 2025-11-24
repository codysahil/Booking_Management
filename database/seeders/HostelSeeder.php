<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Branch;
use App\Models\Room;
use App\Models\Bed;

class HostelSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create Branch 1
        $branch1 = Branch::create([
            'name' => 'Branch 1',
            'address' => '123 Main St, City Center',
            'google_map_url' => 'https://maps.google.com/?q=branch1',
        ]);

        // Create 15 rooms for Branch 1
        $this->createRooms($branch1, 15);

        // Create Branch 2
        $branch2 = Branch::create([
            'name' => 'Branch 2',
            'address' => '456 Side St, Suburb',
            'google_map_url' => 'https://maps.google.com/?q=branch2',
        ]);

        // Create 4 rooms for Branch 2
        $this->createRooms($branch2, 4);
    }

    private function createRooms($branch, $count)
    {
        $types = ['AC', 'Non-AC'];
        $capacities = [2, 3, 4]; // 2-sharing, 3-sharing, 4-sharing

        for ($i = 1; $i <= $count; $i++) {
            $capacity = $capacities[array_rand($capacities)];

            $room = $branch->rooms()->create([
                'room_number' => $branch->id . '-' . str_pad($i, 3, '0', STR_PAD_LEFT),
                'capacity' => $capacity,
                'type' => $types[array_rand($types)],
                'gender_allowed' => 'Female', // Enforce Female for Women's Hostel
            ]);

            // Create beds for the room
            for ($j = 1; $j <= $capacity; $j++) {
                $room->beds()->create([
                    'bed_number' => $room->room_number . '-' . chr(64 + $j), // e.g., 1-001-A
                    'monthly_rent' => 3000 + ($capacity * 500), // Dummy rent logic
                    'status' => 'vacant',
                ]);
            }
        }
    }
}
