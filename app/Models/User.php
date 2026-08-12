<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class User extends Model
{
    /**
     * The table associated with the model.
     * Firebase Data Connect creates tables matching the type name.
     */
    protected $table = 'User';

    /**
     * The primary key is the Firebase UID (string, not auto-increment).
     */
    protected $keyType = 'string';
    public $incrementing = false;

    /**
     * Data Connect does not use Laravel's updated_at/created_at columns.
     */
    public $timestamps = false;

    protected $fillable = [
        'id',
        'studentNumber',
        'name',
        'email',
        'role',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'createdAt' => 'datetime',
        ];
    }

    /**
     * A user can have many bookings.
     */
    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class, 'userId');
    }
}
