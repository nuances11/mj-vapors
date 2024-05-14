<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;

class Branch extends Model implements Auditable
{
    use HasFactory, \OwenIt\Auditing\Auditable;
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'status',
        'time_in',
        'time_out',
        'commission_threshold',
        'commission_rate',
    ];

    public function scopeSearch($query, $keyword)
    {
        $query->when($keyword !== '', function ($query) use ($keyword) {
            $query->where(function ($query) use ($keyword) {
                $query->whereRaw("name LIKE '%$keyword%'");
            });
        });
    }

    public function scopeFilter($query, $filters)
    {
        foreach ($filters as $filter_name => $filter_value) {
            $query->where($filter_name, $filter_value);
        }
    }

    public function scopeGetActiveBranch($query)
    {
        $query->where('status', 'active');
    }
}
