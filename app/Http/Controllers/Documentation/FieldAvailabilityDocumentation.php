<?php

namespace App\Http\Controllers\Documentation;

/**
 * @OA\Get(
 *     path="/api/v1/fieldAvailabilities",
 *     operationId="getFieldAvailabilitiesList",
 *     tags={"FieldAvailabilities"},
 *     summary="Get list of field availabilities",
 *     description="Returns list of field availabilities",
 *     security={{"bearerAuth": {}}},
 *     @OA\Parameter(
 *         name="sort_by",
 *         in="query",
 *         required=false,
 *         @OA\Schema(
 *             type="string",
 *             enum={"created_at", "updated_at", "start_time", "end_time", "day_of_week"},
 *             default="created_at"
 *         ),
 *         description="Field to sort by"
 *     ),
 *     @OA\Parameter(
 *         name="sort_order",
 *         in="query",
 *         required=false,
 *         @OA\Schema(
 *             type="string",
 *             enum={"asc", "desc"},
 *             default="desc"
 *         ),
 *         description="Sort order"
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Successful operation",
 *         @OA\JsonContent(
 *             @OA\Property(property="status", type="string", example="success"),
 *             @OA\Property(property="message", type="string", example="Field availabilities successfully recovered."),
 *             @OA\Property(property="data",
 *                 @OA\Property(property="current_page", type="integer", example=1),
 *                 @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/FieldAvailability")),
 *                 @OA\Property(property="first_page_url", type="string", example="http://api-sport-reserve.juvhost.com/api/v1/fieldAvailabilities?page=1"),
 *                 @OA\Property(property="from", type="integer", example=1),
 *                 @OA\Property(property="last_page", type="integer", example=2),
 *                 @OA\Property(property="last_page_url", type="string", example="http://api-sport-reserve.juvhost.com/api/v1/fieldAvailabilities?page=2"),
 *                 @OA\Property(property="next_page_url", type="string", example="http://api-sport-reserve.juvhost.com/api/v1/fieldAvailabilities?page=2"),
 *                 @OA\Property(property="path", type="string", example="http://api-sport-reserve.juvhost.com/api/v1/fieldAvailabilities"),
 *                 @OA\Property(property="per_page", type="integer", example=15),
 *                 @OA\Property(property="prev_page_url", type="string", example=null),
 *                 @OA\Property(property="to", type="integer", example=15),
 *                 @OA\Property(property="total", type="integer", example=20)
 *             ),
 *             @OA\Property(property="errors", type="null")
 *         )
 *     ),
 *     @OA\Response(
 *         response=400,
 *         description="Invalid sort field",
 *         @OA\JsonContent(
 *             @OA\Property(property="status", type="string", example="error"),
 *             @OA\Property(property="message", type="string", example="Invalid sort field."),
 *             @OA\Property(property="data", type="null"),
 *             @OA\Property(property="errors", type="string", example="Invalid sort field.")
 *         )
 *     ),
 *     @OA\Response(
 *         response=500,
 *         description="Failed to retrieve field availabilities",
 *         @OA\JsonContent(
 *             @OA\Property(property="status", type="string", example="error"),
 *             @OA\Property(property="message", type="string", example="Failed to retrieve field availabilities."),
 *             @OA\Property(property="data", type="null"),
 *             @OA\Property(property="errors", type="string", example="Error message")
 *         )
 *     )
 * )
 * @OA\Post(
 *     path="/api/v1/fieldAvailabilities/{fieldId}",
 *     operationId="storeFieldAvailability",
 *     tags={"FieldAvailabilities"},
 *     summary="Store field availability",
 *     description="Stores a new availability for a specific field",
 *     security={{"bearerAuth": {}}},
 *     @OA\Parameter(
 *         name="fieldId",
 *         in="path",
 *         required=true,
 *         @OA\Schema(type="integer"),
 *         description="Field ID"
 *     ),
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(ref="#/components/schemas/StoreFieldAvailabilityRequest")
 *     ),
 *     @OA\Response(
 *         response=201,
 *         description="Successful operation",
 *         @OA\JsonContent(
 *             @OA\Property(property="status", type="string", example="success"),
 *             @OA\Property(property="message", type="string", example="Availability created successfully."),
 *             @OA\Property(property="data", ref="#/components/schemas/FieldAvailability"),
 *             @OA\Property(property="errors", type="null")
 *         )
 *     ),
 *     @OA\Response(
 *         response=403,
 *         description="Unauthorized",
 *         @OA\JsonContent(
 *             @OA\Property(property="status", type="string", example="error"),
 *             @OA\Property(property="message", type="string", example="Unauthorized"),
 *             @OA\Property(property="data", type="null"),
 *             @OA\Property(property="errors", type="null")
 *         )
 *     ),
 *     @OA\Response(
 *         response=500,
 *         description="Failed to store field availability",
 *         @OA\JsonContent(
 *             @OA\Property(property="status", type="string", example="error"),
 *             @OA\Property(property="message", type="string", example="Failed to store field availability."),
 *             @OA\Property(property="data", type="null"),
 *             @OA\Property(property="errors", type="string", example="Error message")
 *         )
 *     )
 * )
 * @OA\Patch(
 *     path="/api/v1/fieldAvailabilities/{fieldId}/availabilities/{availabilityId}",
 *     operationId="updateFieldAvailability",
 *     tags={"FieldAvailabilities"},
 *     summary="Update field availability",
 *     description="Updates an existing availability for a specific field",
 *     security={{"bearerAuth": {}}},
 *     @OA\Parameter(
 *         name="fieldId",
 *         in="path",
 *         required=true,
 *         @OA\Schema(type="integer"),
 *         description="Field ID"
 *     ),
 *     @OA\Parameter(
 *         name="availabilityId",
 *         in="path",
 *         required=true,
 *         @OA\Schema(type="integer"),
 *         description="Field Availability ID"
 *     ),
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(ref="#/components/schemas/UpdateFieldAvailabilityRequest")
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Successful operation",
 *         @OA\JsonContent(
 *             @OA\Property(property="status", type="string", example="success"),
 *             @OA\Property(property="message", type="string", example="Availability updated successfully."),
 *             @OA\Property(property="data", ref="#/components/schemas/FieldAvailability"),
 *             @OA\Property(property="errors", type="null")
 *         )
 *     ),
 *     @OA\Response(
 *         response=403,
 *         description="Unauthorized",
 *         @OA\JsonContent(
 *             @OA\Property(property="status", type="string", example="error"),
 *             @OA\Property(property="message", type="string", example="Unauthorized"),
 *             @OA\Property(property="data", type="null"),
 *             @OA\Property(property="errors", type="null")
 *         )
 *     ),
 *     @OA\Response(
 *         response=500,
 *         description="Failed to update field availability",
 *         @OA\JsonContent(
 *             @OA\Property(property="status", type="string", example="error"),
 *             @OA\Property(property="message", type="string", example="Failed to update field availability."),
 *             @OA\Property(property="data", type="null"),
 *             @OA\Property(property="errors", type="string", example="Error message")
 *         )
 *     )
 * )
 * @OA\Delete(
 *     path="/api/v1/fieldAvailabilities/{fieldId}/availabilities/{availabilityId}",
 *     operationId="deleteFieldAvailability",
 *     tags={"FieldAvailabilities"},
 *     summary="Delete field availability",
 *     description="Deletes an existing availability for a specific field",
 *     security={{"bearerAuth": {}}},
 *     @OA\Parameter(
 *         name="fieldId",
 *         in="path",
 *         required=true,
 *         @OA\Schema(type="integer"),
 *         description="Field ID"
 *     ),
 *     @OA\Parameter(
 *         name="availabilityId",
 *         in="path",
 *         required=true,
 *         @OA\Schema(type="integer"),
 *         description="Field Availability ID"
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Successful operation",
 *         @OA\JsonContent(
 *             @OA\Property(property="status", type="string", example="success"),
 *             @OA\Property(property="message", type="string", example="Availability deleted successfully."),
 *             @OA\Property(property="data", type="null"),
 *             @OA\Property(property="errors", type="null")
 *         )
 *     ),
 *     @OA\Response(
 *         response=403,
 *         description="Unauthorized",
 *         @OA\JsonContent(
 *             @OA\Property(property="status", type="string", example="error"),
 *             @OA\Property(property="message", type="string", example="Unauthorized"),
 *             @OA\Property(property="data", type="null"),
 *             @OA\Property(property="errors", type="null")
 *         )
 *     ),
 *     @OA\Response(
 *         response=500,
 *         description="Failed to delete field availability",
 *         @OA\JsonContent(
 *             @OA\Property(property="status", type="string", example="error"),
 *             @OA\Property(property="message", type="string", example="Failed to delete field availability."),
 *             @OA\Property(property="data", type="null"),
 *             @OA\Property(property="errors", type="string", example="Error message")
 *         )
 *     )
 * )
 * @OA\Get(
 *     path="/api/v1/fieldAvailabilities/{fieldId}",
 *     operationId="getFieldAvailability",
 *     tags={"FieldAvailabilities"},
 *     summary="Get field availability by field ID",
 *     description="Retrieves the availability details of a specific field by its ID.",
 *     security={{"bearerAuth": {}}},
 *     @OA\Parameter(
 *         name="fieldId",
 *         in="path",
 *         required=true,
 *         @OA\Schema(type="integer"),
 *         description="Field ID for which availability details are to be retrieved."
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Successful operation",
 *         @OA\JsonContent(
 *             @OA\Property(property="status", type="string", example="success"),
 *             @OA\Property(property="message", type="string", example="Field availability retrieved successfully."),
 *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/FieldAvailability")),
 *             @OA\Property(property="errors", oneOf={@OA\Schema(type="string"), @OA\Schema(type="array", @OA\Items(type="string"))}, nullable=true, example=null)
 *         )
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Field availability not found",
 *         @OA\JsonContent(
 *             @OA\Property(property="status", type="string", example="error"),
 *             @OA\Property(property="message", type="string", example="Field availability not found."),
 *             @OA\Property(property="data", type="array", @OA\Items(), nullable=true, example=null),
 *             @OA\Property(property="errors", oneOf={@OA\Schema(type="array", @OA\Items(type="string")), @OA\Schema(type="string")}, nullable=true)
 *         )
 *     ),
 *     @OA\Response(
 *         response=500,
 *         description="Failed to retrieve field availability",
 *         @OA\JsonContent(
 *             @OA\Property(property="status", type="string", example="error"),
 *             @OA\Property(property="message", type="string", example="Failed to retrieve field availability."),
 *             @OA\Property(property="data", type="array", @OA\Items(), nullable=true, example=null),
 *             @OA\Property(property="errors", oneOf={@OA\Schema(type="array", @OA\Items(type="string")), @OA\Schema(type="string")}, nullable=true)
 *         )
 *     )
 * )
 */
class FieldAvailabilityDocumentation {}
