<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use LogicException;

class AuditLog extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'actor_id', 'action', 'auditable_type', 'auditable_id',
        'ip_address', 'context', 'created_at',
    ];

    protected static function booted(): void
    {
        static::updating(static fn () => throw new LogicException('AuditLog est immuable.'));
        static::deleting(static fn () => throw new LogicException('AuditLog est immuable.'));
    }

    protected function casts(): array
    {
        return [
            'context' => 'array',
            'created_at' => 'datetime',
        ];
    }
}
