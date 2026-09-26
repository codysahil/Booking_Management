<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Subscription;
use App\Models\Tenant;

class SubscriptionFactory extends Factory
{
    protected $model = Subscription::class;

    public function definition(): array
    {
        return [
            'tenant_id' => Tenant::factory(),
            'plan_id' => null,
            'status' => Subscription::STATUS_TRIALING,
        ];
    }

    public function internal(): static
    {
        return $this->state(['status' => Subscription::STATUS_INTERNAL]);
    }

    public function active(): static
    {
        return $this->state([
            'status' => Subscription::STATUS_ACTIVE,
            'current_period_start' => now()->subDays(5),
            'current_period_end' => now()->addDays(25),
        ]);
    }

    public function expired(): static
    {
        return $this->state(['status' => Subscription::STATUS_EXPIRED]);
    }

    public function pastDue(bool $withinGrace = true): static
    {
        return $this->state([
            'status' => Subscription::STATUS_PAST_DUE,
            'grace_ends_at' => $withinGrace ? now()->addDay() : now()->subDay(),
        ]);
    }
}
