<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\VpnProtocol;
use Illuminate\Database\Eloquent\Model;

class VpnConfig extends Model
{
    protected $fillable = [
        'name', 'protocol', 'target_app', 'visibility',
        'version', 'download_token', 'template_payload', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'protocol' => VpnProtocol::class,
            'template_payload' => 'array',
            'is_active' => 'boolean',
        ];
    }
}
