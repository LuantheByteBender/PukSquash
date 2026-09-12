<?php

namespace App\Repositories;

use App\DataTransferObjects\TimeSlotDTO;
use App\Services\SqlConnectClient;

class TimeSlotRepository
{
    public function __construct(
        private SqlConnectClient $client,
    ) {}

    /** @return TimeSlotDTO[] */
    public function listActive(): array
    {
        $data = $this->client->execute('ListActiveTimeSlots');

        return array_map(
            fn (array $row) => TimeSlotDTO::fromGraphQL($row),
            $data['timeSlots'] ?? [],
        );
    }

    public function create(array $params): void
    {
        $this->client->execute('CreateTimeSlot', $params);
    }
}
