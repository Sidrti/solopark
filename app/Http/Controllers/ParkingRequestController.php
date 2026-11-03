<?php

namespace App\Http\Controllers;

use App\Services\BrevoService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ParkingRequestController extends Controller
{
    public function create()
    {
        return Inertia::render('RequestParking');
    }

    public function store(Request $request, BrevoService $brevo)
    {
        $data = $request->validate([
            'location' => 'required|string|max:255',
            'parking_type' => 'required|in:hourly,daily,overnight,monthly',
            'start_date' => 'required|date_format:Y-m-d',
            'start_time' => 'required|date_format:H:i',
            'timezone' => 'required|timezone',
            'budget' => 'nullable|numeric|min:0|max:100000',
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'required|string|max:30|regex:/^[+()0-9 .-]{7,30}$/',
        ]);
        $sent = $brevo->sendEmail(config('parking.requests_email'), 'New parking request - Solo Park',
            view('emails.parking-request', ['details' => $data])->render());
        if (!$sent) return back()->withErrors(['submission' => 'Your request could not be sent. Please try again.'])->withInput();
        return back()->with('success', 'Your parking request has been sent. Our team will contact you if we find a suitable host.');
    }
}
