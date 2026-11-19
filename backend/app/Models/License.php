<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\LicenseStatus;
use App\Models\Scopes\ResellerIsolationScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class License extends Model
{
    use HasFactory;

    protected $fillable = [
        'idempotency_key', 'user_id', 'reseller_id', 'plan_id', 'server_id',
        'vpn_username', 'vpn_password_encrypted', 'status',
        'bandwidth_quota_bytes', 'bandwidth_used_bytes', 'max_concurrent_sessions',
        'starts_at', 'expires_at', 'last_error',
    ];

    protected $hidden = ['vpn_password_encrypted'];

    protected static function booted(): void
    {
        static::addGlobalScope(new ResellerIsolationScope());
    }

    protected function casts(): array
    {
        return [
            'status' => LicenseStatus::class,
            'bandwidth_quota_bytes' => 'integer',
            'bandwidth_used_bytes' => 'integer',
            'max_concurrent_sessions' => 'integer',
            'starts_at' => 'datetime',
            'expires_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    public function server(): BelongsTo
    {
        return $this->belongsTo(Server::class);
    }
}
