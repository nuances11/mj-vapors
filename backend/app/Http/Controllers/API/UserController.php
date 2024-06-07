<?php

namespace App\Http\Controllers\API;

use App\Http\Acl;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserBranch;
use App\Models\UserCommission;
use Exception;
use Faker\Provider\Base;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

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

            $query = User::with(['commission'])
                ->search($searchKeyword)
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

            $password = Hash::make($request->password);

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
            $user = User::with(['commission'])->findOrFail($user->id);

            $commission = new UserCommission(['settings' => $request->commission]);
            $user->commission()->save($commission);

            if ($request->branch_id)
            {
                $branch = new UserBranch(['branch_id' => $request->branch_id]);
                $user->branch()->save($branch);
            }

            $role = Role::findByName($request->user_type);

            $user->syncRoles($role);


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
    public function show($id)
    {
        $user = User::findOrFail($id);
        return $this->sendResponse($user, 'User fetched successfully.');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            $user = User::with(['commission'])->findOrFail($id);

            $user->update($request->all());

            if ($user->commission === null)
            {
                $commission = new UserCommission(['settings' => $request->commission]);
                $user->commission()->save($commission);
            }
            else
            {
                $user->commission->update(['settings' => $request->commission]);
            }

//            if ($user->branch === null && $request->branch_id) {
            if ($request->user_type === 'branch_admin' && $request->branch_id) {
                $branch = new UserBranch(['branch_id' => $request->branch_id]);
                $user->branch()->save($branch);
            } else {
                if ($user->branch !== null) {
                    $user->branch()->delete();
                }

            }

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

    public function getBranch($id)
    {
        $userBranch = UserBranch::with(['branch'])
                            ->where('user_id', $id)
                            ->first();
        return $this->sendResponse($userBranch, 'User branch fetched.');
    }

    public function changePassword(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            $user = User::findOrFail($id);
            if ($request->profile_page) {
                if(Hash::check($request->old_password, $user->password)) {
                    $user->password = bcrypt($request->password);
                    $user->save();
                    DB::commit();
                    return $this->sendResponse([], 'Password updated.');

                } else {
                    return response()->json([
                        'success' => false,
                        'message' => 'Password not matched.'
                    ]);
                }
            } else {
                if (Auth::user()->user_type === 'super_admin') {
                    $user->password = bcrypt($request->password);
                    $user->save();
                }
            }

            DB::commit();
            return $this->sendResponse([], 'Password updated.');

        } catch(Exception $e) {
            DB::rollBack();
            return $this->sendError($e->getMessage(), ['error' => $e->getMessage()], 500);
        }
    }
}
