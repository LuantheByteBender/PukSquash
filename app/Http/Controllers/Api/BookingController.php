<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Repositories\BookingPlayerRepository;
use App\Repositories\BookingRepository;
use App\Repositories\CoachAvailabilityRepository;
use App\Repositories\CourtRepository;
use App\Repositories\TimeSlotRepository;
use App\Repositories\UserRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BookingController extends Controller
{
    public function __construct(
        private BookingRepository       $bookings,
        private BookingPlayerRepository $players,
        private UserRepository          $users,
        private CoachAvailabilityRepository $coachAvailability,
        private CourtRepository         $courts,
        private TimeSlotRepository      $timeSlots,
    ) {}

    /**
     * POST /api/bookings
     *
     * Create a new booking. The authenticated user becomes Player 1.
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'court_id'     => 'required|string',
            'time_slot_id' => 'required|string',
            'booking_date' => 'required|date_format:Y-m-d',
            'booking_type' => 'required|in:OPEN,LOCKED,PRIVATE_COACH',
            'coach_id'     => 'nullable|string',
        ]);

        $firebaseUid = $request->attributes->get('firebase_uid');
        $user = $this->users->findByFirebaseUid($firebaseUid);

        if (! $user) {
            $email = $request->attributes->get('firebase_email');

            if (! $email) {
                return response()->json(['message' => 'User profile not found.'], 404);
            }

            $this->users->upsert([
                'firebaseUid' => $firebaseUid,
                'fullName'    => explode('@', $email)[0],
                'email'       => $email,
            ]);

            $user = $this->users->findByFirebaseUid($firebaseUid);

            if (! $user) {
                return response()->json(['message' => 'User profile not found.'], 404);
            }
        }

        // Court must be ACTIVE.
        $court = $this->courts->findById($request->input('court_id'));
        if (! $court || $court->status !== 'ACTIVE') {
            return response()->json(['message' => 'Court is not available.'], 422);
        }

        if ($this->bookings->userHasBookingForSlot(
            $user->id,
            $request->input('booking_date'),
            $request->input('time_slot_id'),
        )) {
            return response()->json([
                'message' => 'You already have a booking for this time slot.',
                'code'    => 'USER_ALREADY_BOOKED',
            ], 422);
        }

        // Slot must not already be taken by a CONFIRMED booking.
        if ($this->bookings->isSlotTaken(
            $request->input('court_id'),
            $request->input('booking_date'),
            $request->input('time_slot_id'),
        )) {
            return response()->json([
                'message' => 'This court cell is already booked.',
                'code'    => 'SLOT_TAKEN',
            ], 409);
        }

        // PRIVATE_COACH must have a coach_id.
        $bookingType = $request->input('booking_type');
        $coachId     = $request->input('coach_id');

        if ($bookingType === 'PRIVATE_COACH' && ! $coachId) {
            return response()->json(['message' => 'A coach must be selected for private-coach bookings.'], 422);
        }

        if ($bookingType === 'PRIVATE_COACH') {
            if (! $this->coachAvailability->isCoachAvailable(
                $request->input('booking_date'),
                $request->input('time_slot_id'),
                $coachId,
            )) {
                return response()->json([
                    'message' => 'Selected coach is not available for this time slot.',
                    'code'    => 'COACH_UNAVAILABLE',
                ], 409);
            }
        }

        // Create the booking.
        $result = $this->bookings->create([
            'courtId'         => $request->input('court_id'),
            'timeSlotId'      => $request->input('time_slot_id'),
            'createdById'     => $user->id,
            'coachId'         => $coachId,
            'bookingDate'     => $request->input('booking_date'),
            'bookingType'     => $bookingType,
            'bookedAt'        => now()->toIso8601ZuluString(),
        ]);

        // The insert returns the key — extract the booking id.
        $bookingId = null;
        $bookingKey = $result['booking_insert'] ?? null;

        if (is_array($bookingKey)) {
            $bookingId = $bookingKey['id'] ?? null;
        } elseif (is_string($bookingKey) && $bookingKey !== '') {
            $decoded = json_decode($bookingKey, true);

            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                $bookingId = $decoded['id'] ?? null;
            } else {
                $bookingId = $bookingKey;
            }
        }

        if (! $bookingId) {
            return response()->json(['message' => 'Booking creation failed.'], 500);
        }

        // Add the creator as Player 1.
        $this->players->add($bookingId, $user->id, 1);

        return response()->json(['id' => $bookingId], 201);
    }

    /**
     * POST /api/bookings/{id}/join
     *
     * Join an existing OPEN booking as Player 2.
     */
    public function join(Request $request, string $id): JsonResponse
    {
        $firebaseUid = $request->attributes->get('firebase_uid');
        $user = $this->users->findByFirebaseUid($firebaseUid);

        if (! $user) {
            return response()->json(['message' => 'User profile not found.'], 404);
        }

        $booking = $this->bookings->findById($id);

        if (! $booking) {
            return response()->json(['message' => 'Booking not found.'], 404);
        }

        if ($booking->status !== 'CONFIRMED') {
            return response()->json(['message' => 'This booking is no longer active.'], 422);
        }

        if ($booking->bookingType !== 'OPEN') {
            return response()->json(['message' => 'Only OPEN bookings can be joined.'], 422);
        }

        $timeSlotId = $booking->timeSlot['id'] ?? null;
        if ($timeSlotId && $this->bookings->userHasBookingForSlot($user->id, $booking->bookingDate, $timeSlotId)) {
            return response()->json([
                'message' => 'You already have a booking for this time slot.',
                'code'    => 'USER_ALREADY_BOOKED',
            ], 422);
        }

        // Check if slot is full (2 players).
        if (count($booking->players) >= 2) {
            return response()->json([
                'message' => 'This booking is already full.',
                'code'    => 'SLOT_TAKEN',
            ], 409);
        }

        // Prevent the same user from joining twice.
        foreach ($booking->players as $p) {
            if (($p->user['id'] ?? null) === $user->id) {
                return response()->json(['message' => 'You are already in this booking.'], 422);
            }
        }

        $this->players->add($id, $user->id, 2);

        return response()->json(['id' => $id]);
    }

    /**
     * GET /api/bookings/mine?scope=upcoming|past|cancelled
     */
    public function mine(Request $request): JsonResponse
    {
        $firebaseUid = $request->attributes->get('firebase_uid');
        $user = $this->users->findByFirebaseUid($firebaseUid);

        if (! $user) {
            return response()->json(['message' => 'User profile not found.'], 404);
        }

        $scope = $request->query('scope', 'upcoming');

        $status = match ($scope) {
            'cancelled' => 'CANCELLED',
            default     => 'CONFIRMED',
        };

        $bookings = $this->bookings->getByUser($user->id, $status);

        $tz = 'Africa/Johannesburg';
        $now = now($tz);
        $today = $now->format('Y-m-d');

        if ($scope === 'upcoming') {
            $bookings = array_filter($bookings, function ($b) use ($now, $today, $tz) {
                $start = $b->timeSlot['start_time']
                    ?? $b->timeSlot['startTime']
                    ?? null;

                if ($b->bookingDate > $today) {
                    return true;
                }

                if ($b->bookingDate < $today) {
                    return false;
                }

                if (! is_string($start) || $start === '') {
                    return true;
                }

                try {
                    $slotStart = \Illuminate\Support\Carbon::parse($b->bookingDate . ' ' . $start, $tz);
                    return $slotStart->greaterThanOrEqualTo($now);
                } catch (\Throwable) {
                    return true;
                }
            });
        } elseif ($scope === 'past') {
            $bookings = array_filter($bookings, function ($b) use ($now, $today, $tz) {
                $start = $b->timeSlot['start_time']
                    ?? $b->timeSlot['startTime']
                    ?? null;

                if ($b->bookingDate < $today) {
                    return true;
                }

                if ($b->bookingDate > $today) {
                    return false;
                }

                if (! is_string($start) || $start === '') {
                    return false;
                }

                try {
                    $slotStart = \Illuminate\Support\Carbon::parse($b->bookingDate . ' ' . $start, $tz);
                    return $slotStart->lessThan($now);
                } catch (\Throwable) {
                    return false;
                }
            });
        }

        usort($bookings, function ($a, $b) use ($tz) {
            $aStart = $a->timeSlot['start_time']
                ?? $a->timeSlot['startTime']
                ?? null;
            $bStart = $b->timeSlot['start_time']
                ?? $b->timeSlot['startTime']
                ?? null;

            try {
                $aDt = \Illuminate\Support\Carbon::parse($a->bookingDate . ' ' . ($aStart ?: '00:00'), $tz);
            } catch (\Throwable) {
                $aDt = \Illuminate\Support\Carbon::parse($a->bookingDate . ' 00:00', $tz);
            }

            try {
                $bDt = \Illuminate\Support\Carbon::parse($b->bookingDate . ' ' . ($bStart ?: '00:00'), $tz);
            } catch (\Throwable) {
                $bDt = \Illuminate\Support\Carbon::parse($b->bookingDate . ' 00:00', $tz);
            }

            // Latest first.
            return $bDt->getTimestamp() <=> $aDt->getTimestamp();
        });

        return response()->json(
            array_values(array_map(fn ($b) => $b->toArray(), $bookings)),
        );
    }

    /**
     * POST /api/bookings/{id}/cancel
     */
    public function cancel(Request $request, string $id): JsonResponse
    {
        $firebaseUid = $request->attributes->get('firebase_uid');
        $user = $this->users->findByFirebaseUid($firebaseUid);

        if (! $user) {
            return response()->json(['message' => 'User profile not found.'], 404);
        }

        $booking = $this->bookings->findById($id);

        if (! $booking) {
            return response()->json(['message' => 'Booking not found.'], 404);
        }

        if ($booking->status !== 'CONFIRMED') {
            return response()->json(['message' => 'This booking is already cancelled.'], 422);
        }

        // Only the creator can cancel.
        $isCreator = ($booking->createdBy['id'] ?? null) === $user->id;

        if (! $isCreator) {
            if ($booking->bookingType !== 'OPEN') {
                return response()->json(['message' => 'You are not authorised to cancel this booking.'], 403);
            }

            $isPlayer = false;
            foreach ($booking->players as $p) {
                if (($p->user['id'] ?? null) === $user->id) {
                    $isPlayer = true;
                    break;
                }
            }

            if (! $isPlayer) {
                return response()->json(['message' => 'You are not authorised to cancel this booking.'], 403);
            }

            $this->players->remove($id, $user->id);

            return response()->json(['left' => true]);
        }

        $this->bookings->cancel($id);

        return response()->json(['left' => false]);
    }
}
