<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\PublishCoachAvailabilityRequest;
use App\Repositories\CoachAvailabilityRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CoachAvailabilityController extends Controller
{
    public function __construct(
        private CoachAvailabilityRepository $availability,
    ) {}

    /**
     * GET /api/coaches/available?date=YYYY-MM-DD&time_slot_id=UUID
     */
    public function index(Request $request): JsonResponse
    {
        $request->validate([
            'date'         => 'required|date_format:Y-m-d',
            'time_slot_id' => 'required|string',
        ]);

        $results = $this->availability->getAvailableCoaches(
            $request->query('date'),
            $request->query('time_slot_id'),
        );

        $coaches = [];
        foreach ($results as $availability) {
            $coach = $availability->coach;

            if (! $coach || empty($coach['id'])) {
                continue;
            }

            $coaches[$coach['id']] = [
                'id'   => $coach['id'],
                'name' => $coach['name'] ?? $coach['fullName'] ?? null,
            ];
        }

        return response()->json(array_values($coaches));


    }


    /**
     * Receive and validate a Coach's selected availability.
     *
     * Database storage still needs to be connected.
     */
    public function publish(
        PublishCoachAvailabilityRequest $request,
    ): JsonResponse {
        // Retrieve only fields that passed validation.
        $validated = $request->validated();

        return response()->json([
            'message' => 'Availability is valid, but database saving is not connected yet.',
            'data' => $validated,
        ], 501);
    }
}
