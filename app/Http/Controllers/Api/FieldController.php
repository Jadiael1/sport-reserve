<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreFieldRequest;
use App\Http\Requests\UpdateFieldRequest;
use App\Models\Field;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\PersonalAccessToken;

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
     *     description="Returns a paginated list of fields with optional sorting",
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Number of fields to return per page",
     *         required=false,
     *         @OA\Schema(
     *             type="integer",
     *             example=15
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="sort_by",
     *         in="query",
     *         description="Field to sort by",
     *         required=false,
     *         @OA\Schema(
     *             type="string",
     *             enum={"id", "status"},
     *             example="id"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="sort_order",
     *         in="query",
     *         description="Sort order",
     *         required=false,
     *         @OA\Schema(
     *             type="string",
     *             enum={"asc", "desc"},
     *             example="desc"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Field successfully recovered."),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="current_page", type="integer"),
     *                 @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/Field")),
     *                 @OA\Property(property="first_page_url", type="string"),
     *                 @OA\Property(property="from", type="integer"),
     *                 @OA\Property(property="last_page", type="integer"),
     *                 @OA\Property(property="last_page_url", type="string"),
     *                 @OA\Property(property="next_page_url", type="string"),
     *                 @OA\Property(property="path", type="string"),
     *                 @OA\Property(property="per_page", type="integer"),
     *                 @OA\Property(property="prev_page_url", type="string"),
     *                 @OA\Property(property="to", type="integer"),
     *                 @OA\Property(property="total", type="integer")
     *             ),
     *             @OA\Property(property="errors", type="object", nullable=true)
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Failed to retrieve fields",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Failed to retrieve fields."),
     *             @OA\Property(property="data", type="null"),
     *             @OA\Property(property="errors", type="string", example="Error message")
     *         )
     *     )
     * )
     */

    public function index(Request $request)
    {
        try {
            $fields = null;
            $token = request()->header('Authorization');
            if (str_starts_with($token, 'Bearer ')) {
                $token = substr($token, 7);
            }
            $accessToken = PersonalAccessToken::findToken($token);
            $user = null;
            if ($accessToken) {
                $user = $accessToken->tokenable;
            }


            $validSortFields = ['id', 'status'];
            $validSortOrders = ['asc', 'desc'];

            $perPage = $request->query('per_page', 15);
            $sortBy = $request->input('sort_by', 'status');
            $sortOrder = $request->input('sort_order', 'desc');

            if (!in_array($sortBy, $validSortFields)) {
                $sortBy = 'id';
            }

            if (!in_array($sortOrder, $validSortOrders)) {
                $sortOrder = 'desc';
            }

            if ($user && $user->is_admin) {
                /** @var \Illuminate\Pagination\LengthAwarePaginator $fields */
                $fields = Field::with(['images'])
                    ->orderByRaw("CASE WHEN status = 'active' THEN 0 ELSE 1 END")
                    ->orderBy($sortBy, $sortOrder)
                    ->paginate($perPage);
            } else {
                /** @var \Illuminate\Pagination\LengthAwarePaginator $fields */
                $fields = Field::with(['images'])
                    ->orderByRaw("CASE WHEN status = 'active' THEN 0 ELSE 1 END")
                    ->orderBy($sortBy, $sortOrder)
                    ->where('status', '!=', 'inactive')
                    ->paginate($perPage);
            }


            // Transforma os campos para incluir o path de imagem completos
            $fields->getCollection()->transform(function ($field) {
                $field->images->transform(function ($image) {
                    $image->path = Storage::url($image->path);
                    return $image;
                });
                return $field;
            });

            return response()->json([
                'status' => 'success',
                'message' => 'Field successfully recovered.',
                'data' => $fields,
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
     *                 required={"name", "location", "type", "hourly_rate", "cep", "district", "address", "number", "city", "uf"},
     *                 @OA\Property(property="name", type="string", description="The name of the field"),
     *                 @OA\Property(property="location", type="object", description="The location of the field with latitude and longitude",
     *                     @OA\Property(property="lat", type="number", format="float"),
     *                     @OA\Property(property="lng", type="number", format="float")
     *                 ),
     *                 @OA\Property(property="type", type="string", description="The type of the field"),
     *                 @OA\Property(property="hourly_rate", type="number", format="float", description="The hourly rate for renting the field"),
     *                 @OA\Property(property="cep", type="string", description="The postal code of the field"),
     *                 @OA\Property(property="district", type="string", description="The district of the field"),
     *                 @OA\Property(property="address", type="string", description="The address of the field"),
     *                 @OA\Property(property="number", type="string", description="The address number of the field"),
     *                 @OA\Property(property="city", type="string", description="The city where the field is located"),
     *                 @OA\Property(property="uf", type="string", description="The state where the field is located"),
     *                 @OA\Property(property="complement", type="string", description="Additional address information", nullable=true),
     *                 @OA\Property(
     *                     property="images[]",
     *                     type="array",
     *                     @OA\Items(type="string", format="binary", description="An image file"),
     *                     description="Array of image files"
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Field created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Field created successfully."),
     *             @OA\Property(property="data", ref="#/components/schemas/Field"),
     *             @OA\Property(property="errors", type="null")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad Request",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Validation error."),
     *             @OA\Property(property="data", type="null"),
     *             @OA\Property(property="errors", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Unauthorized."),
     *             @OA\Property(property="data", type="null"),
     *             @OA\Property(property="errors", type="null")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal Server Error",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Failed to create field."),
     *             @OA\Property(property="data", type="null"),
     *             @OA\Property(property="errors", type="string", example="Error message")
     *         )
     *     )
     * )
     */
    public function store(StoreFieldRequest $request)
    {
        $validatedData = $request->validated();

        try {
            $field = new Field($validatedData);
            $field->save();

            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $image) {
                    $path = $image->store('fields', 'public');
                    $field->images()->create(['path' => $path]);
                }
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Field created successfully.',
                'data' => $field->load('images'),
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
     *         description="Field successfully recovered",
     *         @OA\JsonContent(ref="#/components/schemas/Field")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Field not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Field not found."),
     *             @OA\Property(property="data", type="null"),
     *             @OA\Property(property="errors", type="string", example="Error message")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal Server Error",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Failed to retrieve field."),
     *             @OA\Property(property="data", type="null"),
     *             @OA\Property(property="errors", type="string", example="Error message")
     *         )
     *     )
     * )
     */
    public function show(string $id)
    {
        try {
            $field = null;

            $token = request()->header('Authorization');
            if (str_starts_with($token, 'Bearer ')) {
                $token = substr($token, 7);
            }
            $accessToken = PersonalAccessToken::findToken($token);
            $user = null;
            if ($accessToken) {
                $user = $accessToken->tokenable;
            }

            if ($user && $user->is_admin) {
                $field = Field::with(['images'])->findOrFail($id);
            } else {
                $field = Field::with(['images'])->where('status', '!=', 'inactive')->findOrFail($id);
            }

            // Transforma os campos para incluir o path de imagem completos
            $field->images->transform(function ($image) {
                $image->path = Storage::url($image->path);
                return $image;
            });

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
     *                 required={"_method"},
     *                 @OA\Property(property="_method", type="string", enum={"PATCH"}, default="PATCH", description="This field is required and must be set to PATCH"),
     *                 @OA\Property(property="name", type="string", nullable=true, description="The name of the field"),
     *                 @OA\Property(property="location", type="object", nullable=true, description="The location of the field with latitude and longitude",
     *                     @OA\Property(property="lat", type="number", format="float"),
     *                     @OA\Property(property="lng", type="number", format="float")
     *                 ),
     *                 @OA\Property(property="type", type="string", nullable=true, description="The type of the field"),
     *                 @OA\Property(property="hourly_rate", type="number", format="float", nullable=true, description="The hourly rate for renting the field"),
     *                 @OA\Property(property="cep", type="string", nullable=true, description="The postal code of the field"),
     *                 @OA\Property(property="district", type="string", nullable=true, description="The district of the field"),
     *                 @OA\Property(property="address", type="string", nullable=true, description="The address of the field"),
     *                 @OA\Property(property="number", type="string", nullable=true, description="The address number of the field"),
     *                 @OA\Property(property="city", type="string", nullable=true, description="The city where the field is located"),
     *                 @OA\Property(property="uf", type="string", nullable=true, description="The state where the field is located"),
     *                 @OA\Property(property="complement", type="string", nullable=true, description="Additional address information"),
     *                 @OA\Property(
     *                     property="images[]",
     *                     type="array",
     *                     nullable=true,
     *                     @OA\Items(type="string", format="binary", description="An image file"),
     *                     description="Array of image files"
     *                 ),
     *                 @OA\Property(
     *                     property="image_ids[]",
     *                     type="array",
     *                     nullable=true,
     *                     @OA\Items(type="integer", description="ID of the image to be replaced"),
     *                     description="Array of image IDs to be replaced"
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Field updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Field updated successfully."),
     *             @OA\Property(property="data", ref="#/components/schemas/Field"),
     *             @OA\Property(property="errors", type="null")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad Request",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Validation error."),
     *             @OA\Property(property="data", type="null"),
     *             @OA\Property(property="errors", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Unauthorized."),
     *             @OA\Property(property="data", type="null"),
     *             @OA\Property(property="errors", type="null")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Field not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Field not found."),
     *             @OA\Property(property="data", type="null"),
     *             @OA\Property(property="errors", type="string", example="Error message")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal Server Error",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Failed to update field."),
     *             @OA\Property(property="data", type="null"),
     *             @OA\Property(property="errors", type="string", example="Error message")
     *         )
     *     )
     * )
     */
    public function update(UpdateFieldRequest $request, string $id)
    {
        $validatedData = $request->validated();

        try {
            $field = Field::findOrFail($id);
            $field->update($validatedData);

            $imageIds = $request->input('image_ids', []);
            $images = $request->file('images', []);

            // Atualizar imagens existentes
            foreach ($imageIds as $index => $imageId) {
                $imageRecord = $field->images()->find($imageId);
                if ($imageRecord) {
                    if (isset($images[$index])) {
                        // Substitui a imagem existente por uma nova
                        Storage::disk('public')->delete($imageRecord->path);
                        $path = $images[$index]->store('fields', 'public');
                        $imageRecord->update(['path' => $path]);
                    } else {
                        // Exclui a imagem se não houver nova imagem correspondente
                        Storage::disk('public')->delete($imageRecord->path);
                        $imageRecord->delete();
                    }
                }
            }

            // Adicionar novas imagens
            if (empty($imageIds) && !empty($images)) {
                foreach ($images as $image) {
                    $path = $image->store('fields', 'public');
                    $field->images()->create(['path' => $path]);
                }
            }

            // Carregar imagens e adicionar URLs completas
            $field->load('images');
            $field->images->transform(function ($image) {
                $image->path = Storage::url($image->path);
                return $image;
            });

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
     *         description="Field successfully deleted or inactivated",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Field successfully deleted."),
     *             @OA\Property(property="data", type="null"),
     *             @OA\Property(property="errors", type="null")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Field not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Field not found."),
     *             @OA\Property(property="data", type="null"),
     *             @OA\Property(property="errors", type="string", example="Error message")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Failed to delete field",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Failed to delete field."),
     *             @OA\Property(property="data", type="null"),
     *             @OA\Property(property="errors", type="string", example="Error message")
     *         )
     *     )
     * )
     */
    public function destroy(string $id)
    {
        try {
            $field = Field::findOrFail($id);
            $hasReservations = $field->reservations()->exists();

            if ($hasReservations) {
                // Inativa o campo em vez de excluir
                $field->status = 'inactive';
                $field->save();
                return response()->json([
                    'status' => 'success',
                    'message' => 'Field successfully inactivated due to existing reservations.',
                    'data' => $field,
                    'errors' => null
                ], 200);
            } else {
                // Exclui o campo
                $field->delete();
                return response()->json([
                    'status' => 'success',
                    'message' => 'Field successfully deleted.',
                    'data' => null,
                    'errors' => null
                ], 200);
            }
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
