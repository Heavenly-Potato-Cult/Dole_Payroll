<?php

namespace Modules\Tev\Models;

use Illuminate\Database\Eloquent\Model;

class TevCertification extends Model
{
    protected $table = 'tev_certifications';

    protected $fillable = [
        'tev_request_id',
        'date_returned',
        'place_reported_back',
        'travel_completed',
        'annex_a_amount',
        'annex_a_particulars',
        'agency_visited',
        'appearance_date',
        'contact_person',
        'certified_by',
        'certified_at',
    ];

    protected $casts = [
        'date_returned'    => 'date',
        'appearance_date'  => 'date',
        'certified_at'     => 'datetime',
        'annex_a_amount'   => 'decimal:2',
        'travel_completed' => 'boolean',
    ];

    // ── Relationships ─────────────────────────────────────────────────────

    public function tevRequest()
    {
        return $this->belongsTo(TevRequest::class, 'tev_request_id');
    }

    public function certifier()
    {
        return $this->belongsTo(\App\Models\User::class, 'certified_by');
    }
}
