<?php

namespace App\Http\Controllers;

use App\Models\Vehicle;
use App\Models\MpesaTransaction;
use Illuminate\Http\Request;
use Iankumu\Mpesa\Facades\Mpesa;
use Inertia\Inertia;
use Carbon\Carbon;

class ParkingController extends Controller
{
    /**
     * ROLE: ADMIN - Dashboard Overview
     */
    public function index()
    {
        $active_vehicles = Vehicle::whereIn('status', ['parked', 'paid'])->get();

        // Ensure resources/js/Pages/Dashboard.jsx exists
        return Inertia::render('Dashboard', [
            'vehicles' => $active_vehicles->map(function ($vehicle) {
                return [
                    'id' => $vehicle->id,
                    'plate_number' => $vehicle->plate_number,
                    'phone_number' => $vehicle->phone_number,
                    'slot_number' => $vehicle->slot_number,
                    'status' => $vehicle->status,
                    'arrival_time' => $vehicle->arrival_time ? Carbon::parse($vehicle->arrival_time)->diffForHumans() : 'Unknown',
                    'current_fee' => $this->calculateFee($vehicle->arrival_time),
                ];
            }),
            'stats' => [
                'total' => 50,
                'occupied' => $active_vehicles->where('status', 'parked')->count(),
                'available' => 50 - $active_vehicles->where('status', 'parked')->count(),
            ],
        ]);
    }

    /**
     * ROLE: DRIVER - Public View
     */
    public function publicView()
    {
        $occupied = Vehicle::whereIn('status', ['parked', 'paid'])->pluck('slot_number')->toArray();

        // Ensure resources/js/Pages/PublicParking.jsx exists
        return Inertia::render('PublicParking', [
            'available_slots' => 50 - count($occupied),
            'occupied_slots' => $occupied,
            'searchResult' => session('searchResult'),
        ]);
    }

    /**
     * ROLE: DRIVER - Search Vehicle
     */
    public function search(Request $request)
    {
        $request->validate(['plate_number' => 'required|string']);

        $vehicle = Vehicle::where('plate_number', strtoupper($request->plate_number))
            ->whereIn('status', ['parked', 'paid'])
            ->first();

        if (!$vehicle) {
            return back()->with('error', 'Vehicle not found or already exited.');
        }

        $data = [
            'id' => $vehicle->id,
            'plate_number' => $vehicle->plate_number,
            'slot_number' => $vehicle->slot_number,
            'status' => $vehicle->status,
            'arrival_time' => Carbon::parse($vehicle->arrival_time)->toDateTimeString(),
            'current_fee' => $this->calculateFee($vehicle->arrival_time),
        ];

        return back()->with('searchResult', $data);
    }

    /**
     * ROLE: DRIVER - Initiate Payment (Simulated)
     * FIXED: Added missing pay method referenced in web.php
     */
    public function pay($id)
    {
        $vehicle = Vehicle::findOrFail($id);

        // Simulating a successful payment for now
        $vehicle->update(['status' => 'paid']);

        return back()->with('success', 'Payment confirmed for ' . $vehicle->plate_number);
    }

    /**
     * ROLE: DRIVER - Self-Checkout
     */
    public function exit($id)
    {
        $vehicle = Vehicle::findOrFail($id);
        $fee = $this->calculateFee($vehicle->arrival_time);
        $oldSlot = $vehicle->slot_number;

        if ($vehicle->status === 'parked' && $fee > 0) {
            return back()->with('error', 'Outstanding balance must be paid before exit.');
        }

        $vehicle->update([
            'status' => 'completed',
            'slot_number' => null,
            'exit_time' => now(),
        ]);

        session()->forget('searchResult');

        return redirect()->route('parking.public')->with('success', "Slot #{$oldSlot} is now free.");
    }

    /**
     * ROLE: DRIVER - Generate Receipt
     */
    public function downloadReceipt($id)
    {
        $vehicle = Vehicle::find($id);

        if (!$vehicle) {
            return response("Vehicle not found.", 404);
        }

        // Allow receipt even if status is 'completed' or 'cancelled' for historical records
        if (!in_array($vehicle->status, ['paid', 'completed', 'cancelled'])) {
            return response("Payment required before viewing receipt.", 403);
        }

        $fee = $this->calculateFee($vehicle->arrival_time);

        $data = [
            'plate'   => $vehicle->plate_number,
            'arrival' => Carbon::parse($vehicle->arrival_time)->format('M d, Y h:i A'),
            'exit'    => $vehicle->exit_time ? Carbon::parse($vehicle->exit_time)->format('M d, Y h:i A') : now()->format('M d, Y h:i A'),
            'amount'  => $fee,
            'ref'     => 'SP-' . strtoupper(substr(md5($vehicle->id . $vehicle->arrival_time), 0, 10))
        ];

        return view('receipt', $data);
    }

    /**
     * ROLE: ADMIN - Check-in
     */
    public function store(Request $request)
    {
        $request->validate([
            'plate_number' => 'required|string|max:10',
            'phone_number' => 'required|numeric|digits:12|regex:/^254/',
            'slot_number'  => 'required|integer|min:1|max:50',
        ]);

        $occupied = Vehicle::where('slot_number', $request->slot_number)
            ->whereIn('status', ['parked', 'paid'])
            ->exists();

        if ($occupied) return back()->with('error', 'Slot already occupied.');

        Vehicle::create([
            'plate_number' => strtoupper($request->plate_number),
            'phone_number' => $request->phone_number,
            'slot_number'  => $request->slot_number,
            'arrival_time' => now(),
            'status'       => 'parked',
        ]);

        return back()->with('success', 'Vehicle checked in.');
    }

    /**
     * ROLE: ADMIN - Manual Payment
     */
    public function manualPay($id)
    {
        $vehicle = Vehicle::findOrFail($id);
        $vehicle->update(['status' => 'paid']);
        return back()->with('success', 'Payment confirmed manually.');
    }

    /**
     * ROLE: ADMIN - Force Removal
     */
    public function forceDelete($id)
    {
        $vehicle = Vehicle::findOrFail($id);
        $vehicle->update([
            'status' => 'cancelled',
            'slot_number' => null,
            'exit_time' => now()
        ]);
        return back()->with('success', 'Vehicle removed.');
    }

    private function calculateFee($arrivalTime)
    {
        if (!$arrivalTime) return 50;
        $arrival = Carbon::parse($arrivalTime);
        $totalMinutes = now()->diffInMinutes($arrival);
        if ($totalMinutes <= 15) return 0;
        $hours = ceil($totalMinutes / 60);
        return $hours * 50;
    }

    public function callback(Request $request)
    {
        // ... M-Pesa Logic
        return response()->json(['ResultCode' => 0, 'ResultDesc' => 'Accepted']);
    }
}
