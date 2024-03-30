<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\SettingCompany;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SettingCompanyController extends BaseController
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
    public function show($id)
    {
        try {
            $data = SettingCompany::findOrFail($id);

            return $this->sendResponse($data, 'Company settings fetched.');

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
            $input = $request->all();
            $savedata = Arr::only($input, ['company_name']);

            $model = SettingCompany::findOrFail($id);
            $model->update($savedata);

            $model = SettingCompany::findOrFail($id); //
            DB::commit();
            return $this->sendResponse($model, 'Company settings updated.');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->sendError($e->getMessage(), ['error' => $e->getMessage()], 403);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(SettingCompany $settingCompany)
    {
        //
    }

    public function uploadLogo(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            if ($request->logo) {
                $name = 'logo.png';
                $request->logo->move(public_path("img/"), $name);
                $companySetting = SettingCompany::find($id);

                if (!$companySetting) {
                    return response()->json(['error' => 'Invalid request'], 403);
                }

                $currentUserId = Auth::user() ? Auth::user()->id : null;

                $companySetting->logo = $name;
                $companySetting->updated_by = $currentUserId;
                $companySetting->save();
                $companySetting->refresh();
                DB::commit();
                return $this->sendResponse($companySetting, 'Company settings updated.');

            } else {
                DB::rollBack();
                return response()->json([
                    "message" => 'Invalid request'
                ]);
            }

        }  catch(Exception $e) {
            DB::rollBack();
            return response()->json([
                "message" => $e->getMessage()
            ], 500);
        }
    }
}
