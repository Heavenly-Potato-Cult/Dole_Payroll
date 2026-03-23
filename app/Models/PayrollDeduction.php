<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
// ❌ Remove this if it exists:
// use Illuminate\Database\Eloquent\SoftDeletes;

class PayrollDeduction extends Model
{
    // ❌ Remove this if it exists:
    // use SoftDeletes;

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