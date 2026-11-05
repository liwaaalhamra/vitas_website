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
    | Areeba Payment Gateway (hosted checkout, https://payments-api.areeba.com)
    |--------------------------------------------------------------------------
    |
    | A. Bearer token:      POST {base_url}/{env}/api/Authentication   (grant_type=password)
    | B. Checkout session:  POST {base_url}/{env}/api/tokens/request    -> checkoutUrl
    | C. Verification:      GET  {base_url}/{env}/api/v1/payments/GetTransaction/{txId}
    |
    | Secrets (password, subscription key, webhook secret) may be stored
    | encrypted: run `php artisan secrets:encrypt` and paste the "enc:..." value.
    |
    */
    'areeba' => [
        'base_url'            => env('AREEBA_BASE_URL', 'https://payments-api.areeba.com'),
        'env_path'            => env('AREEBA_ENV_PATH', 'prod'),
        'client_id'           => env('AREEBA_CLIENT_ID', '2503'),
        'product_id'          => (int) env('AREEBA_PRODUCT_ID', 93000533),
        'username'            => env('AREEBA_API_USERNAME', 'Vitas PGW'),
        'password'            => env('AREEBA_API_PASSWORD'),
        'subscription_key'    => env('AREEBA_SUBSCRIPTION_KEY'),
        'subscription_header' => env('AREEBA_SUBSCRIPTION_HEADER', 'Ocp-Apim-Subscription-Key'),
        'currency'            => env('AREEBA_CURRENCY', 'IQD'),
        'token_type_id'       => (int) env('AREEBA_TOKEN_TYPE_ID', 4),
        'tax_included'        => env('AREEBA_TAX_INCLUDED', 'True'),
        // Parameter key used to pass our return URL in the checkout session.
        // Leave empty if the return URL is configured on the Areeba portal instead.
        'return_url_param'    => env('AREEBA_RETURN_URL_PARAM'),
        // Server-to-server notifications (/payment/webhook)
        'webhook_secret'      => env('AREEBA_WEBHOOK_SECRET'),
        'webhook_header'      => env('AREEBA_WEBHOOK_SIGNATURE_HEADER', 'X-Signature'),
        'webhook_algo'        => env('AREEBA_WEBHOOK_ALGO', 'sha256'),
        'timeout'             => (int) env('AREEBA_TIMEOUT', 30),
        // abandoned checkout sessions are closed as cancelled after this many hours
        'session_ttl_hours'   => (int) env('AREEBA_SESSION_TTL_HOURS', 24),
        'loyalty_points'      => (float) env('AREEBA_LOYALTY_POINTS', 150),
    ],

    /*
    |--------------------------------------------------------------------------
    | Vitas Core Banking (web-abacus)
    |--------------------------------------------------------------------------
    |
    | Inquiry:  POST {base_url}/api/VitasPayments/CustomerDataInquiryByLoanNumber
    | Push:     POST {base_url}/api/VitasPayments/CustomerPaymentDetails
    |
    | Inquiry and payment push use separate credentials. Passwords may be stored
    | encrypted ("enc:..." from `php artisan secrets:encrypt`).
    |
    */
    'vitas_core' => [
        'base_url'          => env('CORE_BANKING_API_BASE_URL', 'https://go.web-abacus.com'),
        'inquiry_path'      => env('CORE_BANKING_INQUIRY_PATH', '/api/VitasPayments/CustomerDataInquiryByLoanNumber'),
        'payment_path'      => env('CORE_BANKING_PAYMENT_PATH', '/api/VitasPayments/CustomerPaymentDetails'),
        'inquiry_username'  => env('CORE_BANKING_INQUIRY_USERNAME', env('VITAS_CORE_USERNAME', env('APP_USERNAME'))),
        'inquiry_password'  => env('CORE_BANKING_INQUIRY_PASSWORD', env('VITAS_CORE_PASSWORD', env('APP_PASSWORD'))),
        'payment_username'  => env('CORE_BANKING_PAYMENT_USERNAME', env('VITAS_CORE_CONFIRM_USERNAME')),
        'payment_password'  => env('CORE_BANKING_PAYMENT_PASSWORD', env('VITAS_CORE_CONFIRM_PASSWORD')),
        'source_currency_id'=> (int) env('CORE_BANKING_SOURCE_CURRENCY_ID', 3),
        'push_enabled'      => filter_var(env('CORE_BANKING_PUSH_ENABLED', true), FILTER_VALIDATE_BOOL),
        'push_max_attempts' => (int) env('CORE_BANKING_PUSH_MAX_ATTEMPTS', 10),
        'timeout'           => (int) env('CORE_BANKING_TIMEOUT', 30),
    ],

    'firebase' => [
        // service account JSON from the Firebase console, path relative to the project root
        'credentials' => env('FIREBASE_CREDENTIALS') ? base_path(env('FIREBASE_CREDENTIALS')) : storage_path('app/firebase/service-account.json'),
        // optional, defaults to project_id in the credentials file
        'project_id' => env('FIREBASE_PROJECT_ID'),
    ]

];
