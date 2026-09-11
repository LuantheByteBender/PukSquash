<?php

namespace App\Repositories;

use App\DataTransferObjects\CourtDTO;
use App\Services\SqlConnectClient;
use Illuminate\Support\Facades\Cache;

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
        $key = 'sqlconnect_court_id_' . $id;
        $cached = Cache::get($key);

        if ($cached instanceof CourtDTO) {
            return $cached;
        }

        $data = $this->client->execute('GetCourt', ['id' => $id]);

        if (empty($data['court'])) {
            return null;
        }

        $dto = CourtDTO::fromGraphQL($data['court']);
        Cache::put($key, $dto, now()->addMinutes(10));

        return $dto;
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
