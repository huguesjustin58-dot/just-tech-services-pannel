<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use LogicException;

class ResellerCreditLedger extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'idempotency_key', 'reseller_id', 'actor_id', 'entry_type',
        'amount_fcfa', 'balance_before_fcfa', 'balance_after_fcfa',
        'gb_delta', 'balance_after_gb',
        'description', 'metadata', 'created_at',
    ];

    protected static function booted(): void
    {
        static::updating(static fn () => throw new LogicException('Le journal financier revendeur est strictement immuable.'));
        static::deleting(static fn () => throw new LogicException('La suppression du journal financier est interdite.'));
    }

    protected function casts(): array
    {
        return [
            'gb_delta' => 'integer',
            'balance_after_gb' => 'integer',
            'metadata' => 'array',
            'created_at' => 'datetime',
        ];
    }
}
