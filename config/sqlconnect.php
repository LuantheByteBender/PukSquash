<?php

return [

    'project_id' => env(
        'SQL_CONNECT_PROJECT_ID',
        'squashtest-560f0'
    ),

    'location' => env(
        'SQL_CONNECT_LOCATION',
        'us-central1'
    ),

    'service_id' => env(
        'SQL_CONNECT_SERVICE_ID',
        'puksquash-devdb-1'
    ),

    'connector_id' => env(
        'SQL_CONNECT_CONNECTOR_ID',
        'example'
    ),

    'credentials' => env('FIREBASE_CREDENTIALS'),

    'base_url' => 'https://firebasedataconnect.googleapis.com/v1',

];