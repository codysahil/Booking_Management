<?php

namespace Tests\Feature\Admin;

use App\Models\Setting;
use App\Models\Tenant;
use App\Models\User;
use App\Services\Razorpay;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Tests\Concerns\CreatesTenantContext;
use Tests\TestCase;

/**
 * A hostel admin can connect their own Razorpay account so resident rent
 * payments land in their own bank account instead of the platform's. The
 * one guarantee that matters most here: each tenant's credentials must
 * never leak to or be overwritten by another tenant's, and the secret must
 * never be recoverable as plain text from the database.
 */
class PaymentGatewaySettingsTest extends TestCase
{
    use RefreshDatabase, CreatesTenantContext;

    private function baseSettingsPayload(array $overrides = []): array
    {
        return array_merge([
            'hostel_name' => 'Test Hostel',
            'rent_due_day' => 5,
            'late_fee' => 0,
            'notice_period_days' => 30,
        ], $overrides);
    }

    public function test_admin_can_save_razorpay_credentials_and_the_secret_is_encrypted_at_rest()
    {
        $owner = $this->loginAsStaff(['role' => User::ROLE_ADMIN, 'is_active' => true]);

        $response = $this->actingAs($owner)->put(route('admin.settings.update'), $this->baseSettingsPayload([
            'razorpay_key_id' => 'rzp_test_abc123',
            'razorpay_key_secret' => 'super-secret-value',
        ]));

        $response->assertRedirect();
        $this->assertSame('rzp_test_abc123', Setting::get('razorpay_key_id'));

        $rawSecret = DB::table('settings')->where('key', 'razorpay_key_secret')->value('value');
        $this->assertNotSame('super-secret-value', $rawSecret, 'The secret must never be stored in plain text.');
        $this->assertSame('super-secret-value', Crypt::decryptString($rawSecret));
    }

    public function test_leaving_the_secret_blank_keeps_the_existing_one()
    {
        $owner = $this->loginAsStaff(['role' => User::ROLE_ADMIN, 'is_active' => true]);

        $this->actingAs($owner)->put(route('admin.settings.update'), $this->baseSettingsPayload([
            'razorpay_key_id' => 'rzp_test_original',
            'razorpay_key_secret' => 'original-secret',
        ]));

        // Save again, changing only the key id and leaving the secret field blank.
        $this->actingAs($owner)->put(route('admin.settings.update'), $this->baseSettingsPayload([
            'razorpay_key_id' => 'rzp_test_updated',
            'razorpay_key_secret' => '',
        ]));

        $this->assertSame('rzp_test_updated', Setting::get('razorpay_key_id'));
        $rawSecret = DB::table('settings')->where('key', 'razorpay_key_secret')->value('value');
        $this->assertSame('original-secret', Crypt::decryptString($rawSecret));
    }

    public function test_razorpay_service_resolves_each_tenants_own_credentials()
    {
        $tenantA = Tenant::factory()->create();
        $this->bindTenant($tenantA);
        Setting::putMany(['razorpay_key_id' => 'rzp_tenant_a', 'razorpay_key_secret' => Crypt::encryptString('secret-a')]);

        $tenantB = Tenant::factory()->create();
        $this->bindTenant($tenantB);
        Setting::putMany(['razorpay_key_id' => 'rzp_tenant_b', 'razorpay_key_secret' => Crypt::encryptString('secret-b')]);

        $razorpay = app(Razorpay::class);

        $this->bindTenant($tenantA);
        $this->assertSame('rzp_tenant_a', $razorpay->tenantKeyId());

        $this->bindTenant($tenantB);
        $this->assertSame('rzp_tenant_b', $razorpay->tenantKeyId());
    }

    public function test_falls_back_to_platform_credentials_when_tenant_has_not_configured_its_own()
    {
        config(['services.razorpay.key' => 'platform_key', 'services.razorpay.secret' => 'platform_secret']);

        $this->bindTenant(Tenant::factory()->create());

        $razorpay = app(Razorpay::class);

        $this->assertSame('platform_key', $razorpay->tenantKeyId());
        $this->assertTrue($razorpay->residentPaymentsConfigured());
    }

    /** The platform-billing check (used to decide whether to create a matching Plan on Razorpay) must never be swayed by a tenant's own gateway settings. */
    public function test_platform_level_isconfigured_is_unaffected_by_a_tenants_own_keys()
    {
        config(['services.razorpay.key' => null, 'services.razorpay.secret' => null]);

        $this->bindTenant(Tenant::factory()->create());
        Setting::putMany(['razorpay_key_id' => 'rzp_tenant_only', 'razorpay_key_secret' => Crypt::encryptString('tenant-secret')]);

        $razorpay = app(Razorpay::class);

        $this->assertFalse($razorpay->isConfigured(), 'Platform-level isConfigured() must only reflect config(), never a tenant setting.');
        $this->assertTrue($razorpay->residentPaymentsConfigured(), 'But the tenant-facing check should still see the tenant\'s own keys.');
    }

    /** End-to-end through the real HTTP flow a resident uses, not just the service directly. */
    public function test_a_residents_pay_dues_page_reflects_their_own_hostels_gateway_state()
    {
        config(['services.razorpay.key' => null, 'services.razorpay.secret' => null]);

        $tenant = $this->bindTenant(Tenant::factory()->create());

        $customer = \App\Models\Customer::create([
            'customer_code' => 'PGT-0001', 'name' => 'Resident', 'phone' => '9998887766',
            'password' => bcrypt('password'), 'dob' => '2000-01-01', 'address' => 'Addr', 'guardian_phone' => '9998887767',
        ]);

        // No gateway connected yet: online payment must be off.
        $response = $this->actingAs($customer, 'customer')->get(route('customer.payments.index'));
        $response->assertOk();
        $response->assertViewHas('onlinePaymentsEnabled', false);

        // Owner connects their own Razorpay account.
        $owner = User::factory()->create(['tenant_id' => $tenant->id, 'role' => User::ROLE_ADMIN, 'is_active' => true]);
        $this->actingAs($owner)->put(route('admin.settings.update'), $this->baseSettingsPayload([
            'razorpay_key_id' => 'rzp_this_hostel',
            'razorpay_key_secret' => 'this-hostel-secret',
        ]));

        $response = $this->actingAs($customer, 'customer')->get(route('customer.payments.index'));
        $response->assertOk();
        $response->assertViewHas('onlinePaymentsEnabled', true);
    }
}
