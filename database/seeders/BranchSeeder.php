<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\Room;
use App\Models\Bed;
use Illuminate\Database\Seeder;

/**
 * Demo branches for a Greater Noida PG business: one men's PG near Knowledge
 * Park II, one women's PG near Pari Chowk — the two real PG hubs in the area.
 */
class BranchSeeder extends Seeder
{
    public function run(): void
    {
        // Skip if branches already exist
        if (Branch::count() > 0) {
            $this->command->info('✅ Branches already exist, skipping seeder');
            return;
        }

        // Branch 1 - Knowledge Park II, Men's PG
        $branch1 = Branch::create([
            'name' => 'Nestay PG - Knowledge Park II (Men\'s PG)',
            'address' => 'Plot 14, Knowledge Park II, Greater Noida, Uttar Pradesh - 201310 (Near Gautam Buddha University)',
            'google_map_url' => 'https://maps.google.com/?q=Knowledge+Park+2+Greater+Noida',
        ]);

        $branch1Rooms = [
            ['name' => 'Room-1', 'floor' => 'G Floor', 'type' => 'AC', 'beds' => 3, 'price' => 8500],
            ['name' => 'Room-2', 'floor' => 'G Floor', 'type' => 'AC', 'beds' => 2, 'price' => 10500],
            ['name' => 'Room-3', 'floor' => '1st Floor', 'type' => 'AC', 'beds' => 3, 'price' => 8500],
            ['name' => 'Room-4', 'floor' => '1st Floor', 'type' => 'AC', 'beds' => 2, 'price' => 10500],
            ['name' => 'Room-5', 'floor' => '1st Floor', 'type' => 'Non-AC', 'beds' => 4, 'price' => 6500],
            ['name' => 'Room-6', 'floor' => '2nd Floor', 'type' => 'AC', 'beds' => 3, 'price' => 8500],
            ['name' => 'Room-7', 'floor' => '2nd Floor', 'type' => 'AC', 'beds' => 2, 'price' => 10500],
            ['name' => 'Room-8', 'floor' => '2nd Floor', 'type' => 'Non-AC', 'beds' => 4, 'price' => 6500],
        ];

        foreach ($branch1Rooms as $roomData) {
            $room = Room::create([
                'branch_id' => $branch1->id,
                'room_number' => $roomData['name'],
                'type' => $roomData['type'],
                'capacity' => $roomData['beds'],
                'gender_allowed' => 'Male',
            ]);

            for ($i = 1; $i <= $roomData['beds']; $i++) {
                Bed::create([
                    'room_id' => $room->id,
                    'bed_number' => $i,
                    'monthly_rent' => $roomData['price'],
                    'status' => 'vacant',
                ]);
            }
        }

        // Branch 2 - Pari Chowk, Women's PG
        $branch2 = Branch::create([
            'name' => 'Nestay PG - Pari Chowk (Women\'s PG)',
            'address' => 'House No. 22, Alpha 1 Commercial Belt, Pari Chowk, Greater Noida, Uttar Pradesh - 201308 (Near Ansal Plaza)',
            'google_map_url' => 'https://maps.google.com/?q=Pari+Chowk+Greater+Noida',
        ]);

        $branch2Rooms = [
            ['name' => 'Room-1', 'floor' => 'G Floor', 'type' => 'AC', 'beds' => 2, 'price' => 11000],
            ['name' => 'Room-2', 'floor' => 'G Floor', 'type' => 'AC', 'beds' => 3, 'price' => 9000],
            ['name' => 'Room-3', 'floor' => '1st Floor', 'type' => 'AC', 'beds' => 2, 'price' => 11000],
            ['name' => 'Room-4', 'floor' => '1st Floor', 'type' => 'AC', 'beds' => 3, 'price' => 9000],
            ['name' => 'Room-5', 'floor' => '2nd Floor', 'type' => 'Non-AC', 'beds' => 3, 'price' => 7000],
            ['name' => 'Room-6', 'floor' => '2nd Floor', 'type' => 'AC', 'beds' => 2, 'price' => 11000],
        ];

        foreach ($branch2Rooms as $roomData) {
            $room = Room::create([
                'branch_id' => $branch2->id,
                'room_number' => $roomData['name'],
                'type' => $roomData['type'],
                'capacity' => $roomData['beds'],
                'gender_allowed' => 'Female',
            ]);

            for ($i = 1; $i <= $roomData['beds']; $i++) {
                Bed::create([
                    'room_id' => $room->id,
                    'bed_number' => $i,
                    'monthly_rent' => $roomData['price'],
                    'status' => 'vacant',
                ]);
            }
        }

        $this->command->info('✅ Knowledge Park II (Men\'s PG): 8 rooms created - ' . $branch1->rooms()->withCount('beds')->get()->sum('beds_count') . ' beds total');
        $this->command->info('✅ Pari Chowk (Women\'s PG): 6 rooms created - ' . $branch2->rooms()->withCount('beds')->get()->sum('beds_count') . ' beds total');
        $this->command->info('✅ Total beds created: ' . Bed::count());
    }
}
