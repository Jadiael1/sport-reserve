<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreFieldRequest;
use App\Http\Requests\UpdateFieldRequest;
use App\Models\Field;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

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
            $fields = null;
            if (Auth::user() && Auth::user()->is_admin) {
                /** @var \Illuminate\Pagination\LengthAwarePaginator $fields */
                $fields = Field::with(['images'])->paginate();
            }else{
                /** @var \Illuminate\Pagination\LengthAwarePaginator $fields */
                $fields = Field::with(['images'])->where('status', '!=', 'inactive')->paginate();
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
            $field = null;
            if (Auth::user() && Auth::user()->is_admin) {
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
     * @OA\Post(
     *     path="/api/v1/fields/{id}",
     *     operationId="updateField",
     *     tags={"Fields"},
     *     summary="Update an existing field",
     *     description="Atualiza um campo existente de forma parcial. Pode-se atualizar as informações do campo e as imagens associadas. Para atualizar uma imagem específica, forneça `image_ids[]` com o ID da imagem e `images[]` com a nova imagem correspondente. Para deletar uma imagem, forneça `image_ids[]` com o ID da imagem sem fornecer `images[]`. Para adicionar novas imagens, forneça apenas `images[]` sem `image_ids[]`.",
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
     *                 @OA\Property(
     *                     property="_method",
     *                     type="string",
     *                     enum={"PATCH"},
     *                     default="PATCH",
     *                     description="This field is required and must be set to PATCH"
     *                 ),
     *                 @OA\Property(
     *                     property="name",
     *                     type="string",
     *                     default="",
     *                     nullable=true,
     *                     description="The name of the field"
     *                 ),
     *                 @OA\Property(
     *                     property="location",
     *                     type="string",
     *                     default="",
     *                     nullable=true,
     *                     description="The location of the field"
     *                 ),
     *                 @OA\Property(
     *                     property="type",
     *                     type="string",
     *                     default="",
     *                     nullable=true,
     *                     description="The type of the field"
     *                 ),
     *                 @OA\Property(
     *                     property="hourly_rate",
     *                     type="number",
     *                     format="float",
     *                     default="",
     *                     nullable=true,
     *                     description="The hourly rate for renting the field"
     *                 ),
     *                 @OA\Property(
     *                     property="images[]",
     *                     type="array",
     *                     nullable=true,
     *                     @OA\Items(
     *                         type="string",
     *                         format="binary",
     *                         description="An image file"
     *                     ),
     *                     default={},
     *                     description="Array of image files"
     *                 ),
     *                 @OA\Property(
     *                     property="image_ids[]",
     *                     type="array",
     *                     nullable=true,
     *                     @OA\Items(
     *                         type="integer",
     *                         description="ID of the image to be replaced"
     *                     ),
     *                     default={},
     *                     description="Array of image IDs to be replaced"
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
     *         description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/Field")
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
