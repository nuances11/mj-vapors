<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Attribute;
use App\Models\AttributeOption;
use App\Models\Product;
use App\Models\Sku;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ProductController extends BaseController
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
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

            $query = Product::query()
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
            $slug = Str::slug($name);

            // Check if there is existing product listing
            $exist = Product::where('slug', $slug)
                ->first();

            if ($exist) {

                DB::rollBack();
                return $this->sendError('Product already exist.', [
                    'error' => 'Product already exist.'
                ], 403);

            } else {
                $product = new Product();
                $product->name = $name;
                $product->slug = $slug;
                $product->description = $data['description'];
                $product->save();

                DB::commit();

                return $this->sendResponse($product, 'Product created');

            }

        } catch(Exception $e) {
            DB::rollBack();

            return $this->sendError($e->getMessage(), ['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        try {
            $product = Product::findOrFail($id);

            return $this->sendResponse($product, 'Product retrieved');

        } catch(Exception $e) {

            return $this->sendError($e->getMessage(), ['error' => $e->getMessage()], 500);
        }
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
            $slug = Str::slug($name);

            // Check if there is existing product listing
            $exist = Product::where('slug', $slug)
                ->whereNot('id', $id)
                ->first();

            if ($exist) {

                DB::rollBack();
                return $this->sendError('Product already exist.', [
                    'error' => 'Product already exist.'
                ], 403);

            } else {
                $product = Product::findOrFail($id);
                $product->update([
                    'name' => $name,
                    'slug' => $slug,
                    'description' => $data['description']
                ]);

                DB::commit();

                return $this->sendResponse($product, 'Product updated');

            }

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

            $sku = Product::findOrFail($id);
            $sku->delete();

            DB::commit();
            return $this->sendResponse([], 'Product deleted.');

        } catch(Exception $e) {
            DB::rollBack();

            return $this->sendError($e->getMessage(), ['error' => $e->getMessage()], 500);
        }

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


}
