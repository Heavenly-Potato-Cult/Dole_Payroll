<?php

namespace Modules\Payroll\Models;

use Illuminate\Database\Eloquent\Model;

class PayrollAuditLog extends Model
{
    // Map Laravel's timestamp columns to your migration's column names
    const CREATED_AT = 'performed_at';
    const UPDATED_AT = null;           // immutable — no updated_at

    protected $table = 'payroll_audit_log';

    protected $fillable = [
        'payroll_batch_id',
        'user_id',
        'action',
        'old_value',
        'new_value',
        'notes',
        'ip_address',
        'performed_at',
    ];

    protected $casts = [
        'performed_at' => 'datetime',
    ];

    public function batch()
    {
        return $this->belongsTo(PayrollBatch::class, 'payroll_batch_id');
    }

    public function user()
    {
        return $this->belongsTo(\App\Models\User::class);
    }
}
