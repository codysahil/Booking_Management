<?php

namespace Tests\Feature\Console;

use App\Models\Bed;
use App\Models\Branch;
use App\Models\Customer;
use App\Models\MonthlyCharge;
use App\Models\Room;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreatesTenantContext;
use Tests\TestCase;

/**
 * A resident's first rent charge should only cover the days they actually
 * stayed in their check-in month, not the full month — see
 * MonthlyCharge::proratedRentAmount().
 */
class ChargeProrationTest extends TestCase
{
    use RefreshDatabase, CreatesTenantContext;

    /** @return array{0: Customer, 1: \App\Models\Booking} */
    private function makeBookingCheckedInOn(Carbon $checkInDate, float $monthlyRent = 6000): array
    {
        $this->bindTenant();

        $branch = Branch::create(['name' => 'Branch 1', 'address' => 'Addr']);
        $room = Room::create(['branch_id' => $branch->id, 'room_number' => '101', 'capacity' => 2, 'type' => 'AC', 'gender_allowed' => 'Female']);
        $bed = Bed::create(['room_id' => $room->id, 'bed_number' => '101-A', 'monthly_rent' => $monthlyRent, 'status' => 'occupied']);

        $customer = Customer::create([
            'customer_code' => 'SS-PRO-01', 'name' => 'Test Customer', 'phone' => '9876543212',
            'password' => bcrypt('password'), 'dob' => '2000-01-01',
            'address' => 'Test Address', 'guardian_phone' => '9876543213',
        ]);

        $booking = $customer->bookings()->create([
            'booking_reference' => 'BK-PRO0001', 'bed_id' => $bed->id, 'check_in_date' => $checkInDate,
            'status' => 'active', 'advance_paid' => $monthlyRent,
        ]);

        return [$customer, $booking];
    }

    public function test_the_scheduled_command_prorates_rent_for_a_mid_month_check_in()
    {
        [$customer] = $this->makeBookingCheckedInOn(Carbon::create(2026, 3, 10));

        $this->artisan('charges:generate', ['--month' => '2026-03'])->assertSuccessful();

        // March has 31 days; checking in on the 10th means 22 days stayed.
        $expected = round(6000 * 22 / 31, 2);
        $charge = MonthlyCharge::where('customer_id', $customer->id)->first();
        $this->assertEquals($expected, (float) $charge->rent_amount);
        $this->assertEquals($expected, (float) $charge->total_amount);
        $this->assertTrue($charge->is_prorated);
    }

    public function test_a_check_in_on_the_first_of_the_month_is_not_prorated()
    {
        [$customer] = $this->makeBookingCheckedInOn(Carbon::create(2026, 3, 1));

        $this->artisan('charges:generate', ['--month' => '2026-03'])->assertSuccessful();

        $charge = MonthlyCharge::where('customer_id', $customer->id)->first();
        $this->assertEquals(6000.00, (float) $charge->rent_amount);
        $this->assertFalse($charge->is_prorated);
    }

    public function test_a_later_months_charge_for_the_same_booking_is_full_rent()
    {
        [$customer] = $this->makeBookingCheckedInOn(Carbon::create(2026, 3, 10));

        $this->artisan('charges:generate', ['--month' => '2026-04'])->assertSuccessful();

        $charge = MonthlyCharge::where('customer_id', $customer->id)->where('month_year', '2026-04')->first();
        $this->assertEquals(6000.00, (float) $charge->rent_amount);
        $this->assertFalse($charge->is_prorated);
    }

    public function test_the_admins_manual_generate_action_also_prorates_the_check_in_month()
    {
        [$customer] = $this->makeBookingCheckedInOn(Carbon::create(2026, 3, 20));
        $this->loginAsStaff(['role' => User::ROLE_ADMIN, 'is_active' => true]);

        $response = $this->post(route('admin.charges.generate'), ['month' => '2026-03']);

        $response->assertRedirect(route('admin.charges.index', ['month' => '2026-03']));
        $expected = round(6000 * 12 / 31, 2); // 20th to 31st inclusive = 12 days.
        $charge = MonthlyCharge::where('customer_id', $customer->id)->first();
        $this->assertEquals($expected, (float) $charge->rent_amount);
    }
}
