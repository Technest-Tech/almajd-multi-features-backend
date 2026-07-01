<?php

return [
    'paypal' => [
        'client_id' => env('PAYPAL_CLIENT_ID', 'AQqdMLWwFl4b8evTmnl2yBBfTKpsk2Z8PIkQLXTwAOitqNAQYLhw0fM3CsX_cRal3n-wvrgSsmoJC-NV'),
        'client_secret' => env('PAYPAL_CLIENT_SECRET'),
        'enabled' => env('PAYPAL_ENABLED', true),
    ],
    'xpay' => [
        'api_key' => env('XPAY_API_KEY', 'ZJJclz0v.vfD6HvBp3ShcysOEI8feWDZcy8x6QO91'),
        'community_id' => env('XPAY_COMMUNITY_ID', 'G3dX238'),
        'variable_amount_id' => env('XPAY_VARIABLE_AMOUNT_ID', '135'),
        'api_url' => env('XPAY_API_URL', 'https://community.xpay.app/api/v1/payments/pay/variable-amount'),
        'transaction_url' => env('XPAY_TRANSACTION_URL', 'https://community.xpay.app/api/communities/G3dX238/transactions'),
    ],
    'anubpay' => [
        'token' => env('ANUBPAY_TOKEN', 'GkqJ5bOqVYoeWDqsjCcC9YedffkzCSZpJaplyY6x'),
        'api_url' => env('ANUBPAY_API_URL', 'https://anubpay.com/api/v1/create'),
        'enabled' => env('ANUBPAY_ENABLED', false),
    ],
];
