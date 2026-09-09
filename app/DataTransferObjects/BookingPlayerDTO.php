<?php

namespace App\DataTransferObjects;

final readonly class BookingPlayerDTO
{
    public function __construct(
        public string $id,
        public int    $playerPosition,
        public string $joinedAt,
        public ?array $user,
    ) {}

    public static function fromGraphQL(array $data): self
    {
        return new self(
            id:             $data['id'],
            playerPosition: $data['playerPosition'],
            joinedAt:       $data['joinedAt'],
            user:           $data['user'] ?? null,
        );
    }

    public function toArray(): array
    {
        $userId = $this->user['id'] ?? null;
        $name = $this->user['name']
            ?? $this->user['fullName']
            ?? null;

        return [
            'id'              => $this->id,
            'player_position' => $this->playerPosition,
            'joined_at'       => $this->joinedAt,
            'user_id'         => $userId,
            'name'            => $name,
        ];
    }
}
