<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Table for storing Bhav Booking audit and activity logs.
     */
    public function up(): void
    {
        Schema::create('bhav_booking_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bhav_booking_id')
                ->constrained('bhav_bookings')
                ->cascadeOnDelete();
            $table->enum('action', ['CREATED', 'UPDATED', 'DELETED', 'SILENT_DELETED', 'PRINTED', 'SHARED']);
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamp('created_at')->useCurrent();

            // Performance index for fetching logs by booking ordered by creation time
            $table->index(['bhav_booking_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bhav_booking_logs');
    }
};
