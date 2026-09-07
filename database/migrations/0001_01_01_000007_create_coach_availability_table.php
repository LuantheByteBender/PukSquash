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
        Schema::create('coach_availability', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('coach_id')->constrained('users')->restrictOnDelete();
            $table->foreignUuid('time_slot_id')->constrained('time_slots')->restrictOnDelete();
            $table->date('availability_date');
            $table->timestampTz('created_at');

            $table->unique(['coach_id', 'time_slot_id', 'availability_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('coach_availability');
    }
};
