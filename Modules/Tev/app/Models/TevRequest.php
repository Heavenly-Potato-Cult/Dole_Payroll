<?php

namespace Modules\Tev\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\SharedKernel\Models\Employee;
use App\SharedKernel\Models\OfficeOrder;

class TevRequest extends Model
{
    use SoftDeletes;

    protected $table = 'tev_requests';

    protected $fillable = [
        'tev_no',
        'office_order_id',
        'employee_id',
        'track',
        'purpose',
        'destination',
        'travel_type',
        'travel_date_start',
        'travel_date_end',
        'total_days',
        'total_per_diem',
        'total_transportation',
        'total_other_expenses',
        'grand_total',
        'cash_advance_amount',
        'balance_due',
        'status',
        'submitted_by',
        'submitted_at',
        'remarks',
    ];

    protected $casts = [
        'travel_date_start'    => 'date',
        'travel_date_end'      => 'date',
        'submitted_at'         => 'datetime',
        'grand_total'          => 'decimal:2',
        'total_per_diem'       => 'decimal:2',
        'total_transportation' => 'decimal:2',
        'total_other_expenses' => 'decimal:2',
        'cash_advance_amount'  => 'decimal:2',
        'balance_due'          => 'decimal:2',
        'total_days'           => 'integer',
    ];

    // ── Relationships ─────────────────────────────────────────────────────

    public function officeOrder()
    {
        return $this->belongsTo(OfficeOrder::class, 'office_order_id');
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    public function submitter()
    {
        return $this->belongsTo(\App\Models\User::class, 'submitted_by');
    }

    public function itineraryLines()
    {
        return $this->hasMany(TevItineraryLine::class, 'tev_request_id');
    }

    public function approvalLogs()
    {
        return $this->hasMany(TevApprovalLog::class, 'tev_request_id');
    }

    public function certification()
    {
        return $this->hasOne(TevCertification::class, 'tev_request_id');
    }
}
