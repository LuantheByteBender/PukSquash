<?php

namespace App\Repositories;

use App\DataTransferObjects\CourtDTO;
use App\Services\SqlConnectClient;

class CourtRepository
{
    public function __construct(
        private SqlConnectClient $client,
    ) {}

    /** @return CourtDTO[] */
    public function listActive(): array
    {
        $data = $this->client->execute('ListActiveCourts');

        return array_map(
            fn (array $row) => CourtDTO::fromGraphQL($row),
            $data['courts'] ?? [],
        );
    }

    public function findById(string $id): ?CourtDTO
    {
        $data = $this->client->execute('GetCourt', ['id' => $id]);

        if (empty($data['court'])) {
            return null;
        }

        return CourtDTO::fromGraphQL($data['court']);
    }

    public function create(array $params): void
    {
        $this->client->execute('CreateCourt', $params);
    }

    public function updateStatus(string $id, string $status): void
    {
        $this->client->execute('UpdateCourtStatus', [
            'id'     => $id,
            'status' => $status,
        ]);
    }
}
