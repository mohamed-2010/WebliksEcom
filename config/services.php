<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Stripe, Mailgun, SparkPost and others. This file provides a sane
    | default location for this type of information, allowing packages
    | to have a conventional place to find your various credentials.
    |
    */

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
    ],

    'ses' => [
        'key' => env('SES_KEY'),
        'secret' => env('SES_SECRET'),
        'region' => env('SES_REGION', 'us-east-1'),
    ],

    'sparkpost' => [
        'secret' => env('SPARKPOST_SECRET'),
    ],

    'stripe' => [
        'model' => App\Models\User::class,
        'key' => env('STRIPE_KEY'),
        'secret' => env('STRIPE_SECRET'),
    ],

    'google' => [
        'client_id'     => env('GOOGLE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_CLIENT_SECRET'),
        'redirect'      => env('APP_URL').'/social-login/google/callback',
        'merchant_id'   => env('GOOGLE_MERCHANT_ID'),
        'service_account' => env('GOOGLE_SERVICE_ACCOUNT_JSON'),
    ],

    'facebook' => [
        'client_id'     => env('FACEBOOK_CLIENT_ID'),
        'client_secret' => env('FACEBOOK_CLIENT_SECRET'),
        'redirect'      => env('APP_URL').'/social-login/facebook/callback',
        'access_token' => env('FACEBOOK_ACCESS_TOKEN'),
        'catalog_id' => env('FACEBOOK_CATALOG_ID'),
    ],

    'tabby' => [
        'public_key' => env('TABBY_PUBLIC_KEY'),
        'secret_key' => env('TABBY_SECRET_KEY'),
        'merchant_code' => env('TABBY_MERCHANT_CODE'),
        'webhook_secret' => env('TABBY_WEBHOOK_SECRET'),
    ],

    'uae_gateway' => [
    'url' => env('UAE_GATEWAY_API_URL'),
    'key' => env('UAE_GATEWAY_API_KEY'),
    'secret' => env('UAE_GATEWAY_API_SECRET'),
    'webhook_secret' => env('UAE_GATEWAY_WEBHOOK_SECRET'),
    ],

    'twitter' => [
        'client_id'     => env('TWITTER_CLIENT_ID'),
        'client_secret' => env('TWITTER_CLIENT_SECRET'),
        'redirect'      => env('APP_URL').'/social-login/twitter/callback',
    ],

    'paytm-wallet' => [
        'env' => env('PAYTM_ENVIRONMENT'),
        'merchant_id' => env('PAYTM_MERCHANT_ID'),
        'merchant_key' => env('PAYTM_MERCHANT_KEY'),
        'merchant_website' => env('PAYTM_MERCHANT_WEBSITE'),
        'channel' => env('PAYTM_CHANNEL'),
        'industry_type' => env('PAYTM_INDUSTRY_TYPE'),
    ],
    'recaptcha' => [
    'site_key' => env('RECAPTCHA_SITE_KEY'),
    'secret_key' => env('RECAPTCHA_SECRET_KEY'),
    ],

];
