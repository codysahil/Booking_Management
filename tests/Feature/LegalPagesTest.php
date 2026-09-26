<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LegalPagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_all_legal_pages_are_publicly_reachable()
    {
        $this->get(route('legal.terms'))->assertOk()->assertSee('Terms of Service');
        $this->get(route('legal.privacy'))->assertOk()->assertSee('Privacy Policy');
        $this->get(route('legal.refund-policy'))->assertOk()->assertSee('Refund Policy');
    }

    public function test_login_pages_link_to_the_legal_pages()
    {
        $this->get(route('admin.login'))->assertSee(route('legal.terms'), false);
        $this->get(route('customer.login'))->assertSee(route('legal.terms'), false);
        $this->get(route('super-admin.login'))->assertSee(route('legal.terms'), false);
    }
}
