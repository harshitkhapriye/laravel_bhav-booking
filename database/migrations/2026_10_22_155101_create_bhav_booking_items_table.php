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
        Schema::create('bhav_booking_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bhav_booking_id')->constrained('bhav_bookings')->cascadeOnDelete();
            $table->unsignedInteger('sort_order')->default(1);
            $table->decimal('weight', 10, 3);
            $table->decimal('rate', 12, 2);
            $table->decimal('amount', 15, 2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bhav_booking_items');
    }
};
