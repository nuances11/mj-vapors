<?php

namespace App\Models;

use BinaryCats\Sku\HasSku;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;

class Product extends Model implements Auditable
{
    use HasFactory, \OwenIt\Auditing\Auditable, SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'description'
    ];

    protected $with = array('skus');

    public function skus(): HasMany
    {
        return $this->hasMany(Sku::class);
    }

    public function scopeSearch($query, $keyword)
    {
        $query->when($keyword !== '', function ($query) use ($keyword) {
            $query->where(function ($query) use ($keyword) {
                $query->whereRaw("name LIKE '%$keyword%'")
                    ->orWhereRaw("slug LIKE '%$keyword%'")
                    ->orWhereRaw("description LIKE '%$keyword%'");
            });
        });
    }

    public function scopeFilter($query, $filters)
    {
        foreach ($filters as $filter_name => $filter_value) {
            $query->where($filter_name, $filter_value);
        }
    }
}
