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

class SkuController extends BaseController
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

            $query = Sku::with(['product'])
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

            // Check if there is existing product listing
            $exist = Sku::where('product_id', $data['product_id'])
                        ->whereJsonContains('attributes_options', $data['attribute_options'])
                        ->first();

            if ($exist) {

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

                    return response()->json($product->skus()->latest()->take(1));

//                    DB::commit();

                    return $this->sendResponse($product, 'Product created');

                } else {

                    DB::rollBack();
                    return $this->sendError('Product not found.', [
                        'error' => 'Product not found.'
                    ], 403);

                }
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
            $name = $data['name'];
            $slug = generateSlug($name);

            $data = array_merge($data, [
                'slug' => $slug
            ]);

            $product = Product::where('slug', $slug)->whereNot('id', $id)->first();
            if ($product) {
                DB::rollBack();
                return $this->sendError('Product name already exist.', [
                    'error' => 'Product name already exist.'
                ], 403);
            } else {
                $product = Product::findOrFail($id);
                $toUpdateProduct = Arr::only($data, [
                    'name', 'description', 'slug'
                ]);
                $product->update($toUpdateProduct);

                $product->skus()->update([
                    'price' => $data['price'],
                    'attributes_options' => $data['attribute_options'],
                ]);

                DB::commit();
            }

            return $this->sendResponse($product, 'Product created');

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

            $product = Product::findOrFail($id);
            $product->skus()->delete();
            $product->delete();

            DB::commit();
            return $this->sendResponse([], 'Product deleted.');

        } catch(Exception $e) {
            DB::rollBack();

            return $this->sendError($e->getMessage(), ['error' => $e->getMessage()], 500);
        }
    }

    private function generateSku($id, $sku): string
    {
//        $code = str_pad($id, 8, '0', STR_PAD_LEFT);
        $code = Str::upper(Str::random());
        return "MJV-" . $code . '-' . $id . $sku->id;
    }
}
