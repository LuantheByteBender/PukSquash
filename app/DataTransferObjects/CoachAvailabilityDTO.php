<?php

namespace App\DataTransferObjects;

final readonly class CoachAvailabilityDTO
{
    public function __construct(
        public string $id,
        public string $availabilityDate,
        public string $createdAt,
        public ?array $coach,
        public ?array $timeSlot,
    ) {}

    public static function fromGraphQL(array $data): self
    {
        $coachId = $data['coachId'] ?? ($data['coach']['id'] ?? null);
        $timeSlotId = $data['timeSlotId'] ?? ($data['timeSlot']['id'] ?? null);
        $availabilityDate = $data['availabilityDate'] ?? '';

        $syntheticId = implode(':', array_filter([
            $coachId,
            $availabilityDate,
            $timeSlotId,
        ], fn ($v) => $v !== null && $v !== ''));

        return new self(
            id:               $data['id'] ?? ($syntheticId !== '' ? $syntheticId : 'unknown'),
            availabilityDate: $availabilityDate,
            createdAt:        $data['createdAt'],
            coach:            $data['coach'] ?? null,
            timeSlot:         $data['timeSlot'] ?? null,
        );
    }

    public function toArray(): array
    {
        return [
            'id'                => $this->id,
            'availability_date' => $this->availabilityDate,
            'created_at'        => $this->createdAt,
            'coach'             => $this->coach,
            'time_slot'         => $this->timeSlot,
        ];
    }
}
