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

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Smile API
    |--------------------------------------------------------------------------
    */

    'smile' => [
        'api_key' => env('SMILE_API_KEY'),
        'api_secret' => env('SMILE_API_SECRET'),
        'api_url' => env(
            'SMILE_API_URL',
            'https://sandbox.smileapi.io/v1'
        ),
        'base_url' => env(
            'SMILE_BASE_URL',
            'https://sandbox.smileapi.io/v1'
        ),
    ],

    /*
    |--------------------------------------------------------------------------
    | AI Engine
    |--------------------------------------------------------------------------
    */

    'ai_engine' => [
        'url' => env(
            'PYTHON_ENGINE_URL',
            'http://127.0.0.1:8000'
        ),
    ],

    'python_ai' => [
        'url' => env(
            'PYTHON_AI_URL',
            env('PYTHON_ENGINE_URL', 'http://127.0.0.1:8000')
        ),
    ],

    /*
    |--------------------------------------------------------------------------
    | Twilio
    |--------------------------------------------------------------------------
    */

    'twilio' => [
        'sid' => env('TWILIO_ACCOUNT_SID'),
        'token' => env('TWILIO_AUTH_TOKEN'),
        'from' => env('TWILIO_PHONE_NUMBER'),
    ],

];