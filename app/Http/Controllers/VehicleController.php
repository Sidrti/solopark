<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Vehicle;
use Illuminate\Support\Facades\Auth;

class VehicleController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'license_plate' => 'required|string|max:20',
            'make_model' => 'required|string|max:100',
        ]);

        Vehicle::create([
            'user_id' => Auth::id(),
            'license_plate' => strtoupper($request->license_plate),
            'make_model' => $request->make_model,
        ]);

        return back()->with('success', 'Vehicle added successfully.');
    }
}
