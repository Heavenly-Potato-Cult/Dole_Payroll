<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TevApprovalLog extends Model
{
    protected $table = 'tev_approval_logs';   // WITH 's'

    const CREATED_AT = 'performed_at';
    const UPDATED_AT = null;

    protected $fillable = [
        'tev_request_id',
        'user_id',
        'step',
        'action',
        'remarks',
        'ip_address',
    ];

    protected $casts = [
        'performed_at' => 'datetime',
    ];

    // ── Relationships ─────────────────────────────────────────────────────

    public function tevRequest()
    {
        return $this->belongsTo(TevRequest::class, 'tev_request_id');
    }

    public function user()
    {
        return $this->belongsTo(\App\Models\User::class, 'user_id');
    }
}