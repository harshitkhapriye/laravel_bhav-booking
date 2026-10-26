<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Add performance indexes to customers and bhav_bookings tables.
     */
    public function up(): void
    {
        // Helper to check existing index names on a table
        $hasIndex = function (string $table, string $indexName): bool {
            $indexes = collect(DB::select("SHOW INDEX FROM `{$table}`"))->pluck('Key_name')->all();
            return in_array($indexName, $indexes, true);
        };

        // 1. Indexes on customers table
        Schema::table('customers', function (Blueprint $table) use ($hasIndex) {
            if (!$hasIndex('customers', 'customers_name_index')) {
                $table->index('name', 'customers_name_index');
            }
            if (!$hasIndex('customers', 'customers_mobile_index')) {
                $table->index('mobile', 'customers_mobile_index');
            }
            if (!$hasIndex('customers', 'customers_party_type_index')) {
                $table->index('party_type', 'customers_party_type_index');
            }
        });

        // 2. Indexes on bhav_bookings table
        Schema::table('bhav_bookings', function (Blueprint $table) use ($hasIndex) {
            if (!$hasIndex('bhav_bookings', 'bhav_bookings_status_booked_at_index')) {
                $table->index(['status', 'booked_at'], 'bhav_bookings_status_booked_at_index');
            }
            if (!$hasIndex('bhav_bookings', 'bhav_bookings_trade_type_index')) {
                $table->index('trade_type', 'bhav_bookings_trade_type_index');
            }
            if (!$hasIndex('bhav_bookings', 'bhav_bookings_metal_index')) {
                $table->index('metal', 'bhav_bookings_metal_index');
            }
            if (!$hasIndex('bhav_bookings', 'bhav_bookings_entry_type_index')) {
                $table->index('entry_type', 'bhav_bookings_entry_type_index');
            }
            // Check if customer_id has any index
            $customerIdIndexed = collect(DB::select("SHOW INDEX FROM `bhav_bookings`"))
                ->where('Column_name', 'customer_id')
                ->isNotEmpty();
            if (!$customerIdIndexed && !$hasIndex('bhav_bookings', 'bhav_bookings_customer_id_index')) {
                $table->index('customer_id', 'bhav_bookings_customer_id_index');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $hasIndex = function (string $table, string $indexName): bool {
            $indexes = collect(DB::select("SHOW INDEX FROM `{$table}`"))->pluck('Key_name')->all();
            return in_array($indexName, $indexes, true);
        };

        Schema::table('customers', function (Blueprint $table) use ($hasIndex) {
            if ($hasIndex('customers', 'customers_name_index')) {
                $table->dropIndex('customers_name_index');
            }
            if ($hasIndex('customers', 'customers_mobile_index')) {
                $table->dropIndex('customers_mobile_index');
            }
            if ($hasIndex('customers', 'customers_party_type_index')) {
                $table->dropIndex('customers_party_type_index');
            }
        });

        Schema::table('bhav_bookings', function (Blueprint $table) use ($hasIndex) {
            if ($hasIndex('bhav_bookings', 'bhav_bookings_status_booked_at_index')) {
                $table->dropIndex('bhav_bookings_status_booked_at_index');
            }
            if ($hasIndex('bhav_bookings', 'bhav_bookings_trade_type_index')) {
                $table->dropIndex('bhav_bookings_trade_type_index');
            }
            if ($hasIndex('bhav_bookings', 'bhav_bookings_metal_index')) {
                $table->dropIndex('bhav_bookings_metal_index');
            }
            if ($hasIndex('bhav_bookings', 'bhav_bookings_entry_type_index')) {
                $table->dropIndex('bhav_bookings_entry_type_index');
            }
            if ($hasIndex('bhav_bookings', 'bhav_bookings_customer_id_index')) {
                $table->dropIndex('bhav_bookings_customer_id_index');
            }
        });
    }
};
