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
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE appointment_slots MODIFY status ENUM('available', 'booked', 'blocked') NOT NULL DEFAULT 'available'");
        } else {
            Schema::table('appointment_slots', function (Blueprint $table) {
                $table->string('status')->default('available')->change();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("UPDATE appointment_slots SET status = 'available' WHERE status = 'blocked'");
            DB::statement("ALTER TABLE appointment_slots MODIFY status ENUM('available', 'booked') NOT NULL DEFAULT 'available'");
        } else {
            DB::statement("UPDATE appointment_slots SET status = 'available' WHERE status = 'blocked'");
            Schema::table('appointment_slots', function (Blueprint $table) {
                $table->string('status')->default('available')->change();
            });
        }
    }
};
