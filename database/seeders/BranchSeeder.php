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
        // Skip if branches already exist
        if (Branch::count() > 0) {
            $this->command->info('✅ Branches already exist, skipping seeder');
            return;
        }

        // Branch 1 - Annai Indira Nagar (15 rooms)
        $branch1 = Branch::create([
            'name' => 'Honeybees Hostel - Annai Indira Nagar',
            'address' => '50, Annai Indira Nagar 1st Main Road, Thoraipakkam, Chennai - 600097 (Landmark: Tansq Jewellery)',
            'google_map_url' => 'https://maps.google.com',
        ]);

        // Create 15 rooms for Branch 1 (Hostel-1) - Total 41 beds
        // All rooms are AC except Room-15
        $branch1Rooms = [
            // Ground Floor
            ['name' => 'Room-1', 'floor' => 'G Floor', 'type' => 'AC', 'beds' => 3, 'price' => 1600],
            ['name' => 'Room-2', 'floor' => 'G Floor', 'type' => 'AC', 'beds' => 3, 'price' => 1600],
            ['name' => 'Room-3', 'floor' => 'G Floor', 'type' => 'AC', 'beds' => 2, 'price' => 2000],
            
            // 1st Floor Left Wing
            ['name' => 'Room-4', 'floor' => '1st Floor Left', 'type' => 'AC', 'beds' => 3, 'price' => 1600],
            ['name' => 'Room-5', 'floor' => '1st Floor Left', 'type' => 'AC', 'beds' => 2, 'price' => 2000],
            ['name' => 'Room-6', 'floor' => '1st Floor Left', 'type' => 'AC', 'beds' => 3, 'price' => 1600],
            
            // 1st Floor Right Wing
            ['name' => 'Room-7', 'floor' => '1st Floor Right', 'type' => 'AC', 'beds' => 3, 'price' => 1600],
            ['name' => 'Room-8', 'floor' => '1st Floor Right', 'type' => 'AC', 'beds' => 2, 'price' => 2000],
            ['name' => 'Room-9', 'floor' => '1st Floor Right', 'type' => 'AC', 'beds' => 3, 'price' => 1600],
            
            // 2nd Floor Left Wing
            ['name' => 'Room-10', 'floor' => '2nd Floor Left', 'type' => 'AC', 'beds' => 3, 'price' => 1600],
            ['name' => 'Room-11', 'floor' => '2nd Floor Left', 'type' => 'AC', 'beds' => 2, 'price' => 2000],
            ['name' => 'Room-12', 'floor' => '2nd Floor Left', 'type' => 'AC', 'beds' => 3, 'price' => 1600],
            
            // 2nd Floor Right Wing
            ['name' => 'Room-13', 'floor' => '2nd Floor Right', 'type' => 'AC', 'beds' => 3, 'price' => 1600],
            ['name' => 'Room-14', 'floor' => '2nd Floor Right', 'type' => 'AC', 'beds' => 2, 'price' => 2000],
            ['name' => 'Room-15', 'floor' => '2nd Floor Right', 'type' => 'Non-AC', 'beds' => 4, 'price' => 1200],
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

        // Branch 2 - Nethaji Cross Street (4 rooms)
        $branch2 = Branch::create([
            'name' => 'Honeybees Hostel - Nethaji Cross Street',
            'address' => 'Nethaji 1st Cross Street, Muttukkaranchavadi, Thoraipakkam, Chennai - 600097 (Landmark: Tansq Jewellery)',
            'google_map_url' => 'https://maps.google.com',
        ]);

        // Create 4 rooms for Branch 2 (Hostel-2) - Total 11 beds
        // All rooms are AC
        $branch2Rooms = [
            ['name' => 'Room-16', 'floor' => 'Full Building', 'type' => 'AC', 'beds' => 2, 'price' => 2000],
            ['name' => 'Room-17', 'floor' => 'Full Building', 'type' => 'AC', 'beds' => 3, 'price' => 1600],
            ['name' => 'Room-18', 'floor' => 'Full Building', 'type' => 'AC', 'beds' => 3, 'price' => 1600],
            ['name' => 'Room-19', 'floor' => 'Full Building', 'type' => 'AC', 'beds' => 3, 'price' => 1600],
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

        $this->command->info('✅ Hostel-1 (Annai Indira Nagar): 15 rooms created - 41 beds total');
        $this->command->info('✅ Hostel-2 (Nethaji Cross Street): 4 rooms created - 11 beds total');
        $this->command->info('✅ Total beds created: ' . Bed::count() . ' (52 beds)');
    }
}
