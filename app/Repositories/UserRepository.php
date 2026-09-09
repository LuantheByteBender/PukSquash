<?php

namespace App\Repositories;

use App\DataTransferObjects\UserDTO;
use App\Services\SqlConnectClient;

class UserRepository
{
    public function __construct(
        private SqlConnectClient $client,
    ) {}

    public function findByFirebaseUid(string $firebaseUid): ?UserDTO
    {
        $data = $this->client->execute('GetUserByFirebaseUid', [
            'firebaseUid' => $firebaseUid,
        ]);

        $users = $data['users'] ?? [];

        if (empty($users)) {
            return null;
        }

        return UserDTO::fromGraphQL($users[0]);
    }

    public function findById(string $id): ?UserDTO
    {
        $data = $this->client->execute('GetUserById', [
            'id' => $id,
        ]);

        if (empty($data['user'])) {
            return null;
        }

        return UserDTO::fromGraphQL($data['user']);
    }

    public function upsert(array $params): void
    {
        $params = array_merge([
            'studentNumber' => null,
            'role'          => 'USER',
            'status'        => 'ACTIVE',
        ], $params);

        try {
            $this->client->execute('UpsertUser', $params);
        } catch (\Throwable $exception) {
            $message = $exception->getMessage();

            if (
                stripos($message, 'already exists') !== false
                || stripos($message, 'duplicate key') !== false
                || stripos($message, 'unique') !== false
            ) {
                return;
            }

            throw $exception;
        }
    }
}
