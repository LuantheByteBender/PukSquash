<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Repositories\AvailabilityRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AvailabilityController extends Controller
{
    public function __construct(
        private AvailabilityRepository $availability,
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
        $snapshot = $this->availability->getForDate($date);
        $courts  = $snapshot['courts'];
        $slots   = $snapshot['timeSlots'];
        $booked  = $snapshot['bookings'];

        $firebaseUid = $request->attributes->get('firebase_uid');

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
                    if ($court->status !== 'ACTIVE') {
                        $cells[] = [
                            'court_id'     => $court->id,
                            'time_slot_id' => $slot->id,
                            'state'        => 'UNAVAILABLE',
                            'label'        => 'Inactive',
                            'can_book'     => false,
                            'can_join'     => false,
                            'is_mine'      => false,
                            'booking_id'   => null,
                        ];
                        continue;
                    }

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

                $playersSorted = $booking->players;
                usort($playersSorted, fn ($a, $b) => $a->playerPosition <=> $b->playerPosition);
                $playersPayload = array_map(fn ($p) => $p->toArray(), $playersSorted);

                $playerCount = count($playersPayload);
                $creatorName = $booking->createdBy['fullName']
                    ?? $booking->createdBy['name']
                    ?? null;

                $nameA = $playersPayload[0]['name'] ?? $creatorName;
                $nameB = $playersPayload[1]['name'] ?? null;

                $coach = $booking->coach;
                $coachName = is_array($coach)
                    ? ($coach['name'] ?? $coach['fullName'] ?? null)
                    : null;

                $state = match (true) {
                    $booking->bookingType === 'PRIVATE_COACH'           => 'PRIVATE_COACH',
                    $booking->bookingType === 'LOCKED'                  => 'LOCKED',
                    $booking->bookingType === 'OPEN' && $playerCount < 2 => 'OPEN',
                    default                                             => 'FULL',
                };

                $label = match ($state) {
                    'OPEN'          => $nameA ? ($nameA . ' vs ?') : 'Open',
                    'FULL'          => ($nameA && $nameB) ? ($nameA . ' vs ' . $nameB) : ($nameA ?: 'Full'),
                    'LOCKED'        => $nameA ?: 'Locked',
                    'PRIVATE_COACH' => ($nameA && $coachName)
                        ? ($nameA . ' / Coach ' . $coachName)
                        : ($nameA ?: ($coachName ? ('Coach ' . $coachName) : 'Coach')),
                    default         => $nameA ?: $state,
                };

                $isMine = false;
                if ($firebaseUid) {
                    if (($booking->createdBy['firebaseUid'] ?? null) === $firebaseUid) {
                        $isMine = true;
                    } else {
                        foreach ($booking->players as $p) {
                            if (($p->user['firebaseUid'] ?? null) === $firebaseUid) {
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
                    'players'      => $playersPayload,
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
