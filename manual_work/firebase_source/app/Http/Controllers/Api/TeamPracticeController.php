<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Repositories\AvailabilityRepository;
use App\Repositories\BookingRepository;
use App\Repositories\CourtRepository;
use App\Repositories\TimeSlotRepository;
use App\Repositories\UserRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class TeamPracticeController extends Controller
{
    public function __construct(
        private AvailabilityRepository $availability,
        private BookingRepository $bookings,
        private CourtRepository $courts,
        private TimeSlotRepository $timeSlots,
        private UserRepository $users,
    ) {}

    public function availability(Request $request): JsonResponse
    {
        $request->validate(['date' => 'required|date_format:Y-m-d']);

        $firebaseUid = $request->attributes->get('firebase_uid');
        $user = $this->users->findByFirebaseUid($firebaseUid);

        if (! $user) {
            return response()->json(['message' => 'User profile not found.'], 404);
        }

        if ($user->role !== 'COACH' || $user->status !== 'ACTIVE') {
            return response()->json([
                'message' => 'Only active Coaches may create team practices.',
            ], 403);
        }

        $date = $request->query('date');

        $snapshot = $this->availability->getForDate($date);
        $courts = $snapshot['courts'];
        $slots = $snapshot['timeSlots'];
        $booked = $snapshot['bookings'];

        usort(
            $courts,
            static function ($a, $b) {
                $aName = is_object($a) ? ($a->name ?? '') : '';
                $bName = is_object($b) ? ($b->name ?? '') : '';

                preg_match('/\d+/', (string) $aName, $aMatch);
                preg_match('/\d+/', (string) $bName, $bMatch);

                $aNum = isset($aMatch[0]) ? (int) $aMatch[0] : null;
                $bNum = isset($bMatch[0]) ? (int) $bMatch[0] : null;

                if ($aNum !== null && $bNum !== null && $aNum !== $bNum) {
                    return $aNum <=> $bNum;
                }

                return strnatcasecmp((string) $aName, (string) $bName);
            }
        );

        usort(
            $slots,
            static fn ($a, $b) => ($a->displayOrder ?? PHP_INT_MAX) <=> ($b->displayOrder ?? PHP_INT_MAX)
        );

        $tz = 'Africa/Johannesburg';
        $now = now($tz);
        $today = $now->format('Y-m-d');

        $isPastDate = is_string($date) && $date < $today;
        $isToday = is_string($date) && $date === $today;

        $pastSlotIds = [];
        foreach ($slots as $slot) {
            if ($isPastDate) {
                $pastSlotIds[$slot->id] = true;
                continue;
            }

            if (! $isToday) {
                continue;
            }

            $start = $slot->startTime ?? null;
            if (! is_string($start) || $start === '') {
                continue;
            }

            try {
                $slotStart = Carbon::parse($date . ' ' . $start, $tz);
                if ($slotStart->lessThanOrEqualTo($now)) {
                    $pastSlotIds[$slot->id] = true;
                }
            } catch (\Throwable) {
            }
        }

        $index = [];
        foreach ($booked as $b) {
            $key = ($b->court['id'] ?? '') . ':' . ($b->timeSlot['id'] ?? '');
            $index[$key] = $b;
        }

        $courtsPayload = array_map(fn ($c) => $c->toArray(), $courts);
        $slotsPayload = array_map(fn ($s) => $s->toArray(), $slots);

        $cells = [];
        foreach ($courts as $court) {
            foreach ($slots as $slot) {
                $key = $court->id . ':' . $slot->id;
                $booking = $index[$key] ?? null;

                $slotIsPast = isset($pastSlotIds[$slot->id]);

                if (! $booking) {
                    if ($court->status !== 'ACTIVE') {
                        $cells[] = [
                            'court_id' => $court->id,
                            'time_slot_id' => $slot->id,
                            'state' => 'UNAVAILABLE',
                            'label' => 'Inactive',
                            'can_book' => false,
                            'can_join' => false,
                            'is_mine' => false,
                            'booking_id' => null,
                        ];
                        continue;
                    }

                    if ($slotIsPast) {
                        $cells[] = [
                            'court_id' => $court->id,
                            'time_slot_id' => $slot->id,
                            'state' => 'UNAVAILABLE',
                            'label' => '—',
                            'can_book' => false,
                            'can_join' => false,
                            'is_mine' => false,
                            'booking_id' => null,
                        ];
                        continue;
                    }

                    $cells[] = [
                        'court_id' => $court->id,
                        'time_slot_id' => $slot->id,
                        'state' => 'AVAILABLE',
                        'label' => 'Available',
                        'can_book' => true,
                        'can_join' => false,
                        'is_mine' => false,
                        'booking_id' => null,
                    ];
                    continue;
                }

                $state = match (true) {
                    $booking->bookingType === 'PRIVATE_COACH' => 'PRIVATE_COACH',
                    $booking->bookingType === 'LOCKED' => 'LOCKED',
                    default => 'FULL',
                };

                $creatorName = $booking->createdBy['fullName']
                    ?? $booking->createdBy['name']
                    ?? null;

                $label = $creatorName ?: $state;

                $cells[] = [
                    'court_id' => $court->id,
                    'time_slot_id' => $slot->id,
                    'state' => $state,
                    'label' => $label,
                    'can_book' => false,
                    'can_join' => false,
                    'is_mine' => false,
                    'booking_id' => null,
                ];
            }
        }

        return response()->json([
            'booking_window_days' => 14,
            'courts' => $courtsPayload,
            'time_slots' => $slotsPayload,
            'cells' => $cells,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'selections' => 'required|array|min:1',
            'selections.*.court_id' => 'required|string',
            'selections.*.time_slot_id' => 'required|string',
            'selections.*.booking_date' => 'required|date_format:Y-m-d',
        ]);

        $firebaseUid = $request->attributes->get('firebase_uid');
        $user = $this->users->findByFirebaseUid($firebaseUid);

        if (! $user) {
            return response()->json(['message' => 'User profile not found.'], 404);
        }

        if ($user->role !== 'COACH' || $user->status !== 'ACTIVE') {
            return response()->json([
                'message' => 'Only active Coaches may create team practices.',
            ], 403);
        }

        $tz = 'Africa/Johannesburg';
        $now = now($tz);
        $today = $now->format('Y-m-d');
        $maxDate = $now->copy()->addDays(14)->format('Y-m-d');

        $activeSlots = $this->timeSlots->listActive();
        $activeSlotIds = array_fill_keys(array_map(fn ($s) => $s->id, $activeSlots), true);
        $slotStartTimes = [];
        foreach ($activeSlots as $s) {
            $slotStartTimes[$s->id] = $s->startTime;
        }

        $seen = [];
        foreach ($validated['selections'] as $sel) {
            $key = $sel['booking_date'] . ':' . $sel['court_id'] . ':' . $sel['time_slot_id'];
            if (isset($seen[$key])) {
                return response()->json([
                    'message' => 'Duplicate court selections are not allowed.',
                ], 422);
            }
            $seen[$key] = true;

            if ($sel['booking_date'] < $today) {
                return response()->json(['message' => 'Past dates are not allowed.'], 422);
            }

            if ($sel['booking_date'] > $maxDate) {
                return response()->json(['message' => 'Selected date is outside the booking window.'], 422);
            }

            if (! isset($activeSlotIds[$sel['time_slot_id']])) {
                return response()->json(['message' => 'One or more selected time slots are invalid or inactive.'], 422);
            }

            $court = $this->courts->findById($sel['court_id']);
            if (! $court || $court->status !== 'ACTIVE') {
                return response()->json(['message' => 'Court is not available.'], 422);
            }

            if ($sel['booking_date'] === $today) {
                $start = $slotStartTimes[$sel['time_slot_id']] ?? null;
                if (! is_string($start) || $start === '') {
                    return response()->json(['message' => 'This time slot is no longer available.'], 422);
                }

                try {
                    $slotStart = Carbon::parse($sel['booking_date'] . ' ' . $start, $tz);
                    if ($slotStart->lessThanOrEqualTo($now)) {
                        return response()->json(['message' => 'This time slot is no longer available.'], 422);
                    }
                } catch (\Throwable) {
                    return response()->json(['message' => 'This time slot is no longer available.'], 422);
                }
            }

            if ($this->bookings->isSlotTaken($sel['court_id'], $sel['booking_date'], $sel['time_slot_id'])) {
                return response()->json([
                    'message' => 'One or more selected cells are already booked.',
                    'code' => 'SLOT_TAKEN',
                ], 409);
            }
        }

        $bookedAt = now()->toIso8601ZuluString();

        $rows = array_map(fn (array $sel) => [
            'courtId' => $sel['court_id'],
            'timeSlotId' => $sel['time_slot_id'],
            'createdById' => $user->id,
            'coachId' => null,
            'bookingDate' => $sel['booking_date'],
            'bookingType' => 'TEAM_PRACTICE',
            'bookedAt' => $bookedAt,
        ], $validated['selections']);

        $results = $this->bookings->createMany($rows);

        $createdBookingIds = [];
        foreach ($results as $result) {
            if (! ($result['ok'] ?? false)) {
                foreach ($createdBookingIds as $id) {
                    try {
                        $this->bookings->cancel($id);
                    } catch (\Throwable) {
                    }
                }

                return response()->json([
                    'message' => 'Team practice creation failed. Please try again.',
                ], 409);
            }

            $data = $result['data'] ?? [];
            $bookingKey = $data['booking_insert'] ?? null;

            $bookingId = null;
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
                foreach ($createdBookingIds as $id) {
                    try {
                        $this->bookings->cancel($id);
                    } catch (\Throwable) {
                    }
                }

                return response()->json([
                    'message' => 'Team practice creation failed. Please try again.',
                ], 500);
            }

            $createdBookingIds[] = $bookingId;
        }

        return response()->json([
            'message' => 'Team practice booked successfully.',
            'data' => [
                'booked_at' => $bookedAt,
                'booking_ids' => $createdBookingIds,
            ],
        ], 201);
    }

    public function mine(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'date' => 'required|date_format:Y-m-d',
        ]);

        $firebaseUid = $request->attributes->get('firebase_uid');
        $user = $this->users->findByFirebaseUid($firebaseUid);

        if (! $user) {
            return response()->json(['message' => 'User profile not found.'], 404);
        }

        if ($user->role !== 'COACH' || $user->status !== 'ACTIVE') {
            return response()->json([
                'message' => 'Only active Coaches may view team practices.',
            ], 403);
        }

        $bookings = $this->bookings->getTeamPracticeByCoachAndDate(
            $user->id,
            $validated['date'],
        );

        return response()->json(array_map(fn ($b) => $b->toArray(), $bookings));
    }
}
