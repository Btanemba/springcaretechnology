<?php

return [
    'currency' => 'NGN',
    'symbol' => '₦',
    'payment' => [
        'bank_name' => env('STORE_PAYMENT_BANK_NAME'),
        'account_name' => env('STORE_PAYMENT_ACCOUNT_NAME'),
        'account_number' => env('STORE_PAYMENT_ACCOUNT_NUMBER'),
    ],
];