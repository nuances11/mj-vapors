<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\TimeTracking;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Calculation\DateTimeExcel\Time;

class TimeTrackingController extends BaseController
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
    public function show(TimeTracking $timeTracking)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, TimeTracking $timeTracking)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(TimeTracking $timeTracking)
    {
        //
    }

    public function checkLogData(Request $request)
    {
        $data = $request->all();
        $timeData = TimeTracking::where('user_id', $data['user_id'] ?? Auth::user()->id)
                    ->where('branch_id', $data['branch_id'])
                    ->whereDay('created_at', Carbon::now()->timezone('Asia/Manila')->day)
                    ->first();

        return $this->sendResponse($timeData, 'Time data fetched.');
    }

    public function logTime(Request $request)
    {
        DB::beginTransaction();
        try {
            $currentTime = Carbon::now()->timezone('Asia/Manila');
            $currentDate = Carbon::now()->timezone('Asia/Manila');
            $data = $request->all();
            if ($data['action'] === 'clock_in') {
                $timeData = TimeTracking::where('user_id', $data['user_id'] ?? Auth::user()->id)
                    ->where('branch_id', $data['branch_id'])
                    ->whereDay('created_at', Carbon::now()->timezone('Asia/Manila')->day)
                    ->first();

                if ($timeData) {
                    return $this->sendError('Duplicate clock in entry', ['error' => 'Duplicate clock in entry'], 500);
                }

                $timeRecord = new TimeTracking();
                $timeRecord->user_id = $data['user_id'];
                $timeRecord->branch_id = $data['branch_id'];
                $timeRecord->work_time = $currentTime;
                $timeRecord->work_date = $currentDate;
                $timeRecord->clock_in = $currentTime;
                $timeRecord->last_action = $data['action'];
                $timeRecord->save();

                DB::commit();

                return $this->sendResponse($timeRecord, 'Time logged');
            } elseif ($data['action'] === 'clock_out') {

                $timeData = TimeTracking::where('user_id', $data['user_id'] ?? Auth::user()->id)
                    ->where('branch_id', $data['branch_id'])
                    ->whereDay('created_at', Carbon::now()->timezone('Asia/Manila')->day)
                    ->first();
                
                if ($timeData->clock_in === null) {
                    return $this->sendError('Not yet clocked in', ['error' => 'Not yet clocked in'], 500);
                }

                $timeData->{$data['action']} = $currentTime;
                $timeData->last_action = $data['action'];

                $timeIn = Carbon::parse($timeData->clock_in, 'Asia/Manila');
                $timeData->total_hours_in_seconds = $currentTime->diffInSeconds($timeIn);

                if ($timeData->break_in === null) {
                    $timeData->break_in = $currentTime;
                }

                if ($timeData->break_out === null) {
                    $timeData->break_out = $currentTime;
                    $breakIn = Carbon::parse($timeData->break_in, 'Asia/Manila');
                    $timeData->break_time_in_seconds = $currentTime->diffInSeconds($breakIn);
                }

                $timeData->save();
                DB::commit();
                return $this->sendResponse($timeData, 'Time logged');

            } else {

                $timeData = TimeTracking::where('user_id', $data['user_id'] ?? Auth::user()->id)
                    ->where('branch_id', $data['branch_id'])
                    ->whereDay('created_at', Carbon::now()->timezone('Asia/Manila')->day)
                    ->first();
                
                if (!$timeData) {
                    return $this->sendError('No clock in entry', ['error' => 'No clock in entry'], 500);
                }

                if ($data['action'] === 'break_in' && $timeData->last_action !== 'clock_in') {
                    return $this->sendError('Not yet clocked in', ['error' => 'Not yet clocked in'], 500);
                }

                if ($data['action'] === 'break_out' && $timeData->last_action !== 'break_in') {
                    return $this->sendError('No break in record found', ['error' => 'No break in record found'], 500);
                }

                $timeData->{$data['action']} = $currentTime;
                $timeData->last_action = $data['action'];
                if ($data['action'] === 'break_out') {
                    $breakIn = Carbon::parse($timeData->break_in, 'Asia/Manila');
                    $timeData->break_time_in_seconds = $currentTime->diffInSeconds($breakIn);
                }
                $timeData->save();
                DB::commit();
                return $this->sendResponse($timeData, 'Time logged');
            }

        } catch(Exception $e) {
            DB::rollBack();

            return $this->sendError($e->getMessage(), ['error' => $e->getMessage()], 500);
        }

    }
}
