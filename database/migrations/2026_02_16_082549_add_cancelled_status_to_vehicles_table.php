<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // We use a raw query because changing ENUMs via Blueprint can be tricky in some MySQL versions
        DB::statement("ALTER TABLE vehicles MODIFY COLUMN status ENUM('parked', 'paid', 'completed', 'cancelled') DEFAULT 'parked'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE vehicles MODIFY COLUMN status ENUM('parked', 'paid', 'completed') DEFAULT 'parked'");
    }
};
