<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\Plan;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $adminEmail = trim((string) env('JTS_SUPERADMIN_EMAIL', ''));
        $adminPassword = (string) env('JTS_SUPERADMIN_PASSWORD', '');

        if ($adminEmail === '' && $adminPassword === '') {
            $this->command?->warn('Compte super-admin non créé : définissez JTS_SUPERADMIN_EMAIL et JTS_SUPERADMIN_PASSWORD avant le seed.');
        } else {
            if (! filter_var($adminEmail, FILTER_VALIDATE_EMAIL) || strlen($adminPassword) < 16) {
                throw new \InvalidArgumentException('JTS_SUPERADMIN_EMAIL doit être une adresse valide et JTS_SUPERADMIN_PASSWORD doit contenir au moins 16 caractères.');
            }

            User::firstOrCreate(
                ['email' => $adminEmail],
                [
                    'name' => 'Super Admin JTS',
                    'password' => Hash::make($adminPassword),
                    'role' => UserRole::SUPER_ADMIN,
                    'is_active' => true,
                ],
            );
        }

        $plans = [
            // NPV TUNNEL
            ['code' => 'npv_500', 'name' => 'NPV 5 Go (5 jours)', 'price_fcfa' => 500, 'reseller_cost_fcfa' => 400, 'duration_days' => 5, 'quota_gb' => 5, 'max_concurrent_sessions' => 1, 'allowed_protocols' => ['ssh', 'ssh_slowdns', 'ssh_wss'], 'is_vip' => false],
            ['code' => 'npv_1000', 'name' => 'NPV 10 Go (7 jours)', 'price_fcfa' => 1000, 'reseller_cost_fcfa' => 800, 'duration_days' => 7, 'quota_gb' => 10, 'max_concurrent_sessions' => 1, 'allowed_protocols' => ['ssh', 'ssh_slowdns', 'ssh_wss'], 'is_vip' => false],
            ['code' => 'npv_2000', 'name' => 'NPV 20 Go (10 jours)', 'price_fcfa' => 2000, 'reseller_cost_fcfa' => 1600, 'duration_days' => 10, 'quota_gb' => 20, 'max_concurrent_sessions' => 2, 'allowed_protocols' => ['ssh', 'ssh_slowdns', 'ssh_wss'], 'is_vip' => false],
            ['code' => 'npv_2500', 'name' => 'NPV 25 Go (12 jours)', 'price_fcfa' => 2500, 'reseller_cost_fcfa' => 2000, 'duration_days' => 12, 'quota_gb' => 25, 'max_concurrent_sessions' => 2, 'allowed_protocols' => ['ssh', 'ssh_slowdns', 'ssh_wss'], 'is_vip' => false],
            ['code' => 'npv_4000', 'name' => 'NPV 40 Go (15 jours)', 'price_fcfa' => 4000, 'reseller_cost_fcfa' => 3200, 'duration_days' => 15, 'quota_gb' => 40, 'max_concurrent_sessions' => 2, 'allowed_protocols' => ['ssh', 'ssh_slowdns', 'ssh_wss'], 'is_vip' => false],
            ['code' => 'npv_5000', 'name' => 'NPV 60 Go (20 jours)', 'price_fcfa' => 5000, 'reseller_cost_fcfa' => 4000, 'duration_days' => 20, 'quota_gb' => 60, 'max_concurrent_sessions' => 3, 'allowed_protocols' => ['ssh', 'ssh_slowdns', 'ssh_wss'], 'is_vip' => true],
            // OFFRE TEST GRATUIT
            ['code' => 'free_test_3go_2h', 'name' => 'Offre Test Gratuit (3 Go / 2h)', 'price_fcfa' => 0, 'reseller_cost_fcfa' => 0, 'duration_days' => 1, 'quota_gb' => 3, 'max_concurrent_sessions' => 1, 'allowed_protocols' => ['ssh', 'ssh_slowdns', 'ssh_wss'], 'is_vip' => false],
        ];

        foreach ($plans as $plan) {
            Plan::updateOrCreate(['code' => $plan['code']], $plan);
        }
    }
}
