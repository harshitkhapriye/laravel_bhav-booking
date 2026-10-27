<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('bhav_bookings', function (Blueprint $table) {
            $table->char('booking_group_id', 36)->nullable()->after('booking_no')->index();
        });

        Schema::table('bhav_booking_items', function (Blueprint $table) {
            $table->enum('metal', ['GOLD', 'SILVER'])->nullable()->after('sort_order');
        });

        // Fill existing rows from parent booking metal
        DB::statement('UPDATE bhav_booking_items i JOIN bhav_bookings b ON i.bhav_booking_id = b.id SET i.metal = b.metal WHERE i.metal IS NULL');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bhav_booking_items', function (Blueprint $table) {
            $table->dropColumn('metal');
        });

        Schema::table('bhav_bookings', function (Blueprint $table) {
            $table->dropIndex(['booking_group_id']);
            $table->dropColumn('booking_group_id');
        });
    }
};
