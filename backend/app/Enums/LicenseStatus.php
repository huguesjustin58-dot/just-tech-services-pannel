<?php

declare(strict_types=1);

namespace App\Enums;

enum LicenseStatus: string
{
    case PENDING = 'pending';
    case PROVISIONING = 'provisioning';
    case ACTIVE = 'active';
    case SUSPENDED = 'suspended';
    case QUOTA_EXHAUSTED = 'quota_exhausted';
    case EXPIRED = 'expired';
    case REVOKED = 'revoked';

    public function label(): string
    {
        return match ($this) {
            self::PENDING => 'En attente',
            self::PROVISIONING => 'En provisionnement',
            self::ACTIVE => 'Active',
            self::SUSPENDED => 'Suspendue',
            self::QUOTA_EXHAUSTED => 'Quota épuisé',
            self::EXPIRED => 'Expirée',
            self::REVOKED => 'Révoquée',
        };
    }
}
