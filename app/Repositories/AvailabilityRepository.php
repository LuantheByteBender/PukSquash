<?php

namespace App\Repositories;

use App\DataTransferObjects\BookingDTO;
use App\DataTransferObjects\CourtDTO;
use App\DataTransferObjects\TimeSlotDTO;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class AvailabilityRepository
{
    public function getForDate(string $bookingDate): array
    {
        $courts = Cache::remember('availability:courts:active', now()->addMinutes(5), function () {
            return array_map(
                fn (object $row) => new CourtDTO(
                    id:          (string) $row->id,
                    name:        (string) ($row->name ?? ''),
                    description: $row->description ?? null,
                    status:      (string) ($row->status ?? 'ACTIVE'),
                    createdAt:   (string) ($row->created_at ?? ''),
                    updatedAt:   (string) ($row->updated_at ?? ''),
                ),
                DB::table('courts')
                    ->where('status', 'ACTIVE')
                    ->orderBy('name')
                    ->get()
                    ->all(),
            );
        });

        $timeSlots = Cache::remember('availability:time_slots:active', now()->addMinutes(5), function () {
            return array_map(
                fn (object $row) => new TimeSlotDTO(
                    id:           (string) $row->id,
                    startTime:    (string) ($row->start_time ?? ''),
                    endTime:      (string) ($row->end_time ?? ''),
                    displayOrder: (int) ($row->display_order ?? 0),
                    isActive:     (bool) ($row->is_active ?? true),
                ),
                DB::table('time_slots')
                    ->where('is_active', true)
                    ->orderBy('display_order')
                    ->get()
                    ->all(),
            );
        });

        $bookingRows = DB::table('bookings as b')
            ->leftJoin('courts as c', 'c.id', '=', 'b.court_id')
            ->leftJoin('time_slots as ts', 'ts.id', '=', 'b.time_slot_id')
            ->leftJoin('users as u_creator', 'u_creator.id', '=', 'b.created_by_user_id')
            ->leftJoin('users as u_coach', 'u_coach.id', '=', 'b.coach_id')
            ->where('b.booking_date', $bookingDate)
            ->where('b.status', 'CONFIRMED')
            ->select([
                'b.id',
                'b.booking_date',
                'b.booking_type',
                'b.status',
                'b.booked_at',
                'b.cancelled_at',
                'b.created_at',
                'b.court_id',
                'b.time_slot_id',
                'b.created_by_user_id',
                'b.coach_id',
                'c.name as court_name',
                'ts.start_time as slot_start_time',
                'ts.end_time as slot_end_time',
                'ts.display_order as slot_display_order',
                'ts.is_active as slot_is_active',
                'u_creator.firebase_uid as creator_firebase_uid',
                'u_creator.full_name as creator_full_name',
                'u_creator.email as creator_email',
                'u_creator.role as creator_role',
                'u_creator.status as creator_status',
                'u_coach.firebase_uid as coach_firebase_uid',
                'u_coach.full_name as coach_full_name',
                'u_coach.email as coach_email',
                'u_coach.role as coach_role',
                'u_coach.status as coach_status',
            ])
            ->get();

        if ($bookingRows->isEmpty()) {
            return [
                'courts'    => $courts,
                'timeSlots' => $timeSlots,
                'bookings'  => [],
            ];
        }

        $bookingIds = $bookingRows->pluck('id')->map(fn ($v) => (string) $v)->all();

        $playerRows = DB::table('booking_players as bp')
            ->leftJoin('users as u', 'u.id', '=', 'bp.user_id')
            ->whereIn('bp.booking_id', $bookingIds)
            ->select([
                'bp.id',
                'bp.booking_id',
                'bp.player_position',
                'bp.joined_at',
                'u.id as user_id',
                'u.firebase_uid',
                'u.student_number',
                'u.full_name',
                'u.email',
                'u.role',
                'u.status',
            ])
            ->get();

        $playersByBookingId = [];
        foreach ($playerRows as $p) {
            $bid = (string) ($p->booking_id ?? '');
            if ($bid === '') {
                continue;
            }
            $playersByBookingId[$bid] ??= [];
            $playersByBookingId[$bid][] = [
                'id' => (string) $p->id,
                'playerPosition' => (int) ($p->player_position ?? 0),
                'joinedAt' => (string) ($p->joined_at ?? ''),
                'user' => ! empty($p->user_id)
                    ? [
                        'id' => (string) $p->user_id,
                        'firebaseUid' => (string) ($p->firebase_uid ?? ''),
                        'studentNumber' => $p->student_number,
                        'fullName' => (string) ($p->full_name ?? ''),
                        'email' => (string) ($p->email ?? ''),
                        'role' => (string) ($p->role ?? 'USER'),
                        'status' => (string) ($p->status ?? 'ACTIVE'),
                    ]
                    : null,
            ];
        }

        $bookings = [];
        foreach ($bookingRows as $row) {
            $bookingId = (string) $row->id;
            $courtId = (string) ($row->court_id ?? '');
            $slotId = (string) ($row->time_slot_id ?? '');
            $creatorId = (string) ($row->created_by_user_id ?? '');
            $coachId = (string) ($row->coach_id ?? '');

            $bookings[] = BookingDTO::fromGraphQL([
                'id' => $bookingId,
                'bookingDate' => (string) ($row->booking_date ?? ''),
                'bookingType' => (string) ($row->booking_type ?? ''),
                'status' => (string) $row->status,
                'bookedAt' => (string) ($row->booked_at ?? ''),
                'cancelledAt' => $row->cancelled_at ? (string) ($row->cancelled_at) : null,
                'createdAt' => (string) ($row->created_at ?? ''),
                'court' => $courtId !== ''
                    ? [
                        'id' => $courtId,
                        'name' => (string) ($row->court_name ?? ''),
                    ]
                    : null,
                'timeSlot' => $slotId !== ''
                    ? [
                        'id' => $slotId,
                        'startTime' => (string) ($row->slot_start_time ?? ''),
                        'endTime' => (string) ($row->slot_end_time ?? ''),
                        'displayOrder' => (int) ($row->slot_display_order ?? 0),
                        'isActive' => (bool) ($row->slot_is_active ?? true),
                    ]
                    : null,
                'createdBy' => $creatorId !== ''
                    ? [
                        'id' => $creatorId,
                        'firebaseUid' => (string) ($row->creator_firebase_uid ?? ''),
                        'fullName' => (string) ($row->creator_full_name ?? ''),
                        'email' => (string) ($row->creator_email ?? ''),
                        'role' => (string) ($row->creator_role ?? 'USER'),
                        'status' => (string) ($row->creator_status ?? 'ACTIVE'),
                    ]
                    : null,
                'coach' => $coachId !== ''
                    ? [
                        'id' => $coachId,
                        'firebaseUid' => (string) ($row->coach_firebase_uid ?? ''),
                        'fullName' => (string) ($row->coach_full_name ?? ''),
                        'email' => (string) ($row->coach_email ?? ''),
                        'role' => (string) ($row->coach_role ?? 'COACH'),
                        'status' => (string) ($row->coach_status ?? 'ACTIVE'),
                    ]
                    : null,
                'bookingPlayers_on_booking' => $playersByBookingId[$bookingId] ?? [],
            ]);
        }

        return [
            'courts'    => $courts,
            'timeSlots' => $timeSlots,
            'bookings'  => $bookings,
        ];
    }
}
