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
        'scheme' => 'https',
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'impact_feed' => [
        'url' => env('IMPACT_FEED_URL'),
        'api_key' => env('IMPACT_FEED_API_KEY'),
    ],

    'facebook' => [
        'client_id' => env('SOCIAL_FACEBOOK_CLIENT_ID'),
        'client_secret' => env('SOCIAL_FACEBOOK_CLIENT_SECRET'),
        'redirect' => env('SOCIAL_FACEBOOK_REDIRECT_URI'),
    ],

    'instagram' => [
        'client_id' => env('SOCIAL_INSTAGRAM_CLIENT_ID'),
        'client_secret' => env('SOCIAL_INSTAGRAM_CLIENT_SECRET'),
        'redirect' => env('SOCIAL_INSTAGRAM_REDIRECT_URI'),
        'verify_token' => env('INSTAGRAM_VERIFY_TOKEN'),
    ],

    'twitter' => [
        'client_id' => env('SOCIAL_X_CLIENT_ID'),
        'client_secret' => env('SOCIAL_X_CLIENT_SECRET'),
        'redirect' => env('SOCIAL_X_REDIRECT_URI'),
    ],

    'google' => [
        'client_id' => env('SOCIAL_GOOGLE_CLIENT_ID'),
        'client_secret' => env('SOCIAL_GOOGLE_CLIENT_SECRET'),
        'redirect' => env('SOCIAL_GOOGLE_REDIRECT_URI'),
    ],

];
