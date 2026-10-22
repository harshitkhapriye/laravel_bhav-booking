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
        Schema::create('bhav_bookings', function (Blueprint $table) {
            $table->id();
            $table->string('booking_no')->unique(); // Format: BB-000001
            $table->foreignId('customer_id')->constrained('customers')->restrictOnDelete();
            $table->enum('trade_type', ['BUY', 'SELL']);
            $table->enum('entry_type', ['ONLY_ENTRY', 'BHAV_CUT_LEDGER']);
            $table->enum('metal', ['GOLD', 'SILVER']);
            $table->decimal('total_weight', 12, 3);
            $table->decimal('total_amount', 15, 2);
            $table->decimal('delivered_weight', 12, 3)->default(0);
            $table->decimal('given_amount', 15, 2)->default(0);
            $table->decimal('received_amount', 15, 2)->default(0);
            $table->enum('status', ['PENDING', 'PARTIAL', 'DELIVERED', 'CANCELLED'])->default('PENDING');
            $table->string('narration', 255)->nullable();
            $table->boolean('send_whatsapp')->default(false);
            $table->uuid('request_token')->unique(); // Duplicate save protection
            $table->dateTime('booked_at');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bhav_bookings');
    }
};
