<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('parking_spots', function (Blueprint $table) {
            if (!Schema::hasColumn('parking_spots', 'price_monthly_limited')) {
                $table->decimal('price_monthly_limited', 10, 2)->nullable()->after('price_daily');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('parking_spots', function (Blueprint $table) {
            if (Schema::hasColumn('parking_spots', 'price_monthly_limited')) {
                $table->dropColumn('price_monthly_limited');
            }
        });
    }
};

