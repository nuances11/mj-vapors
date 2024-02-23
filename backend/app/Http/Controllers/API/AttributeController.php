<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Attribute;
use App\Models\AttributeOption;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\DB;

class AttributeController extends BaseController
{
    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse|\Illuminate\Http\Resources\Json\AnonymousResourceCollection
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

            $query = Attribute::with('attributeOptions:id,value,attribute_id')
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
    public function store(Request $request): JsonResponse
    {
        DB::beginTransaction();
        try {
            $data = $request->all();
            $name = $data['name'];
            $slug = generateSlug($name);
            $attrChecker = Attribute::where('slug', $slug)->first();
            if ($attrChecker) {
                DB::rollBack();
                return $this->sendError('Attribute name already exist.', ['error' => 'Attribute name already exist.'], 403);
            } else {
                $attribute = new Attribute();
                $attribute->name = $name;
                $attribute->slug = $slug;
                $attribute->save();
                $attribute->refresh();
            }

            if (count($data['options']) > 0) {
                foreach ($data['options'] as $option) {
                    $attributeOption = new AttributeOption();
                    $attributeOption->value =$option['value'];
                    $attribute->attributeOptions()->save($attributeOption);
                }
            } else {
                DB::rollBack();
                return $this->sendError('No attribute options.', ['error' => 'No attribute options.'], 403);
            }

            DB::commit();

            return $this->sendResponse($attribute, 'Attribute created');

        } catch(Exception $e) {
            DB::rollBack();

            return $this->sendError($e->getMessage(), ['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Attribute $attribute)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
//        $collection = collect($request->options);
//        return response()->json($collection);

        DB::beginTransaction();
        try {
            $data = $request->all();
            $name = $data['name'];
            $slug = generateSlug($name);
            $attrChecker = Attribute::where('slug', $slug)->whereNot('id', $data['id'])->first();
            if ($attrChecker) {
                DB::rollBack();
                return $this->sendError('Attribute name already exist.', ['error' => 'Attribute name already exist.'], 403);
            } else {
//                $attribute = Attribute::update([
//                    'name' => $name,
//                    'slug' => $slug
//                ]);
                $attribute = Attribute::findOrFail($data['id']);
                $attribute->name = $name;
                $attribute->slug = $slug;
                $attribute->save();

                $options = collect($request->options);

                $options->each(function ($option) use ($attribute) {
                    $attribute->attributeOptions()->updateOrCreate([
                        'id' => $option['id'] ?? null,
                    ], $option);
                });

                DB::commit();

                return $this->sendResponse($attribute, 'Attribute updated');
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
            $attribute = Attribute::findOrFail($id);
            $attribute->delete();
            DB::commit();
            return $this->sendResponse([], 'Attribute deleted');

        } catch(Exception $e) {
            DB::rollBack();

            return $this->sendError($e->getMessage(), ['error' => $e->getMessage()], 500);
        }
    }

    public function deleteOption($id)
    {
        DB::beginTransaction();
        try {
            $attribute = AttributeOption::findOrFail($id);
            $attribute->delete();
            DB::commit();
            return $this->sendResponse([], 'Attribute option deleted');

        } catch(Exception $e) {
            DB::rollBack();

            return $this->sendError($e->getMessage(), ['error' => $e->getMessage()], 500);
        }
    }

}
