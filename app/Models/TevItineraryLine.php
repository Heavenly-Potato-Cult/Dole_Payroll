<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TevItineraryLine extends Model
{
    protected $table = 'tev_itinerary_lines';

    protected $fillable = [
        'tev_request_id',
        'travel_date',
        'origin',
        'destination',
        'departure_time',
        'arrival_time',
        'mode_of_transport',
        'transportation_cost',
        'per_diem_amount',
        'is_half_day',
        'remarks',
    ];

    protected $casts = [
        'travel_date'        => 'date',
        'transportation_cost' => 'decimal:2',
        'per_diem_amount'    => 'decimal:2',
        'is_half_day'        => 'boolean',
    ];

    // ── Relationships ─────────────────────────────────────────────────────

    public function tevRequest()
    {
        return $this->belongsTo(TevRequest::class, 'tev_request_id');
    }
}