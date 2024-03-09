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
    public function update(Request $request, $id)
    {

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {


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
