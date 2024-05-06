<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class SettingUser extends Model implements Auditable
{
    use HasFactory;
    use \OwenIt\Auditing\Auditable;

    protected $fillable = [
        'base_salary',
        'commission_threshold',
        'commission_rate',
        'additional_salary',
        'updated_by',
        'created_by',
    ];

    protected $casts = [
        'base_salary' => 'decimal:2',
        'commission_threshold' => 'decimal:2',
        'commission_rate' => 'decimal:2',
        'additional_salary' => 'decimal:2',
    ];
}
