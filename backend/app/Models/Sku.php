<?php

namespace App\Models;

use BinaryCats\Sku\HasSku;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use OwenIt\Auditing\Contracts\Auditable;

class Sku extends Model implements Auditable
{
    use HasFactory, \OwenIt\Auditing\Auditable;

    protected $fillable = [
        'product_id',
        'code',
        'price',
        'attributes_options'
    ];

    protected $appends = ['variants', 'formatted_price'];

    protected $casts = [
        'attributes_options' => 'array',
        'price' => 'decimal:2'
    ];

    public function getFormattedPriceAttribute()
    {
        return number_format($this->price, 2);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function attributeOptions(): BelongsToMany
    {
        return $this->belongsToMany(AttributeOption::class,
            'attribute_option_sku', 'sku_id', 'attribute_option_id');

    }

    public function inventory(): HasMany
    {
        return $this->hasMany(Inventory::class, 'skus_id');

    }

    public function getVariantsAttribute()
    {
        $variant = [];
        if ($this->attributes_options) {
            if (count($this->attributes_options) > 0) {
                foreach ($this->attributes_options as $attrOption)
                {
                    $variant[] = $attrOption;
                }
            }
        }
        return $variant;
    }

    public function scopeSearch($query, $keyword)
    {
        $query->when($keyword !== '', function ($query) use ($keyword) {
            $query->where(function ($query) use ($keyword) {
                $query->whereRaw("code LIKE '%$keyword%'");
            });
            $query->orWhereHas('product', function($q)  use ($keyword) {
                $q->whereRaw("name LIKE '%$keyword%'");
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
