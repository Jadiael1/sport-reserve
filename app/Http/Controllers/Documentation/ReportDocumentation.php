<?php

namespace App\Http\Controllers\Documentation;

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
class ReportDocumentation {}
