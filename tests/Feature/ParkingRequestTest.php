<?php

namespace Tests\Feature;

use App\Services\BrevoService;
use Tests\TestCase;

class ParkingRequestTest extends TestCase
{
    private function payload(): array
    {
        return ['location' => 'M5V 2T6', 'parking_type' => 'overnight', 'start_date' => '2026-11-01',
            'start_time' => '18:00', 'timezone' => 'America/Toronto', 'budget' => null,
            'name' => 'Test Driver', 'email' => 'driver@example.com', 'phone' => '+1 416 555 1234'];
    }

    public function test_request_sends_admin_email_without_booking(): void
    {
        $this->mock(BrevoService::class)->shouldReceive('sendEmail')->once()
            ->withArgs(fn ($to, $subject, $html) => $to === config('parking.requests_email')
                && str_contains($html, 'M5V 2T6') && str_contains($html, 'America/Toronto'))
            ->andReturn(true);
        $this->post(route('parking-requests.store'), $this->payload())->assertSessionHasNoErrors()->assertSessionHas('success');
    }

    public function test_email_failure_does_not_report_success(): void
    {
        $this->mock(BrevoService::class)->shouldReceive('sendEmail')->once()->andReturn(false);
        $this->post(route('parking-requests.store'), $this->payload())->assertSessionHasErrors('submission')->assertSessionMissing('success');
    }

    public function test_missing_contact_details_are_rejected(): void
    {
        $this->mock(BrevoService::class)->shouldNotReceive('sendEmail');
        $this->post(route('parking-requests.store'), [])->assertSessionHasErrors(['location', 'name', 'email', 'phone']);
    }
}
