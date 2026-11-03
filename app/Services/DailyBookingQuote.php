<?php

namespace App\Services;

use App\Models\ParkingSpot;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class DailyBookingQuote
{
    public function quote(ParkingSpot $spot, array $input): array
    {
        $data = Validator::make($input, [
            'startDate' => 'required|date_format:Y-m-d',
            'endDate' => 'required|date_format:Y-m-d|after_or_equal:startDate',
            'startTime' => 'required|date_format:H:i',
            'endTime' => 'required|date_format:H:i',
            'timezone' => 'required|timezone',
            'days' => ['required', 'regex:/^(Mon|Tue|Wed|Thu|Fri|Sat|Sun)(,(Mon|Tue|Wed|Thu|Fri|Sat|Sun))*$/'],
            'spaces_count' => 'required|integer|min:1',
        ])->validate();
        $current = Carbon::parse($data['startDate'], $data['timezone']);
        $last = Carbon::parse($data['endDate'], $data['timezone']);
        if ($current->diffInDays($last) >= 1000) {
            throw ValidationException::withMessages(['endDate' => 'Choose a date range shorter than 1,000 days.']);
        }
        $selected = explode(',', $data['days']);
        $minutes = fn ($time) => ((int) substr($time, 0, 2)) * 60 + (int) substr($time, 3, 2);
        $start = $minutes($data['startTime']);
        $duration = ($minutes($data['endTime']) - $start + 1440) % 1440 ?: 1440;
        $availableDays = $spot->is_24_7 ? ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun']
            : $spot->availabilities->filter(function ($window) use ($minutes, $start, $duration) {
                $windowStart = $minutes($window->start_time);
                $windowLength = ($minutes($window->end_time) - $windowStart + 1440) % 1440 ?: 1440;
                $offset = ($start - $windowStart + 1440) % 1440;
                return $offset + $duration <= $windowLength;
            })->pluck('day_of_week')->all();

        $daily = (float) ($spot->price_daily ?? $spot->price_hourly * 12);
        $limited = $spot->price_monthly_limited;
        $count = 0;
        $base = 0;
        $month = 0;
        $bookingStart = $current->copy();

        while ($current->lte($last)) {
            $nextMonth = $bookingStart->copy()->addMonthsNoOverflow(++$month);
            $capacity = 0;
            $booked = 0;

            while ($current->lt($nextMonth)) {
                $day = $current->format('D');
                $available = in_array($day, $availableDays);
                $capacity += (int) $available;
                if ($current->lte($last) && in_array($day, $selected)) {
                    if (!$available) {
                        throw ValidationException::withMessages(['startTime' => 'Select parking hours within the host availability on every booked day.']);
                    }
                    $booked++;
                }
                $current->addDay();
            }

            $count += $booked;
            if ($booked && $limited !== null) {
                $average = (float) $limited / $capacity;
                $first = max($average, min($daily, 1.5 * $average));
                $progress = ($booked - 1) / max(1, $capacity - 1);
                $base += $booked === $capacity ? (float) $limited
                    : $booked * ($first + ($average - $first) * $progress);
            }
        }

        if (!$count) {
            throw ValidationException::withMessages(['days' => 'No selected days fall in this date range.']);
        }
        if ($limited === null) {
            $monthly = $spot->price_monthly !== null ? (float) $spot->price_monthly : null;
            $base = DailyPricing::base($count, $daily, $monthly, (int) ceil(max(0, $duration - 720) / 60));
        }

        return DailyPricing::fees($base * $data['spaces_count']);
    }
}
