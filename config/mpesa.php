<?php

return [
    /*
    |-----------------------------------------
    | Mpesa Environment
    |-----------------------------------------
    | Can be 'sandbox' or 'production'
    */
    'environment' => env('MPESA_ENVIRONMENT', 'sandbox'),

    /*
    |-----------------------------------------
    | Authentication Credentials
    |-----------------------------------------
    */
    'mpesa_consumer_key' => env('MPESA_CONSUMER_KEY'),
    'mpesa_consumer_secret' => env('MPESA_CONSUMER_SECRET'),

    /*
    |-----------------------------------------
    | Lipa Na Mpesa Settings (STK Push)
    |-----------------------------------------
    */
    'passkey' => env('SAFARICOM_PASSKEY', 'bfb279f9aa9bdbcf158e97dd71a467cd2e0c893059b10f78e6b72ada1ed2c919'),
    'shortcode' => env('MPESA_BUSINESS_SHORTCODE', '174379'),
    'initiator_name' => env('MPESA_INITIATOR_NAME', 'testapi'),
    'initiator_password' => env('MPESA_INITIATOR_PASSWORD'),

    /*
    |-----------------------------------------
    | THE CRITICAL FIX: Callback URL
    |-----------------------------------------
    | The package looks for this specific key.
    | Do not hide it inside another array.
    */
    'callback_url' => env('MPESA_CALLBACK_URL'),

    /*
    |-----------------------------------------
    | Other Service Settings
    |-----------------------------------------
    */
    'till_number' => env('MPESA_BUY_GOODS_TILL', '174379'),
    'b2c_shortcode' => env('MPESA_B2C_SHORTCODE'),
    'b2c_consumer_key' => env('B2C_CONSUMER_KEY'),
    'b2c_consumer_secret' => env('B2C_CONSUMER_SECRET'),

    /*
    |-----------------------------------------
    | Optional Sub-URL Mapping
    |-----------------------------------------
    */
    'callbacks' => [
        'c2b_validation_url' => env('MPESA_C2B_VALIDATION_URL'),
        'c2b_confirmation_url' => env('MPESA_C2B_CONFIRMATION_URL'),
        'b2c_result_url' => env('MPESA_B2C_RESULT_URL'),
        'b2c_timeout_url' => env('MPESA_B2C_TIMEOUT_URL'),
    ],
];
