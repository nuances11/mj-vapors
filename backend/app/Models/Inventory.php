<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class Inventory extends Model implements Auditable
{
    use HasFactory;
    use \OwenIt\Auditing\Auditable;

    protected $appends = [
        'branch_name',
        'product_sku',
    ];

    protected $fillable = ['branch_id', 'stock_quantity', 'skus_id'];

    public function branch(){
        return $this->belongsTo(Branch::class);
    }

    public function skus()
    {
        return $this->belongsTo(Sku::class);
    }

    public function getBranchNameAttribute()
    {
        return $this->branch->name ?? '';
    }

    public function getProductSkuAttribute()
    {
        return $this->skus->code ?? '';
    }

    public function scopeSearch($query, $keyword)
    {
        $query->when($keyword !== '', function ($query) use ($keyword) {
            $query->whereHas('skus', function($q)  use ($keyword) {
                $q->whereRaw("code LIKE '%$keyword%'");
            });
            $query->orWhereHas('branch', function($q)  use ($keyword) {
                $q->whereRaw("name LIKE '%$keyword%'");
            });
        });
    }

    public function scopeFilter($query, $filters)
    {
        $query->when(
            $filters['branch'] ?? false,
            fn ($query, $branchId) =>
            $query->where(
                fn ($query) =>
                $query
                    ->where('branch_id', $branchId)
            )
        );
    }
}
