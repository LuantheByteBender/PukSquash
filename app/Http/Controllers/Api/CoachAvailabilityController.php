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
        $activeTimeSlots = $this->timeSlots->listActive();
        $activeTimeSlotIds = array_map(
            fn ($timeSlot) => $timeSlot->id,
            $activeTimeSlots,
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

        // when publishing for today, reject slots whose start time has already passed.
        if ($validated['availability_date'] === now()->format('Y-m-d')) {
            $currentTime = now()->format('H:i:s');

            $slotStartTimes = [];
            foreach ($activeTimeSlots as $ts) {
                $slotStartTimes[$ts->id] = $ts->startTime;
            }

            $expiredTimeSlotIds = array_values(array_filter(
                $validated['time_slot_ids'],
                function (string $id) use ($slotStartTimes, $currentTime) {
                    $start = $slotStartTimes[$id] ?? null;

                    return $start !== null && $start <= $currentTime;
                },
            ));

            if (! empty($expiredTimeSlotIds)) {
                return response()->json([
                    'message' => 'One or more selected time slots have already started or passed.',
                    'expired_time_slot_ids' => $expiredTimeSlotIds,
                ], 422);
            }
        }

        $createdTimeSlotIds = [];
        $existingTimeSlotIds = [];

        $rowsToCreate = [];

        $existingAvailability = $this->availability->getForCoachAndDate(
            $user->id,
            $validated['availability_date'],
        );

        $existingAvailabilitySlotIds = [];
        foreach ($existingAvailability as $slot) {
            $timeSlotId = $slot->timeSlot['id'] ?? null;

            if (! $timeSlotId) {
                continue;
            }

            $existingAvailabilitySlotIds[$timeSlotId] = true;
        }

        foreach ($validated['time_slot_ids'] as $timeSlotId) {
            // do not create a duplicate Coach/date/time-slot row.
            if (isset($existingAvailabilitySlotIds[$timeSlotId])) {
                $existingTimeSlotIds[] = $timeSlotId;

                continue;
            }

            $rowsToCreate[] = [
                'coachId' => $user->id,
                'timeSlotId' => $timeSlotId,
                'availabilityDate' => $validated['availability_date'],
            ];
        }

        if (! empty($rowsToCreate)) {
            $results = $this->availability->createMany($rowsToCreate);

            foreach ($results as $index => $result) {
                $timeSlotId = $rowsToCreate[$index]['timeSlotId'];

                if (! ($result['ok'] ?? false)) {
                    $error = strtolower((string) ($result['error'] ?? ''));

                    if (str_contains($error, 'unique') || str_contains($error, 'duplicate')) {
                        $existingTimeSlotIds[] = $timeSlotId;
                        continue;
                    }

                    throw new \RuntimeException((string) ($result['error'] ?? 'Failed to publish coach availability.'));
                }

                $createdTimeSlotIds[] = $timeSlotId;
            }
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

    /**
    * GET /api/notice-board/coaches?date=YYYY-MM-DD
    *
    * Return all published Coach availability for the Notice Board
    * on a selected date.
    */
    public function noticeBoard(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'date' => [
                'required',
                'date_format:Y-m-d',
            ],
        ]);
        
        $availability = $this->availability->getNoticeBoardForDate(
            $validated['date']
        );

        $coaches = [];

        foreach ($availability as $item) {
            $coach = $item->coach;
            $timeSlot = $item->timeSlot;

            if (
                ! $coach ||
                empty($coach['id']) ||
                ($coach['role'] ?? null) !== 'COACH' ||
                ($coach['status'] ?? null) !== 'ACTIVE' ||
                ! $timeSlot ||
                empty($timeSlot['id']) ||
                ($timeSlot['isActive'] ?? false) !== true
            ) {
                continue;
            }

            $coachId = $coach['id'];
            $slotId = $timeSlot['id'];

            if (! isset($coaches[$coachId])) {
                $coaches[$coachId] = [
                    'id' => $coachId,
                    'name' => $coach['fullName'] ?? $coach['name'] ?? null,
                    'time_slots' => [],
                ];
            }

            $coaches[$coachId]['time_slots'][$slotId] = [
                'id' => $slotId,
                'start_time' => $timeSlot['startTime'] ?? null,
                'end_time' => $timeSlot['endTime'] ?? null,
                'display_order' => $timeSlot['displayOrder'] ?? null,
            ];
        }

        foreach ($coaches as &$coachRow) {
            $coachRow['time_slots'] = array_values($coachRow['time_slots']);

            usort(
                $coachRow['time_slots'],
                fn (array $a, array $b) =>
                ($a['display_order'] ?? PHP_INT_MAX)
                <=>
                ($b['display_order'] ?? PHP_INT_MAX)
            );
        }
        unset($coachRow);

        $payload = array_values($coaches);
        usort(
            $payload,
            fn (array $a, array $b) =>
            strcmp((string) ($a['name'] ?? ''), (string) ($b['name'] ?? ''))
        );

        return response()->json([
            'date' => $validated['date'],
            'data' => $payload,
        ]);
    }

    /**
    * return the logged in Coach's published availability for a date
    */
    public function mine(Request $request): JsonResponse
    {
        // check that the selected date has the expected format.
        $validated = $request->validate([
            'availability_date' => ['required', 'date_format:Y-m-d'],
        ]);

        // identify the caller using the verified firebase identity
        $firebaseUid = $request->attributes->get('firebase_uid');
        $user = $this->users->findByFirebaseUid($firebaseUid);

        if (! $user) {
            return response()->json([
                'message' => 'User profile not found.',
            ], 404);
        }

        // only active Coaches may access this management endpoint
        if ($user->role !== 'COACH' || $user->status !== 'ACTIVE') {
            return response()->json([
                'message' => 'Only active Coaches may view their availability.',
            ], 403);
        }

        // retrieve only this Coach's availability for the selected date
        $availability = $this->availability->getForCoachAndDate(
            $user->id,
            $validated['availability_date'],
        );

        $confirmedTimeSlotIds = $this->availability->listConfirmedBookingTimeSlotIdsForDate(
            $user->id,
            $validated['availability_date'],
        );
        $confirmedTimeSlotIdSet = array_fill_keys($confirmedTimeSlotIds, true);

        // convert the DTO objects into arrays and check for confirmed bookings
        $result = array_map(function ($slot) use ($confirmedTimeSlotIdSet) {
            $arr = $slot->toArray();
            $timeSlotId = $slot->timeSlot['id'] ?? null;

            $arr['has_confirmed_booking'] = $timeSlotId
                ? isset($confirmedTimeSlotIdSet[$timeSlotId])
                : false;

            return $arr;
        }, $availability);

        return response()->json([
            'data' => $result,
        ]);
    }

    /**
    * remove the logged in Coach's availability unless a booking uses it
    */
    public function destroy(Request $request): JsonResponse
    {
        // validate the date and one time slot ID
        $validated = $request->validate([
            'availability_date' => ['required', 'date_format:Y-m-d'],
            'time_slot_id' => [
                'required',
                'string',
                'regex:/\A[0-9a-fA-F]{32}\z/',
            ],
        ]);

        // identify the Coach from their verified login
        $user = $this->users->findByFirebaseUid(
            $request->attributes->get('firebase_uid')
        );

        if (! $user) {
            return response()->json([
                'message' => 'User profile not found.',
            ], 404);
        }

        if ($user->role !== 'COACH' || $user->status !== 'ACTIVE') {
            return response()->json([
                'message' => 'Only active Coaches may remove availability.',
            ], 403);
        }

        $date = $validated['availability_date'];
        $slotId = $validated['time_slot_id'];

        // only target availability belonging to this Coach
        if (! $this->availability->isCoachAvailable($date, $slotId, $user->id)) {
            return response()->json([
                'message' => 'Availability not found.',
            ], 404);
        }

        // preserve an existing confirmed coaching booking
        if ($this->availability->hasConfirmedBooking($user->id, $date, $slotId)) {
            return response()->json([
                'message' => 'This time slot has a confirmed coaching booking.',
            ], 409);
        }

        // delete using Coach + time slot + date
        $this->availability->deleteForCoachAndSlot(
            $user->id,
            $slotId,
            $date,
        );

        return response()->json([
            'message' => 'Coach availability removed successfully.',
        ]);
    }
}
