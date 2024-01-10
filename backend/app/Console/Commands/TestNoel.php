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
            // Create Product
            $product = Product::create([
                'name' => 'Test Product 2',
                'slug' => Str::slug('Test Product 2'),
                'description' => 'Test Description'
            ]);

            dump($product);

            // Create SKU
            $skuPrefix = "MJV";
            $skuNumber = Str::upper(Str::random(8));

            $code = "$skuPrefix-$skuNumber";

            $product->skus()->create([
                'code' => $code,
                'price' => 10.33
            ]);
            $product->refresh();

            dump($product->skus);

            $productSku = Sku::with('attributeOptions')->latest()->first();
            $attributeOptions = AttributeOption::latest()->first();

            $productSku->attributeOptions()->attach($attributeOptions->id);

            $productSku->refresh();

            dump($productSku);



            // Attach SKU to attribute options


//            DB::rollBack();
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            dump($e->getMessage());
        }

    }
}
