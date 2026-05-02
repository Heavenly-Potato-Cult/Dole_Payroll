<?php

namespace Modules\Payroll\Models;

use Illuminate\Database\Eloquent\Model;

class PayrollDeduction extends Model
{
    protected $fillable = [
        'payroll_entry_id',
        'deduction_type_id',
        'code',
        'name',
        'amount',
        'is_overridden',
        'override_reason',
    ];

    protected $casts = [
        'amount'       => 'decimal:2',
        'is_overridden' => 'boolean',
    ];

    public function entry()
    {
        return $this->belongsTo(PayrollEntry::class, 'payroll_entry_id');
    }

    public function deductionType()
    {
        return $this->belongsTo(DeductionType::class);
    }
}
