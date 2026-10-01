<?php

namespace App\Repositories;

use App\DataTransferObjects\BookingDTO;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class BookingRepository
{
    /** @return BookingDTO[] */
    private function hydrateBookings(iterable $bookingRows): array
    {
        $rows = is_array($bookingRows)
            ? collect($bookingRows)
            : collect($bookingRows);

        if ($rows->isEmpty()) {
            return [];
        }

        $bookingIds = $rows->pluck('id')->map(fn ($v) => (string) $v)->all();

        $userIds = [];
        $courtIds = [];
        $slotIds = [];

        foreach ($rows as $row) {
            $creator = $row->created_by_user_id ?? null;
            $coach = $row->coach_id ?? null;
            $court = $row->court_id ?? null;
            $slot = $row->time_slot_id ?? null;

            if (! empty($creator)) {
                $userIds[(string) $creator] = true;
            }
            if (! empty($coach)) {
                $userIds[(string) $coach] = true;
            }
            if (! empty($court)) {
                $courtIds[(string) $court] = true;
            }
            if (! empty($slot)) {
                $slotIds[(string) $slot] = true;
            }
        }

        $playerRows = DB::table('booking_players')
            ->whereIn('booking_id', $bookingIds)
            ->get();

        foreach ($playerRows as $p) {
            $uid = $p->user_id ?? null;
            if (! empty($uid)) {
                $userIds[(string) $uid] = true;
            }
        }

        $usersById = DB::table('users')
            ->whereIn('id', array_keys($userIds))
            ->get()
            ->keyBy('id');

        $courtsById = DB::table('courts')
            ->whereIn('id', array_keys($courtIds))
            ->get()
            ->keyBy('id');

        $slotsById = DB::table('time_slots')
            ->whereIn('id', array_keys($slotIds))
            ->get()
            ->keyBy('id');

        $playersByBookingId = [];
        foreach ($playerRows as $p) {
            $bookingId = (string) ($p->booking_id ?? '');
            if ($bookingId === '') {
                continue;
            }

            $playerUserId = $p->user_id ?? null;
            $user = $playerUserId ? $usersById->get($playerUserId) : null;

            $playersByBookingId[$bookingId] ??= [];
            $playersByBookingId[$bookingId][] = [
                'id' => (string) $p->id,
                'playerPosition' => (int) ($p->player_position ?? 0),
                'joinedAt' => (string) ($p->joined_at ?? ''),
                'user' => $user
                    ? [
                        'id' => (string) $user->id,
                        'firebaseUid' => (string) ($user->firebase_uid ?? ''),
                        'fullName' => (string) ($user->full_name ?? ''),
                        'email' => (string) ($user->email ?? ''),
                        'role' => (string) ($user->role ?? 'USER'),
                        'status' => (string) ($user->status ?? 'ACTIVE'),
                    ]
                    : null,
            ];
        }

        $bookings = [];
        foreach ($rows as $row) {
            $courtId = $row->court_id ?? null;
            $slotId = $row->time_slot_id ?? null;
            $creatorId = $row->created_by_user_id ?? null;
            $coachId = $row->coach_id ?? null;

            $court = $courtId ? $courtsById->get($courtId) : null;
            $slot = $slotId ? $slotsById->get($slotId) : null;
            $creator = $creatorId ? $usersById->get($creatorId) : null;
            $coach = $coachId ? $usersById->get($coachId) : null;

            $bookings[] = BookingDTO::fromGraphQL([
                'id' => (string) $row->id,
                'bookingDate' => (string) ($row->booking_date ?? ''),
                'bookingType' => (string) ($row->booking_type ?? ''),
                'status' => (string) $row->status,
                'bookedAt' => (string) ($row->booked_at ?? ''),
                'cancelledAt' => $row->cancelled_at ? (string) $row->cancelled_at : null,
                'createdAt' => (string) ($row->created_at ?? ''),
                'court' => $court
                    ? [
                        'id' => (string) $court->id,
                        'name' => (string) ($court->name ?? ''),
                    ]
                    : null,
                'timeSlot' => $slot
                    ? [
                        'id' => (string) $slot->id,
                        'startTime' => (string) ($slot->start_time ?? ''),
                        'endTime' => (string) ($slot->end_time ?? ''),
                        'displayOrder' => (int) ($slot->display_order ?? 0),
                        'isActive' => (bool) ($slot->is_active ?? true),
                    ]
                    : null,
                'createdBy' => $creator
                    ? [
                        'id' => (string) $creator->id,
                        'firebaseUid' => (string) ($creator->firebase_uid ?? ''),
                        'fullName' => (string) ($creator->full_name ?? ''),
                        'email' => (string) ($creator->email ?? ''),
                        'role' => (string) ($creator->role ?? 'USER'),
                        'status' => (string) ($creator->status ?? 'ACTIVE'),
                    ]
                    : null,
                'coach' => $coach
                    ? [
                        'id' => (string) $coach->id,
                        'firebaseUid' => (string) ($coach->firebase_uid ?? ''),
                        'fullName' => (string) ($coach->full_name ?? ''),
                        'email' => (string) ($coach->email ?? ''),
                        'role' => (string) ($coach->role ?? 'COACH'),
                        'status' => (string) ($coach->status ?? 'ACTIVE'),
                    ]
                    : null,
                'bookingPlayers_on_booking' => $playersByBookingId[(string) $row->id] ?? [],
            ]);
        }

        return $bookings;
    }

    /** @return BookingDTO[] */
    public function getForDate(string $bookingDate): array
    {
        $rows = DB::table('bookings')
            ->where('booking_date', $bookingDate)
            ->where('status', 'CONFIRMED')
            ->get();

        return $this->hydrateBookings($rows);
    }

    public function findById(string $id): ?BookingDTO
    {
        $row = DB::table('bookings')
            ->where('id', $id)
            ->first();

        if (! $row) {
            return null;
        }

        return $this->hydrateBookings([$row])[0] ?? null;
    }

    /** @return BookingDTO[] */
    public function getByUser(string $userId, string $status): array
    {
        $rows = DB::table('bookings as b')
            ->where('b.status', $status)
            ->where(function ($q) use ($userId) {
                $q->where('b.created_by_user_id', $userId)
                    ->orWhereExists(function ($sub) use ($userId) {
                        $sub->select(DB::raw(1))
                            ->from('booking_players as bp')
                            ->whereColumn('bp.booking_id', 'b.id')
                            ->where('bp.user_id', $userId);
                    });
            })
            ->get();

        return $this->hydrateBookings($rows);
    }

    /**
     * Insert a new booking. Returns the raw GraphQL response data
     * (including the generated key).
     */
    public function create(array $params): array
    {
        $id = $params['id'] ?? Str::uuid()->toString();
        $now = now();

        DB::table('bookings')->insert([
            'id'            => $id,
            'court_id'      => $params['courtId'],
            'time_slot_id'  => $params['timeSlotId'],
            'created_by_user_id' => $params['createdById'],
            'coach_id'      => $params['coachId'] ?? null,
            'booking_date'  => $params['bookingDate'],
            'booking_type'  => $params['bookingType'],
            'status'        => $params['status'] ?? 'CONFIRMED',
            'booked_at'     => $params['bookedAt'] ?? $now,
            'cancelled_at'  => null,
            'created_at'    => $now,
            'updated_at'    => $now,
        ]);

        return [
            'booking_insert' => [
                'id' => $id,
            ],
        ];
    }

    public function createMany(array $rows): array
    {
        $results = [];

        foreach ($rows as $row) {
            try {
                $data = $this->create($row);
                $results[] = [
                    'ok' => true,
                    'data' => $data,
                ];
            } catch (\Throwable $exception) {
                $results[] = [
                    'ok' => false,
                    'error' => $exception->getMessage(),
                ];
            }
        }

        return $results;
    }

    public function cancel(string $id): void
    {
        DB::table('bookings')
            ->where('id', $id)
            ->update([
                'status' => 'CANCELLED',
                'cancelled_at' => now(),
                'updated_at' => now(),
            ]);
    }

    /**
     * Check whether a CONFIRMED booking already exists for the given
     * court + date + time-slot cell.
     */
    public function isSlotTaken(string $courtId, string $bookingDate, string $timeSlotId): bool
    {
        return DB::table('bookings')
            ->where('booking_date', $bookingDate)
            ->where('status', 'CONFIRMED')
            ->where('court_id', $courtId)
            ->where('time_slot_id', $timeSlotId)
            ->limit(1)
            ->exists();
    }

    public function userHasBookingForSlot(string $userId, string $bookingDate, string $timeSlotId): bool
    {
        return DB::table('bookings as b')
            ->where('b.booking_date', $bookingDate)
            ->where('b.time_slot_id', $timeSlotId)
            ->where('b.status', 'CONFIRMED')
            ->where(function ($q) use ($userId) {
                $q->where('b.created_by_user_id', $userId)
                    ->orWhereExists(function ($sub) use ($userId) {
                        $sub->select(DB::raw(1))
                            ->from('booking_players as bp')
                            ->whereColumn('bp.booking_id', 'b.id')
                            ->where('bp.user_id', $userId);
                    });
            })
            ->limit(1)
            ->exists();
    }

    public function coachHasBookingForSlot(string $coachId, string $bookingDate, string $timeSlotId): bool
    {
        return DB::table('bookings as b')
            ->where('b.booking_date', $bookingDate)
            ->where('b.time_slot_id', $timeSlotId)
            ->where('b.status', 'CONFIRMED')
            ->where('b.coach_id', $coachId)
            ->limit(1)
            ->exists();
    }

    /**
    * get confirmed private-coaching bookings assigned to one Coach
    *
    * @return BookingDTO[]
    */
    public function getConfirmedByCoach(string $coachId): array
    {
        $rows = DB::table('bookings')
            ->where('coach_id', $coachId)
            ->where('booking_type', 'PRIVATE_COACH')
            ->where('status', 'CONFIRMED')
            ->get();

        return $this->hydrateBookings($rows);
    }
    public function getTeamPracticeByCoachAndDate(string $coachId, string $bookingDate): array
    {
        $rows = DB::table('bookings')
            ->where('created_by_user_id', $coachId)
            ->where('booking_date', $bookingDate)
            ->where('booking_type', 'TEAM_PRACTICE')
            ->where('status', 'CONFIRMED')
            ->get();

        return $this->hydrateBookings($rows);
    }
}
