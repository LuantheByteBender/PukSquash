<?php

return [

    'project_id' => env(
        'SQL_CONNECT_PROJECT_ID',
        'puksquash-development'
    ),

    'location' => env(
        'SQL_CONNECT_LOCATION',
        'southamerica-west1'
    ),

    'service_id' => env(
        'SQL_CONNECT_SERVICE_ID',
        'puksquash'
    ),

    'connector_id' => env(
        'SQL_CONNECT_CONNECTOR_ID',
        'example'
    ),

    'credentials' => env('FIREBASE_CREDENTIALS'),

    'base_url' => 'https://firebasedataconnect.googleapis.com/v1',

];