<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Default Payment Gateway
    |--------------------------------------------------------------------------
    |
    | Pilih payment gateway default yang akan digunakan.
    | Options: manual, midtrans, xendit, tripay
    |
    */

    'default' => env('PAYMENT_DEFAULT', 'manual'),

    /*
    |--------------------------------------------------------------------------
    | Midtrans Configuration
    |--------------------------------------------------------------------------
    |
    | Konfigurasi untuk Midtrans payment gateway.
    |
    */

    'midtrans' => [
        'server_key' => env('MIDTRANS_SERVER_KEY'),
        'client_key' => env('MIDTRANS_CLIENT_KEY'),
        'is_production' => env('MIDTRANS_IS_PRODUCTION', false),
        'verify_webhook_signature' => env('MIDTRANS_VERIFY_WEBHOOK_SIGNATURE', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Xendit Configuration
    |--------------------------------------------------------------------------
    |
    | Konfigurasi untuk Xendit payment gateway.
    |
    */

    'xendit' => [
        'api_key' => env('XENDIT_API_KEY'),
        'public_key' => env('XENDIT_PUBLIC_KEY'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Tripay Configuration
    |--------------------------------------------------------------------------
    |
    | Konfigurasi untuk Tripay payment gateway.
    |
    */

    'tripay' => [
        'api_key' => env('TRIPAY_API_KEY'),
        'merchant_code' => env('TRIPAY_MERCHANT_CODE'),
        'is_production' => env('TRIPAY_IS_PRODUCTION', false),
    ],
];
