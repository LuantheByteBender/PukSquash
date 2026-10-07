<?php

namespace App\Repositories;

use App\DataTransferObjects\BookingPlayerDTO;
use App\Services\SqlConnectClient;

class BookingPlayerRepository
{
    public function __construct(
        private SqlConnectClient $client,
    ) {}

    /** @return BookingPlayerDTO[] */
    public function getByBooking(string $bookingId): array
    {
        $data = $this->client->execute('GetBookingPlayers', [
            'bookingId' => $bookingId,
        ]);

        return array_map(
            fn (array $row) => BookingPlayerDTO::fromGraphQL($row),
            $data['bookingPlayers'] ?? [],
        );
    }

    public function add(string $bookingId, string $userId, int $playerPosition): void
    {
        $this->client->execute('AddBookingPlayer', [
            'bookingId'      => $bookingId,
            'userId'         => $userId,
            'playerPosition' => $playerPosition,
            'joinedAt'       => now()->toIso8601ZuluString(),
        ]);
    }

    public function remove(string $bookingId, string $userId): void
    {
        $this->client->execute('LeaveBooking', [
            'bookingId' => $bookingId,
            'userId'    => $userId,
        ]);
    }
}
