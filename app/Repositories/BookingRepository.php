<?php

namespace App\Repositories;

use App\DataTransferObjects\BookingDTO;
use App\Services\SqlConnectClient;

class BookingRepository
{
    public function __construct(
        private SqlConnectClient $client,
    ) {}

    /** @return BookingDTO[] */
    public function getForDate(string $bookingDate): array
    {
        $data = $this->client->execute('GetBookingsForDate', [
            'bookingDate' => $bookingDate,
        ]);

        return array_map(
            fn (array $row) => BookingDTO::fromGraphQL($row),
            $data['bookings'] ?? [],
        );
    }

    public function findById(string $id): ?BookingDTO
    {
        $data = $this->client->execute('GetBookingById', ['id' => $id]);

        if (empty($data['booking'])) {
            return null;
        }

        return BookingDTO::fromGraphQL($data['booking']);
    }

    /** @return BookingDTO[] */
    public function getByUser(string $userId, string $status): array
    {
        $data = $this->client->execute('GetBookingsByStatus', [
            'status' => $status,
        ]);

        $rows = array_filter($data['bookings'] ?? [], function (array $booking) use ($userId) {
            if (($booking['createdBy']['id'] ?? null) === $userId) {
                return true;
            }

            foreach ($booking['bookingPlayers_on_booking'] ?? [] as $p) {
                if (($p['user']['id'] ?? null) === $userId) {
                    return true;
                }
            }

            return false;
        });

        return array_map(
            fn (array $row) => BookingDTO::fromGraphQL($row),
            array_values($rows),
        );
    }

    /**
     * Insert a new booking. Returns the raw GraphQL response data
     * (including the generated key).
     */
    public function create(array $params): array
    {
        return $this->client->execute('CreateBooking', $params);
    }

    public function cancel(string $id): void
    {
        $this->client->execute('CancelBooking', [
            'id'          => $id,
            'cancelledAt' => now()->toIso8601ZuluString(),
        ]);
    }

    /**
     * Check whether a CONFIRMED booking already exists for the given
     * court + date + time-slot cell.
     */
    public function isSlotTaken(string $courtId, string $bookingDate, string $timeSlotId): bool
    {
        $data = $this->client->execute('GetBookingForCell', [
            'bookingDate' => $bookingDate,
            'courtId'     => $courtId,
            'timeSlotId'  => $timeSlotId,
        ]);

        return ! empty($data['bookings'] ?? []);
    }

    public function userHasBookingForSlot(string $userId, string $bookingDate, string $timeSlotId): bool
    {
        $data = $this->client->execute('GetBookingsForTimeSlot', [
            'bookingDate' => $bookingDate,
            'timeSlotId'  => $timeSlotId,
        ]);

        foreach ($data['bookings'] ?? [] as $booking) {
            if (($booking['createdBy']['id'] ?? null) === $userId) {
                return true;
            }

            foreach ($booking['bookingPlayers_on_booking'] ?? [] as $p) {
                if (($p['user']['id'] ?? null) === $userId) {
                    return true;
                }
            }
        }

        return false;
    }
}
