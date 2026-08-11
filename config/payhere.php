<?php

return [

    /*
    |--------------------------------------------------------------------------
    | PayHere Mode
    |--------------------------------------------------------------------------
    | 'sandbox' for testing, 'live' for production.
    */
    'sandbox' => env('PAYHERE_SANDBOX', true),

    /*
    |--------------------------------------------------------------------------
    | Checkout URLs
    |--------------------------------------------------------------------------
    */
    'checkout_url' => [
        'live'    => 'https://www.payhere.lk/pay/checkout',
        'sandbox' => 'https://sandbox.payhere.lk/pay/checkout',
    ],

    /*
    |--------------------------------------------------------------------------
    | Merchant Credentials
    |--------------------------------------------------------------------------
    | Found in PayHere merchant dashboard under Integrations.
    */
    'merchant_id'     => env('PAYHERE_MERCHANT_ID'),
    'merchant_secret' => env('PAYHERE_MERCHANT_SECRET'),

    /*
    |--------------------------------------------------------------------------
    | Default currency
    |--------------------------------------------------------------------------
    */
    'currency' => env('PAYHERE_CURRENCY', 'LKR'),

    /*
    |--------------------------------------------------------------------------
    | Return / Cancel / Notify URLs
    |--------------------------------------------------------------------------
    | Route names or absolute URLs. Route names are resolved via route().
    | notify_url MUST be a publicly reachable URL (PayHere's servers call it
    | server-to-server) — it will not work on localhost without a tunnel.
    */
    'return_url' => env('PAYHERE_RETURN_URL', '/payment/success'),
    'cancel_url' => env('PAYHERE_CANCEL_URL', '/payment/cancel'),
    'notify_url' => env('PAYHERE_NOTIFY_URL', '/payment/notify'),

    /*
    |--------------------------------------------------------------------------
    | Auto-register package routes
    |--------------------------------------------------------------------------
    | If true, the package registers /payment/checkout, /payment/success,
    | /payment/cancel and /payment/notify for you (see routes/payhere.php).
    | Set to false if you'd rather define your own routes/controller.
    */
    'register_routes' => env('PAYHERE_REGISTER_ROUTES', true),

];
