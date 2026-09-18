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
        Schema::table('appointments', function (Blueprint $table) {
            $table->string('cancellation_reason')->nullable()->after('status');
        });

        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE appointments MODIFY status ENUM('booked', 'cancelled', 'cancelled_by_break') NOT NULL DEFAULT 'booked'");
        } else {
            Schema::table('appointments', function (Blueprint $table) {
                $table->string('status')->default('booked')->change();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("UPDATE appointments SET status = 'cancelled' WHERE status = 'cancelled_by_break'");
            DB::statement("ALTER TABLE appointments MODIFY status ENUM('booked', 'cancelled') NOT NULL DEFAULT 'booked'");
        } else {
            DB::statement("UPDATE appointments SET status = 'cancelled' WHERE status = 'cancelled_by_break'");
            Schema::table('appointments', function (Blueprint $table) {
                $table->string('status')->default('booked')->change();
            });
        }

        Schema::table('appointments', function (Blueprint $table) {
            $table->dropColumn('cancellation_reason');
        });
    }
};
