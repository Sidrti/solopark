<?php

return [
    'requests_email' => env('PARKING_REQUESTS_EMAIL', 'teron@live.ca'),
    // Manual payout estimates only; no automatic transfer is made.
    'host_deduction_percentage' => (float) env('HOST_PAYOUT_DEDUCTION_PERCENTAGE', 10),
];
