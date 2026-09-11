<?php

namespace App\DataTransferObjects;

final readonly class CourtDTO
{
    public function __construct(
        public string  $id,
        public string  $name,
        public ?string $description,
        public string  $status,
        public string  $createdAt,
        public string  $updatedAt,
    ) {}

    public static function fromGraphQL(array $data): self
    {
        return new self(
            id:          $data['id'],
            name:        $data['name'],
            description: $data['description'] ?? null,
            status:      $data['status'],
            createdAt:   $data['createdAt'],
            updatedAt:   $data['updatedAt'],
        );
    }

    public function toArray(): array
    {
        return [
            'id'          => $this->id,
            'name'        => $this->name,
            'description' => $this->description,
            'status'      => $this->status,
            'created_at'  => $this->createdAt,
            'updated_at'  => $this->updatedAt,
        ];
    }
}
