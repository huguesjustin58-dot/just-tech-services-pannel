<?php

declare(strict_types=1);

namespace App\Services\Config;

use App\Models\License;
use App\Models\VpnConfig;
use Illuminate\Support\Facades\Crypt;

class VpnConfigGeneratorService
{
    public function generateForLicense(VpnConfig $config, License $license): array
    {
        $server = $license->server;
        $password = Crypt::decryptString($license->vpn_password_encrypted);
        $payload = $config->template_payload;

        return [
            'app' => $config->target_app,
            'profile_name' => "{$config->name} v{$config->version}",
            'protocol' => $config->protocol->value,
            'server' => [
                'host' => $server->hostname ?: $server->public_ip,
                'ssh_port' => $server->ssh_port,
                'wss_port' => $server->wss_port,
                'slowdns_port' => $server->slowdns_port,
                'slowdns_ns' => $server->slowdns_ns_domain,
                'slowdns_pubkey' => $server->slowdns_public_key,
            ],
            'auth' => [
                'username' => $license->vpn_username,
                'password' => $password,
            ],
            'payload_template' => $payload,
            'expires_at' => $license->expires_at->toIso8601String(),
        ];
    }
}
