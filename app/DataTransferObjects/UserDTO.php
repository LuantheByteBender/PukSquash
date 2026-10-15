<?php

namespace App\DataTransferObjects;

/**
 * Read-only representation of a User row from SQL Connect.
 *
 * Laravel never writes to the database directly — it calls SQL Connect
 * mutations through the SqlConnectClient. This DTO is the PHP shape
 * that repositories return to services and controllers.
 */
final readonly class UserDTO
{
    public function __construct(
        public string  $id,
        public string  $firebaseUid,
        public ?string $studentNumber,
        public string  $fullName,
        public string  $email,
        public ?string $phoneNumber = null,
        public string  $role = 'USER',
        public string  $status = 'ACTIVE',
        public string  $createdAt = '',
        public string  $updatedAt = '',
        public int     $fundsBalanceCents = 0,
        public int     $privateCoachingFeeCents = 0,
    ) {}

    public static function fromGraphQL(array $data): self
    {
        return new self(
            id:            $data['id'],
            firebaseUid:   $data['firebaseUid'],
            studentNumber: $data['studentNumber'] ?? null,
            fullName:      $data['fullName'],
            email:         $data['email'],
            phoneNumber:   $data['phoneNumber'] ?? null,
            role:          $data['role'] ?? 'USER',
            status:        $data['status'] ?? 'ACTIVE',
            createdAt:     $data['createdAt'] ?? '',
            updatedAt:     $data['updatedAt'] ?? '',
            fundsBalanceCents: (int) ($data['fundsBalanceCents'] ?? 0),
            privateCoachingFeeCents: (int) ($data['privateCoachingFeeCents'] ?? 0),
        );
    }

    public function toArray(): array
    {
        return [
            'id'             => $this->id,
            'firebase_uid'   => $this->firebaseUid,
            'student_number' => $this->studentNumber,
            'full_name'      => $this->fullName,
            'email'          => $this->email,
            'phone_number'   => $this->phoneNumber,
            'role'           => $this->role,
            'status'         => $this->status,
            'created_at'     => $this->createdAt,
            'updated_at'     => $this->updatedAt,
            'funds_balance_cents' => $this->fundsBalanceCents,
            'private_coaching_fee_cents' => $this->privateCoachingFeeCents,
        ];
    }
}
