<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('bookings', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('court_id')->constrained('courts')->restrictOnDelete();
            $table->foreignUuid('time_slot_id')->constrained('time_slots')->restrictOnDelete();
            $table->foreignUuid('created_by_user_id')->constrained('users')->restrictOnDelete();
            $table->foreignUuid('coach_id')->nullable()->constrained('users')->restrictOnDelete();
            $table->date('booking_date');
            $table->string('booking_type');
            $table->string('status');
            $table->timestampTz('booked_at');
            $table->timestampTz('cancelled_at')->nullable();
            $table->timestampsTz();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
