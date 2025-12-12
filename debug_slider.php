<?php

require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\HeroSlider;
use Illuminate\Support\Facades\Storage;

echo "Default Disk: " . config('filesystems.default') . "\n";
echo "APP_URL: " . env('APP_URL') . "\n";

$sliders = HeroSlider::all();
echo "Slider Count: " . $sliders->count() . "\n";

foreach ($sliders as $slider) {
    echo "Slider ID: " . $slider->id . "\n";
    echo "Image Path: " . $slider->image_path . "\n";
    echo "Storage URL: " . Storage::url($slider->image_path) . "\n";

    $exists = Storage::disk(config('filesystems.default'))->exists($slider->image_path);
    echo "Exists on default disk? " . ($exists ? 'Yes' : 'No') . "\n";

    // Check if it exists on public disk specifically
    $existsPublic = Storage::disk('public')->exists($slider->image_path);
    echo "Exists on public disk? " . ($existsPublic ? 'Yes' : 'No') . "\n";
    echo "-------------------\n";
}
