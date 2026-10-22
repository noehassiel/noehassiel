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
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    // Where the client's answers to proposals and payment notices are sent
    'proposals' => [
        'notify_email' => env('PROPOSALS_NOTIFY_EMAIL', 'admin@noehassiel.com'),
    ],

    // Shown to clients who choose to pay by bank transfer
    'bank' => [
        'name' => env('BANK_NAME'),
        'beneficiary' => env('BANK_BENEFICIARY'),
        'clabe' => env('BANK_CLABE'),
    ],

    // Stripe's card fee in Mexico, passed on to clients who pay by card. Defaults: 3.6% + $3 MXN, plus IVA on the fee
    'stripe_fees' => [
        'percent' => (float) env('STRIPE_FEE_PERCENT', 3.6),
        'fixed' => (float) env('STRIPE_FEE_FIXED', 3.0),
        'tax_percent' => (float) env('STRIPE_FEE_TAX_PERCENT', 16),
    ],

    'leads' => [
        'email' => env('LEADS_EMAIL', env('MAIL_FROM_ADDRESS')),
    ],

    'os1' => [
        'voice_id' => env('ELEVENLABS_VOICE_ID'),
        'max_turns' => (int) env('OS1_MAX_TURNS', 8),
        'requests_per_minute' => (int) env('OS1_REQUESTS_PER_MINUTE', 6),
        'requests_per_day' => (int) env('OS1_REQUESTS_PER_DAY', 60),
    ],

    'github' => [
        'username' => env('GITHUB_USERNAME', 'noehassiel'),
        'token' => env('GITHUB_TOKEN'),
    ],

    'spotify' => [
        'client_id' => env('SPOTIFY_CLIENT_ID'),
        'client_secret' => env('SPOTIFY_CLIENT_SECRET'),
        'redirect_uri' => env('SPOTIFY_REDIRECT_URI'),
    ],

];
