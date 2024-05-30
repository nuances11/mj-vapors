<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use OwenIt\Auditing\Contracts\Auditable;

class TimeTracking extends Model implements Auditable
{
    use HasFactory, \OwenIt\Auditing\Auditable;

    protected $fillable = [
        'user_id',
        'branch_id',
        'work_time',
        'work_date',
        'clock_in',
        'clock_in_remarks',
        'clock_out',
        'clock_out_remarks',
        'break_in',
        'break_out',
        'break_time_in_seconds',
        'total_hours_in_seconds',
        'last_action',
    ];

    protected $with = ['branch', 'user'];

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeSearch($query, $keyword)
    {
        $query->when($keyword !== '', function ($query) use ($keyword) {
            $query->whereHas('branch', function($q) use ($keyword) {
                $q->whereRaw("name LIKE '%$keyword%'");
            });
            $query->orWhereHas('user', function($q) use ($keyword) {
                $q->whereRaw("first_name LIKE '%$keyword%'");
                $q->orWhereRaw("last_name LIKE '%$keyword%'");
            });
        });
    }

    public function scopeFilter($query, array $filters)
    {

        foreach ($filters as $filter_name => $filter_value) {
            $query->where($filter_name, $filter_value);
        }
    }

}
