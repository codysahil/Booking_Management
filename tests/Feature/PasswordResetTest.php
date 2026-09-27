<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Tests\Concerns\CreatesTenantContext;
use Tests\TestCase;

/**
 * Laravel's default ResetPassword notification hard-codes route('password.reset'),
 * which doesn't exist in this app (routes are admin.password.reset and
 * customer.password.reset instead) — without AppServiceProvider's
 * ResetPassword::createUrlUsing() override, every real request here throws
 * RouteNotFoundException. These tests would have caught that: they don't
 * just check the HTTP response, they inspect the actual notification and
 * resolve the URL it carries.
 */
class PasswordResetTest extends TestCase
{
    use RefreshDatabase, CreatesTenantContext;

    public function test_requesting_a_reset_link_for_an_admin_builds_a_working_admin_url_not_a_crash()
    {
        Notification::fake();

        $admin = User::factory()->create(['role' => User::ROLE_ADMIN, 'is_active' => true]);

        $response = $this->post(route('admin.password.email'), ['email' => $admin->email]);

        $response->assertSessionHas('status');
        $response->assertSessionHasNoErrors();

        Notification::assertSentTo($admin, function (ResetPassword $notification) use ($admin) {
            $mail = $notification->toMail($admin);
            $url = $mail->actionUrl;

            $this->assertStringContainsString('/admin/reset-password/', $url);
            $this->assertStringNotContainsString('password.reset?', $url); // the broken bare route would've produced this if it hadn't thrown first

            return true;
        });
    }

    public function test_an_admin_can_complete_a_full_reset_with_the_link_from_the_notification()
    {
        Notification::fake();
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN, 'is_active' => true]);

        $this->post(route('admin.password.email'), ['email' => $admin->email]);

        $token = null;
        Notification::assertSentTo($admin, function (ResetPassword $notification) use (&$token) {
            $token = $notification->token;

            return true;
        });

        $response = $this->post(route('admin.password.update'), [
            'token' => $token,
            'email' => $admin->email,
            'password' => 'a-new-strong-password',
            'password_confirmation' => 'a-new-strong-password',
        ]);

        $response->assertRedirect(route('admin.login'));
        $this->assertTrue(Hash::check('a-new-strong-password', $admin->fresh()->password));
    }

    public function test_requesting_a_reset_link_for_a_customer_builds_a_working_customer_url()
    {
        Notification::fake();
        $this->bindTenant();

        $customer = Customer::create([
            'customer_code' => 'CUST-500', 'name' => 'Resident', 'email' => 'resident@example.com',
            'password' => bcrypt('password'), 'phone' => '9998887766', 'dob' => '2000-01-01',
            'address' => 'Addr', 'guardian_phone' => '9998887767',
        ]);

        $response = $this->post(route('customer.password.email'), ['email' => $customer->email]);

        $response->assertSessionHas('status');

        Notification::assertSentTo($customer, function (ResetPassword $notification) use ($customer) {
            $url = $notification->toMail($customer)->actionUrl;

            $this->assertStringContainsString('/customer/reset-password/', $url);

            return true;
        });
    }

    public function test_a_customer_can_complete_a_full_reset_with_the_link_from_the_notification()
    {
        Notification::fake();
        $this->bindTenant();

        $customer = Customer::create([
            'customer_code' => 'CUST-501', 'name' => 'Resident', 'email' => 'resident2@example.com',
            'password' => bcrypt('old-password'), 'phone' => '9998887771', 'dob' => '2000-01-01',
            'address' => 'Addr', 'guardian_phone' => '9998887772',
        ]);

        $this->post(route('customer.password.email'), ['email' => $customer->email]);

        $token = null;
        Notification::assertSentTo($customer, function (ResetPassword $notification) use (&$token) {
            $token = $notification->token;

            return true;
        });

        $response = $this->post(route('customer.password.update'), [
            'token' => $token,
            'email' => $customer->email,
            'password' => 'brand-new-password',
            'password_confirmation' => 'brand-new-password',
        ]);

        $response->assertRedirect(route('customer.login'));
        $this->assertTrue(Hash::check('brand-new-password', $customer->fresh()->password));

        $login = $this->post(route('customer.login'), [
            'customer_code' => 'CUST-501',
            'password' => 'brand-new-password',
        ]);
        $login->assertRedirect(route('customer.dashboard'));
    }

    /** A customer and a staff member sharing an email must not collide on the same reset token row. */
    public function test_customer_and_staff_password_resets_use_separate_token_tables_and_dont_collide()
    {
        $sharedEmail = 'shared@example.com';
        $admin = User::factory()->create(['email' => $sharedEmail, 'role' => User::ROLE_ADMIN, 'is_active' => true]);
        $this->bindTenant();
        $customer = Customer::create([
            'customer_code' => 'CUST-502', 'name' => 'Resident', 'email' => $sharedEmail,
            'password' => bcrypt('password'), 'phone' => '9998887799', 'dob' => '2000-01-01',
            'address' => 'Addr', 'guardian_phone' => '9998887798',
        ]);

        $adminToken = Password::broker('users')->createToken($admin);
        $customerToken = Password::broker('customers')->createToken($customer);

        $this->assertDatabaseHas('password_reset_tokens', ['email' => $sharedEmail]);
        $this->assertDatabaseHas('customer_password_reset_tokens', ['email' => $sharedEmail]);
        $this->assertNotSame($adminToken, $customerToken);
    }
}
