<?php

namespace App\Observers;

use App\Models\Sku;
use Illuminate\Support\Str;

class SkuObserver
{
    /**
     * Handle the Sku "created" event.
     */
    public function created(Sku $sku): void
    {
//        $code = Str::upper(Str::random());
//        $code = "MJV-" . $code . '-' . $sku->product_id . $sku->id;
//        dump($sku);
//        $sku->code = $code;
//        $sku->save();
    }

    /**
     * Handle the Sku "updated" event.
     */
    public function updated(Sku $sku): void
    {
        //
    }

    /**
     * Handle the Sku "deleted" event.
     */
    public function deleted(Sku $sku): void
    {
        //
    }

    /**
     * Handle the Sku "restored" event.
     */
    public function restored(Sku $sku): void
    {
        //
    }

    /**
     * Handle the Sku "force deleted" event.
     */
    public function forceDeleted(Sku $sku): void
    {
        //
    }
}
