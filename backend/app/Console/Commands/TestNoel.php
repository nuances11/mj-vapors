<?php

namespace App\Console\Commands;

use App\Models\AttributeOption;
use App\Models\Inventory;
use App\Models\Product;
use App\Models\Sku;
use App\Models\Transaction;
use App\Models\TransactionSku;
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
            $transactionSku = TransactionSku::where('sku_id', 3)->first();
            dump($transactionSku);

            $inventory = Inventory::where('branch_id', 2)
                            ->where('skus_id', 3)
                            ->first();

            dump($inventory);

            return 0;

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            dump($e->getMessage());
        }

    }
}
