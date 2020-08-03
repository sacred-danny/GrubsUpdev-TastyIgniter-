<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default API Prefix
    |--------------------------------------------------------------------------
    |
    | A default prefix to use for your API routes so you don't have to
    | specify it for each group.
    |
    */
    'prefix' => null,

    /*
    |--------------------------------------------------------------------------
    | Default API Domain
    |--------------------------------------------------------------------------
    |
    | A default domain to use for your API routes so you don't have to
    | specify it for each group.
    |
    */
    'domain' => null,

    /*
    |--------------------------------------------------------------------------
    | Generic Error Format
    |--------------------------------------------------------------------------
    |
    | When some HTTP exceptions are not caught and dealt with the API will
    | generate a generic error response in the format provided. Any
    | keys that aren't replaced with corresponding values will be
    | removed from the final response.
    |
    */
    'errorFormat' => [
        'message',
        'errors',
        'code',
        'status_code',
        'debug',
    ],

    'stripe_key_test_public' => 'pk_test_KGSgL4Ccd2oGEKsSYXBF4SD600LfoqUiWa',
    'stripe_key_test_secret' => 'sk_test_0GKPRu2IwccbM9NMmvl56JrO00bDvBvXPh',
    'stripe_key_live_public' => 'pk_live_KGSgL4Ccd2oGEKsSYXBF4SD600LfoqUiWa',
    'stripe_key_live_secret' => 'sk_live_0GKPRu2IwccbM9NMmvl56JrO00bDvBvXPh',
    'pepipost_api_key' => '44db0e969fee401958c4d0a4a06b22a6',
    'from_mail'=> 'grubsupdev@gmail.com',
    'from_mail_name' => 'Daniel Brown',
    'from_mail_password' => 'n?QE8N"_b?X#Qh&y',
    'stripe_test_mode' => true,
    'stripe_verify_ssl' => false,
];
