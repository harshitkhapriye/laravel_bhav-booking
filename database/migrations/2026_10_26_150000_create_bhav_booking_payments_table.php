<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Table for Bhav Booking payments (Cash and Online multi-entry).
     */
    public function up(): void
    {
        Schema::create('bhav_booking_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bhav_booking_id')
                ->constrained('bhav_bookings')
                ->cascadeOnDelete();
            $table->enum('mode', ['CASH', 'ONLINE']);
            $table->enum('online_method', ['UPI', 'BANK', 'CARD', 'CHEQUE'])->nullable();
            $table->string('reference_no', 50)->nullable();
            $table->decimal('amount', 15, 2);
            $table->enum('source', ['BOOKING', 'BUY_SELL'])->default('BOOKING');
            $table->integer('sort_order')->default(0);
            $table->dateTime('paid_at');
            $table->timestamps();

            // Performance index for fetching payments by booking and source
            $table->index(['bhav_booking_id', 'source']);
            $table->index('paid_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bhav_booking_payments');
    }
};
