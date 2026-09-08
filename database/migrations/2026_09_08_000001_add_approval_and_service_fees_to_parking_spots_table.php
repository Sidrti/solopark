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
            $table->boolean('is_approved')->default(false)->after('contact_number');
            $table->decimal('service_fee_percentage', 5, 2)->default(10.00)->after('price_monthly');
            $table->decimal('service_fee_monthly_percentage', 5, 2)->default(30.00)->after('service_fee_percentage');
        });

        // Set existing parking spots to approved and populate default fees
        DB::table('parking_spots')->update([
            'is_approved' => true,
            'service_fee_percentage' => 10.00,
            'service_fee_monthly_percentage' => 30.00,
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('parking_spots', function (Blueprint $table) {
            $table->dropColumn([
                'is_approved',
                'service_fee_percentage',
                'service_fee_monthly_percentage',
            ]);
        });
    }
};
