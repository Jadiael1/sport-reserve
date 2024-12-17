<?php

namespace App\Http\Controllers\Documentation;

/**
 * @OA\Get(
 *     path="/api/v1/reservations",
 *     operationId="getReservationsList",
 *     tags={"Reservations"},
 *     summary="Get list of reservations",
 *     description="Returns list of reservations",
 *     security={{"bearerAuth": {}}},
 *     @OA\Parameter(
 *         name="sort_by",
 *         in="query",
 *         required=false,
 *         @OA\Schema(type="string", enum={"created_at", "updated_at", "start_time", "end_time", "status"}),
 *         description="Field to sort by, e.g., created_at, updated_at, start_time, end_time, status"
 *     ),
 *     @OA\Parameter(
 *         name="sort_order",
 *         in="query",
 *         required=false,
 *         @OA\Schema(type="string", enum={"asc", "desc"}),
 *         description="Sort order: asc or desc"
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Successful operation",
 *         @OA\JsonContent(type="array", @OA\Items(ref="#/components/schemas/Reservation"))
 *     ),
 *     @OA\Response(
 *         response=400,
 *         description="Invalid sort field or sort order",
 *         @OA\JsonContent(
 *             @OA\Property(property="status", type="string", example="error"),
 *             @OA\Property(property="message", type="string", example="Invalid sort field or sort order."),
 *             @OA\Property(property="data", type="null"),
 *             @OA\Property(property="errors", type="null")
 *         )
 *     ),
 *     @OA\Response(
 *         response=500,
 *         description="Failed to retrieve reservations",
 *         @OA\JsonContent(
 *             @OA\Property(property="status", type="string", example="error"),
 *             @OA\Property(property="message", type="string", example="Failed to retrieve reservations."),
 *             @OA\Property(property="data", type="object", nullable=true),
 *             @OA\Property(property="errors", type="string", example="Error message")
 *         )
 *     )
 * )
 * @OA\Post(
 *     path="/api/v1/reservations",
 *     operationId="storeReservation",
 *     tags={"Reservations"},
 *     summary="Store a new reservation",
 *     description="Stores a new reservation and returns the reservation data",
 *     security={{"bearerAuth": {}}},
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(ref="#/components/schemas/StoreReservationRequest")
 *     ),
 *     @OA\Response(
 *         response=201,
 *         description="Successful operation",
 *         @OA\JsonContent(ref="#/components/schemas/Reservation")
 *     ),
 *     @OA\Response(
 *         response=403,
 *         description="Forbidden: Cannot create a reservation for an inactive field",
 *         @OA\JsonContent(
 *             @OA\Property(property="status", type="string", example="error"),
 *             @OA\Property(property="message", type="string", example="Cannot create a reservation for an inactive field."),
 *             @OA\Property(property="data", type="null"),
 *             @OA\Property(property="errors", type="null")
 *         )
 *     ),
 *     @OA\Response(
 *         response=500,
 *         description="Internal Server Error",
 *         @OA\JsonContent(
 *             @OA\Property(property="status", type="string", example="error"),
 *             @OA\Property(property="message", type="string", example="Failed to create reservation."),
 *             @OA\Property(property="data", type="null"),
 *             @OA\Property(property="errors", type="string", example="Error message")
 *         )
 *     )
 * )
 * @OA\Get(
 *     path="/api/v1/reservations/{id}",
 *     operationId="getReservationById",
 *     tags={"Reservations"},
 *     summary="Get reservation information",
 *     description="Returns reservation data",
 *     security={{"bearerAuth": {}}},
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         required=true,
 *         @OA\Schema(type="string")
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Successful operation",
 *         @OA\JsonContent(ref="#/components/schemas/Reservation")
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Reservation not found"
 *     )
 * )
 * @OA\Patch(
 *     path="/api/v1/reservations/{id}",
 *     operationId="updateReservation",
 *     tags={"Reservations"},
 *     summary="Update an existing reservation",
 *     description="Updates an existing reservation and returns the updated data",
 *     security={{"bearerAuth": {}}},
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         required=true,
 *         @OA\Schema(type="string")
 *     ),
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(ref="#/components/schemas/UpdateReservationRequest")
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Successful operation",
 *         @OA\JsonContent(ref="#/components/schemas/Reservation")
 *     ),
 *     @OA\Response(
 *         response=403,
 *         description="Forbidden: Cannot update reservation for an inactive field or unauthorized access.",
 *         @OA\JsonContent(
 *             @OA\Property(property="status", type="string", example="error"),
 *             @OA\Property(property="message", type="string", example="Cannot update reservation for an inactive field."),
 *             @OA\Property(property="data", type="null"),
 *             @OA\Property(property="errors", type="null")
 *         )
 *     ),
 *     @OA\Response(
 *         response=500,
 *         description="Failed to update reservation",
 *         @OA\JsonContent(
 *             @OA\Property(property="status", type="string", example="error"),
 *             @OA\Property(property="message", type="string", example="Failed to update reservation."),
 *             @OA\Property(property="data", type="null"),
 *             @OA\Property(property="errors", type="string", example="Error message")
 *         )
 *     )
 * )
 * @OA\Delete(
 *     path="/api/v1/reservations/{id}",
 *     operationId="deleteReservation",
 *     tags={"Reservations"},
 *     summary="Delete an existing reservation",
 *     description="Deletes an existing reservation and returns success message",
 *     security={{"bearerAuth": {}}},
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         required=true,
 *         @OA\Schema(type="string")
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Successful operation",
 *         @OA\JsonContent(
 *             @OA\Property(property="status", type="string", example="success"),
 *             @OA\Property(property="message", type="string", example="Reservation canceled successfully."),
 *             @OA\Property(property="data", type="null"),
 *             @OA\Property(property="errors", type="null")
 *         )
 *     ),
 *     @OA\Response(
 *         response=403,
 *         description="Unauthorized access",
 *         @OA\JsonContent(
 *             @OA\Property(property="status", type="string", example="error"),
 *             @OA\Property(property="message", type="string", example="Unauthorized access."),
 *             @OA\Property(property="data", type="null"),
 *             @OA\Property(property="errors", type="null")
 *         )
 *     ),
 *     @OA\Response(
 *         response=500,
 *         description="Failed to delete reservation",
 *         @OA\JsonContent(
 *             @OA\Property(property="status", type="string", example="error"),
 *             @OA\Property(property="message", type="string", example="Failed to cancel reservation."),
 *             @OA\Property(property="data", type="null"),
 *             @OA\Property(property="errors", type="string", example="Error message")
 *         )
 *     )
 * )
 */
class ReservationDocumentation {}
