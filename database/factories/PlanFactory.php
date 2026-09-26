<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Plan;

class PlanFactory extends Factory
{
    protected $model = Plan::class;

    public function definition(): array
    {
        $name = fake()->unique()->words(2, true);

        return [
            'name' => ucwords($name),
            'slug' => \Illuminate\Support\Str::slug($name) . '-' . fake()->unique()->numberBetween(1000, 9999),
            'price' => fake()->randomElement([999, 1999, 2999]),
            'billing_interval' => Plan::INTERVAL_MONTHLY,
            'razorpay_plan_id' => null,
            'is_active' => true,
        ];
    }
}
