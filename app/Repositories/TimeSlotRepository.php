<?php

namespace App\Repositories;

use App\DataTransferObjects\TimeSlotDTO;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class TimeSlotRepository
{
    private function dtoFromRow(object $row): TimeSlotDTO
    {
        return new TimeSlotDTO(
            id:           (string) $row->id,
            startTime:    (string) ($row->start_time ?? ''),
            endTime:      (string) ($row->end_time ?? ''),
            displayOrder: (int) ($row->display_order ?? 0),
            isActive:     (bool) ($row->is_active ?? true),
        );
    }

    /** @return TimeSlotDTO[] */
    public function listActive(): array
    {
        $rows = DB::table('time_slots')
            ->where('is_active', true)
            ->orderBy('display_order')
            ->get();

        return array_map(
            fn (object $row) => $this->dtoFromRow($row),
            $rows->all(),
        );
    }

    public function create(array $params): void
    {
        DB::table('time_slots')->insert([
            'id'            => $params['id'] ?? Str::uuid()->toString(),
            'start_time'    => $params['startTime'] ?? $params['start_time'] ?? null,
            'end_time'      => $params['endTime'] ?? $params['end_time'] ?? null,
            'display_order' => $params['displayOrder'] ?? $params['display_order'] ?? 0,
            'is_active'     => $params['isActive'] ?? $params['is_active'] ?? true,
        ]);
    }
}
