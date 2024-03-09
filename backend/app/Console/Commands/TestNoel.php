<?php

namespace App\Console\Commands;

use App\Models\AttributeOption;
use App\Models\Product;
use App\Models\Sku;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class TestNoel extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:test-noel';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        DB::beginTransaction();

        try {
            $product = Product::findOrFail(1);
            $sku = Sku::findOrFail(1);
            $defaultStr = (string) $product->id;
            foreach($sku->attributes_options as $option) {
                dump($option);
                $defaultStr += $option['attribute']['id'];
                $defaultStr += $option['attribute_option']['id'];
            }

            $code = str_pad(Str::uuid(), 8, '0', STR_PAD_LEFT);
            dump("MJV-" . Str::uuid());
            dump(Str::upper(Str::random()));

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            dump($e->getMessage());
        }

    }
}
