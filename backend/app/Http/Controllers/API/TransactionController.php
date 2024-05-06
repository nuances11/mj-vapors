<?php

namespace App\Http\Controllers\API;

use App\Exports\TransactionsExport;
use App\Models\Inventory;
use App\Models\Transaction;
use App\Models\TransactionSku;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Excel;

class TransactionController extends BaseController
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {

            [
                'sort' => $sort,
                'sort_field' => $sortField,
                'page_limit' => $pageLimit,
                'search_keyword' => $searchKeyword,
                'show_all_records' => $showAllRecords,
                'filters' => $filters
            ] = paginatedRequest();

            $query = Transaction::query()
                ->filter($filters)
                ->search($searchKeyword);

            if ($showAllRecords) {
                $attributes = $query->get();
            } else {
                $attributes = $query->paginate($pageLimit);
            }

            return JsonResource::collection($attributes);


        } catch(Exception $e) {
            return response()->json([
                "message" => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        DB::beginTransaction();
        try {
            $data = $request->all();
            $transactionForm = $data['transaction_form'];
            $transactionReference = '';
            if ($transactionForm['transaction_type'] !== 'cash') {
                 $transactionReference = $transactionForm['bank'] . ' - ' . $transactionForm['reference_number'];
            }

            $transaction = new Transaction();
            $transaction->branch_id = $transactionForm['branch_id'];
            $transaction->user_id = $transactionForm['user_id'] ?? Auth::user()->id;
            $transaction->status = 'success';
            $transaction->total_amount = $data['total_amount'];
            $transaction->transaction_type = $transactionForm['transaction_type'];
            $transaction->reference_number = $transactionReference;
            $transaction->save();
            $transaction->refresh();

            if (count($data['items']) > 0) {
                foreach ($data['items'] as $item) {
                    $transactionSku = new TransactionSku();
                    $transactionSku->sku_id = $item['id'];
                    $transactionSku->quantity = $item['qty'];
                    $transactionSku->unit_price = $item['price'];
                    $transactionSku->total_price = $item['sub_total'];

                    $transaction->transactionSku()->save($transactionSku);

                    // Update Inventory
                    $inventory = Inventory::where('branch_id', $transactionForm['branch_id'])
                        ->where('skus_id', $item['id'])
                        ->first();

                    $updatedInventory = $inventory->stock_quantity - $item['qty'];

                    if ($updatedInventory > 0) {
                        $inventory->stock_quantity = (int)$updatedInventory;
                        $inventory->save();
                    } else {
                        DB::rollBack();
                        return $this->sendError('Item ' . $item['code'] . ' out of stock.', ['error' => 'Item ' . $item['code'] . ' out of stock.'], 403);
                    }
                }
            } else {
                DB::rollBack();
                return $this->sendError('No items added.', ['error' => 'No items added.'], 403);
            }
            DB::commit();
            return $this->sendResponse($transaction, 'Transaction created');

        } catch(Exception $e) {
            DB::rollBack();

            return $this->sendError($e->getMessage(), ['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Transaction $transaction)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Transaction $transaction)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Transaction $transaction)
    {
        //
    }

    public function getTransactionItems(Request $request, $id)
    {

        try {

            $transaction = Transaction::findOrFail($id);
            $data['transaction'] = $transaction;
            $data['items'] = $transaction->transactionSku;

            return $this->sendResponse($data, 'Transaction items retrieved');

        } catch(Exception $e) {
            return $this->sendError($e->getMessage(), ['error' => $e->getMessage()], 500);
        }

    }

    public function updateTransactionStatus(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            $transaction = Transaction::findOrFail($id);

            $transaction->update([
                'status' => $request->status
            ]);

            if ($request->status === 'cancelled')
            {
                $transaction = Transaction::where('id', $id)->first();
                if ($transaction->transactionSku) {

                    foreach ($transaction->transactionSku as $sku)
                    {

                        // Update Inventory
                        $inventory = Inventory::where('branch_id', $transaction->branch_id)
                            ->where('skus_id', $sku->sku_id)
                            ->first();

                        $updatedInventory = $inventory->stock_quantity + $sku->quantity;

                        $inventory->stock_quantity = (int)$updatedInventory;
                        $inventory->save();
                    }
                }
            }

            DB::commit();

            return $this->sendResponse($transaction, 'Transaction updated.');

        } catch(Exception $e) {
            DB::rollBack();
            return $this->sendError($e->getMessage(), ['error' => $e->getMessage()], 500);
        }
    }

    public function exportToCsv(Request $request)
    {
        $name = 'Transaction_Report-' . Carbon::now()->timestamp . '.csv';
        return (new TransactionsExport($request->all()))->download($name, Excel::CSV, ['Content-Type' =>
            'text/csv']);

    }
}
