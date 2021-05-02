<?php

return [
    'name' => env('APP_NAME'),
    
    'env' => env('APP_ENV', 'production'),
    
    'api_key' => env('APP_API_KEY'),
    
    'whitelist' => env('APP_IP_WHITELIST'),
    
    'debug' => env('APP_DEBUG', true),
    
    'partner_restriction' => env('APP_PARTNER_RESTRICTION', true),
    
    'search_cache_lifetime' => 10,
    
    /*
     * Services
     */
    
    'services' => [
        'iat'  => [
            'code'             => env('SERVICE_IAT_CODE'),
            'url'              => env('SERVICE_IAT_URL'),
            'key'              => env('SERVICE_IAT_KEY'),
            'secret'           => env('SERVICE_IAT_SECRET'),
            'currency_code'    => env('SERVICE_IAT_CURRENCY'),
            'electricity_code' => env('SERVICE_IAT_ELECTRICITY_CODE'),
        ],
        'eneo' => [
            'code'              => env('SERVICE_ENEO_PREPAID_CODE'),
            'url'               => env('SERVICE_ENEO_PREPAID_URL'),
            'username'          => env('SERVICE_ENEO_PREPAID_USERNAME'),
            'password'          => env('SERVICE_ENEO_PREPAID_PWD'),
            'counter_code'      => env('SERVICE_ENEO_PREPAID_COUNTER_CODE'),
            'client_id'         => env('SERVICE_ENEO_PREPAID_CLIENT_ID'),
            'terminal_id'       => env('SERVICE_ENEO_PREPAID_TERMINAL_ID'),
            'operator_name'     => env('SERVICE_ENEO_PREPAID_OPERATOR_NAME'),
            'operator_password' => env('SERVICE_ENEO_PREPAID_OPERATOR_PASSWORD'),
            'auth_url'          => env('SERVICE_ENEO_PREPAID_AUTH_URL'),
            'auth_key'          => env('SERVICE_ENEO_PREPAID_AUTH_KEY'),
        ],
    ],
];