<?php
/**
 * PayPal Setting & API Credentials
 * Created by Raza Mehdi <srmk@outlook.com>.
 */
// dd(env('PAYPAL_LIVE_CLIENT_SECRET', ''));
return [
    'mode'    => env('PAYPAL_MODE', 'live'), // Can only be 'sandbox' Or 'live'. If empty or invalid, 'live' will be used.
    'sandbox' => [
        'client_id'         => 'Afd5tQ3xKYrnq_93Wk33mPy-ZX3ARJDsgarWVRQ5EwE0rat5sfcV-sCp8Mlu9G6AsAVPdzyugA7S8goO',
        'client_secret'     => 'EL8QiBWNSaO5FLufL-nexWXDrjhnKoP5EC9i4pAn0kx2xMKi_6tyy1iI489UHlSiCvByYejXNgfh5y6i',
        'app_id'            => 'APP-80W284485P519543T',
    ],
    'live' => [
        'client_id'         => 'here',
        'client_secret'     => 'here',
        'app_id'            => env('PAYPAL_LIVE_APP_ID', ''),
    ],

    'payment_action' => env('PAYPAL_PAYMENT_ACTION', 'Sale'), // Can only be 'Sale', 'Authorization' or 'Order'
    'currency'       => env('PAYPAL_CURRENCY', 'EUR'),
    'notify_url'     => env('PAYPAL_NOTIFY_URL', ''), // Change this accordingly for your application.
    'locale'         => env('PAYPAL_LOCALE', 'en_US'), // force gateway language  i.e. it_IT, es_ES, en_US ... (for express checkout only)
    'validate_ssl'   => env('PAYPAL_VALIDATE_SSL', true), // Validate SSL when creating api client.
];
