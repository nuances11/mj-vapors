<?php

namespace App\Http\Controllers\API;

use App\Constants\App;
use App\Constants\BaseConstant;
use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Sku;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class   SkuController extends BaseController
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

            $query = Sku::with(['product', 'inventory'])
                ->filter($filters)
                ->search($searchKeyword);

            if (request()->has('branch_id')) {
                $branchId = request()->branch_id;
                $query->whereHas('inventory', function($q) use ($branchId) {
                    $q->where('branch_id', $branchId);
                });
            }

            if (request()->has('product_id')) {
                $productId = request()->product_id;
                $query->where('product_id', $productId);
            }

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

            // Check if there is existing product listing
            $exist = Sku::where('product_id', $data['product_id'])
//                        ->where('attributes_options', $data['attribute_options'])
                        ->get();

            $duplicates = 0;
            if ($exist) {

                foreach ($exist as $item) {
                    if ($item->attributes_options === $data['attribute_options']) {
                        $duplicates++;
                    }
                }

                if ($duplicates > 0) {
                    DB::rollBack();
                    return $this->sendError('Product listing already exist.', [
                        'error' => 'Product listing already exist.'
                    ], 403);
                } else {
                    $product = Product::findOrFail($data['product_id']);

                    if ($product) {
                        $sku = new Sku();
                        $sku->price = $data['price'];
                        $sku->attributes_options = $data['attribute_options'];

                        $product->skus()->save($sku);
                        $latest = $product->skus()->latest()->first();

                        $latest->update([
                            'code' => $this->generateSku($product->id, $latest->id)
                        ]);

                        DB::commit();

                        return $this->sendResponse($product, 'Listing created');

                    } else {

                        DB::rollBack();
                        return $this->sendError('Product not found.', [
                            'error' => 'Product not found.'
                        ], 403);

                    }
                }
            } else {
                DB::rollBack();
                return $this->sendError('No product found.', [
                    'error' => 'No product found.'
                ], 403);
            }

        } catch(Exception $e) {
            DB::rollBack();

            return $this->sendError($e->getMessage(), ['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Sku $sku)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            $data = $request->all();

            $exist = Sku::where('product_id', $data['product_id'])
                ->whereJsonContains('attributes_options', $data['attribute_options'])
                ->whereNot('id', $id)
                ->first();
            if ($exist) {
                DB::rollBack();
                return $this->sendError('Product listing already exist.', [
                    'error' => 'Product listing already exist.'
                ], 403);
            } else {
                $sku = Sku::findOrFail($id);

                $sku->update([
                    'price' => $data['price'],
                    'attributes_options' => $data['attribute_options'],
                ]);

                DB::commit();
            }

            return $this->sendResponse($sku, 'Product updated');

        } catch(Exception $e) {
            DB::rollBack();

            return $this->sendError($e->getMessage(), ['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        DB::beginTransaction();
        try {

            $sku = Sku::findOrFail($id);
            $sku->delete();

            DB::commit();
            return $this->sendResponse([], 'Listing deleted.');

        } catch(Exception $e) {
            DB::rollBack();

            return $this->sendError($e->getMessage(), ['error' => $e->getMessage()], 500);
        }
    }

    private function generateSku($id, $sku): string
    {
        $code = Str::upper(Str::random());
        return "MJV-" . $code . '-' . $id . $sku;
    }
}
