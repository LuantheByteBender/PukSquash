<?php

namespace App\Repositories;

use App\DataTransferObjects\CourtDTO;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CourtRepository
{
    private function dtoFromRow(object $row): CourtDTO
    {
        return new CourtDTO(
            id:          (string) $row->id,
            name:        (string) ($row->name ?? ''),
            description: $row->description ?? null,
            status:      (string) ($row->status ?? 'ACTIVE'),
            createdAt:   (string) ($row->created_at ?? ''),
            updatedAt:   (string) ($row->updated_at ?? ''),
        );
    }

    /** @return CourtDTO[] */
    public function listActive(): array
    {
        $rows = DB::table('courts')
            ->where('status', 'ACTIVE')
            ->orderBy('name')
            ->get();

        return array_map(
            fn (object $row) => $this->dtoFromRow($row),
            $rows->all(),
        );
    }

    /** @return CourtDTO[] */
    public function listAll(): array
    {
        $rows = DB::table('courts')
            ->orderBy('name')
            ->get();

        return array_map(
            fn (object $row) => $this->dtoFromRow($row),
            $rows->all(),
        );
    }

    public function findById(string $id): ?CourtDTO
    {
        $key = 'sqlconnect_court_id_' . $id;
        $cached = Cache::get($key);

        if ($cached instanceof CourtDTO) {
            return $cached;
        }

        $row = DB::table('courts')
            ->where('id', $id)
            ->first();

        if (! $row) {
            return null;
        }

        $dto = $this->dtoFromRow($row);
        Cache::put($key, $dto, now()->addMinutes(10));

        return $dto;
    }

    public function create(array $params): void
    {
        $now = now();

        DB::table('courts')->insert([
            'id'          => $params['id'] ?? Str::uuid()->toString(),
            'name'        => $params['name'],
            'description' => $params['description'] ?? null,
            'status'      => $params['status'] ?? 'ACTIVE',
            'created_at'  => $now,
            'updated_at'  => $now,
        ]);

        Cache::forget('availability:courts:active');
    }

    public function delete(string $id): void
    {
        Cache::forget('sqlconnect_court_id_' . $id);
        DB::table('courts')->where('id', $id)->delete();
        Cache::forget('sqlconnect_court_id_' . $id);
        Cache::forget('availability:courts:active');
    }

    public function updateCourt(string $id, array $params): void
    {
        Cache::forget('sqlconnect_court_id_' . $id);

        $updates = [];
        if (array_key_exists('name', $params)) {
            $updates['name'] = $params['name'];
        }
        if (array_key_exists('description', $params)) {
            $updates['description'] = $params['description'];
        }

        if (! empty($updates)) {
            $updates['updated_at'] = now();
            DB::table('courts')->where('id', $id)->update($updates);
        }

        Cache::forget('sqlconnect_court_id_' . $id);
        Cache::forget('availability:courts:active');
    }

    public function updateStatus(string $id, string $status): void
    {
        Cache::forget('sqlconnect_court_id_' . $id);

        DB::table('courts')
            ->where('id', $id)
            ->update([
                'status' => $status,
                'updated_at' => now(),
            ]);

        Cache::forget('sqlconnect_court_id_' . $id);
        Cache::forget('availability:courts:active');
    }
}
