<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use Exception;
use Faker\Provider\Base;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserController extends BaseController
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

            $query = User::search($searchKeyword)
                ->filter($filters);

            if ($request->for_transaction) {
                $query->whereNot('id', Auth::user()->id);
            }

            if ($showAllRecords) {
                $users = $query->get();
            } else {
                $users = $query->paginate($pageLimit);
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
            $user = new User();

            $randomPassword = Str::random(8);
            $password = Hash::make($randomPassword);

            $user = $user->create([
                "first_name" => $request->first_name,
                "last_name" => $request->last_name,
                "user_name" => $request->user_name,
                "email" => $request->email,
                'user_type' => $request->user_type,
                'remember_token' => Str::random(10),
                'password' => $password
            ]);
            $user->refresh();

            DB::commit();
            return $this->sendResponse($user, 'User created');
        } catch(Exception $e) {
            DB::rollBack();

            return $this->sendError($e->getMessage(), ['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(User $user)
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
            $user = User::findOrFail($id);

            $user = $user->update($request->all());

            DB::commit();
            return $this->sendResponse($user, 'User updated successfully.');
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
            $user = User::findOrFail($id);
            $user->delete();
            DB::commit();
            return $this->sendResponse([], 'User deleted successfully.');

        } catch(Exception $e) {
            DB::rollBack();
            return $this->sendError($e->getMessage(), ['error' => $e->getMessage()], 500);
        }
    }

    public function checkPassword (Request $request)
    {
        try {
            $user = Auth::user();
            $matched = Hash::check($request->password, $user->password);
            if ($matched) {
                return $this->sendResponse([
                    'matched' => $matched
                ], 'Password matched.');
            } else {
                return $this->sendResponse([
                    'matched' => $matched
                ], 'Password not matched.');
            }

        }
        catch(Exception $e) {

            return $this->sendError($e->getMessage(), ['error' => $e->getMessage()], 500);
        }
    }
}
