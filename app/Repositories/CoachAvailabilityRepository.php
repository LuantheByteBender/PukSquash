<?php

namespace App\Repositories;

use App\DataTransferObjects\CoachAvailabilityDTO;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CoachAvailabilityRepository
{
    private string $table = 'coach_availability';

    /** @return CoachAvailabilityDTO[] */
    public function getAvailableCoaches(string $availabilityDate, string $timeSlotId): array
    {
        $rows = DB::table($this->table . ' as ca')
            ->leftJoin('users as u', 'u.id', '=', 'ca.coach_id')
            ->leftJoin('time_slots as ts', 'ts.id', '=', 'ca.time_slot_id')
            ->where('ca.availability_date', $availabilityDate)
            ->where('ca.time_slot_id', $timeSlotId)
            ->select([
                'ca.availability_date',
                'ca.created_at',
                'ca.coach_id',
                'ca.time_slot_id',
                'u.id as coach_user_id',
                'u.full_name as coach_full_name',
                'u.email as coach_email',
                'u.role as coach_role',
                'u.status as coach_status',
                Schema::hasColumn('users', 'private_coaching_fee_cents')
                    ? 'u.private_coaching_fee_cents as coach_private_coaching_fee_cents'
                    : DB::raw('0 as coach_private_coaching_fee_cents'),
                'ts.id as ts_id',
                'ts.start_time as ts_start_time',
                'ts.end_time as ts_end_time',
                'ts.display_order as ts_display_order',
                'ts.is_active as ts_is_active',
            ])
            ->get();

        return array_map(function (object $row) {
            return CoachAvailabilityDTO::fromGraphQL([
                'availabilityDate' => (string) ($row->availability_date ?? ''),
                'createdAt' => (string) ($row->created_at ?? ''),
                'coachId' => (string) ($row->coach_id ?? ''),
                'timeSlotId' => (string) ($row->time_slot_id ?? ''),
                'coach' => ! empty($row->coach_user_id)
                    ? [
                        'id' => (string) $row->coach_user_id,
                        'fullName' => (string) ($row->coach_full_name ?? ''),
                        'email' => (string) ($row->coach_email ?? ''),
                        'role' => (string) ($row->coach_role ?? ''),
                        'status' => (string) ($row->coach_status ?? ''),
                        'privateCoachingFeeCents' => (int) ($row->coach_private_coaching_fee_cents ?? 0),
                    ]
                    : null,
                'timeSlot' => ! empty($row->ts_id)
                    ? [
                        'id' => (string) $row->ts_id,
                        'startTime' => (string) ($row->ts_start_time ?? ''),
                        'endTime' => (string) ($row->ts_end_time ?? ''),
                        'displayOrder' => (int) ($row->ts_display_order ?? 0),
                        'isActive' => (bool) ($row->ts_is_active ?? false),
                    ]
                    : null,
            ]);
        }, $rows->all());
    }

    public function create(string $coachId, string $timeSlotId, string $availabilityDate): void
    {
        DB::table($this->table)->insert([
            'coach_id' => $coachId,
            'time_slot_id' => $timeSlotId,
            'availability_date' => $availabilityDate,
            'created_at' => now(),
        ]);
    }

    public function createMany(array $rows): array
    {
        $results = [];

        foreach ($rows as $row) {
            try {
                $this->create(
                    $row['coachId'],
                    $row['timeSlotId'],
                    $row['availabilityDate'],
                );

                $results[] = [
                    'ok' => true,
                    'data' => null,
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

    public function isCoachAvailable(string $availabilityDate, string $timeSlotId, string $coachId): bool
    {
        return DB::table($this->table)
            ->where('availability_date', $availabilityDate)
            ->where('time_slot_id', $timeSlotId)
            ->where('coach_id', $coachId)
            ->limit(1)
            ->exists();
    }

    /**
     * get one Coach's published time slots for a selected date
     *
     * @return CoachAvailabilityDTO[]
     */
    public function getForCoachAndDate(
        string $coachId,
        string $availabilityDate,
    ): array {
        $rows = DB::table($this->table . ' as ca')
            ->leftJoin('users as u', 'u.id', '=', 'ca.coach_id')
            ->leftJoin('time_slots as ts', 'ts.id', '=', 'ca.time_slot_id')
            ->where('ca.coach_id', $coachId)
            ->where('ca.availability_date', $availabilityDate)
            ->select([
                'ca.availability_date',
                'ca.created_at',
                'ca.coach_id',
                'ca.time_slot_id',
                'u.id as coach_user_id',
                'u.full_name as coach_full_name',
                'u.email as coach_email',
                'u.role as coach_role',
                'u.status as coach_status',
                Schema::hasColumn('users', 'private_coaching_fee_cents')
                    ? 'u.private_coaching_fee_cents as coach_private_coaching_fee_cents'
                    : DB::raw('0 as coach_private_coaching_fee_cents'),
                'ts.id as ts_id',
                'ts.start_time as ts_start_time',
                'ts.end_time as ts_end_time',
                'ts.display_order as ts_display_order',
                'ts.is_active as ts_is_active',
            ])
            ->get();

        return array_map(function (object $row) {
            return CoachAvailabilityDTO::fromGraphQL([
                'availabilityDate' => (string) ($row->availability_date ?? ''),
                'createdAt' => (string) ($row->created_at ?? ''),
                'coachId' => (string) ($row->coach_id ?? ''),
                'timeSlotId' => (string) ($row->time_slot_id ?? ''),
                'coach' => ! empty($row->coach_user_id)
                    ? [
                        'id' => (string) $row->coach_user_id,
                        'fullName' => (string) ($row->coach_full_name ?? ''),
                        'email' => (string) ($row->coach_email ?? ''),
                        'role' => (string) ($row->coach_role ?? ''),
                        'status' => (string) ($row->coach_status ?? ''),
                        'privateCoachingFeeCents' => (int) ($row->coach_private_coaching_fee_cents ?? 0),
                    ]
                    : null,
                'timeSlot' => ! empty($row->ts_id)
                    ? [
                        'id' => (string) $row->ts_id,
                        'startTime' => (string) ($row->ts_start_time ?? ''),
                        'endTime' => (string) ($row->ts_end_time ?? ''),
                        'displayOrder' => (int) ($row->ts_display_order ?? 0),
                        'isActive' => (bool) ($row->ts_is_active ?? false),
                    ]
                    : null,
            ]);
        }, $rows->all());
    }

    /**
    * Get all published Coach availability for the Notice Board
    * on a selected date.
    *
    * @return CoachAvailabilityDTO[]
    */
    public function getNoticeBoardForDate(string $availabilityDate): array
    {
        $rows = DB::table($this->table . ' as ca')
            ->leftJoin('users as u', 'u.id', '=', 'ca.coach_id')
            ->leftJoin('time_slots as ts', 'ts.id', '=', 'ca.time_slot_id')
            ->where('ca.availability_date', $availabilityDate)
            ->select([
                'ca.availability_date',
                'ca.created_at',
                'ca.coach_id',
                'ca.time_slot_id',
                'u.id as coach_user_id',
                'u.full_name as coach_full_name',
                'u.role as coach_role',
                'u.status as coach_status',
                Schema::hasColumn('users', 'private_coaching_fee_cents')
                    ? 'u.private_coaching_fee_cents as coach_private_coaching_fee_cents'
                    : DB::raw('0 as coach_private_coaching_fee_cents'),
                'ts.id as ts_id',
                'ts.start_time as ts_start_time',
                'ts.end_time as ts_end_time',
                'ts.display_order as ts_display_order',
                'ts.is_active as ts_is_active',
            ])
            ->get();

        return array_map(function (object $row) {
            return CoachAvailabilityDTO::fromGraphQL([
                'availabilityDate' => (string) ($row->availability_date ?? ''),
                'createdAt' => (string) ($row->created_at ?? ''),
                'coachId' => (string) ($row->coach_id ?? ''),
                'timeSlotId' => (string) ($row->time_slot_id ?? ''),
                'coach' => ! empty($row->coach_user_id)
                    ? [
                        'id' => (string) $row->coach_user_id,
                        'fullName' => (string) ($row->coach_full_name ?? ''),
                        'role' => (string) ($row->coach_role ?? ''),
                        'status' => (string) ($row->coach_status ?? ''),
                        'privateCoachingFeeCents' => (int) ($row->coach_private_coaching_fee_cents ?? 0),
                    ]
                    : null,
                'timeSlot' => ! empty($row->ts_id)
                    ? [
                        'id' => (string) $row->ts_id,
                        'startTime' => (string) ($row->ts_start_time ?? ''),
                        'endTime' => (string) ($row->ts_end_time ?? ''),
                        'displayOrder' => (int) ($row->ts_display_order ?? 0),
                        'isActive' => (bool) ($row->ts_is_active ?? false),
                    ]
                    : null,
            ]);
        }, $rows->all());
    }

    /**
     * remove one Coach's published availability using its composite key
     */
    public function deleteForCoachAndSlot(
        string $coachId,
        string $timeSlotId,
        string $availabilityDate,
    ): void {
        DB::table($this->table)
            ->where('coach_id', $coachId)
            ->where('time_slot_id', $timeSlotId)
            ->where('availability_date', $availabilityDate)
            ->delete();
    }

    /**
     * check whether a confirmed booking uses this coach's selected slot
     */
    public function hasConfirmedBooking(
        string $coachId,
        string $availabilityDate,
        string $timeSlotId,
    ): bool {
        return DB::table('bookings')
            ->where('coach_id', $coachId)
            ->where('booking_date', $availabilityDate)
            ->where('time_slot_id', $timeSlotId)
            ->where('status', 'CONFIRMED')
            ->limit(1)
            ->exists();
    }

    public function listConfirmedBookingTimeSlotIdsForDate(
        string $coachId,
        string $availabilityDate,
    ): array {
        return DB::table('bookings')
            ->where('coach_id', $coachId)
            ->where('booking_date', $availabilityDate)
            ->where('status', 'CONFIRMED')
            ->whereNotNull('time_slot_id')
            ->distinct()
            ->pluck('time_slot_id')
            ->map(fn ($v) => (string) $v)
            ->all();
    }
}
