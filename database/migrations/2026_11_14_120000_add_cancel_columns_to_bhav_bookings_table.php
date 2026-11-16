<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Add cancel_reason, cancelled_at to bhav_bookings and CANCELLED to bhav_booking_logs action enum.
     */
    public function up(): void
    {
        Schema::table('bhav_bookings', function (Blueprint $table) {
            $table->string('cancel_reason', 255)->nullable()->after('status');
            $table->dateTime('cancelled_at')->nullable()->after('cancel_reason');
        });

        DB::statement("ALTER TABLE bhav_booking_logs MODIFY COLUMN action ENUM('CREATED', 'UPDATED', 'DELETED', 'SILENT_DELETED', 'PRINTED', 'SHARED', 'RESTORED', 'CANCELLED') NOT NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE bhav_booking_logs MODIFY COLUMN action ENUM('CREATED', 'UPDATED', 'DELETED', 'SILENT_DELETED', 'PRINTED', 'SHARED', 'RESTORED') NOT NULL");

        Schema::table('bhav_bookings', function (Blueprint $table) {
            $table->dropColumn(['cancel_reason', 'cancelled_at']);
        });
    }
};

