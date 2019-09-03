<?php

return [

    'api_key' => env('API_KEY'),

    'debug' => env('APP_DEBUG', true),

    'meter_code_regex' => env('HEXCELL_METER_CODE_REGEX'),

    'hub_url' => env('SELENIUM_HUB_URL'),

    'hexcell_credentials' => [

        'url' => env('HEXCELL_URL'),

        'username' => env('HEXCELL_USERNAME'),

        'password' => env('HEXCELL_PASSWORD'),

    ],

    'minimum_purchase_amount' => env('MINIMUM_AMOUNT', 500),

];