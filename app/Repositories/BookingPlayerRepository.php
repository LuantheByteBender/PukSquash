<?php

namespace App\Repositories;

use App\DataTransferObjects\BookingPlayerDTO;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class BookingPlayerRepository
{
    /** @return BookingPlayerDTO[] */
    public function getByBooking(string $bookingId): array
    {
        $rows = DB::table('booking_players as bp')
            ->leftJoin('users as u', 'u.id', '=', 'bp.user_id')
            ->where('bp.booking_id', $bookingId)
            ->select([
                'bp.id',
                'bp.player_position',
                'bp.joined_at',
                'u.id as user_id',
                'u.firebase_uid',
                'u.full_name',
                'u.email',
                'u.role',
                'u.status',
            ])
            ->get();

        return array_map(function (object $row) {
            $user = null;

            if (! empty($row->user_id)) {
                $user = [
                    'id' => (string) $row->user_id,
                    'firebaseUid' => (string) ($row->firebase_uid ?? ''),
                    'fullName' => (string) ($row->full_name ?? ''),
                    'email' => (string) ($row->email ?? ''),
                    'role' => (string) ($row->role ?? 'USER'),
                    'status' => (string) ($row->status ?? 'ACTIVE'),
                ];
            }

            return BookingPlayerDTO::fromGraphQL([
                'id' => (string) $row->id,
                'playerPosition' => (int) $row->player_position,
                'joinedAt' => (string) ($row->joined_at ?? ''),
                'user' => $user,
            ]);
        }, $rows->all());
    }

    public function add(string $bookingId, string $userId, int $playerPosition): void
    {
        DB::table('booking_players')->insert([
            'id'              => Str::uuid()->toString(),
            'booking_id'      => $bookingId,
            'user_id'         => $userId,
            'player_position' => $playerPosition,
            'joined_at'       => now(),
        ]);
    }

    public function remove(string $bookingId, string $userId): void
    {
        DB::table('booking_players')
            ->where('booking_id', $bookingId)
            ->where('user_id', $userId)
            ->delete();
    }
}
