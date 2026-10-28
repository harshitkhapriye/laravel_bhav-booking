<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Add RESTORED action to bhav_booking_logs action enum.
     */
    public function up(): void
    {
        DB::statement("ALTER TABLE bhav_booking_logs MODIFY COLUMN action ENUM('CREATED', 'UPDATED', 'DELETED', 'SILENT_DELETED', 'PRINTED', 'SHARED', 'RESTORED') NOT NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE bhav_booking_logs MODIFY COLUMN action ENUM('CREATED', 'UPDATED', 'DELETED', 'SILENT_DELETED', 'PRINTED', 'SHARED') NOT NULL");
    }
};

