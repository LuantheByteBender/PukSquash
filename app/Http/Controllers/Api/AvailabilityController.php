<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Repositories\BookingRepository;
use App\Repositories\CourtRepository;
use App\Repositories\TimeSlotRepository;
use App\Repositories\UserRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AvailabilityController extends Controller
{
    public function __construct(
        private CourtRepository    $courts,
        private TimeSlotRepository $timeSlots,
        private BookingRepository  $bookings,
        private UserRepository     $users,
    ) {}

    /**
     * GET /api/availability?date=YYYY-MM-DD
     *
     * Build the court × time-slot grid for a single date.
     * Each cell carries its state so the frontend never has to derive it.
     */
    public function index(Request $request): JsonResponse
    {
        $request->validate(['date' => 'required|date_format:Y-m-d']);

        $date    = $request->query('date');
        $courts  = $this->courts->listActive();
        $slots   = $this->timeSlots->listActive();
        $booked  = $this->bookings->getForDate($date);

        $firebaseUid = $request->attributes->get('firebase_uid');
        $actingUser = $firebaseUid
            ? $this->users->findByFirebaseUid($firebaseUid)
            : null;

        // Index confirmed bookings by "courtId:timeSlotId" for O(1) lookups.
        $index = [];
        foreach ($booked as $b) {
            $key = ($b->court['id'] ?? '') . ':' . ($b->timeSlot['id'] ?? '');
            $index[$key] = $b;
        }

        $courtsPayload = array_map(fn ($c) => $c->toArray(), $courts);
        $slotsPayload  = array_map(fn ($s) => $s->toArray(), $slots);

        $cells = [];
        foreach ($courts as $court) {
            foreach ($slots as $slot) {
                $key     = $court->id . ':' . $slot->id;
                $booking = $index[$key] ?? null;

                if (! $booking) {
                    $cells[] = [
                        'court_id'     => $court->id,
                        'time_slot_id' => $slot->id,
                        'state'        => 'AVAILABLE',
                        'label'        => 'Available',
                        'can_book'     => true,
                        'can_join'     => false,
                        'is_mine'      => false,
                        'booking_id'   => null,
                    ];
                    continue;
                }

                $playerCount = count($booking->players);

                $state = match (true) {
                    $booking->bookingType === 'PRIVATE_COACH'           => 'PRIVATE_COACH',
                    $booking->bookingType === 'LOCKED'                  => 'LOCKED',
                    $booking->bookingType === 'OPEN' && $playerCount < 2 => 'OPEN',
                    default                                             => 'FULL',
                };

                $label = match ($state) {
                    'OPEN'          => 'Open',
                    'FULL'          => 'Full',
                    'LOCKED'        => 'Locked',
                    'PRIVATE_COACH' => 'Coach',
                    default         => $state,
                };

                $isMine = false;
                if ($actingUser) {
                    if (($booking->createdBy['id'] ?? null) === $actingUser->id) {
                        $isMine = true;
                    } else {
                        foreach ($booking->players as $p) {
                            if (($p->toArray()['user_id'] ?? null) === $actingUser->id) {
                                $isMine = true;
                                break;
                            }
                        }
                    }
                }

                $canJoin = $state === 'OPEN' && ! $isMine;

                $cells[] = [
                    'court_id'     => $court->id,
                    'time_slot_id' => $slot->id,
                    'state'        => $state,
                    'label'        => $label,
                    'can_book'     => false,
                    'can_join'     => $canJoin,
                    'is_mine'      => $isMine,
                    'booking_id'   => $canJoin ? $booking->id : null,
                    'players'      => array_map(fn ($p) => $p->toArray(), $booking->players),
                ];
            }
        }

        return response()->json([
            'booking_window_days' => 14,
            'courts'              => $courtsPayload,
            'time_slots'          => $slotsPayload,
            'cells'               => $cells,
        ]);
    }
}
