<?php

namespace App\Services;

class DailyPricing
{
    public static function base(int $days, float $daily, ?float $monthly, int $extraHours = 0): float
    {
        if ($days <= 0) return 0;
        if (!$monthly || $monthly >= 28 * $daily) {
            $base = $days * $daily;
        } elseif ($monthly * 0.775 > 4 * $daily) {
            if ($days <= 4) {
                $base = $days * $daily;
            } elseif ($days <= 7) {
                $base = 4 * $daily + ($days - 4) / 3 * ($monthly * 0.775 - 4 * $daily);
            } elseif ($days <= 28) {
                $base = $monthly * (0.775 + ($days - 7) / 21 * 0.225);
            } else {
                $base = $monthly + ($days - 28) * $monthly / 28;
            }
        } else {
            // When monthly rate is lower than the standard anchor, smoothly interpolate to reach $monthly at day 28
            $k = max(1, min(3, (int) floor(($monthly - 1) / max(0.01, $daily))));
            if ($days <= $k) {
                $base = $days * $daily;
            } elseif ($days <= 28) {
                $base = $k * $daily + ($days - $k) / (28 - $k) * ($monthly - $k * $daily);
            } else {
                $base = $monthly + ($days - 28) * $monthly / 28;
            }
        }
        return $base * (1 + max(0, $extraHours) / 12);
    }

    public static function fees(float $base): array
    {
        $fee = $base * 0.10;
        $tax = ($base + $fee) * 0.13;
        $gateway = ($base + $fee + $tax) * 0.03;
        return ['subtotal' => $base, 'service_fee' => $fee, 'tax' => $tax,
            'gateway_fee' => $gateway, 'total_price' => ceil(round($base + $fee + $tax + $gateway, 8))];
    }
}
