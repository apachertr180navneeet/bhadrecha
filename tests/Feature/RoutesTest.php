<?php

namespace Tests\Feature;

use Tests\TestCase;

class RoutesTest extends TestCase
{
    public function test_front_home_page_loads(): void
    {
        $response = $this->get('/');
        $response->assertStatus(200);
    }

    public function test_front_about_page_loads(): void
    {
        $response = $this->get('/about');
        $response->assertStatus(200);
    }

    public function test_front_services_page_loads(): void
    {
        $response = $this->get('/services');
        $response->assertStatus(200);
    }

    public function test_front_contact_page_loads(): void
    {
        $response = $this->get('/contact');
        $response->assertStatus(200);
    }

    public function test_front_tracking_page_loads(): void
    {
        $response = $this->get('/tracking');
        $response->assertStatus(200);
    }

    public function test_admin_login_page_loads(): void
    {
        $response = $this->get('/admin/login');
        $response->assertStatus(200);
    }

    public function test_admin_dashboard_requires_authentication(): void
    {
        $response = $this->get('/admin/dashboard');
        $response->assertStatus(302);
    }

    public function test_api_splash_screen_returns_json(): void
    {
        $response = $this->get('/api/splash-screen');
        $response->assertStatus(200);
        $response->assertJson(['status' => true]);
    }

    public function test_api_timezones_returns_json(): void
    {
        $response = $this->get('/api/timezones');
        $response->assertStatus(200);
    }
}
