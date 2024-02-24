<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Attribute;
use App\Models\AttributeOption;
use App\Models\Product;
use App\Models\Sku;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ProductController extends BaseController
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
            $name = $data['name'];
            $slug = generateSlug($name);
            $product = Product::where('slug', $slug)->first();
            if ($product) {
                DB::rollBack();
                return $this->sendError('Product name already exist.', [
                    'error' => 'Product name already exist.'
                ], 403);
            } else {
                $product = new Product();
                $product->name = $name;
                $product->slug = $slug;
                $product->description = $data['description'];
                $product->save();
                $product->refresh();

                $sku = new Sku();
                $sku->code = (string)$this->generateSku($product->id);
                $sku->price = $data['price'];
                $sku->attributes_options = $data['attribute_options'];

                $product->skus()->save($sku);

                DB::commit();
            }

            return $this->sendResponse($product, 'Product created');

        } catch(Exception $e) {
            DB::rollBack();

            return $this->sendError($e->getMessage(), ['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Product $product)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Product $product)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Product $product)
    {
        //
    }

    public function filterProductByAttributeOptions(Request $request)
    {
        // Assuming $attributeOptionIds is an array of attribute option IDs you're looking for
        $attributeOptionIds = [1, 2, 4, 5];

        $products = Product::whereHas('skus', function ($query) use ($attributeOptionIds) {
            foreach ($attributeOptionIds as $attributeOptionId) {
                $query->orWhereJsonContains('attributes_options', $attributeOptionId);
            }
        })->get();

        /*to get product count only
        $productCount = Product::whereHas('skus', function ($query) use ($attributeOptionIds) {
            foreach ($attributeOptionIds as $attributeOptionId) {
                $query->orWhereJsonContains('attributes_options', $attributeOptionId);
            }
        })->count();*/
    }

    private function generateSku($id): string
    {
//        $code = Str::upper(Str::random(10));
        $code = str_pad($id, 8, '0', STR_PAD_LEFT);
        return "MJV-" . $code;
    }
}
