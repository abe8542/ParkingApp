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
        Schema::table('vehicles', function (Blueprint $table) {
            // Adds the slot number column after plate_number
            // We use unsignedInteger because slot numbers are positive
            $table->unsignedInteger('slot_number')->nullable()->after('plate_number');

            // Optional: Index it for faster lookups when rendering the map
            $table->index('slot_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vehicles', function (Blueprint $table) {
            $table->dropColumn('slot_number');
        });
    }
};
