<?php

declare(strict_types=1);

namespace App\Enums;

enum UserRole: string
{
    case SUPER_ADMIN = 'super_admin';
    case ADMIN = 'admin';
    case MANAGER = 'manager';
    case RESELLER = 'reseller';
    case SUB_RESELLER = 'sub_reseller';
    case CLIENT = 'client';

    public function label(): string
    {
        return match ($this) {
            self::SUPER_ADMIN => 'Super Administrateur',
            self::ADMIN => 'Administrateur',
            self::MANAGER => 'Gestionnaire',
            self::RESELLER => 'Revendeur',
            self::SUB_RESELLER => 'Sous-revendeur',
            self::CLIENT => 'Client',
        };
    }

    public function isStaff(): bool
    {
        return in_array($this, [self::SUPER_ADMIN, self::ADMIN, self::MANAGER], true);
    }

    public function isResellerTier(): bool
    {
        return in_array($this, [self::RESELLER, self::SUB_RESELLER], true);
    }
}
