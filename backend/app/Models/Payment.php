<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Models\Scopes\ResellerIsolationScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    protected $fillable = [
        'invoice_number', 'idempotency_key', 'user_id', 'reseller_id',
        'plan_id', 'license_id', 'amount_fcfa', 'method', 'status',
        'external_reference', 'verified_by', 'verified_at', 'rejection_reason',
    ];

    protected static function booted(): void
    {
        static::addGlobalScope(new ResellerIsolationScope());
    }

    protected function casts(): array
    {
        return [
            'method' => PaymentMethod::class,
            'status' => PaymentStatus::class,
            'amount_fcfa' => 'integer',
            'verified_at' => 'datetime',
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
}
