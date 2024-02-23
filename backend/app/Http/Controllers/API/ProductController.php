<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
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
}
