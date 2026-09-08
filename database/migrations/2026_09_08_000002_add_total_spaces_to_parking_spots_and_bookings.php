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
        Schema::table('parking_spots', function (Blueprint $table) {
            $table->unsignedInteger('total_spaces')->default(1)->after('parking_type');
        });

        Schema::table('bookings', function (Blueprint $table) {
            $table->unsignedInteger('spaces_count')->default(1)->after('vehicle_id');
        });

        // Ensure all existing rows have default 1 space
        DB::table('parking_spots')->whereNull('total_spaces')->orWhere('total_spaces', 0)->update([
            'total_spaces' => 1,
        ]);

        DB::table('bookings')->whereNull('spaces_count')->orWhere('spaces_count', 0)->update([
            'spaces_count' => 1,
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('parking_spots', function (Blueprint $table) {
            $table->dropColumn('total_spaces');
        });

        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn('spaces_count');
        });
    }
};
