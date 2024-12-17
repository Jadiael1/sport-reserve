<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ReportRequest;
use App\Http\Resources\ReportResource;
use App\Models\Reservation;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Exception;

class ReportController extends Controller
{

    public function performance(ReportRequest $request)
    {
        try {
            $startDate = Carbon::parse($request->start_date);
            $endDate = Carbon::parse($request->end_date);

            $performanceData = Reservation::select(DB::raw('DATE(start_time) as date'), DB::raw('COUNT(*) as total_reservations'))
                ->whereBetween('start_time', [$startDate, $endDate])
                ->where('status', '!=', 'CANCELED')
                ->where('status', '!=', 'WAITING')
                ->groupBy('date')
                ->orderBy('date')
                ->paginate(15);

            return new ReportResource($performanceData);
        } catch (Exception $e) {
            Log::error('Error generating performance report: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Internal server error',
                'data' => null,
                'errors' => $e->getMessage()
            ], 500);
        }
    }

    public function financial(ReportRequest $request)
    {
        try {
            $startDate = Carbon::parse($request->start_date);
            $endDate = Carbon::parse($request->end_date);

            $financialData = Payment::select(
                DB::raw('DATE(payment_date) as date'),
                DB::raw('SUM(amount) as total_amount'),
                DB::raw('COUNT(*) as total_transactions')
            )
                ->whereBetween('payment_date', [$startDate, $endDate])
                ->where('status', '!=', 'CANCELED')
                ->where('status', '!=', 'WAITING')
                ->groupBy('date')
                ->orderBy('date')
                ->paginate(15);

            return new ReportResource($financialData);
        } catch (Exception $e) {
            Log::error('Error generating financial report: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Internal server error',
                'data' => null,
                'errors' => $e->getMessage()
            ], 500);
        }
    }

    public function users(ReportRequest $request)
    {
        try {
            $startDate = Carbon::parse($request->start_date);
            $endDate = Carbon::parse($request->end_date);

            $userData = User::select(DB::raw('DATE(created_at) as date'), DB::raw('COUNT(*) as total_users'))
                ->whereBetween('created_at', [$startDate, $endDate])
                ->where('active', '!=', 0)
                ->groupBy('date')
                ->orderBy('date')
                ->paginate(15);

            return new ReportResource($userData);
        } catch (Exception $e) {
            Log::error('Error generating user report: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Internal server error',
                'data' => null,
                'errors' => $e->getMessage()
            ], 500);
        }
    }

    public function occupancy(ReportRequest $request)
    {
        try {
            $startDate = Carbon::parse($request->start_date);
            $endDate = Carbon::parse($request->end_date);

            $occupancyData = Reservation::select('reservations.field_id', 'fields.name as field_name', DB::raw('COUNT(*) as total_reservations'))
                ->join('fields', 'reservations.field_id', '=', 'fields.id')
                ->whereBetween('start_time', [$startDate, $endDate])
                ->where('reservations.status', '!=', 'CANCELED')
                ->where('reservations.status', '!=', 'WAITING')
                ->groupBy('reservations.field_id', 'fields.name')
                ->orderBy('total_reservations', 'desc')
                ->paginate(15);

            return new ReportResource($occupancyData);
        } catch (Exception $e) {
            Log::error('Error generating occupancy report: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Internal server error',
                'data' => null,
                'errors' => $e->getMessage()
            ], 500);
        }
    }
}
