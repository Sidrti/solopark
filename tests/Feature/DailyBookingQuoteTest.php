<?php

namespace Tests\Feature;

use App\Models\ParkingSpot;
use App\Models\ParkingSpotAvailability;
use App\Services\DailyBookingQuote;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class DailyBookingQuoteTest extends TestCase
{
    private function spot(?float $limitedMonthly = null): ParkingSpot
    {
        $attrs = ['price_daily' => 12, 'price_monthly' => 80, 'is_24_7' => false];
        if ($limitedMonthly !== null) {
            $attrs['price_monthly_limited'] = $limitedMonthly;
        }
        $spot = new ParkingSpot($attrs);
        $spot->setRelation('availabilities', collect([
            new ParkingSpotAvailability(['day_of_week' => 'Mon', 'start_time' => '18:00:00', 'end_time' => '06:00:00']),
        ]));
        return $spot;
    }

    private function input(): array
    {
        return ['startDate' => '2026-10-26', 'endDate' => '2026-10-26', 'startTime' => '18:00',
            'endTime' => '06:00', 'days' => 'Mon', 'timezone' => 'America/Toronto', 'spaces_count' => 1];
    }

    public function test_overnight_window_ends_the_following_morning(): void
    {
        $quote = (new DailyBookingQuote())->quote($this->spot(), $this->input());
        $this->assertSame(12.0, $quote['subtotal']);
        // 12 base + 10% fee (1.20) + 13% tax (1.716) + 3% gateway (0.44748) = 15.36348 => ceil = 16.0
        $this->assertSame(16.0, $quote['total_price']);
    }

    public function test_uses_price_monthly_limited_anchor(): void
    {
        $spot = $this->spot(45.0);
        $quote = (new DailyBookingQuote())->quote($spot, $this->input());
        $this->assertSame(12.0, $quote['subtotal']);
        $this->assertSame(16.0, $quote['total_price']);
    }

    public function test_driver_cannot_extend_overnight_window_past_checkout(): void
    {
        $this->expectException(ValidationException::class);
        (new DailyBookingQuote())->quote($this->spot(), array_replace($this->input(), ['endTime' => '07:00']));
    }

    public function test_host_must_offer_every_selected_day(): void
    {
        $this->expectException(ValidationException::class);
        (new DailyBookingQuote())->quote($this->spot(), array_replace($this->input(), ['endDate' => '2026-10-27', 'days' => 'Mon,Tue']));
    }
}
