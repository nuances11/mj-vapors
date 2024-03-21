<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;

class TransactionSku extends Model implements Auditable
{
    use HasFactory, \OwenIt\Auditing\Auditable, SoftDeletes;

    protected $table = 'transaction_sku';

    protected $with = ['skus'];

    public function transactions(): BelongsTo
    {
        return $this->belongsTo(Transaction::class, 'transaction_id');
    }

    public function skus(): BelongsTo
    {
        return $this->belongsTo(Sku::class, 'sku_id');
    }
}
