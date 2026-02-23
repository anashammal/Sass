<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'whatsapp' => [
        'url' => env('WHATSAPP_SERVER_URL', 'https://wa.tech-sys.online'),
    ],

    'google_maps' => [
        'key' => env('GOOGLE_MAPS_API_KEY'),
    ],

    'kuveyt_turk' => [
        'client_id' => env('KUVEYT_TURK_CLIENT_ID'),
        'merchant_id' => env('KUVEYT_TURK_MERCHANT_ID'),
        'username' => env('KUVEYT_TURK_USERNAME'),
        'password' => env('KUVEYT_TURK_PASSWORD'),
        'private_key' => env('KUVEYT_TURK_PRIVATE_KEY'),
        'api_client_id' => env('KUVEYT_TURK_API_CLIENT_ID'),
        'api_client_secret' => env('KUVEYT_TURK_API_CLIENT_SECRET'),
        'api_key' => env('KUVEYT_TURK_API_KEY'),
        'env' => env('KUVEYT_TURK_ENV', 'production'),
    ],

];
