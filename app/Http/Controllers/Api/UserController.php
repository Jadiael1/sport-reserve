<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\User;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $sort_by = $request->query('sort_by', 'created_at');
        $sort_order = $request->query('sort_order', 'desc');

        $validSortFields = ['name', 'cpf', 'phone', 'email', 'is_admin', 'email_verified_at', 'remember_token', 'created_at', 'updated_at'];
        if (!in_array($sort_by, $validSortFields)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid sort field.',
                'data' => null,
                'errors' => 'Invalid sort field.'
            ], 400);
        }

        if (!in_array($sort_order, ['asc', 'desc'])) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid sort order.',
                'data' => null,
                'errors' => 'Invalid sort order.'
            ], 400);
        }

        try {
            $users = User::orderBy($sort_by, $sort_order)->paginate();
            return response()->json([
                'status' => 'success',
                'message' => 'Users retrieved successfully.',
                'data' => $users,
                'errors' => null
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to retrieve users.',
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
    public function store(StoreUserRequest $request)
    {
        $validatedData = $request->validated();
        $currentUser = Auth::user();

        $isAdmin = User::count() == 0;

        if ($currentUser && $currentUser->is_admin && isset($validatedData['is_admin'])) {
            $isAdmin = $validatedData['is_admin'];
        }

        try {
            $user = User::create([
                'name' => $validatedData['name'],
                'cpf' => $validatedData['cpf'],
                'phone' => $validatedData['phone'],
                'email' => $validatedData['email'],
                'password' => bcrypt($validatedData['password']),
                'is_admin' => $isAdmin,
            ]);

            $user->sendEmailVerificationNotification();

            return response()->json([
                'status' => 'success',
                'message' => 'User created successfully. Please check your email to verify your account.',
                'data' => $user,
                'errors' => null
            ], 201);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to create user.',
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
            $user = User::findOrFail($id);

            return response()->json([
                'status' => 'success',
                'message' => 'User retrieved successfully.',
                'data' => $user,
                'errors' => null
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'User not found.',
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
    public function update(UpdateUserRequest $request, string $id)
    {
        $validatedData = $request->validated();
        $currentUser = Auth::user();

        try {
            $user = User::findOrFail($id);

            if (!$currentUser->is_admin && $currentUser->id !== $user->id) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'You are not authorized to update this user.',
                    'data' => null,
                    'errors' => 'Unauthorized'
                ], 403);
            }

            // Verifica se o e-mail foi alterado
            $emailChanged = isset($validatedData['email']) && $validatedData['email'] !== $user->email;

            // Atualizar apenas os campos que foram validados
            $user->update($validatedData);

            // Se o e-mail foi alterado, atualiza o campo email_verified_at e envia o e-mail de verificação
            if ($emailChanged) {
                $user->email_verified_at = null;
                $user->save();

                // Envia o e-mail de verificação
                $user->sendEmailVerificationNotification();
            }

            return response()->json([
                'status' => 'success',
                'message' => 'User updated successfully.',
                'data' => $user,
                'errors' => null
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to update user.',
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
            $user = User::findOrFail($id);
            $user->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'User deleted successfully.',
                'data' => null,
                'errors' => null
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to delete user.',
                'data' => null,
                'errors' => $e->getMessage()
            ], 500);
        }
    }

    public function toggleActive(Request $request, $id)
    {
        try {
            $user = User::findOrFail($id);

            if ($request->has('active')) {
                $user->active = $request->input('active');
            } else {
                $user->active = !$user->active;
            }

            $user->save();

            return response()->json([
                'status' => 'success',
                'message' => 'User active status updated successfully.',
                'data' => $user,
                'errors' => null
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'User not found.',
                'data' => null,
                'errors' => $e->getMessage()
            ], 404);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to update user active status.',
                'data' => null,
                'errors' => $e->getMessage()
            ], 500);
        }
    }

    public function toggleConfirmation(Request $request, $id)
    {
        try {
            $user = User::findOrFail($id);

            if ($request->has('confirm')) {
                $user->email_verified_at = $request->input('confirm') ? now() : null;
            } else {
                $user->email_verified_at = $user->email_verified_at ? null : now();
            }

            $user->save();

            return response()->json([
                'status' => 'success',
                'message' => 'User confirmation status updated successfully.',
                'data' => $user,
                'errors' => null
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'User not found.',
                'data' => null,
                'errors' => $e->getMessage()
            ], 404);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to update user confirmation status.',
                'data' => null,
                'errors' => $e->getMessage()
            ], 500);
        }
    }
}
