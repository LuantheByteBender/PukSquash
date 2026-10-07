<?php

namespace App\DataTransferObjects;

final readonly class TimeSlotDTO
{
    public function __construct(
        public string $id,
        public string $startTime,
        public string $endTime,
        public int    $displayOrder,
        public bool   $isActive,
    ) {}

    public static function fromGraphQL(array $data): self
    {
        return new self(
            id:           $data['id'],
            startTime:    $data['startTime'],
            endTime:      $data['endTime'],
            displayOrder: $data['displayOrder'],
            isActive:     $data['isActive'],
        );
    }

    public function toArray(): array
    {
        return [
            'id'            => $this->id,
            'start_time'    => $this->startTime,
            'end_time'      => $this->endTime,
            'display_order' => $this->displayOrder,
            'is_active'     => $this->isActive,
        ];
    }
}
