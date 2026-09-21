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

    /*
    |--------------------------------------------------------------------------
    | Areeba Gateway (IXOPAY based, https://gateway.areebapayment.com)
    |--------------------------------------------------------------------------
    |
    | Transaction API:  POST {base_url}/transaction/{api_key}/debit
    | Status API:       GET  {base_url}/status/{api_key}/getByMerchantTransactionId/{ref}
    | Auth:             HTTP Basic  ->  base64(username:password)
    |
    | NOTE: `username` / `password` are the API user created in the Areeba
    | gateway portal (Settings -> Users / API). They are NOT necessarily the
    | portal login. `webhook_secret` is optional; if set we verify the
    | X-Signature header on the async notification but still fall back to the
    | status API as the source of truth.
    |
    */
    'areeba' => [
        'base_url'       => env('AREEBA_BASE_URL', 'https://gateway.areebapayment.com/api/v3'),
        'api_key'        => env('AREEBA_API_KEY'),
        'username'       => env('AREEBA_API_USERNAME'),
        'password'       => env('AREEBA_API_PASSWORD'),
        'webhook_secret' => env('AREEBA_WEBHOOK_SECRET'),
        'timeout'        => (int) env('AREEBA_TIMEOUT', 30),
        'loyalty_points' => (float) env('AREEBA_LOYALTY_POINTS', 150),
        'company_name'   => env('AREEBA_COMPANY_NAME', 'Vitas Iraq'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Vitas core "CustomerDataInquiry" (web-abacus)
    |--------------------------------------------------------------------------
    |
    | Same endpoint + credentials the legacy ZainCash flow uses to read the
    | outstanding installment for a loan number.
    |
    */
    'vitas_core' => [
        'inquiry_url' => env('VITAS_PAYMENTS_INQUIRY_URL', 'https://go.web-abacus.com/api/VitasPayments/CustomerDataInquiry'),

        // web-abacus API credentials. CustomerDataInquiry is confirmed to require
        // the APP_USERNAME / APP_PASSWORD pair — the ZAIN_CASH_* pair is rejected
        // by it ("Invalid user or password").
        'username'    => env('VITAS_CORE_USERNAME', env('APP_USERNAME')),
        'password'    => env('VITAS_CORE_PASSWORD', env('APP_PASSWORD')),

        // After a paid Areeba installment we post the payment back to the core so
        // the loan system marks it settled — same as the legacy ZainCash
        // CustomerPaymentDetails call. Point this at an Areeba-specific endpoint
        // once the Vitas backend provides one; the ZainCash path is the proven
        // default. Set VITAS_CORE_CONFIRM_ENABLED=false to only record locally.
        'confirm_enabled'  => filter_var(env('VITAS_CORE_CONFIRM_ENABLED', true), FILTER_VALIDATE_BOOL),
        'confirm_url'      => env('VITAS_PAYMENTS_CONFIRM_URL', 'https://go.web-abacus.com/api/ZainCash/CustomerPaymentDetails'),
        // The legacy ZainCash callback used the ZAIN_CASH_* pair for the
        // post-back; override here if CustomerPaymentDetails needs different
        // credentials than CustomerDataInquiry.
        'confirm_username' => env('VITAS_CORE_CONFIRM_USERNAME', env('VITAS_CORE_USERNAME', env('APP_USERNAME'))),
        'confirm_password' => env('VITAS_CORE_CONFIRM_PASSWORD', env('VITAS_CORE_PASSWORD', env('APP_PASSWORD'))),
    ],

];
