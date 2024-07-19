<?php
return [
    'email' => env('PAGSEGURO_EMAIL'),
    'token' => env('PAGSEGURO_TOKEN'),
    'tokenSandBox' => env('PAGSEGURO_TOKEN_SANDBOX'),
    'environment' => env('PAGSEGURO_ENVIRONMENT', 'sandbox'),
    'appIdSandBox' => env('PAGSEGURO_APP_ID_SANDBOX'),
    'appKeySandBox' => env('PAGSEGURO_APP_KEY_SANDBOX'),
    'baseUrl' => env('PAGSEGURO_BASE_URL', 'https://api.pagseguro.com'),
    'baseUrlSandBox' => env('PAGSEGURO_BASE_URL_SANDBOX', 'https://sandbox.api.pagseguro.com'),
];
