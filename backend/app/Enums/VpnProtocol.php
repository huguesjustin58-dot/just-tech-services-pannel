<?php

declare(strict_types=1);

namespace App\Enums;

enum VpnProtocol: string
{
    case SSH = 'ssh';
    case SSH_SLOWDNS = 'ssh_slowdns';
    case SSH_WSS = 'ssh_wss';

    public function label(): string
    {
        return match ($this) {
            self::SSH => 'SSH Direct',
            self::SSH_SLOWDNS => 'SSH SlowDNS',
            self::SSH_WSS => 'SSH WebSocket TLS',
        };
    }
}
