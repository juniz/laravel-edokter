<?php

return [
    /*
    |--------------------------------------------------------------------------
    | PPH Rate (Pajak Penghasilan)
    |--------------------------------------------------------------------------
    |
    | Tarif PPH yang akan dikenakan pada transaksi.
    | Default: 11% sesuai regulasi Indonesia untuk jasa digital.
    |
    */

    'pph_rate' => env('PPH_RATE', 0.11),

    /*
    |--------------------------------------------------------------------------
    | Annual Discount Rate
    |--------------------------------------------------------------------------
    |
    | Diskon default untuk pembelian tahunan (jika tidak ada plan tahunan spesifik).
    | Default: 20% (2 bulan gratis)
    |
    */

    'annual_discount_rate' => env('ANNUAL_DISCOUNT_RATE', 0.20),
];
