
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
        // 1. Table for Vehicles currently in the yard
        Schema::create('vehicles', function (Blueprint $table) {
            $table->id();
            $table->string('plate_number');
            $table->string('phone_number'); // The phone we will push the M-Pesa prompt to
            $table->enum('status', ['parked', 'exited'])->default('parked');
            $table->timestamp('arrival_time');
            $table->timestamps();
        });

        // 2. Table to log every M-Pesa transaction
        Schema::create('mpesa_transactions', function (Blueprint $table) {
            $table->id();
            $table->string('checkout_request_id')->unique();
            $table->string('merchant_request_id');
            $table->decimal('amount', 10, 2);
            $table->string('status')->default('pending'); // pending, success, failed
            $table->foreignId('vehicle_id')->constrained('vehicles')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mpesa_transactions');
        Schema::dropIfExists('vehicles');
    }
};
