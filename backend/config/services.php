<?php

declare(strict_types=1);

return [
    'jts_mobile_money' => [
        'orange_ceiling_fcfa' => (int) env('ORANGE_CEILING_FCFA', 1_500_000),
        'mtn_momo_beneficiary' => env('MTN_MOMO_BENEFICIARY', ''),
        'mtn_momo_number' => env('MTN_MOMO_NUMBER', ''),
        'orange_money_beneficiary' => env('ORANGE_MONEY_BENEFICIARY', ''),
        'orange_money_number' => env('ORANGE_MONEY_NUMBER', ''),
        'orange_merchant_om_asso' => env('ORANGE_MERCHANT_OM_ASSO', ''),
    ],
];
