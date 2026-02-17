<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MpesaTransaction extends Model
{
    protected $fillable = [
        'checkout_request_id',
        'merchant_request_id',
        'amount',
        'status',
        'vehicle_id'
    ];

    /**
     * Get the vehicle that owns the transaction.
     */
    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }
}
