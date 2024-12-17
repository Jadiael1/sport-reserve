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
                    ->where('status', 'active')
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
