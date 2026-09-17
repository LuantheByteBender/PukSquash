<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\PublishCoachAvailabilityRequest;
use App\Repositories\CoachAvailabilityRepository;
use App\Repositories\TimeSlotRepository;
use App\Repositories\UserRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CoachAvailabilityController extends Controller
{
    public function __construct(
        private CoachAvailabilityRepository $availability,
        private TimeSlotRepository $timeSlots,
        private UserRepository $users,
    ) {}

    /**
     * GET /api/coaches/available?date=YYYY-MM-DD&time_slot_id=UUID
     */
    public function index(Request $request): JsonResponse
    {
        $request->validate([
            'date' => 'required|date_format:Y-m-d',
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
                'id' => $coach['id'],
                'name' => $coach['name'] ?? $coach['fullName'] ?? null,
            ];
        }

        return response()->json(array_values($coaches));

    }

    /**
     * Publish a Coach's selected availability.
     */
    public function publish(
        PublishCoachAvailabilityRequest $request,
    ): JsonResponse {
        // get the data that passed PublishCoachAvailabilityRequest validation.
        $validated = $request->validated();

        // firebaseAuth middleware placed the authenticated user's UID here.
        $firebaseUid = $request->attributes->get('firebase_uid');

        // find the application's User record using the Firebase UID.
        $user = $this->users->findByFirebaseUid($firebaseUid);

        // the Firebase account exists, but no application profile was found.
        if (! $user) {
            return response()->json([
                'message' => 'User profile not found.',
            ], 404);
        }

        // only active Coaches may publish Coach availability.
        if ($user->role !== 'COACH' || $user->status !== 'ACTIVE') {
            return response()->json([
                'message' => 'Only active Coaches may publish availability.',
            ], 403);
        }

        // retrieve the IDs of all active time slots from SQL Connect.
        $activeTimeSlotIds = array_map(
            fn ($timeSlot) => $timeSlot->id,
            $this->timeSlots->listActive(),
        );

        // find submitted IDs that do not belong to active time slots.
        $invalidTimeSlotIds = array_values(array_diff(
            $validated['time_slot_ids'],
            $activeTimeSlotIds,
        ));

        // reject the complete request if any selected slot is invalid or inactive.
        if (! empty($invalidTimeSlotIds)) {
            return response()->json([
                'message' => 'One or more selected time slots are invalid or inactive.',
                'invalid_time_slot_ids' => $invalidTimeSlotIds,
            ], 422);
        }

        $createdTimeSlotIds = [];
        $existingTimeSlotIds = [];

        foreach ($validated['time_slot_ids'] as $timeSlotId) {
            // do not create a duplicate Coach/date/time-slot row.
            if ($this->availability->isCoachAvailable(
                $validated['availability_date'],
                $timeSlotId,
                $user->id, )) {
                $existingTimeSlotIds[] = $timeSlotId;

                continue;
            }

            // create one CoachAvailability row for this selected time slot.
            $this->availability->create(
                $user->id,
                $timeSlotId,
                $validated['availability_date'],
            );

            $createdTimeSlotIds[] = $timeSlotId;
        }

        return response()->json([
            'message' => 'Coach availability published successfully.',
            'data' => [
                'availability_date' => $validated['availability_date'],
                'time_slot_ids' => $validated['time_slot_ids'],
                'created_time_slot_ids' => $createdTimeSlotIds,
                'existing_time_slot_ids' => $existingTimeSlotIds,
            ],
        ]);
    }
}
