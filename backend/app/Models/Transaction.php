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

    public function scopeSearch($query, $keyword)
    {
        $query->when($keyword !== '', function ($query) use ($keyword) {
            $query->where(function ($query) use ($keyword) {
                $query->whereRaw("id LIKE '%$keyword%'");
                $query->orWhereRaw("total_amount LIKE '%$keyword%'");
                $query->orWhereRaw("transaction_type LIKE '%$keyword%'");
                $query->orWhereRaw("status LIKE '%$keyword%'");
                $query->orWhereHas('branch', function($q) use ($keyword) {
                    $q->whereRaw("name LIKE '%$keyword%'");
                });
            });
        });
    }

    public function scopeFilter($query, array $filters)
    {
        $query->when(
            $filters['status'] ?? false,
            fn ($query, $status) =>
            $query->where(
                fn ($query) =>
                $query
                    ->where('status', $status)
            )
        );

        $query->when(
            $filters['transaction_type'] ?? false,
            fn ($query, $transactionType) =>
            $query->where(
                fn ($query) =>
                $query
                    ->where('transaction_type', $transactionType)
            )
        );

        $query->when(
            $filters['user'] ?? false,
            fn ($query, $user) =>
            $query->where(
                fn ($query) =>
                $query
                    ->where('user_id', $user['id'])
            )
        );

        $query->when(
            $filters['branch'] ?? false,
            fn ($query, $branchId) =>
            $query->where(
                fn ($query) =>
                $query
                    ->where('branch_id', $branchId)
            )
        );

        foreach ($filters as $filter_name => $filter_value) {
            if ($filter_name === 'transaction_date') {
                if (isset($filter_value['from']) && isset($filter_value['to'])) {
                    $from = $filter_value['from'] . ' 00:00:00';
                    $to = $filter_value['to'] . ' 23:59:59';

                    $query->whereBetween("created_at", [$from, $to]);
                } else {
                    $query->where('created_at', '>=', $filter_value);
                }
            }
        }
    }
}
