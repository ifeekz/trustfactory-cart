<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Shop Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may configure settings for your e-commerce shop such as
    | admin email and stock thresholds.
    |
    */

    'admin_email' => env('SHOP_ADMIN_EMAIL', 'admin@trustfactory.test'),
    'low_stock_threshold' => env('LOW_STOCK_THRESHOLD', 5),
];
