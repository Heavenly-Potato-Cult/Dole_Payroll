<?php

namespace Modules\Payroll\Models;

use Illuminate\Database\Eloquent\Model;

class PayrollEntry extends Model
{
    protected $fillable = [
        'payroll_batch_id',
        'employee_id',
        'basic_salary',
        'pera',
        'rata',
        'gross_income',
        'lwop_days',
        'lwop_deduction',
        'tardiness',
        'undertime',
        'total_deductions',
        'net_amount',
    ];

    public function batch()
    {
        return $this->belongsTo(PayrollBatch::class, 'payroll_batch_id');
    }

    public function employee()
    {
        return $this->belongsTo(\App\SharedKernel\Models\Employee::class);
    }

    public function deductions()
    {
        return $this->hasMany(PayrollDeduction::class);
    }
}
