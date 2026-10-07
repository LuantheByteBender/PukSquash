<?php

namespace App\Http\Controllers\Api;

use App\DataTransferObjects\BookingDTO;
use App\Http\Controllers\Controller;
use App\Repositories\BookingRepository;
use App\Repositories\UserRepository;
use Carbon\CarbonImmutable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CoachBookingController extends Controller
{
    public function __construct(
        private BookingRepository $bookings,
        private UserRepository $users,
    ) {}

    /**
     * show the authenticated Coach's bookings until the session ends (i.e. future sessions and sessions that are still in progress).
     */
    public function upcoming(Request $request): JsonResponse
    {
        $firebaseUid = $request->attributes->get('firebase_uid');
        $user = $this->users->findByFirebaseUid($firebaseUid);

        if (! $user) {
            return response()->json([
                'message' => 'User profile not found.',
            ], 404);
        }

        if ($user->role !== 'COACH' || $user->status !== 'ACTIVE') {
            return response()->json([
                'message' => 'Only active Coaches may view coaching bookings.',
            ], 403);
        }

        $now = CarbonImmutable::now('Africa/Johannesburg');
        $bookings = $this->bookings->getConfirmedByCoach($user->id);

        // keep future sessions and sessions that are still in progress
        $bookings = array_values(array_filter(
            $bookings,
            function (BookingDTO $booking) use ($now): bool {
                $endTime = $booking->timeSlot['endTime'] ?? null;

                if (! $endTime) {
                    return false;
                }

                $endsAt = CarbonImmutable::parse(
                    $booking->bookingDate.' '.$endTime,
                    'Africa/Johannesburg',
                );

                return $endsAt->greaterThan($now);
            },
        ));

        // order the schedule by date, starting time, then ID for ties
        usort($bookings, function (BookingDTO $first, BookingDTO $second): int {
            return strcmp($first->bookingDate, $second->bookingDate)
                ?: strcmp(
                    $first->timeSlot['startTime'] ?? '',
                    $second->timeSlot['startTime'] ?? '',
                )
                ?: strcmp($first->id, $second->id);
        });

        return response()->json(array_map(
            fn (BookingDTO $booking) => $this->toResponseArray($booking),
            $bookings,
        ));
    }

    /**
     * return the fields needed by the Coach bookings table
     */
    private function toResponseArray(BookingDTO $booking): array
    {
        $data = $booking->toArray();

        return [
            'id' => $booking->id,
            'player' => $booking->createdBy
                ? [
                    'id' => $booking->createdBy['id'] ?? null,
                    'name' => $booking->createdBy['fullName'] ?? null,
                ]
                : null,
            'booking_date' => $booking->bookingDate,
            'time_slot' => $data['time_slot'],
            'court' => $data['court'],
            'status' => $booking->status,
        ];
    }
}
