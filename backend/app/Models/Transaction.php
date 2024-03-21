<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use OwenIt\Auditing\Contracts\Auditable;

class Transaction extends Model implements Auditable
{
    use HasFactory, \OwenIt\Auditing\Auditable, SoftDeletes;

    protected $fillable = [
        'branch_id',
        'user_id',
        'transaction_type',
        'reference_number',
        'total_amount',
        'status',
    ];

    protected $appends = [
        'transaction_date',
        'total_items'
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

    public function transactionSku(): HasMany
    {
        return $this->hasMany(TransactionSku::class);
    }

    public function getTotalItemsAttribute(){
        return $this->transactionSku->sum('quantity') ?? 0;
    }

    public function getTransactionDateAttribute(){
        return Carbon::parse($this->created_at)->toDateTimeString();
    }

    public function getIdAttribute($value){
        $id = $value;
        return str_pad($id, 6, 0, STR_PAD_LEFT);
    }

    public function getTransactionTypeAttribute($value){
        return Str::ucfirst($value);
    }

//    public function scopeSearch($query, $keyword)
//    {
//        $query->when($keyword !== '', function ($query) use ($keyword) {
//            $query->where(function ($query) use ($keyword) {
//                $query->whereRaw("code LIKE '%$keyword%'");
//            });
//            $query->orWhereHas('product', function($q)  use ($keyword) {
//                $q->whereRaw("name LIKE '%$keyword%'");
//            });
//        });
//    }
//
//    public function scopeFilter($query, $filters)
//    {
//        foreach ($filters as $filter_name => $filter_value) {
//            $query->where($filter_name, $filter_value);
//        }
//    }
}
