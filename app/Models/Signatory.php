<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Signatory extends Model
{
    protected $fillable = [
        'role_type',
        'full_name',
        'position_title',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];
}
