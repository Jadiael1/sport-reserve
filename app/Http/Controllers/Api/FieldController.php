<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreFieldRequest;
use App\Http\Requests\UpdateFieldRequest;
use App\Models\Field;
use Exception;
use Illuminate\Http\Request;


class FieldController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    /**
     * @OA\Get(
     *     path="/api/v1/fields",
     *     operationId="getFieldsList",
     *     tags={"Fields"},
     *     summary="Get list of fields",
     *     description="Returns list of fields",
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(type="array", @OA\Items(ref="#/components/schemas/Field"))
     *     )
     * )
     */
    public function index()
    {
        try {
            $field = Field::paginate();
            return response()->json([
                'status' => 'success',
                'message' => 'Field successfully recovered.',
                'data' => $field,
                'errors' => null
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to retrieve field.',
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
     * Store a newly created resource in storage.
     */
    /**
     * @OA\Post(
     *     path="/api/v1/fields",
     *     operationId="storeField",
     *     tags={"Fields"},
     *     summary="Store a new field",
     *     description="Stores a new field and returns the field data",
     *     security={{"bearerAuth": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 type="object",
     *                 required={"name", "location", "type", "hourly_rate"},
     *                 @OA\Property(
     *                     property="name",
     *                     type="string",
     *                     description="The name of the field"
     *                 ),
     *                 @OA\Property(
     *                     property="location",
     *                     type="string",
     *                     description="The location of the field"
     *                 ),
     *                 @OA\Property(
     *                     property="type",
     *                     type="string",
     *                     description="The type of the field"
     *                 ),
     *                 @OA\Property(
     *                     property="hourly_rate",
     *                     type="number",
     *                     format="float",
     *                     description="The hourly rate for renting the field"
     *                 ),
     *                 @OA\Property(
     *                     property="images[]",
     *                     type="array",
     *                     @OA\Items(
     *                         type="string",
     *                         format="binary",
     *                         description="An image file"
     *                     ),
     *                     description="Array of image files"
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/Field")
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad Request"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal Server Error"
     *     )
     * )
     */
    public function store(StoreFieldRequest $request)
    {
        $validatedData = $request->validated();

        try {
            $field = new Field([
                'name' => $validatedData['name'],
                'location' => $validatedData['location'],
                'type' => $validatedData['type'],
                'hourly_rate' => $validatedData['hourly_rate'],
            ]);

            if ($request->hasFile('images')) {
                $images = [];
                foreach ($request->file('images') as $image) {
                    if ($image->isValid()) {
                        $path = $image->store('fields', 'public');
                        $images[] = $path;
                    } else {
                        return response()->json([
                            'status' => 'error',
                            'message' => 'One or more images failed to upload.',
                            'data' => null,
                            'errors' => ['images' => ['One or more images failed to upload.']]
                        ], 422);
                    }
                }
                $field->images = json_encode($images);
            }

            $field->save();

            return response()->json([
                'status' => 'success',
                'message' => 'Field created successfully.',
                'data' => $field,
                'errors' => null
            ], 201);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to create field.',
                'data' => null,
                'errors' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    /**
     * @OA\Get(
     *     path="/api/v1/fields/{id}",
     *     operationId="getFieldById",
     *     tags={"Fields"},
     *     summary="Get field information",
     *     description="Returns field data",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/Field")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Field not found"
     *     )
     * )
     */
    public function show(string $id)
    {
        try {
            $field = Field::findOrFail($id);

            return response()->json([
                'status' => 'success',
                'message' => 'Field successfully recovered.',
                'data' => $field,
                'errors' => null
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Field not found.',
                'data' => null,
                'errors' => $e->getMessage()
            ], 404);
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
    /**
     * @OA\Patch(
     *     path="/api/v1/fields/{id}",
     *     operationId="updateField",
     *     tags={"Fields"},
     *     summary="Update an existing field",
     *     description="Updates an existing field and returns the updated data",
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 type="object",
     *                 @OA\Property(
     *                     property="name",
     *                     type="string",
     *                     description="The name of the field"
     *                 ),
     *                 @OA\Property(
     *                     property="location",
     *                     type="string",
     *                     description="The location of the field"
     *                 ),
     *                 @OA\Property(
     *                     property="type",
     *                     type="string",
     *                     description="The type of the field"
     *                 ),
     *                 @OA\Property(
     *                     property="hourly_rate",
     *                     type="number",
     *                     format="float",
     *                     description="The hourly rate for renting the field"
     *                 ),
     *                 @OA\Property(
     *                     property="images",
     *                     type="array",
     *                     @OA\Items(
     *                         type="string",
     *                         format="binary",
     *                         description="An image file"
     *                     ),
     *                     description="Array of image files"
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/Field")
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad Request"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Not Found"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal Server Error"
     *     )
     * )
     */
    public function update(UpdateFieldRequest $request, string $id)
    {
        // A validação já foi feita pela classe UpdateReservationRequest
        $validatedData = $request->validated();

        try {
            $field = Field::findOrFail($id);
            $field->update($validatedData);

            if ($request->hasFile('images')) {
                $images = [];
                foreach ($request->file('images') as $image) {
                    $path = $image->store('fields', 'public');
                    $images[] = $path;
                }
                $field->images = json_encode($images);
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Field updated successfully.',
                'data' => $field,
                'errors' => null
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to update field.',
                'data' => null,
                'errors' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    /**
     * @OA\Delete(
     *     path="/api/v1/fields/{id}",
     *     operationId="deleteField",
     *     tags={"Fields"},
     *     summary="Delete an existing field",
     *     description="Deletes an existing field and returns success message",
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
     *         @OA\JsonContent(ref="#/components/schemas/Field")
     *     )
     * )
     */
    public function destroy(string $id)
    {
        try {
            $field = Field::findOrFail($id);
            $field->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'Field successfully deleted.',
                'data' => null,
                'errors' => null
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to delete field.',
                'data' => null,
                'errors' => $e->getMessage()
            ], 500);
        }
    }
}
