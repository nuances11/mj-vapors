<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\SettingUser;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class SettingUserController extends BaseController
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {

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
    public function show($id)
    {
        try {
            $data = SettingUser::findOrFail($id);

            return $this->sendResponse($data, 'User settings fetched.');

        }  catch(Exception $e) {

            return response()->json([
                "message" => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            $savedata = $request->all();

            $model = SettingUser::findOrFail($id);
            $model->update($savedata);

            $model = SettingUser::findOrFail($id); //
            DB::commit();
            return $this->sendResponse($model, 'User settings updated.');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->sendError($e->getMessage(), ['error' => $e->getMessage()], 403);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(SettingUser $settingUser)
    {
        //
    }
}
