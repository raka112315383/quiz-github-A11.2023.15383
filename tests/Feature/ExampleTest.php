<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_application_returns_a_successful_response(): void
    {
        $response = $this->get('/');
        $response->assertStatus(200);
    }

    public function test_login_page_renders_successfully(): void
    {
        $response = $this->get('/login');
        $response->assertStatus(200);
    }

    public function test_pasien_can_access_dashboard(): void
    {
        $pasien = User::create([
            'nama' => 'Pasien Test',
            'email' => 'pasien_test@gmail.com',
            'password' => bcrypt('password'),
            'role' => 'pasien',
            'no_rm' => '202610-001'
        ]);

        $response = $this->actingAs($pasien)->get('/pasien/dashboard');
        $response->assertStatus(200);
        $response->assertSee('Halo Selamat Datang Pasien');
    }

    public function test_admin_can_access_dashboard_and_polis(): void
    {
        $admin = User::create([
            'nama' => 'Admin Test',
            'email' => 'admin_test@gmail.com',
            'password' => bcrypt('password'),
            'role' => 'admin'
        ]);

        $response = $this->actingAs($admin)->get('/admin/dashboard');
        $response->assertStatus(200);
        $response->assertSee('Halo Selamat Datang Admin');

        $responsePolis = $this->actingAs($admin)->get('/admin/polis');
        $responsePolis->assertStatus(200);
    }

    public function test_dokter_can_access_dashboard(): void
    {
        $dokter = User::create([
            'nama' => 'Dokter Test',
            'email' => 'dokter_test@gmail.com',
            'password' => bcrypt('password'),
            'role' => 'dokter'
        ]);

        $response = $this->actingAs($dokter)->get('/dokter/dashboard');
        $response->assertStatus(200);
        $response->assertSee('Halo Selamat Datang Dokter');
    }
}
