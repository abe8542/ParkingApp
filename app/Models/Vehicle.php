<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Vehicle extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'plate_number',
        'phone_number',
        'slot_number',
        'arrival_time',
        'status', // 'parked' or 'paid'
    ];

    /**
     * The attributes that should be cast to native types.
     * This allows us to use Carbon methods like ->diffInHours()
     */
    protected $casts = [
        'arrival_time' => 'datetime',
    ];

    /**
     * Get the M-Pesa transactions for the vehicle.
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(MpesaTransaction::class);
    }

    /**
     * Scope a query to only include parked vehicles.
     */
    public function scopeParked($query)
    {
        return $query->where('status', 'parked');
    }
}
