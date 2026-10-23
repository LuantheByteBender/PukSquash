
<?php

return [

    // Enable the PayFast testing environment.
    'sandbox' => env('PAYFAST_SANDBOX', true),

    // Merchant credentials.
    'merchant_id' => env('PAYFAST_MERCHANT_ID'),
    'merchant_key' => env('PAYFAST_MERCHANT_KEY'),
    'passphrase' => env('PAYFAST_PASSPHRASE'),

    // Optional: validate ITN source IP against PayFast host DNS.
    // Default off for local development / proxies.
    'validate_ip' => env('PAYFAST_VALIDATE_IP', false),

    // Optional: relax ITN validation checks (signature + server confirmation)
    // for sandbox demos.
    'relax_itn' => env('PAYFAST_RELAX_ITN', env('PAYFAST_SANDBOX', true)),

    // Registration payment amount in ZAR.
    'registration_fee' => env(
        'PAYFAST_REGISTRATION_FEE',
        '100.00'
    ),

    // Official PayFast checkout endpoints.
    'process_url' => env('PAYFAST_SANDBOX', true)
        ? 'https://sandbox.payfast.co.za/eng/process'
        : 'https://www.payfast.co.za/eng/process',

];
