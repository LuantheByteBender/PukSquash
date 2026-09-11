<?php

namespace App\Repositories;

use App\DataTransferObjects\BookingDTO;
use App\DataTransferObjects\CourtDTO;
use App\DataTransferObjects\TimeSlotDTO;
use App\Services\SqlConnectClient;

class AvailabilityRepository
{
    public function __construct(
        private SqlConnectClient $client,
    ) {}

    public function getForDate(string $bookingDate): array
    {
        $data = $this->client->execute('GetAvailabilityData', [
            'bookingDate' => $bookingDate,
        ]);

        $courts = array_map(
            fn (array $row) => CourtDTO::fromGraphQL($row),
            $data['courts'] ?? [],
        );

        $timeSlots = array_map(
            fn (array $row) => TimeSlotDTO::fromGraphQL($row),
            $data['timeSlots'] ?? [],
        );

        $bookings = array_map(
            fn (array $row) => BookingDTO::fromGraphQL($row),
            $data['bookings'] ?? [],
        );

        return [
            'courts'    => $courts,
            'timeSlots' => $timeSlots,
            'bookings'  => $bookings,
        ];
    }
}
