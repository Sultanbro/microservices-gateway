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

    'document_designer' => [
        'base_uri' => env('DOCUMENT_DESIGNER_SERVICE_BASE_URI'),
        'secret' => env('DOCUMENT_DESIGNER_SERVICE_SECRET')
    ],

    'mycent-quotation' => [
        'base_uri' => env('MYCENT_QUOTATION_SERVICE_BASE_URI'),
        'secret' => env('MYCENT_QUOTATION_SERVICE_SECRET')
    ],

];
