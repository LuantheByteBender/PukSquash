<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\PublishCoachAvailabilityRequest;
use Illuminate\Http\JsonResponse;

class CoachAvailabilityController extends Controller
{
    /**
     * Receive and validate a Coach's selected availability
     *
     * Database saving yet to be completed
     */

    public function publish(
        PublishCoachAvailabilityRequest $request
        ): JsonResponse {
            // get only the fields that have been validated by
            // the request availability rules
            $validated = $request->validated();


            // This temporary response proves that authentication, routing
            // and validation work without falsely claiming that data was saved
             return response()->json([
                'message' => 'Availability is valid, but database saving is not connected yet.',
                'data' => $validated,
            ], 501);


        }
}
