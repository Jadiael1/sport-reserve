<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreFieldAvailabilityRequest;
use App\Http\Requests\UpdateFieldAvailabilityRequest;
use App\Models\Field;
use App\Models\FieldAvailability;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FieldAvailabilityController extends Controller
{
    /**
     * Display a listing of the resource.
     */
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
     */
    public function index(Request $request)
    {
        try {
            $sort_by = $request->query('sort_by', 'created_at');
            $sort_order = $request->query('sort_order', 'desc');

            $validSortFields = ['created_at', 'updated_at', 'start_time', 'end_time', 'day_of_week'];
            if (!in_array($sort_by, $validSortFields)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Invalid sort field.',
                    'data' => null,
                    'errors' => 'Invalid sort field.'
                ], 400);
            }

            $fieldAvailabilities = FieldAvailability::orderBy($sort_by, $sort_order)->paginate();

            return response()->json([
                'status' => 'success',
                'message' => 'Field availabilities successfully recovered.',
                'data' => $fieldAvailabilities,
                'errors' => null
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to retrieve field availabilities.',
                'data' => null,
                'errors' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store field availability
     */
    /**
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
     */
    public function store(StoreFieldAvailabilityRequest $request, $fieldId)
    {
        $validatedData = $request->validated();

        try {
            $field = Field::findOrFail($fieldId);

            if (!Auth::user()->is_admin) {
                return response()->json(['status' => 'error', 'message' => 'Unauthorized'], 403);
            }

            $availability = new FieldAvailability($validatedData);
            $field->availabilities()->save($availability);

            return response()->json([
                'status' => 'success',
                'message' => 'Availability created successfully.',
                'data' => $availability,
                'errors' => null
            ], 201);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to store field availability.',
                'data' => null,
                'errors' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    /**
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
     */
    public function update(UpdateFieldAvailabilityRequest $request, $fieldId, $availabilityId)
    {
        $field = Field::findOrFail($fieldId);
        $availability = FieldAvailability::findOrFail($availabilityId);

        if (!Auth::user()->is_admin) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized.',
                'data' => null,
                'errors' => null
            ], 403);
        }

        $availability->update($request->validated());
        return response()->json([
            'status' => 'success',
            'message' => 'Availability updated successfully.',
            'data' => $availability,
            'errors' => null
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    /**
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
     */
    public function destroy($fieldId, $availabilityId)
    {
        $field = Field::findOrFail($fieldId);
        $availability = FieldAvailability::findOrFail($availabilityId);

        if (!Auth::user()->is_admin) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized.',
                'data' => null,
                'errors' => null
            ], 403);
        }

        $availability->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Availability deleted successfully.',
            'data' => null,
            'errors' => null
        ], 200);
    }
}
