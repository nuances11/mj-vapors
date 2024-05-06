<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UserReportController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request, $id)
    {
        $dateRange = $request->transaction_date;
        $from = $dateRange['from'] . ' 00:00:00';
        $to = $dateRange['to'] . ' 23:59:59';
        $query = Transaction::select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('SUM(total_amount) as total_sales'),
                DB::raw('COUNT(*) as transaction_count')
            )
            ->whereBetween("created_at", [$from, $to])
            ->where('user_id', $id)
            ->groupBy('date')
            ->orderBy('date', 'asc')
            ->get();

        return response()->json([
            'data' => $query,
            'transaction_count' => $query->sum('transaction_count'),
            'total_sales' => number_format($query->sum('total_sales'), 2),
        ]);
    }
}
