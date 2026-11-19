<?php

declare(strict_types=1);

namespace App\Services\Vpn;

use App\Models\Server;
use Illuminate\Support\Str;
use RuntimeException;

class VpnAgentClient
{
    public function createUser(
        Server $server,
        string $username,
        string $password,
        string $expiresAt,
        int $maxSessions,
        int $quotaBytes,
    ): array {
        return $this->request($server, 'POST', '/v1/users/provision', [
            'username' => $username,
            'password' => $password,
            'expires_at' => $expiresAt,
            'max_sessions' => $maxSessions,
            'quota_bytes' => $quotaBytes,
        ]);
    }

    public function updateLimits(Server $server, string $username, int $quotaBytes, int $maxSessions): array
    {
        return $this->request($server, 'POST', '/v1/users/limits', [
            'username' => $username,
            'quota_bytes' => $quotaBytes,
            'max_sessions' => $maxSessions,
        ]);
    }

    public function suspendUser(Server $server, string $username): array
    {
        return $this->request($server, 'POST', '/v1/users/suspend', ['username' => $username]);
    }

    public function revokeUser(Server $server, string $username): array
    {
        return $this->request($server, 'POST', '/v1/users/revoke', ['username' => $username]);
    }

    protected function request(Server $server, string $method, string $path, array $payload): array
    {
        $socketPath = $server->agent_endpoint ?: config('jts_vpn.agent.socket_path', '/run/jts-agent/agent.sock');
        $secret = (string) config('jts_vpn.agent.hmac_secret');
        $body = json_encode($payload, JSON_THROW_ON_ERROR);
        $timestamp = (string) time();
        $nonce = Str::uuid()->toString();
        $signature = hash_hmac('sha256', "{$timestamp}.{$nonce}.{$method}.{$path}.{$body}", $secret);

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_UNIX_SOCKET_PATH => $socketPath,
            CURLOPT_URL => "http://localhost{$path}",
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_POSTFIELDS => $body,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => (int) config('jts_vpn.agent.timeout', 15),
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                "X-JTS-Timestamp: {$timestamp}",
                "X-JTS-Nonce: {$nonce}",
                "X-JTS-Signature: {$signature}",
            ],
        ]);

        $raw = curl_exec($ch);
        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $err = curl_error($ch);
        curl_close($ch);

        if ($raw === false || $status >= 400) {
            throw new RuntimeException("Erreur Agent VPN ({$status}) : {$err} {$raw}");
        }

        return json_decode($raw, true, 512, JSON_THROW_ON_ERROR);
    }
}
