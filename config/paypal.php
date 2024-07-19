<?php
/**
 * PayPal Setting & API Credentials
 * Created by Raza Mehdi <srmk@outlook.com>.
 */
// dd(env('PAYPAL_LIVE_CLIENT_SECRET', ''));
return [
    'mode'    => env('PAYPAL_MODE', 'sandbox'), // Can only be 'sandbox' Or 'live'. If empty or invalid, 'live' will be used.
    'sandbox' => [
        'client_id'         => 'AZ_EH4XG1YNtCN395Z1z7o',
        'client_secret'     => 'EE7fEq6tRzuh0lHZpcesAIvL8DrGtPaOTOqaQAIDnLZGAznpW2ODBGLs6eWOpKygbi3agzJwS4mLUQhx',
        'app_id'            => '',
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
