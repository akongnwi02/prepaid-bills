<?php

return [

    'api_key' => env('APP_API_KEY'),

    'debug' => env('APP_DEBUG', true),
    
    /*
     * Service codes
     */
    'services' => [
        'codes' => [
            'iat' => env('SERVICE_CODE_IAT_PREPAID')
        ]
    ]
];