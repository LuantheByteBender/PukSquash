<?php

namespace App\Repositories;

use App\DataTransferObjects\UserDTO;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class UserRepository
{
    private function dtoFromRow(object $row): UserDTO
    {
        return new UserDTO(
            id:            (string) $row->id,
            firebaseUid:   (string) ($row->firebase_uid ?? ''),
            studentNumber: $row->student_number ?? null,
            fullName:      (string) ($row->full_name ?? ''),
            email:         (string) ($row->email ?? ''),
            phoneNumber:   Schema::hasColumn('users', 'phone_number')
                ? ($row->phone_number ?? null)
                : null,
            role:          (string) ($row->role ?? 'USER'),
            status:        (string) ($row->status ?? 'ACTIVE'),
            createdAt:     (string) ($row->created_at ?? ''),
            updatedAt:     (string) ($row->updated_at ?? ''),
        );
    }

    private function forgetCachedUser(UserDTO $user): void
    {
        Cache::forget('sqlconnect_user_id_' . $user->id);
        Cache::forget('sqlconnect_user_firebase_uid_' . $user->firebaseUid);
    }

    private function forgetCachedFirebaseUid(string $firebaseUid): void
    {
        Cache::forget('sqlconnect_user_firebase_uid_' . $firebaseUid);
    }

    public function findByFirebaseUid(string $firebaseUid): ?UserDTO
    {
        $key = 'sqlconnect_user_firebase_uid_' . $firebaseUid;
        $cached = Cache::get($key);

        if ($cached instanceof UserDTO) {
            return $cached;
        }

        $row = DB::table('users')
            ->where('firebase_uid', $firebaseUid)
            ->first();

        if (! $row) {
            return null;
        }

        $dto = $this->dtoFromRow($row);
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

        $row = DB::table('users')
            ->where('id', $id)
            ->first();

        if (! $row) {
            return null;
        }

        $dto = $this->dtoFromRow($row);
        Cache::put($key, $dto, now()->addMinutes(10));

        return $dto;
    }

    public function upsert(array $params): void
    {
        $firebaseUid = (string) ($params['firebaseUid'] ?? $params['firebase_uid'] ?? '');
        if ($firebaseUid === '') {
            throw new \InvalidArgumentException('firebaseUid is required.');
        }

        $now = now();

        $existing = DB::table('users')
            ->where('firebase_uid', $firebaseUid)
            ->first();

        if ($existing) {
            $existingDto = $this->dtoFromRow($existing);
            $this->forgetCachedUser($existingDto);

            $updates = [
                'email'          => $params['email'] ?? $existing->email ?? null,
                'student_number' => $params['studentNumber'] ?? $params['student_number'] ?? $existing->student_number ?? null,
                'full_name'      => $params['fullName'] ?? $params['full_name'] ?? $existing->full_name ?? null,
                'role'           => $params['role'] ?? $existing->role ?? 'USER',
                'status'         => $params['status'] ?? $existing->status ?? 'ACTIVE',
                'updated_at'     => $now,
            ];

            if (Schema::hasColumn('users', 'phone_number')) {
                $updates['phone_number'] = $params['phoneNumber'] ?? $params['phone_number'] ?? $existing->phone_number ?? null;
            }

            DB::table('users')
                ->where('firebase_uid', $firebaseUid)
                ->update($updates);

            $this->forgetCachedUser($existingDto);

            return;
        }

        $this->forgetCachedFirebaseUid($firebaseUid);

        $insert = [
            'id'             => $params['id'] ?? Str::uuid()->toString(),
            'firebase_uid'   => $firebaseUid,
            'email'          => $params['email'] ?? null,
            'student_number' => $params['studentNumber'] ?? $params['student_number'] ?? null,
            'full_name'      => $params['fullName'] ?? $params['full_name'] ?? null,
            'role'           => $params['role'] ?? 'USER',
            'status'         => $params['status'] ?? 'ACTIVE',
            'created_at'     => $now,
            'updated_at'     => $now,
        ];

        if (Schema::hasColumn('users', 'phone_number')) {
            $insert['phone_number'] = $params['phoneNumber'] ?? $params['phone_number'] ?? null;
        }

        DB::table('users')->insert($insert);

        Cache::forget('sqlconnect_user_id_' . $insert['id']);
        $this->forgetCachedFirebaseUid($firebaseUid);
    }

    public function listAll(): array
    {
        $rows = DB::table('users')
            ->orderBy('full_name')
            ->get();

        return array_map(
            fn (object $row) => $this->dtoFromRow($row),
            $rows->all(),
        );
    }

    public function updateUser(string $id, array $params): void
    {
        $existing = $this->findById($id);
        if ($existing) {
            $this->forgetCachedUser($existing);
        }

        $updates = [];
        if (array_key_exists('role', $params)) {
            $updates['role'] = $params['role'];
        }
        if (array_key_exists('status', $params)) {
            $updates['status'] = $params['status'];
        }

        if (! empty($updates)) {
            $updates['updated_at'] = now();
            DB::table('users')->where('id', $id)->update($updates);
        }

        if ($existing) {
            $this->forgetCachedUser($existing);
        }
    }

    public function hasBookingReferences(string $id): bool
    {
        $createdBookingsExist = DB::table('bookings')
            ->where('created_by_user_id', $id)
            ->exists();

        if ($createdBookingsExist) {
            return true;
        }

        $coachBookingsExist = DB::table('bookings')
            ->where('coach_id', $id)
            ->exists();

        if ($coachBookingsExist) {
            return true;
        }

        return DB::table('booking_players')
            ->where('user_id', $id)
            ->exists();
    }

    public function deleteUser(string $id): void
    {
        $existing = $this->findById($id);
        if ($existing) {
            $this->forgetCachedUser($existing);
        }

        DB::table('users')->where('id', $id)->delete();

        if ($existing) {
            $this->forgetCachedUser($existing);
        }
    }

    public function updateProfile(
        string $firebaseUid,
        string $fullName,
        ?string $studentNumber,
        ?string $phoneNumber,
        ?string $email
    ): void
    {
        $keyFirebase = 'sqlconnect_user_firebase_uid_' . $firebaseUid;

        $existing = $this->findByFirebaseUid($firebaseUid);

        Cache::forget($keyFirebase);
        if ($existing) {
            Cache::forget('sqlconnect_user_id_' . $existing->id);
        }

        $updates = [
            'full_name'      => $fullName,
            'student_number' => $studentNumber,
            'updated_at'     => now(),
        ];

        if (! empty($email)) {
            $updates['email'] = $email;
        }

        if (Schema::hasColumn('users', 'phone_number')) {
            $updates['phone_number'] = $phoneNumber;
        }

        DB::table('users')
            ->where('firebase_uid', $firebaseUid)
            ->update($updates);

        Cache::forget($keyFirebase);
        if ($existing) {
            Cache::forget('sqlconnect_user_id_' . $existing->id);
        }
    }
}
