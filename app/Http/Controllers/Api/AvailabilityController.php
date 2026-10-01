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

        $firebaseUid = $request->attributes->get('firebase_uid');

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
                $slotStart = \Illuminate\Support\Carbon::parse($date . ' ' . $start, $tz);
                if ($slotStart->lessThanOrEqualTo($now)) {
                    $pastSlotIds[$slot->id] = true;
                }
            } catch (\Throwable) {
                // If parsing fails, do not block the slot here.
            }
        }

        $userBusySlotIds = [];
        if ($firebaseUid) {
            foreach ($booked as $b) {
                $slotId = $b->timeSlot['id'] ?? null;
                if (! is_string($slotId) || $slotId === '') {
                    continue;
                }

                $busy = ($b->createdBy['firebaseUid'] ?? null) === $firebaseUid;

                if (! $busy) {
                    foreach ($b->players as $p) {
                        if (($p->user['firebaseUid'] ?? null) === $firebaseUid) {
                            $busy = true;
                            break;
                        }
                    }
                }

                if ($busy) {
                    $userBusySlotIds[$slotId] = true;
                }
            }
        }

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

                $slotIsPast = isset($pastSlotIds[$slot->id]);
                $userBusy = isset($userBusySlotIds[$slot->id]);

                if (! $booking) {
                    if ($slotIsPast) {
                        $cells[] = [
                            'court_id'     => $court->id,
                            'time_slot_id' => $slot->id,
                            'state'        => 'UNAVAILABLE',
                            'label'        => '—',
                            'can_book'     => false,
                            'can_join'     => false,
                            'is_mine'      => false,
                            'booking_id'   => null,
                        ];
                        continue;
                    }

                    if ($userBusy) {
                        $cells[] = [
                            'court_id'     => $court->id,
                            'time_slot_id' => $slot->id,
                            'state'        => 'UNAVAILABLE',
                            'label'        => 'Unavailable',
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
                    $booking->bookingType === 'TEAM_PRACTICE'            => 'LOCKED',
                    $booking->bookingType === 'PRIVATE_COACH'           => 'PRIVATE_COACH',
                    $booking->bookingType === 'LOCKED'                  => 'LOCKED',
                    $booking->bookingType === 'OPEN' && $playerCount < 2 => 'OPEN',
                    default                                             => 'FULL',
                };

                $label = match (true) {
                    $booking->bookingType === 'TEAM_PRACTICE' => 'NWU Team',
                    default => match ($state) {
                    'OPEN'          => $nameA ? ($nameA . ' vs ?') : 'Open',
                    'FULL'          => ($nameA && $nameB) ? ($nameA . ' vs ' . $nameB) : ($nameA ?: 'Full'),
                    'LOCKED'        => $nameA ?: 'Locked',
                    'PRIVATE_COACH' => ($nameA && $coachName)
                        ? ($nameA . ' / Coach ' . $coachName)
                        : ($nameA ?: ($coachName ? ('Coach ' . $coachName) : 'Coach')),
                    default         => $nameA ?: $state,
                    },
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

                $canJoin = $state === 'OPEN' && ! $isMine && ! $slotIsPast && ! $userBusy;

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
