<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Server extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'region_code', 'public_ip', 'hostname',
        'ssh_port', 'wss_port', 'slowdns_port', 'slowdns_ns_domain',
        'slowdns_public_key', 'agent_endpoint', 'transport_mode',
        'ssh_mgmt_user', 'ssh_mgmt_port', 'ssh_host_fingerprint',
        'ssh_private_key_encrypted', 'ssh_private_key_passphrase_encrypted',
        'max_connections', 'active_connections', 'cpu_usage_percent',
        'ram_usage_percent', 'latency_ms', 'is_active', 'last_heartbeat_at',
    ];

    protected $hidden = [
        'ssh_private_key_encrypted',
        'ssh_private_key_passphrase_encrypted',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'cpu_usage_percent' => 'float',
            'ram_usage_percent' => 'float',
            'last_heartbeat_at' => 'datetime',
        ];
    }

    public function licenses(): HasMany
    {
        return $this->hasMany(License::class);
    }

    public function loadScore(): float
    {
        $connRatio = $this->max_connections > 0
            ? ($this->active_connections / $this->max_connections) * 100
            : 100.0;

        return ($connRatio * 0.5) + ($this->cpu_usage_percent * 0.3) + ($this->ram_usage_percent * 0.2);
    }
}
