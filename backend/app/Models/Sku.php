<?php

namespace App\Models;

use BinaryCats\Sku\HasSku;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
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

    protected $appends = ['variant'];

    protected $casts = [
        'attributes_options' => 'json'
    ];

    protected function price(): Attribute
    {
        return Attribute::make(
            get: static fn($value) => $value / 100,
            set: static fn($value) => $value * 100,
        );
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

    public function getVariantAttribute()
    {

        return 'Test';

    }

    public function scopeSearch($query, $keyword)
    {
        $query->when($keyword !== '', function ($query) use ($keyword) {
            $query->whereHas('product', function($q)  use ($keyword) {
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
