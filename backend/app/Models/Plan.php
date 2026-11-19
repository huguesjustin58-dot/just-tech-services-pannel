<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Plan extends Model
{
    use HasFactory;

    protected $fillable = [
        'code', 'name', 'price_fcfa', 'reseller_cost_fcfa',
        'duration_days', 'quota_gb', 'max_concurrent_sessions',
        'allowed_protocols', 'is_vip', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'allowed_protocols' => 'array',
            'is_vip' => 'boolean',
            'is_active' => 'boolean',
        ];
    }
}
