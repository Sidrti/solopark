<?php

namespace Tests\Unit;

use App\Services\DailyPricing;
use PHPUnit\Framework\TestCase;

class DailyPricingTest extends TestCase
{
    public function test_requested_prices_and_every_intermediate_day(): void
    {
        foreach ([1 => 12, 2 => 24, 3 => 36, 4 => 48, 5 => 52.67, 6 => 57.33, 7 => 62, 14 => 68, 21 => 74, 28 => 80] as $days => $expected) {
            $this->assertSame((float) $expected, round(DailyPricing::base($days, 12, 80), 2));
        }
        for ($day = 1; $day < 60; $day++) {
            $this->assertGreaterThan(DailyPricing::base($day, 12, 80), DailyPricing::base($day + 1, 12, 80));
        }
    }

    public function test_minimum_monthly_price_reaches_anchor_and_strictly_increases(): void
    {
        for ($day = 1; $day < 60; $day++) {
            $this->assertGreaterThan(DailyPricing::base($day, 12, 45), DailyPricing::base($day + 1, 12, 45));
        }
        $this->assertSame(45.0, round(DailyPricing::base(28, 12, 45), 2));
        $this->assertSame(50.0, round(DailyPricing::base(28, 12, 50), 2));
    }

    public function test_fees_and_fallbacks(): void
    {
        // 10% service fee, 13% tax, 3% Stripe processing
        $fees = DailyPricing::fees(80);
        $this->assertSame(80.0, $fees['subtotal']);
        $this->assertSame(8.0, $fees['service_fee']);
        $this->assertSame(11.44, round($fees['tax'], 2));
        $this->assertSame(2.98, round($fees['gateway_fee'], 2));
        $this->assertSame(103.0, $fees['total_price']);

        $this->assertSame(60.0, DailyPricing::base(5, 12, null));
        $this->assertSame(160.0, DailyPricing::base(28, 12, 80, 12));
    }
}
