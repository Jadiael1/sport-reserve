<?php
return [
    'email' => env('PAGSEGURO_EMAIL', 'ishraellenrai@gmail.com'),
    'token' => env('PAGSEGURO_TOKEN'),
    'tokenSandBox' => env('PAGSEGURO_TOKEN_SANDBOX', '65C90E7EE04A406A8ABB192EFD722293'),
    'environment' => env('PAGSEGURO_ENVIRONMENT', 'sandbox'),
    'appIdSandBox' => env('PAGSEGURO_APP_ID_SANDBOX', 'app5552857845'),
    'appKeySandBox' => env('PAGSEGURO_APP_KEY_SANDBOX', 'B98F73541111A601148D0F87B7CFFE7F'),
    'baseUrl' => env('PAGSEGURO_BASE_URL', 'https://api.pagseguro.com'),
    'baseUrlSandBox' => env('PAGSEGURO_BASE_URL_SANDBOX', 'https://sandbox.api.pagseguro.com'),
];
