<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'jts_mobile_money' => [
        'orange_ceiling_fcfa' => (int) env('ORANGE_CEILING_FCFA', 1_500_000),
        'mtn_momo_beneficiary' => env('MTN_MOMO_BENEFICIARY', ''),
        'mtn_momo_number' => env('MTN_MOMO_NUMBER', ''),
        'orange_money_beneficiary' => env('ORANGE_MONEY_BENEFICIARY', ''),
        'orange_money_number' => env('ORANGE_MONEY_NUMBER', ''),
        'orange_merchant_om_asso' => env('ORANGE_MERCHANT_OM_ASSO', ''),
    ],
];
