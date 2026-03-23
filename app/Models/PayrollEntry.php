<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
// ❌ Remove this line if it exists:
// use Illuminate\Database\Eloquent\SoftDeletes;

class PayrollEntry extends Model
{
    // ❌ Remove this line if it exists:
    // use SoftDeletes;

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
        return $this->belongsTo(Employee::class);
    }

    public function deductions()
    {
        return $this->hasMany(PayrollDeduction::class);
    }
}