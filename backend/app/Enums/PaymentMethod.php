<?php

declare(strict_types=1);

namespace App\Enums;

enum PaymentMethod: string
{
    case MTN_MOMO = 'mtn_momo';
    case ORANGE_MONEY = 'orange_money';
    case ORANGE_OM_ASSO = 'orange_om_asso';
    case MANUAL = 'manual';
    case RESELLER_CREDIT = 'reseller_credit';

    public function label(): string
    {
        return match ($this) {
            self::MTN_MOMO => 'MTN Mobile Money',
            self::ORANGE_MONEY => 'Orange Money',
            self::ORANGE_OM_ASSO => 'Orange Marchand OM ASSO',
            self::MANUAL => 'Manuel',
            self::RESELLER_CREDIT => 'Crédit Revendeur',
        };
    }
}
