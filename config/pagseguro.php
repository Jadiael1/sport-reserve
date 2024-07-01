<?php
return [
    'email' => env('PAGSEGURO_EMAIL'),
    'token' => env('PAGSEGURO_TOKEN'),
    'tokenSandBox' => env('PAGSEGURO_TOKEN_SANDBOX'),
    'environment' => env('PAGSEGURO_ENVIRONMENT', 'sandbox'), // or 'production'
    'appIdSandBox' => env('PAGSEGURO_APP_ID', 'c17755716848287965845@sandbox.pagseguro.com.br'),
    'appKeySandBox' => env('PAGSEGURO_APP_KEY', 'c17755716848287965845@sandbox.pagseguro.com.br'),
    'baseUrl' => env('PAGSEGURO_BASE_URL', 'https://api.pagseguro.com'),
    'baseUrlSandBox' => env('PAGSEGURO_BASE_URL_SANDBOX', 'https://sandbox.api.pagseguro.com'),
];
