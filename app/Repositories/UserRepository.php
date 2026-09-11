<?php

namespace App\Repositories;

use App\DataTransferObjects\UserDTO;
use App\Services\SqlConnectClient;
use Illuminate\Support\Facades\Cache;

class UserRepository
{
    public function __construct(
        private SqlConnectClient $client,
    ) {}

    public function findByFirebaseUid(string $firebaseUid): ?UserDTO
    {
        $key = 'sqlconnect_user_firebase_uid_' . $firebaseUid;
        $cached = Cache::get($key);

        if ($cached instanceof UserDTO) {
            return $cached;
        }

        $data = $this->client->execute('GetUserByFirebaseUid', [
            'firebaseUid' => $firebaseUid,
        ]);

        $users = $data['users'] ?? [];

        if (empty($users)) {
            return null;
        }

        $dto = UserDTO::fromGraphQL($users[0]);
        Cache::put($key, $dto, now()->addMinutes(10));

        return $dto;
    }

    public function findById(string $id): ?UserDTO
    {
        $key = 'sqlconnect_user_id_' . $id;
        $cached = Cache::get($key);

        if ($cached instanceof UserDTO) {
            return $cached;
        }

        $data = $this->client->execute('GetUserById', [
            'id' => $id,
        ]);

        if (empty($data['user'])) {
            return null;
        }

        $dto = UserDTO::fromGraphQL($data['user']);
        Cache::put($key, $dto, now()->addMinutes(10));

        return $dto;
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
    
    public function listAll(): array
    {
        $data = $this->client->execute('ListAllUsers');

        return array_map(
            fn (array $row) => UserDTO::fromGraphQL($row),
            $data['users'] ?? [],
        );
    }

    public function updateUser(string $id, array $params): void
    {
        $this->client->execute('UpdateUser', array_merge(
            ['id' => $id],
            $params,
        ));
    }

    public function deleteUser(string $id): void
    {
        $this->client->execute('DeleteUser', ['id' => $id]);
    }
}
