<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\LicenseStatus;
use App\Enums\UserRole;
use App\Models\License;
use App\Models\Plan;
use App\Models\Server;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Str;
use Tests\TestCase;

class ThreeTierAccessSecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_view_public_vitrine_without_login_redirect_and_sees_no_admin_link(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertDontSee('/admin');
        $response->assertDontSee('Administration');
    }

    public function test_public_registration_always_forces_client_role_even_if_admin_role_is_injected(): void
    {
        $response = $this->post('/register', [
            'name' => 'Attaquant Test',
            'email' => 'client-test@example.com',
            'phone' => 'TEST-PHONE-001',
            'password' => 'StrongPass123!',
            'password_confirmation' => 'StrongPass123!',
            'role' => 'super_admin',
        ]);

        $response->assertRedirect(route('client.dashboard'));

        $user = User::where('email', 'client-test@example.com')->firstOrFail();
        $this->assertSame(UserRole::CLIENT, $user->role);
        $this->assertFalse($user->isAdmin());
    }

    public function test_guest_and_client_receive_404_on_admin_routes_and_client_sees_no_admin_dom_link(): void
    {
        $this->get('/admin')->assertStatus(404);

        $client = User::create([
            'name' => 'Client Standard',
            'email' => 'client@example.com',
            'password' => bcrypt('SecretPass123!'),
            'role' => UserRole::CLIENT,
            'is_active' => true,
        ]);

        $this->actingAs($client)
            ->get('/admin')
            ->assertStatus(404);

        $dashboard = $this->actingAs($client)->get('/client/dashboard');
        $dashboard->assertStatus(200);
        $dashboard->assertDontSee('/admin');
        $dashboard->assertDontSee('Administration');
    }

    public function test_client_cannot_access_another_clients_license_or_vpn_delivery(): void
    {
        $plan = Plan::create([
            'code' => 'npv_500',
            'name' => 'NPV 5 Go',
            'price_fcfa' => 500,
            'reseller_cost_fcfa' => 400,
            'duration_days' => 5,
            'quota_gb' => 5,
            'max_concurrent_sessions' => 1,
            'allowed_protocols' => ['ssh'],
            'is_vip' => false,
            'is_active' => true,
        ]);

        $server = Server::create([
            'name' => 'Node 1',
            'region_code' => 'FR',
            'public_ip' => '203.0.113.10',
            'hostname' => 'fr1.example.com',
            'ssh_port' => 22,
            'wss_port' => 443,
            'slowdns_port' => 53,
            'max_connections' => 100,
            'active_connections' => 0,
            'is_active' => true,
        ]);

        $clientA = User::create([
            'name' => 'Client A',
            'email' => 'clienta@example.com',
            'password' => bcrypt('SecretPass123!'),
            'role' => UserRole::CLIENT,
            'is_active' => true,
        ]);

        $clientB = User::create([
            'name' => 'Client B',
            'email' => 'clientb@example.com',
            'password' => bcrypt('SecretPass123!'),
            'role' => UserRole::CLIENT,
            'is_active' => true,
        ]);

        $licenseA = License::create([
            'idempotency_key' => (string) Str::uuid(),
            'user_id' => $clientA->id,
            'plan_id' => $plan->id,
            'server_id' => $server->id,
            'vpn_username' => 'jts_clienta',
            'vpn_password_encrypted' => Crypt::encryptString('SecretVpnPass'),
            'status' => LicenseStatus::ACTIVE,
            'bandwidth_quota_bytes' => 5 * 1024 * 1024 * 1024,
            'bandwidth_used_bytes' => 0,
            'max_concurrent_sessions' => 1,
            'starts_at' => now(),
            'expires_at' => now()->addDays(5),
        ]);

        $this->actingAs($clientB)
            ->post(route('client.delivery.android', $licenseA))
            ->assertStatus(404);
    }

    public function test_admin_can_access_admin_panel_and_sees_admin_navigation_link(): void
    {
        $admin = User::create([
            'name' => 'Super Admin',
            'email' => 'admin@justtechservices.xyz',
            'password' => bcrypt('AdminSecret123!'),
            'role' => UserRole::SUPER_ADMIN,
            'is_active' => true,
        ]);

        $this->actingAs($admin)
            ->get('/')
            ->assertStatus(200)
            ->assertSee('Administration')
            ->assertSee('/admin');

        $this->actingAs($admin)
            ->get('/admin')
            ->assertStatus(200);
    }

    public function test_login_view_redirects_admin_to_filament_panel_and_client_to_client_dashboard(): void
    {
        $admin = User::create([
            'name' => 'Admin Login',
            'email' => 'admin-login@justtechservices.xyz',
            'password' => bcrypt('AdminSecret123!'),
            'role' => UserRole::ADMIN,
            'is_active' => true,
        ]);

        $client = User::create([
            'name' => 'Client Login',
            'email' => 'client-login@example.com',
            'password' => bcrypt('ClientSecret123!'),
            'role' => UserRole::CLIENT,
            'is_active' => true,
        ]);

        $this->get('/login')->assertStatus(200)->assertDontSee('/admin');

        $this->post('/login', [
            'email' => $admin->email,
            'password' => 'AdminSecret123!',
        ])->assertRedirect('/admin');

        $this->post('/logout')->assertRedirect(route('home'));

        $this->withSession(['url.intended' => '/admin/users'])
            ->post('/login', [
                'email' => $client->email,
                'password' => 'ClientSecret123!',
            ])
            ->assertRedirect(route('client.dashboard'));
    }
}
