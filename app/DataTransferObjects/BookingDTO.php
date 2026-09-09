<?php

namespace App\DataTransferObjects;

final readonly class BookingDTO
{
    public function __construct(
        public string  $id,
        public string  $bookingDate,
        public string  $bookingType,
        public string  $status,
        public string  $bookedAt,
        public ?string $cancelledAt,
        public string  $createdAt,
        public ?array  $court,
        public ?array  $timeSlot,
        public ?array  $createdBy,
        public ?array  $coach,
        public array   $players,
    ) {}

    public static function fromGraphQL(array $data): self
    {
        $players = [];
        foreach ($data['bookingPlayers_on_booking'] ?? [] as $p) {
            $players[] = BookingPlayerDTO::fromGraphQL($p);
        }

        return new self(
            id:            $data['id'],
            bookingDate:   $data['bookingDate'],
            bookingType:   $data['bookingType'],
            status:        $data['status'],
            bookedAt:      $data['bookedAt'],
            cancelledAt:   $data['cancelledAt'] ?? null,
            createdAt:     $data['createdAt'],
            court:         $data['court'] ?? null,
            timeSlot:      $data['timeSlot'] ?? null,
            createdBy:     $data['createdBy'] ?? null,
            coach:         $data['coach'] ?? null,
            players:       $players,
        );
    }

    public function toArray(): array
    {
        $court = $this->court
            ? [
                'id'   => $this->court['id'] ?? null,
                'name' => $this->court['name'] ?? null,
            ]
            : null;

        $timeSlot = $this->timeSlot
            ? [
                'id'         => $this->timeSlot['id'] ?? null,
                'start_time' => $this->timeSlot['start_time']
                    ?? $this->timeSlot['startTime']
                    ?? null,
                'end_time'   => $this->timeSlot['end_time']
                    ?? $this->timeSlot['endTime']
                    ?? null,
            ]
            : null;

        $coach = $this->coach
            ? [
                'id'   => $this->coach['id'] ?? null,
                'name' => $this->coach['name']
                    ?? $this->coach['fullName']
                    ?? null,
            ]
            : null;

        return [
            'id'              => $this->id,
            'booking_date'    => $this->bookingDate,
            'booking_type'    => $this->bookingType,
            'status'          => $this->status,
            'booked_at'       => $this->bookedAt,
            'cancelled_at'    => $this->cancelledAt,
            'created_at'      => $this->createdAt,
            'court'           => $court,
            'time_slot'       => $timeSlot,
            'coach'           => $coach,
            'players'         => array_map(fn (BookingPlayerDTO $p) => $p->toArray(), $this->players),
        ];
    }
}
