<?php

namespace App\Http\Controllers;

use App\Models\Vehicle;
use Illuminate\Http\Request;
use Iankumu\Mpesa\Facades\Mpesa;
use Inertia\Inertia;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class ParkingController extends Controller
{
    /**
     * ROLE: ADMIN - Dashboard Overview
     */
    public function index()
    {
        $active_vehicles = Vehicle::whereIn('status', ['parked', 'paid'])->get();

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
     * ROLE: DRIVER - Initiate M-Pesa Payment
     */
    public function pay($id)
    {
        $vehicle = Vehicle::findOrFail($id);
        $amount = $this->calculateFee($vehicle->arrival_time);

        if ($amount <= 0) {
            $vehicle->update(['status' => 'paid']);
            return back()->with('success', 'Free parking period applied.');
        }

        try {
            // Trigger STK Push
            $response = Mpesa::stkpush($vehicle->phone_number, $amount, 'ParkingAcc');

            return back()->with('success', 'STK Push sent to ' . $vehicle->phone_number);
        } catch (\Exception $e) {
            Log::error('Mpesa Pay Error: ' . $e->getMessage());
            return back()->with('error', 'M-Pesa Error: ' . $e->getMessage());
        }
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

    /**
     * Private Utility: Calculate Fee
     */
    private function calculateFee($arrivalTime)
    {
        if (!$arrivalTime) return 50;
        $arrival = Carbon::parse($arrivalTime);
        $totalMinutes = now()->diffInMinutes($arrival);

        if ($totalMinutes <= 15) return 0;

        $hours = ceil($totalMinutes / 60);
        return $hours * 50;
    }

    /**
     * ROLE: MPESA - Callback
     */
    public function callback(Request $request)
    {
        $data = json_decode($request->getContent(), true);
        $resultCode = $data['Body']['stkCallback']['ResultCode'] ?? 1;

        if ($resultCode == 0) {
            $meta = $data['Body']['stkCallback']['CallbackMetadata']['Item'] ?? [];
            $phoneItem = collect($meta)->firstWhere('Name', 'PhoneNumber');
            $phoneNumber = $phoneItem['Value'] ?? null;

            if ($phoneNumber) {
                $vehicle = Vehicle::where('phone_number', $phoneNumber)
                    ->where('status', 'parked')
                    ->latest()
                    ->first();

                if ($vehicle) {
                    $vehicle->update(['status' => 'paid']);
                }
            }
        }

        return response()->json(['ResultCode' => 0, 'ResultDesc' => 'Accepted']);
    }
}
