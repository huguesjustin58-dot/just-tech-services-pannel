<?php

declare(strict_types=1);

namespace App\Services\Billing;

use Illuminate\Support\Facades\Cache;

/**
 * Passerelle Mobile Money Cameroun + bascule configurable au plafond Orange.
 */
class MobileMoneyGatewayService
{
    public function getCheckoutChannels(): array
    {
        $orangeVolume = (int) Cache::get('jts:orange_monthly_volume_fcfa', 0);
        $orangeCeiling = (int) config('services.jts_mobile_money.orange_ceiling_fcfa', 1500000);
        $ceilingReached = (bool) Cache::get('jts:orange_ceiling_forced', false) || $orangeVolume >= $orangeCeiling;
        $merchantCode = (string) config('services.jts_mobile_money.orange_merchant_om_asso', '');
        $mtnNumber = (string) config('services.jts_mobile_money.mtn_momo_number', '');
        $orangeNumber = (string) config('services.jts_mobile_money.orange_money_number', '');

        return [
            'mtn_momo' => [
                'label' => 'MTN Mobile Money',
                'beneficiary' => config('services.jts_mobile_money.mtn_momo_beneficiary'),
                'number' => $mtnNumber,
                'active' => $mtnNumber !== '',
            ],
            'orange_money' => [
                'label' => 'Orange Money',
                'beneficiary' => config('services.jts_mobile_money.orange_money_beneficiary'),
                'number' => $orangeNumber,
                'ceiling_reached' => $ceilingReached,
                'active' => !$ceilingReached && $orangeNumber !== '',
            ],
            'orange_merchant_fallback' => [
                'label' => 'Paiement Marchand Orange (OM ASSO)',
                'code' => $merchantCode !== '' ? $merchantCode : null,
                'ussd' => $merchantCode !== '' ? '#150*47*' . $merchantCode . '#' : null,
                'recommended' => $ceilingReached && $merchantCode !== '',
            ],
        ];
    }
}
