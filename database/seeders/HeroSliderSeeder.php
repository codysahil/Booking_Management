<?php

namespace Database\Seeders;

use App\Models\HeroSlider;
use Illuminate\Database\Seeder;

class HeroSliderSeeder extends Seeder
{
    public function run(): void
    {
        // Only seed if no sliders exist
        if (HeroSlider::count() > 0) {
            return;
        }

        HeroSlider::create([
            'image_path' => 'sliders/default-1.jpg',
            'title' => 'Welcome to Honeybees Hostel',
            'description' => 'Your comfortable home away from home',
            'order' => 1,
            'is_active' => true,
        ]);

        HeroSlider::create([
            'image_path' => 'sliders/default-2.jpg',
            'title' => 'Safe & Secure Living',
            'description' => 'Experience comfort and safety in our modern facilities',
            'order' => 2,
            'is_active' => true,
        ]);

        HeroSlider::create([
            'image_path' => 'sliders/default-3.jpg',
            'title' => 'Book Your Room Today',
            'description' => 'Affordable rates with premium amenities',
            'order' => 3,
            'is_active' => true,
        ]);
    }
}
