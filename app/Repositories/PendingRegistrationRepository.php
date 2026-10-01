<?php

namespace App\Repositories;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class PendingRegistrationRepository
{
    public function findByFirebaseUid(string $firebaseUid): ?object
    {
        return DB::table('pending_registrations')
            ->where('firebase_uid', $firebaseUid)
            ->first();
    }

    public function findByEmail(string $email): ?object
    {
        $email = trim($email);
        if ($email === '') {
            return null;
        }

        return DB::table('pending_registrations')
            ->whereRaw('LOWER(email) = ?', [strtolower($email)])
            ->first();
    }

    public function createOrGet(
        string $firebaseUid,
        string $email,
        ?string $fullName,
        ?string $studentNumber,
        ?string $phoneNumber,
    ): object {
        $existing = $this->findByFirebaseUid($firebaseUid);
        if ($existing) {
            return $existing;
        }

        $existingByEmail = $this->findByEmail($email);
        if ($existingByEmail) {
            $update = [];

            if ((string) $existingByEmail->firebase_uid !== $firebaseUid) {
                $update['firebase_uid'] = $firebaseUid;
            }

            if ($fullName !== null && trim($fullName) !== '') {
                $update['full_name'] = $fullName;
            }

            if ($studentNumber !== null && trim($studentNumber) !== '') {
                $update['student_number'] = $studentNumber;
            }

            if ($phoneNumber !== null && trim($phoneNumber) !== '') {
                $update['phone_number'] = $phoneNumber;
            }

            if ($update !== []) {
                DB::table('pending_registrations')
                    ->where('id', (string) $existingByEmail->id)
                    ->update($update);
            }

            return DB::table('pending_registrations')
                ->where('id', (string) $existingByEmail->id)
                ->first();
        }

        $now = now();

        $id = (string) Str::uuid();

        $insert = [
            'id' => $id,
            'firebase_uid' => $firebaseUid,
            'email' => trim($email),
            'full_name' => $fullName !== null ? trim($fullName) : null,
            'student_number' => $studentNumber !== null ? trim($studentNumber) : null,
            'phone_number' => $phoneNumber !== null ? trim($phoneNumber) : null,
            'status' => 'PENDING_PAYMENT',
            'created_at' => $now,
        ];

        DB::table('pending_registrations')->insert($insert);

        return DB::table('pending_registrations')->where('id', $id)->first();
    }

    public function markEmailVerifiedChecked(string $pendingRegistrationId): void
    {
        DB::table('pending_registrations')
            ->where('id', $pendingRegistrationId)
            ->update([
                'email_verified_checked_at' => now(),
            ]);
    }

    public function markCompleted(string $pendingRegistrationId): void
    {
        DB::table('pending_registrations')
            ->where('id', $pendingRegistrationId)
            ->update([
                'status' => 'COMPLETED',
                'completed_at' => now(),
            ]);
    }
}
