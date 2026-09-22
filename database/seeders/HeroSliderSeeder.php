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
            'image_path' => 'https://images.unsplash.com/photo-1522708323590-d24dbb6b0267?w=1920&q=80',
            'title' => 'Welcome to Nestay PG',
            'description' => 'Comfortable, fully-furnished PG stays near Knowledge Park & Pari Chowk, Greater Noida',
            'order' => 1,
            'is_active' => true,
        ]);

        HeroSlider::create([
            'image_path' => 'https://images.unsplash.com/photo-1560448204-e02f11c3d0e2?w=1920&q=80',
            'title' => 'Safe & Secure Living',
            'description' => '24/7 security, biometric access, and CCTV across every branch',
            'order' => 2,
            'is_active' => true,
        ]);

        HeroSlider::create([
            'image_path' => 'https://images.unsplash.com/photo-1502672260266-1c1ef2d93688?w=1920&q=80',
            'title' => 'Book Your Room Today',
            'description' => 'Separate branches for men and women, with affordable rates and premium amenities',
            'order' => 3,
            'is_active' => true,
        ]);
    }
}
