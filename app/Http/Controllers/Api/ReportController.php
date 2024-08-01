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
    /**
     * @OA\Get(
     *     path="/api/v1/reports/performance",
     *     summary="Get performance report",
     *     operationId="getPerformanceReport",
     *     tags={"Reports"},
     *     security={{"bearerAuth": {}}},
     *     description="Retrieve performance data based on reservations within a date range.",
     *     @OA\Parameter(
     *         name="start_date",
     *         in="query",
     *         required=true,
     *         @OA\Schema(type="string", format="date"),
     *         description="Start date for the report"
     *     ),
     *     @OA\Parameter(
     *         name="end_date",
     *         in="query",
     *         required=true,
     *         @OA\Schema(type="string", format="date"),
     *         description="End date for the report"
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Successful response",
     *         @OA\JsonContent(ref="#/components/schemas/ReportResource")
     *     ),
     *     @OA\Response(
     *         response="400",
     *         description="Bad request",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Bad request"),
     *             @OA\Property(property="data", type="object", nullable=true),
     *             @OA\Property(property="errors", type="string", example="Validation error details")
     *         )
     *     ),
     *     @OA\Response(
     *         response="500",
     *         description="Internal server error",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Internal server error"),
     *             @OA\Property(property="data", type="object", nullable=true),
     *             @OA\Property(property="errors", type="string", example="Error details")
     *         )
     *     )
     * )
     */
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

    /**
     * @OA\Get(
     *     path="/api/v1/reports/financial",
     *     summary="Get financial report",
     *     operationId="getFinancialReport",
     *     tags={"Reports"},
     *     security={{"bearerAuth": {}}},
     *     description="Retrieve financial data based on payments within a date range.",
     *     @OA\Parameter(
     *         name="start_date",
     *         in="query",
     *         required=true,
     *         @OA\Schema(type="string", format="date"),
     *         description="Start date for the report"
     *     ),
     *     @OA\Parameter(
     *         name="end_date",
     *         in="query",
     *         required=true,
     *         @OA\Schema(type="string", format="date"),
     *         description="End date for the report"
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Successful response",
     *         @OA\JsonContent(ref="#/components/schemas/ReportResource")
     *     ),
     *     @OA\Response(
     *         response="400",
     *         description="Bad request",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Bad request"),
     *             @OA\Property(property="data", type="object", nullable=true),
     *             @OA\Property(property="errors", type="string", example="Validation error details")
     *         )
     *     ),
     *     @OA\Response(
     *         response="500",
     *         description="Internal server error",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Internal server error"),
     *             @OA\Property(property="data", type="object", nullable=true),
     *             @OA\Property(property="errors", type="string", example="Error details")
     *         )
     *     )
     * )
     */
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

    /**
     * @OA\Get(
     *     path="/api/v1/reports/users",
     *     summary="Get user report",
     *     operationId="getUserReport",
     *     tags={"Reports"},
     *     security={{"bearerAuth": {}}},
     *     description="Retrieve user data based on registrations within a date range.",
     *     @OA\Parameter(
     *         name="start_date",
     *         in="query",
     *         required=true,
     *         @OA\Schema(type="string", format="date"),
     *         description="Start date for the report"
     *     ),
     *     @OA\Parameter(
     *         name="end_date",
     *         in="query",
     *         required=true,
     *         @OA\Schema(type="string", format="date"),
     *         description="End date for the report"
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Successful response",
     *         @OA\JsonContent(ref="#/components/schemas/ReportResource")
     *     ),
     *     @OA\Response(
     *         response="400",
     *         description="Bad request",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Bad request"),
     *             @OA\Property(property="data", type="object", nullable=true),
     *             @OA\Property(property="errors", type="string", example="Validation error details")
     *         )
     *     ),
     *     @OA\Response(
     *         response="500",
     *         description="Internal server error",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Internal server error"),
     *             @OA\Property(property="data", type="object", nullable=true),
     *             @OA\Property(property="errors", type="string", example="Error details")
     *         )
     *     )
     * )
     */
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

    /**
     * @OA\Get(
     *     path="/api/v1/reports/occupancy",
     *     summary="Get occupancy report",
     *     operationId="getOccupancyReport",
     *     tags={"Reports"},
     *     security={{"bearerAuth": {}}},
     *     description="Retrieve occupancy data based on reservations within a date range.",
     *     @OA\Parameter(
     *         name="start_date",
     *         in="query",
     *         required=true,
     *         @OA\Schema(type="string", format="date"),
     *         description="Start date for the report"
     *     ),
     *     @OA\Parameter(
     *         name="end_date",
     *         in="query",
     *         required=true,
     *         @OA\Schema(type="string", format="date"),
     *         description="End date for the report"
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Successful response",
     *         @OA\JsonContent(ref="#/components/schemas/ReportResource")
     *     ),
     *     @OA\Response(
     *         response="400",
     *         description="Bad request",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Bad request"),
     *             @OA\Property(property="data", type="object", nullable=true),
     *             @OA\Property(property="errors", type="string", example="Validation error details")
     *         )
     *     ),
     *     @OA\Response(
     *         response="500",
     *         description="Internal server error",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Internal server error"),
     *             @OA\Property(property="data", type="object", nullable=true),
     *             @OA\Property(property="errors", type="string", example="Error details")
     *         )
     *     )
     * )
     */
    public function occupancy(ReportRequest $request)
    {
        try {
            $startDate = Carbon::parse($request->start_date);
            $endDate = Carbon::parse($request->end_date);

            $occupancyData = Reservation::select('field_id', DB::raw('COUNT(*) as total_reservations'))
                ->whereBetween('start_time', [$startDate, $endDate])
                ->where('status', '!=', 'CANCELED')
                ->where('status', '!=', 'WAITING')
                ->groupBy('field_id')
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
