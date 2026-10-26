<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Expand columns to TEXT to accommodate encrypted ciphertexts
        DB::statement("ALTER TABLE customers MODIFY aadhaar_no TEXT NULL");
        DB::statement("ALTER TABLE customers MODIFY pan_no TEXT NULL");

        // 2. Encrypt existing plain values, skipping any that are already encrypted
        $customers = DB::table('customers')
            ->whereNotNull('aadhaar_no')
            ->orWhereNotNull('pan_no')
            ->get();

        foreach ($customers as $customer) {
            $updates = [];

            if (!empty($customer->aadhaar_no)) {
                try {
                    Crypt::decryptString($customer->aadhaar_no);
                } catch (\Throwable $e) {
                    $updates['aadhaar_no'] = Crypt::encryptString($customer->aadhaar_no);
                }
            }

            if (!empty($customer->pan_no)) {
                try {
                    Crypt::decryptString($customer->pan_no);
                } catch (\Throwable $e) {
                    $updates['pan_no'] = Crypt::encryptString($customer->pan_no);
                }
            }

            if (!empty($updates)) {
                DB::table('customers')->where('id', $customer->id)->update($updates);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // 1. Decrypt back to plain text
        $customers = DB::table('customers')
            ->whereNotNull('aadhaar_no')
            ->orWhereNotNull('pan_no')
            ->get();

        foreach ($customers as $customer) {
            $updates = [];

            if (!empty($customer->aadhaar_no)) {
                try {
                    $updates['aadhaar_no'] = Crypt::decryptString($customer->aadhaar_no);
                } catch (\Throwable $e) {
                    // Already plain or decrypt failed
                }
            }

            if (!empty($customer->pan_no)) {
                try {
                    $updates['pan_no'] = Crypt::decryptString($customer->pan_no);
                } catch (\Throwable $e) {
                    // Already plain or decrypt failed
                }
            }

            if (!empty($updates)) {
                DB::table('customers')->where('id', $customer->id)->update($updates);
            }
        }

        // 2. Restore column lengths back to VARCHAR(12) and VARCHAR(10)
        DB::statement("ALTER TABLE customers MODIFY aadhaar_no VARCHAR(12) NULL");
        DB::statement("ALTER TABLE customers MODIFY pan_no VARCHAR(10) NULL");
    }
};
