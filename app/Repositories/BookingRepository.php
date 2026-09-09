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
    public function getByUser(string $userId, ?string $status = null): array
    {
        $vars = ['userId' => $userId];

        if ($status !== null) {
            $vars['status'] = $status;
        }

        $data = $this->client->execute('GetBookingsByUser', $vars);

        return array_map(
            fn (array $row) => BookingDTO::fromGraphQL($row),
            $data['bookings'] ?? [],
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
}
