<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class User extends Model
{
    use HasUuids;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'firebase_uid',
        'student_number',
        'full_name',
        'email',
        'role',
        'status',
    ];

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class, 'created_by_user_id');
    }

    public function coachedBookings(): HasMany
    {
        return $this->hasMany(Booking::class, 'coach_id');
    }

    public function bookingPlayers(): HasMany
    {
        return $this->hasMany(BookingPlayer::class);
    }

    public function coachAvailabilities(): HasMany
    {
        return $this->hasMany(CoachAvailability::class, 'coach_id');
    }
}
