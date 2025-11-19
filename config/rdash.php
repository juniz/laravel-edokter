<?php

return [
    /*
    |--------------------------------------------------------------------------
    | RDASH API Configuration
    |--------------------------------------------------------------------------
    |
    | Konfigurasi untuk koneksi ke RDASH API
    |
    */

    'api_url' => env('RDASH_API_URL', 'https://api.rdash.id/v1'),

    'reseller_id' => env('RDASH_RESELLER_ID'),

    'api_key' => env('RDASH_API_KEY'),

    /*
    |--------------------------------------------------------------------------
    | Timeout Settings
    |--------------------------------------------------------------------------
    |
    | Timeout untuk request ke RDASH API (dalam detik)
    |
    */

    'timeout' => env('RDASH_TIMEOUT', 30),

    /*
    |--------------------------------------------------------------------------
    | Retry Settings
    |--------------------------------------------------------------------------
    |
    | Jumlah retry dan delay untuk request yang gagal
    |
    */

    'retry_times' => env('RDASH_RETRY_TIMES', 3),

    'retry_delay' => env('RDASH_RETRY_DELAY', 100),

    /*
    |--------------------------------------------------------------------------
    | Auto Sync Settings
    |--------------------------------------------------------------------------
    |
    | Pengaturan untuk auto sync ke RDASH
    |
    */

    'auto_sync_on_user_create' => env('RDASH_AUTO_SYNC_ON_USER_CREATE', false),
    'auto_sync_on_customer_create' => env('RDASH_AUTO_SYNC_ON_CUSTOMER_CREATE', true),
    'auto_sync_on_customer_update' => env('RDASH_AUTO_SYNC_ON_CUSTOMER_UPDATE', true),
];

