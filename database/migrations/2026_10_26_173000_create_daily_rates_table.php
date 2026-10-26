<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Table for storing today's metal bullion rates (GOLD, SILVER).
     */
    public function up(): void
    {
        Schema::create('daily_rates', function (Blueprint $table) {
            $table->id();
            $table->enum('metal', ['GOLD', 'SILVER']);
            $table->decimal('rate', 12, 2);
            $table->date('rate_date');
            $table->timestamps();

            // Each metal can only have one rate entry per date
            $table->unique(['metal', 'rate_date']);
            $table->index('rate_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('daily_rates');
    }
};
