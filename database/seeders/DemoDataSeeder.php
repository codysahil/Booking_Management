<?php

namespace Database\Seeders;

use App\Models\Announcement;
use App\Models\Branch;
use App\Models\Customer;
use App\Models\Due;
use App\Models\Employee;
use App\Models\Expense;
use App\Models\HeroSlider;
use App\Models\MonthlyCharge;
use App\Models\Room;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * A full, realistic dataset for one demo hostel — every entity type in the
 * app, in a mix of statuses, so every screen has something real to show.
 * Safe to re-run: does nothing if the demo tenant already exists.
 */
class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        if (Tenant::where('slug', 'demo-pg')->exists()) {
            $this->command->info('Demo tenant already exists — skipping. Delete it first to reseed.');
            return;
        }

        $tenant = Tenant::create([
            'name' => 'Nestay PG',
            'slug' => 'demo-pg',
            'status' => Tenant::STATUS_ACTIVE,
            'owner_name' => 'Demo Owner',
            'owner_email' => 'owner@nestaypg.in',
            'owner_phone' => '9876500000',
        ]);

        app()->instance('currentTenantId', $tenant->id);

        $admin = User::create([
            'tenant_id' => $tenant->id,
            'name' => 'Nestay Admin',
            'email' => 'admin@nestaypg.in',
            'password' => bcrypt('admin123'),
            'role' => User::ROLE_ADMIN,
            'is_active' => true,
        ]);

        $manager = User::create([
            'tenant_id' => $tenant->id,
            'name' => 'Priya Sharma',
            'email' => 'manager@nestaypg.in',
            'password' => bcrypt('manager123'),
            'role' => User::ROLE_MANAGER,
            'is_active' => true,
        ]);

        [$branch1, $branch2] = $this->seedBranchesAndRooms($tenant);
        $this->seedHeroSliders();
        $this->seedEmployees($branch1, $branch2);
        $this->seedExpenses($branch1, $branch2, $admin);
        $this->seedAnnouncements($admin);
        $customers = $this->seedCustomersAndBookings($branch1, $branch2, $admin);
        $this->seedChargesAndDues($customers);
        $this->seedRequests($customers, $admin);

        $this->command->info("Demo tenant '{$tenant->name}' seeded (slug: {$tenant->slug}).");
        $this->command->info("Admin login: admin@nestaypg.in / admin123");
        $this->command->info("Manager login: manager@nestaypg.in / manager123");
    }

    /** @return array{0: Branch, 1: Branch} */
    private function seedBranchesAndRooms(Tenant $tenant): array
    {
        $branch1 = Branch::create([
            'tenant_id' => $tenant->id,
            'name' => "Nestay PG - Knowledge Park II (Men's PG)",
            'address' => 'Plot 14, Knowledge Park II, Greater Noida, Uttar Pradesh - 201310',
            'google_map_url' => 'https://maps.google.com/?q=Knowledge+Park+2+Greater+Noida',
        ]);

        foreach ([
            ['no' => 'Room-1', 'type' => 'AC', 'beds' => 3, 'rent' => 8500],
            ['no' => 'Room-2', 'type' => 'AC', 'beds' => 2, 'rent' => 10500],
            ['no' => 'Room-3', 'type' => 'Non-AC', 'beds' => 4, 'rent' => 6500],
        ] as $r) {
            $room = $branch1->rooms()->create([
                'room_number' => $r['no'], 'type' => $r['type'], 'capacity' => $r['beds'], 'gender_allowed' => 'Male',
            ]);
            for ($i = 1; $i <= $r['beds']; $i++) {
                $room->beds()->create(['bed_number' => "{$r['no']}-{$i}", 'monthly_rent' => $r['rent'], 'status' => 'vacant']);
            }
        }

        $branch2 = Branch::create([
            'tenant_id' => $tenant->id,
            'name' => "Nestay PG - Pari Chowk (Women's PG)",
            'address' => 'House No. 22, Alpha 1 Commercial Belt, Pari Chowk, Greater Noida, Uttar Pradesh - 201308',
            'google_map_url' => 'https://maps.google.com/?q=Pari+Chowk+Greater+Noida',
        ]);

        foreach ([
            ['no' => 'Room-1', 'type' => 'AC', 'beds' => 2, 'rent' => 11000],
            ['no' => 'Room-2', 'type' => 'AC', 'beds' => 3, 'rent' => 9000],
        ] as $r) {
            $room = $branch2->rooms()->create([
                'room_number' => $r['no'], 'type' => $r['type'], 'capacity' => $r['beds'], 'gender_allowed' => 'Female',
            ]);
            for ($i = 1; $i <= $r['beds']; $i++) {
                $room->beds()->create(['bed_number' => "{$r['no']}-{$i}", 'monthly_rent' => $r['rent'], 'status' => 'vacant']);
            }
        }

        return [$branch1, $branch2];
    }

    private function seedHeroSliders(): void
    {
        HeroSlider::create(['image_path' => 'https://images.unsplash.com/photo-1522708323590-d24dbb6b0267?w=1920&q=80', 'title' => 'Welcome to Nestay PG', 'description' => 'Comfortable, fully-furnished PG stays', 'order' => 1, 'is_active' => true]);
        HeroSlider::create(['image_path' => 'https://images.unsplash.com/photo-1560448204-e02f11c3d0e2?w=1920&q=80', 'title' => 'Safe & Secure Living', 'description' => '24/7 security and CCTV across every branch', 'order' => 2, 'is_active' => true]);
    }

    private function seedEmployees(Branch $branch1, Branch $branch2): void
    {
        Employee::create(['employee_code' => 'EMP-001', 'name' => 'Ramesh Kumar', 'role' => 'Warden', 'phone' => '9990000001', 'address' => 'Staff Quarters', 'branch_id' => $branch1->id]);
        Employee::create(['employee_code' => 'EMP-002', 'name' => 'Sunita Devi', 'role' => 'Cook', 'phone' => '9990000002', 'address' => 'Staff Quarters', 'branch_id' => $branch1->id]);
        Employee::create(['employee_code' => 'EMP-003', 'name' => 'Geeta Yadav', 'role' => 'Warden', 'phone' => '9990000003', 'address' => 'Staff Quarters', 'branch_id' => $branch2->id]);
    }

    private function seedExpenses(Branch $branch1, Branch $branch2, User $admin): void
    {
        Expense::create(['branch_id' => $branch1->id, 'category' => 'Electricity', 'amount' => 8500, 'date' => now()->subDays(5), 'vendor' => 'UPPCL', 'payment_method' => 'upi', 'created_by' => $admin->id]);
        Expense::create(['branch_id' => $branch1->id, 'category' => 'Food & Groceries', 'amount' => 22000, 'date' => now()->subDays(3), 'vendor' => 'Local Kirana', 'payment_method' => 'cash', 'created_by' => $admin->id]);
        Expense::create(['branch_id' => $branch2->id, 'category' => 'Maintenance & Repairs', 'amount' => 3200, 'date' => now()->subDays(1), 'vendor' => 'Local Plumber', 'payment_method' => 'cash', 'created_by' => $admin->id]);
        Expense::create(['branch_id' => null, 'category' => 'Marketing', 'amount' => 1500, 'date' => now()->subDays(10), 'vendor' => 'Facebook Ads', 'payment_method' => 'card', 'created_by' => $admin->id]);
    }

    private function seedAnnouncements(User $admin): void
    {
        Announcement::create(['title' => 'Water supply maintenance', 'body' => 'Water will be off from 10am to 2pm this Saturday for tank cleaning.', 'is_pinned' => true, 'created_by' => $admin->id]);
        Announcement::create(['title' => 'Diwali holiday notice', 'body' => 'Office will remain closed on the day of Diwali. Emergency contact will be shared separately.', 'created_by' => $admin->id]);
    }

    /** @return array<int, Customer> */
    private function seedCustomersAndBookings(Branch $branch1, Branch $branch2, User $admin): array
    {
        $beds1 = Room::where('branch_id', $branch1->id)->get()->flatMap->beds->values();
        $beds2 = Room::where('branch_id', $branch2->id)->get()->flatMap->beds->values();

        $residents = [
            ['name' => 'Amit Verma', 'phone' => '9876500001', 'bed' => $beds1[0], 'daysAgo' => 60],
            ['name' => 'Rohit Singh', 'phone' => '9876500002', 'bed' => $beds1[1], 'daysAgo' => 45],
            ['name' => 'Vikram Rathore', 'phone' => '9876500003', 'bed' => $beds1[2], 'daysAgo' => 20],
            ['name' => 'Sneha Gupta', 'phone' => '9876500004', 'bed' => $beds2[0], 'daysAgo' => 35],
            ['name' => 'Pooja Mishra', 'phone' => '9876500005', 'bed' => $beds2[1], 'daysAgo' => 10],
        ];

        $customers = [];

        foreach ($residents as $i => $r) {
            $customer = Customer::create([
                'customer_code' => 'SS-DEMO-' . str_pad($i + 1, 4, '0', STR_PAD_LEFT),
                'name' => $r['name'],
                'phone' => $r['phone'],
                'email' => strtolower(str_replace(' ', '.', $r['name'])) . '@example.com',
                'password' => bcrypt($r['phone']),
                'dob' => now()->subYears(22 + $i)->format('Y-m-d'),
                'address' => 'Permanent address on file',
                'guardian_phone' => '99900000' . (10 + $i),
                'is_active' => true,
            ]);

            $checkIn = now()->subDays($r['daysAgo']);
            $booking = $customer->bookings()->create([
                'booking_reference' => 'BK-DEMO' . str_pad($i + 1, 4, '0', STR_PAD_LEFT),
                'bed_id' => $r['bed']->id,
                'check_in_date' => $checkIn,
                'status' => 'active',
                'advance_paid' => $r['bed']->monthly_rent,
            ]);

            $r['bed']->update(['status' => 'occupied']);

            $customer->payments()->create([
                'booking_id' => $booking->id,
                'amount' => $r['bed']->monthly_rent,
                'payment_type' => 'Advance',
                'payment_method' => 'cash',
                'transaction_ref' => 'ADV-DEMO' . str_pad($i + 1, 4, '0', STR_PAD_LEFT),
                'status' => 'paid',
                'paid_at' => $checkIn,
                'recorded_by' => $admin->id,
            ]);

            $customers[] = $customer;
        }

        // One vacated (completed) resident, for history/reporting screens.
        $vacatedBed = $beds1[3];
        $vacatedCustomer = Customer::create([
            'customer_code' => 'SS-DEMO-0006',
            'name' => 'Karan Mehta',
            'phone' => '9876500006',
            'password' => bcrypt('9876500006'),
            'dob' => now()->subYears(24)->format('Y-m-d'),
            'address' => 'Permanent address on file',
            'guardian_phone' => '9990000016',
            'is_active' => false,
        ]);
        $vacatedCustomer->bookings()->create([
            'booking_reference' => 'BK-DEMO0006',
            'bed_id' => $vacatedBed->id,
            'check_in_date' => now()->subDays(120),
            'check_out_date' => now()->subDays(15),
            'status' => 'completed',
            'advance_paid' => $vacatedBed->monthly_rent,
        ]);
        $customers[] = $vacatedCustomer;

        return $customers;
    }

    /** @param array<int, Customer> $customers */
    private function seedChargesAndDues(array $customers): void
    {
        $thisMonth = now()->format('Y-m');
        $lastMonth = now()->subMonth()->format('Y-m');

        foreach ($customers as $i => $customer) {
            $booking = $customer->bookings()->latest()->first();
            if (! $booking || $booking->status !== 'active') {
                continue;
            }

            $rent = (float) $booking->bed->monthly_rent;

            // Last month: paid.
            MonthlyCharge::create([
                'customer_id' => $customer->id, 'booking_id' => $booking->id, 'month_year' => $lastMonth,
                'rent_amount' => $rent, 'eb_amount' => 300, 'other_charges' => 0,
                'total_amount' => $rent + 300, 'status' => 'paid',
                'due_date' => now()->subMonth()->day(5), 'paid_date' => now()->subMonth()->day(3),
                'payment_method' => 'cash',
            ]);

            // This month: mix of pending / overdue.
            $isOverdue = $i % 3 === 0;
            MonthlyCharge::create([
                'customer_id' => $customer->id, 'booking_id' => $booking->id, 'month_year' => $thisMonth,
                'rent_amount' => $rent, 'eb_amount' => 0, 'other_charges' => 0, 'total_amount' => $rent,
                'status' => $isOverdue ? 'overdue' : 'pending',
                'due_date' => $isOverdue ? now()->subDays(3) : now()->addDays(5),
            ]);

            if ($i === 1) {
                $customer->dues()->create([
                    'due_type' => 'fine', 'title' => 'Late night entry fine', 'amount' => 200,
                    'status' => 'pending', 'due_date' => now()->addDays(3),
                ]);
            }

            if ($i === 2) {
                $customer->dues()->create([
                    'due_type' => 'damage', 'title' => 'Broken chair', 'amount' => 500,
                    'status' => 'paid', 'due_date' => now()->subDays(10), 'paid_date' => now()->subDays(8),
                    'payment_method' => 'cash',
                ]);
            }
        }
    }

    /** @param array<int, Customer> $customers */
    private function seedRequests(array $customers, User $admin): void
    {
        $customers[0]->requests()->create(['type' => 'service', 'subject' => 'Leaking tap in bathroom', 'description' => 'Dripping for two days.', 'status' => 'pending']);
        $customers[1]->requests()->create(['type' => 'complaint', 'subject' => 'Noisy neighbours', 'description' => 'Loud music after 11pm.', 'status' => 'approved', 'admin_response' => 'Spoken to the resident concerned.', 'handled_by' => $admin->id, 'resolved_at' => now()->subDay()]);
        $customers[2]->requests()->create(['type' => 'vacation', 'subject' => 'Moving out next month', 'preferred_date' => now()->addDays(35), 'status' => 'pending']);
        $customers[3]->requests()->create(['type' => 'swap', 'subject' => 'Room swap request', 'description' => 'Would like a quieter room.', 'status' => 'rejected', 'admin_response' => 'No alternate bed currently available.', 'handled_by' => $admin->id, 'resolved_at' => now()->subDays(2)]);
    }
}
