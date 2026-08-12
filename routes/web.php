<?php

use Illuminate\Support\Facades\Route;

Route::get('/openapi.json', function () {
    return response()->json([
        'openapi' => '3.0.3',
        'info' => [
            'title' => 'PukSquash API',
            'version' => '1.0.0',
            'description' => 'REST API documentation for PukSquash Laravel backend.',
        ],
        'servers' => [
            [
                'url' => config('app.url'),
            ],
        ],
        'components' => [
            'securitySchemes' => [
                'bearerAuth' => [
                    'type' => 'http',
                    'scheme' => 'bearer',
                    'bearerFormat' => 'JWT',
                    'description' => 'Firebase ID token in the Authorization header.',
                ],
            ],
            'schemas' => [
                'Court' => [
                    'type' => 'object',
                    'properties' => [
                        'id' => ['type' => 'string'],
                        'name' => ['type' => 'string'],
                        'status' => [
                            'type' => 'string',
                            'enum' => ['AVAILABLE', 'UNAVAILABLE', 'MAINTENANCE'],
                        ],
                        'createdAt' => ['type' => 'string', 'format' => 'date-time'],
                    ],
                ],
                'Booking' => [
                    'type' => 'object',
                    'properties' => [
                        'id' => ['type' => 'string'],
                        'userId' => ['type' => 'string'],
                        'courtId' => ['type' => 'string'],
                        'startTime' => ['type' => 'string', 'format' => 'date-time'],
                        'endTime' => ['type' => 'string', 'format' => 'date-time'],
                        'status' => [
                            'type' => 'string',
                            'enum' => ['CONFIRMED', 'CANCELLED', 'COMPLETED'],
                        ],
                        'createdAt' => ['type' => 'string', 'format' => 'date-time'],
                    ],
                ],
            ],
        ],
        'security' => [
            ['bearerAuth' => []],
        ],
        'paths' => [
            '/api/me' => [
                'get' => [
                    'summary' => 'Get current authenticated Firebase user context',
                    'responses' => [
                        '200' => [
                            'description' => 'Authenticated user details',
                        ],
                        '401' => [
                            'description' => 'Unauthenticated',
                        ],
                    ],
                ],
            ],
            '/api/courts' => [
                'get' => [
                    'summary' => 'List courts',
                    'responses' => [
                        '200' => [
                            'description' => 'Court list',
                        ],
                    ],
                ],
                'post' => [
                    'summary' => 'Create a court',
                    'requestBody' => [
                        'required' => true,
                        'content' => [
                            'application/json' => [
                                'schema' => [
                                    'type' => 'object',
                                    'required' => ['name'],
                                    'properties' => [
                                        'name' => ['type' => 'string'],
                                        'status' => [
                                            'type' => 'string',
                                            'enum' => ['AVAILABLE', 'UNAVAILABLE', 'MAINTENANCE'],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                    'responses' => [
                        '201' => [
                            'description' => 'Court created',
                            'content' => [
                                'application/json' => [
                                    'schema' => ['$ref' => '#/components/schemas/Court'],
                                ],
                            ],
                        ],
                        '422' => ['description' => 'Validation error'],
                    ],
                ],
            ],
            '/api/courts/{court}' => [
                'parameters' => [
                    [
                        'name' => 'court',
                        'in' => 'path',
                        'required' => true,
                        'schema' => ['type' => 'string'],
                    ],
                ],
                'get' => [
                    'summary' => 'Get court by ID',
                    'responses' => [
                        '200' => [
                            'description' => 'Court details',
                            'content' => [
                                'application/json' => [
                                    'schema' => ['$ref' => '#/components/schemas/Court'],
                                ],
                            ],
                        ],
                        '404' => ['description' => 'Not found'],
                    ],
                ],
                'put' => [
                    'summary' => 'Update a court',
                    'requestBody' => [
                        'required' => true,
                        'content' => [
                            'application/json' => [
                                'schema' => [
                                    'type' => 'object',
                                    'properties' => [
                                        'name' => ['type' => 'string'],
                                        'status' => [
                                            'type' => 'string',
                                            'enum' => ['AVAILABLE', 'UNAVAILABLE', 'MAINTENANCE'],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                    'responses' => [
                        '200' => ['description' => 'Court updated'],
                        '422' => ['description' => 'Validation error'],
                    ],
                ],
                'delete' => [
                    'summary' => 'Delete a court',
                    'responses' => [
                        '204' => ['description' => 'Deleted'],
                        '404' => ['description' => 'Not found'],
                    ],
                ],
            ],
            '/api/bookings' => [
                'get' => [
                    'summary' => 'List bookings',
                    'responses' => [
                        '200' => ['description' => 'Booking list'],
                    ],
                ],
                'post' => [
                    'summary' => 'Create a booking',
                    'requestBody' => [
                        'required' => true,
                        'content' => [
                            'application/json' => [
                                'schema' => [
                                    'type' => 'object',
                                    'required' => ['userId', 'courtId', 'startTime', 'endTime'],
                                    'properties' => [
                                        'userId' => ['type' => 'string'],
                                        'courtId' => ['type' => 'string'],
                                        'startTime' => ['type' => 'string', 'format' => 'date-time'],
                                        'endTime' => ['type' => 'string', 'format' => 'date-time'],
                                        'status' => [
                                            'type' => 'string',
                                            'enum' => ['CONFIRMED', 'CANCELLED', 'COMPLETED'],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                    'responses' => [
                        '201' => [
                            'description' => 'Booking created',
                            'content' => [
                                'application/json' => [
                                    'schema' => ['$ref' => '#/components/schemas/Booking'],
                                ],
                            ],
                        ],
                        '422' => ['description' => 'Validation error'],
                    ],
                ],
            ],
            '/api/bookings/{booking}' => [
                'parameters' => [
                    [
                        'name' => 'booking',
                        'in' => 'path',
                        'required' => true,
                        'schema' => ['type' => 'string'],
                    ],
                ],
                'get' => [
                    'summary' => 'Get booking by ID',
                    'responses' => [
                        '200' => ['description' => 'Booking details'],
                        '404' => ['description' => 'Not found'],
                    ],
                ],
                'put' => [
                    'summary' => 'Update booking',
                    'requestBody' => [
                        'required' => true,
                        'content' => [
                            'application/json' => [
                                'schema' => [
                                    'type' => 'object',
                                    'properties' => [
                                        'userId' => ['type' => 'string'],
                                        'courtId' => ['type' => 'string'],
                                        'startTime' => ['type' => 'string', 'format' => 'date-time'],
                                        'endTime' => ['type' => 'string', 'format' => 'date-time'],
                                        'status' => [
                                            'type' => 'string',
                                            'enum' => ['CONFIRMED', 'CANCELLED', 'COMPLETED'],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                    'responses' => [
                        '200' => ['description' => 'Booking updated'],
                        '422' => ['description' => 'Validation error'],
                    ],
                ],
                'delete' => [
                    'summary' => 'Delete booking',
                    'responses' => [
                        '204' => ['description' => 'Deleted'],
                        '404' => ['description' => 'Not found'],
                    ],
                ],
            ],
        ],
    ]);
})->name('openapi.json');

Route::get('/docs', function () {
    return view('swagger', [
        'openApiUrl' => route('openapi.json'),
    ]);
})->name('swagger.docs');

Route::get('/{any?}', function () {
    return view('app');
})->where('any', '.*');