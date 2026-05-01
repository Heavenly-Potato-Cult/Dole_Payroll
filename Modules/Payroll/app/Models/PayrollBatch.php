<?php

namespace Modules\Payroll\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PayrollBatch extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'period_year',
        'period_month',
        'cutoff',
        'period_start',
        'period_end',
        'release_date',
        'status',
        'created_by',
        'prepared_at',
        'reviewed_by',
        'reviewed_at',
        'approved_by',
        'approved_at',
        'released_by',
        'released_at',
        'remarks',
    ];

    protected $casts = [
        'period_year'  => 'integer',
        'period_month' => 'integer',
        'prepared_at'  => 'datetime',
        'reviewed_at'  => 'datetime',
        'approved_at'  => 'datetime',
        'released_at'  => 'datetime',
    ];

    // ── Relationships ─────────────────────────────────────────────────

    /** HR/Payroll Officer who submitted the batch */
    public function creator()
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }

    /** Accountant who certified funds (certify step) */
    public function reviewer()
    {
        return $this->belongsTo(\App\Models\User::class, 'reviewed_by');
    }

    /** RD/ARD who approved and released (approve step) */
    public function approver()
    {
        return $this->belongsTo(\App\Models\User::class, 'approved_by');
    }

    /** Cashier who locked after disbursement (lock step) */
    public function releaser()
    {
        return $this->belongsTo(\App\Models\User::class, 'released_by');
    }

    public function entries()
    {
        return $this->hasMany(PayrollEntry::class);
    }

    public function auditLogs()
    {
        return $this->hasMany(PayrollAuditLog::class);
    }
}
