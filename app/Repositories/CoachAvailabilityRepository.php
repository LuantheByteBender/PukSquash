<?php

namespace App\Repositories;

use App\DataTransferObjects\CoachAvailabilityDTO;
use App\Services\SqlConnectClient;

class CoachAvailabilityRepository
{
    public function __construct(
        private SqlConnectClient $client,
    ) {}

    /** @return CoachAvailabilityDTO[] */
    public function getAvailableCoaches(string $availabilityDate, string $timeSlotId): array
    {
        $data = $this->client->execute('GetAvailableCoaches', [
            'availabilityDate' => $availabilityDate,
            'timeSlotId'       => $timeSlotId,
        ]);

        return array_map(
            fn (array $row) => CoachAvailabilityDTO::fromGraphQL($row),
            $data['coachAvailabilities'] ?? [],
        );
    }

    public function create(string $coachId, string $timeSlotId, string $availabilityDate): void
    {
        $this->client->execute('CreateCoachAvailability', [
            'coachId'          => $coachId,
            'timeSlotId'       => $timeSlotId,
            'availabilityDate' => $availabilityDate,
        ]);
    }

    public function delete(string $id): void
    {
        $this->client->execute('DeleteCoachAvailability', ['id' => $id]);
    }
}
