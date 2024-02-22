<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class BranchController extends BaseController
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

            $query = Branch::search($searchKeyword)
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
            $user = new Branch();

            $user = $user->create($request->all());
            $user->refresh();

            DB::commit();
            return $this->sendResponse($user, 'Branch created');
        } catch(Exception $e) {
            DB::rollBack();

            return $this->sendError($e->getMessage(), ['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Branch $branch)
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
            $branch = Branch::findOrFail($id);

            $branch = $branch->update($request->all());

            DB::commit();
            return $this->sendResponse($branch, 'Branch updated successfully.');
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
            $user = Branch::findOrFail($id);
            $user->delete();
            DB::commit();
            return $this->sendResponse([], 'Branch deleted successfully.');

        } catch(Exception $e) {
            DB::rollBack();
            return $this->sendError($e->getMessage(), ['error' => $e->getMessage()], 500);
        }
    }
}
