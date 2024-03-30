<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Inventory;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\DB;

class InventoryController extends BaseController
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

            $query = Inventory::search($searchKeyword)
                ->filter($filters);

            $users = $query->paginate($pageLimit);

            if ($showAllRecords) {
                $users = $query->get();
            }

            return JsonResource::collection($users);
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
            $checker = Inventory::where('branch_id', $request->branch_id)
                            ->where('skus_id', $request->skus_id)
                            ->first();
            if ($checker) {
                return $this->sendError('Inventory data already exist.',
                    ['error' => 'Inventory data already exist.'], 403);
            }

            $inventory = new Inventory();

            $inventory = $inventory->create($request->all());
            $inventory->refresh();

            DB::commit();
            return $this->sendResponse($inventory, 'Inventory created');
        } catch(Exception $e) {
            DB::rollBack();

            return $this->sendError($e->getMessage(), ['error' => $e->getMessage()], 403);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Inventory $inventory)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $checker = Inventory::where('branch_id', $request->branch_id)
            ->where('skus_id', $request->skus_id)
            ->whereNot('id', $id)
            ->first();
        if ($checker) {
            return $this->sendError('Inventory data already exist.',
                ['error' => 'Inventory data already exist.'], 403);
        }

        DB::beginTransaction();
        try {
            $inventory = Inventory::findOrFail($id);

            $inventory = $inventory->update($request->all());

            DB::commit();
            return $this->sendResponse($inventory, 'Inventory updated successfully.');
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
            $inventory = Inventory::findOrFail($id);
            $inventory->delete();
            DB::commit();
            return $this->sendResponse([], 'Inventory data deleted successfully.');

        } catch(Exception $e) {
            DB::rollBack();
            return $this->sendError($e->getMessage(), ['error' => $e->getMessage()], 500);
        }
    }
}
