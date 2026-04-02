<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PerDiemRate extends Model
{
    protected $table = 'per_diem_rates';

    protected $fillable = [
        'travel_type',
        'destination_category',
        'year',
        'daily_rate',
        'half_day_rate',
        'coa_circular_ref',
    ];

    protected $casts = [
        'daily_rate'    => 'decimal:2',
        'half_day_rate' => 'decimal:2',
        'year'          => 'integer',
    ];
}