<?php

return [
    'paypal' => [
        'client_id' => env('PAYPAL_CLIENT_ID', 'AQqdMLWwFl4b8evTmnl2yBBfTKpsk2Z8PIkQLXTwAOitqNAQYLhw0fM3CsX_cRal3n-wvrgSsmoJC-NV'),
        'client_secret' => env('PAYPAL_CLIENT_SECRET'),
        'enabled' => env('PAYPAL_ENABLED', true),
    ],
    'xpay' => [
        // New XPay API (api.xpay.app) - Checkout Sessions.
        // Secrets must come from env only - never hardcode keys here.
        'secret_key' => env('XPAY_SECRET_KEY'),          // sk_test_... / sk_live_...
        'publishable_key' => env('XPAY_PUBLISHABLE_KEY'), // pk_test_... / pk_live_... (only needed for embedded/Elements)
        'webhook_secret' => env('XPAY_WEBHOOK_SECRET'),  // whsec_... (per webhook endpoint)
        'base_url' => env('XPAY_BASE_URL', 'https://api.xpay.app'),
    ],
    'anubpay' => [
        'token' => env('ANUBPAY_TOKEN', 'GkqJ5bOqVYoeWDqsjCcC9YedffkzCSZpJaplyY6x'),
        'api_url' => env('ANUBPAY_API_URL', 'https://anubpay.com/api/v1/create'),
        'enabled' => env('ANUBPAY_ENABLED', false),
    ],
];
