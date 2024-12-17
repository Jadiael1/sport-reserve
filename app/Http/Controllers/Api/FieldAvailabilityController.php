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
use Laravel\Sanctum\PersonalAccessToken;

class FieldAvailabilityController extends Controller
{
    /**
     * Display a listing of the resource.
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
        try {

            $token = request()->header('Authorization');
            if (str_starts_with($token, 'Bearer ')) {
                $token = substr($token, 7);
            }
            $accessToken = PersonalAccessToken::findToken($token);
            $user = $accessToken->tokenable ?? null;

            $availability = $user && $user->is_admin ?
                FieldAvailability::where('field_id', $id)->get()
                :
                FieldAvailability::whereHas('field', fn($query) => $query->where('status', 'active'))->where('field_id', $id)->get();

            if (is_object($availability) && !count($availability)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Field availability not found.',
                    'data' => $availability,
                    'errors' => null
                ], 404);
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Field availability retrieved successfully.',
                'data' => $availability,
                'errors' => null
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to retrieve field availability.',
                'data' => null,
                'errors' => $e->getMessage()
            ], 500);
        }
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
