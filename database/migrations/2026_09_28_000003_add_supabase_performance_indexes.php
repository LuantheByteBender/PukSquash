<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private function firstExistingColumn(string $table, array $candidates): ?string
    {
        foreach ($candidates as $column) {
            if (Schema::hasColumn($table, $column)) {
                return $column;
            }
        }

        return null;
    }

    private function createIndexIfPossible(string $table, array $columns, string $indexName): void
    {
        if (! Schema::hasTable($table)) {
            return;
        }

        foreach ($columns as $column) {
            if (! is_string($column) || $column === '' || ! Schema::hasColumn($table, $column)) {
                return;
            }
        }

        $quotedColumns = implode(', ', array_map(
            static fn (string $col) => '"' . str_replace('"', '""', $col) . '"',
            $columns,
        ));

        DB::statement(sprintf(
            'CREATE INDEX IF NOT EXISTS %s ON %s (%s)',
            $indexName,
            $table,
            $quotedColumns,
        ));
    }

    public function up(): void
    {
        $usersFirebaseUid = $this->firstExistingColumn('users', ['firebase_uid', 'firebaseUid', 'firebaseuid']);
        if ($usersFirebaseUid) {
            $this->createIndexIfPossible('users', [$usersFirebaseUid], 'users_firebase_uid_idx');
        }

        $bookingDate = $this->firstExistingColumn('bookings', ['booking_date', 'bookingDate', 'bookingdate']);
        $status = $this->firstExistingColumn('bookings', ['status']);
        $timeSlotId = $this->firstExistingColumn('bookings', ['time_slot_id', 'timeSlotId', 'timeslotid', 'time_slot', 'timeslot_id']);
        $courtId = $this->firstExistingColumn('bookings', ['court_id', 'courtId', 'courtid', 'court']);
        $createdById = $this->firstExistingColumn('bookings', ['created_by_user_id', 'created_by_id', 'createdById', 'createdbyid', 'created_by', 'createdBy']);
        $coachId = $this->firstExistingColumn('bookings', ['coach_id', 'coachId', 'coachid', 'coach']);

        if ($bookingDate && $status) {
            $this->createIndexIfPossible('bookings', [$bookingDate, $status], 'bookings_booking_date_status_idx');
        }
        if ($bookingDate && $timeSlotId && $status) {
            $this->createIndexIfPossible('bookings', [$bookingDate, $timeSlotId, $status], 'bookings_booking_date_slot_status_idx');
        }
        if ($bookingDate && $courtId && $timeSlotId && $status) {
            $this->createIndexIfPossible('bookings', [$bookingDate, $courtId, $timeSlotId, $status], 'bookings_cell_status_idx');
        }
        if ($createdById && $status) {
            $this->createIndexIfPossible('bookings', [$createdById, $status], 'bookings_created_by_status_idx');
        }
        if ($coachId && $bookingDate && $status) {
            $this->createIndexIfPossible('bookings', [$coachId, $bookingDate, $status], 'bookings_coach_date_status_idx');
        }

        $bookingPlayersBookingId = $this->firstExistingColumn('booking_players', ['booking_id', 'bookingId', 'bookingid']);
        $bookingPlayersUserId = $this->firstExistingColumn('booking_players', ['user_id', 'userId', 'userid']);
        if ($bookingPlayersBookingId) {
            $this->createIndexIfPossible('booking_players', [$bookingPlayersBookingId], 'booking_players_booking_id_idx');
        }
        if ($bookingPlayersUserId) {
            $this->createIndexIfPossible('booking_players', [$bookingPlayersUserId], 'booking_players_user_id_idx');
        }

        $timeSlotsIsActive = $this->firstExistingColumn('time_slots', ['is_active', 'isActive', 'isactive']);
        $timeSlotsDisplayOrder = $this->firstExistingColumn('time_slots', ['display_order', 'displayOrder', 'displayorder']);
        if ($timeSlotsIsActive && $timeSlotsDisplayOrder) {
            $this->createIndexIfPossible('time_slots', [$timeSlotsIsActive, $timeSlotsDisplayOrder], 'time_slots_active_order_idx');
        }

        $courtsStatus = $this->firstExistingColumn('courts', ['status']);
        $courtsName = $this->firstExistingColumn('courts', ['name']);
        if ($courtsStatus && $courtsName) {
            $this->createIndexIfPossible('courts', [$courtsStatus, $courtsName], 'courts_status_name_idx');
        }

        $coachAvailabilityTable = Schema::hasTable('coach_availability')
            ? 'coach_availability'
            : (Schema::hasTable('coach_availabilities') ? 'coach_availabilities' : null);

        if ($coachAvailabilityTable) {
            $availabilityDate = $this->firstExistingColumn($coachAvailabilityTable, ['availability_date', 'availabilityDate', 'availabilitydate']);
            $caTimeSlotId = $this->firstExistingColumn($coachAvailabilityTable, ['time_slot_id', 'timeSlotId', 'timeslotid', 'time_slot', 'timeslot_id']);
            $caCoachId = $this->firstExistingColumn($coachAvailabilityTable, ['coach_id', 'coachId', 'coachid', 'coach']);

            if ($availabilityDate && $caTimeSlotId) {
                $this->createIndexIfPossible($coachAvailabilityTable, [$availabilityDate, $caTimeSlotId], 'coach_availabilities_date_slot_idx');
            }
            if ($caCoachId && $availabilityDate) {
                $this->createIndexIfPossible($coachAvailabilityTable, [$caCoachId, $availabilityDate], 'coach_availabilities_coach_date_idx');
            }
        }
    }

    public function down(): void
    {
        DB::statement('DROP INDEX IF EXISTS users_firebase_uid_idx');

        DB::statement('DROP INDEX IF EXISTS bookings_booking_date_status_idx');
        DB::statement('DROP INDEX IF EXISTS bookings_booking_date_slot_status_idx');
        DB::statement('DROP INDEX IF EXISTS bookings_cell_status_idx');
        DB::statement('DROP INDEX IF EXISTS bookings_created_by_status_idx');
        DB::statement('DROP INDEX IF EXISTS bookings_coach_date_status_idx');

        DB::statement('DROP INDEX IF EXISTS booking_players_booking_id_idx');
        DB::statement('DROP INDEX IF EXISTS booking_players_user_id_idx');

        DB::statement('DROP INDEX IF EXISTS time_slots_active_order_idx');

        DB::statement('DROP INDEX IF EXISTS courts_status_name_idx');

        DB::statement('DROP INDEX IF EXISTS coach_availabilities_date_slot_idx');
        DB::statement('DROP INDEX IF EXISTS coach_availabilities_coach_date_idx');
    }
};
