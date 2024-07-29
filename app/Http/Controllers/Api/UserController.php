<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    /**
     * @OA\Get(
     *     path="/api/v1/users",
     *     operationId="getUsersList",
     *     tags={"Users"},
     *     summary="Get list of users",
     *     description="Returns list of users",
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="sort_by",
     *         in="query",
     *         required=false,
     *         @OA\Schema(
     *             type="string",
     *             enum={"name", "cpf", "phone", "email", "is_admin", "email_verified_at", "remember_token", "created_at", "updated_at"},
     *             default="name"
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
     *             default="asc"
     *         ),
     *         description="Sort order"
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="status",
     *                 type="string",
     *                 example="success"
     *             ),
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Users retrieved successfully."
     *             ),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(
     *                     property="current_page",
     *                     type="integer",
     *                     example=1
     *                 ),
     *                 @OA\Property(
     *                     property="data",
     *                     type="array",
     *                     @OA\Items(ref="#/components/schemas/User")
     *                 ),
     *                 @OA\Property(
     *                     property="first_page_url",
     *                     type="string",
     *                     example="http://api.example.com/users?page=1"
     *                 ),
     *                 @OA\Property(
     *                     property="from",
     *                     type="integer",
     *                     example=1
     *                 ),
     *                 @OA\Property(
     *                     property="last_page",
     *                     type="integer",
     *                     example=10
     *                 ),
     *                 @OA\Property(
     *                     property="last_page_url",
     *                     type="string",
     *                     example="http://api.example.com/users?page=10"
     *                 ),
     *                 @OA\Property(
     *                     property="next_page_url",
     *                     type="string",
     *                     example="http://api.example.com/users?page=2"
     *                 ),
     *                 @OA\Property(
     *                     property="path",
     *                     type="string",
     *                     example="http://api.example.com/users"
     *                 ),
     *                 @OA\Property(
     *                     property="per_page",
     *                     type="integer",
     *                     example=15
     *                 ),
     *                 @OA\Property(
     *                     property="prev_page_url",
     *                     type="string",
     *                     example=null
     *                 ),
     *                 @OA\Property(
     *                     property="to",
     *                     type="integer",
     *                     example=15
     *                 ),
     *                 @OA\Property(
     *                     property="total",
     *                     type="integer",
     *                     example=150
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Failed to retrieve users",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="status",
     *                 type="string",
     *                 example="error"
     *             ),
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Failed to retrieve users."
     *             ),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 nullable=true
     *             ),
     *             @OA\Property(
     *                 property="errors",
     *                 type="string",
     *                 example="Error message"
     *             )
     *         )
     *     )
     * )
     */

    public function index(Request $request)
    {
        $sort_by = $request->query('sort_by', 'name');
        $sort_order = $request->query('sort_order', 'asc');

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
    /**
     * @OA\Post(
     *     path="/api/v1/users",
     *     operationId="storeUser",
     *     tags={"Users"},
     *     summary="Store a new user",
     *     description="Stores a new user and returns the user data. Only administrators can set the is_admin field.",
     *     security={{"bearerAuth": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name", "email", "cpf", "phone", "password", "password_confirmation"},
     *             @OA\Property(
     *                 property="name",
     *                 type="string",
     *                 description="Name of the user",
     *                 example="John Doe"
     *             ),
     *             @OA\Property(
     *                 property="email",
     *                 type="string",
     *                 format="email",
     *                 description="Email of the user",
     *                 example="john.doe@example.com"
     *             ),
     *             @OA\Property(
     *                 property="cpf",
     *                 type="string",
     *                 description="CPF of the user",
     *                 example="12345678901"
     *             ),
     *             @OA\Property(
     *                 property="phone",
     *                 type="string",
     *                 description="Phone number of the user",
     *                 example="(81) 99999-9999"
     *             ),
     *             @OA\Property(
     *                 property="password",
     *                 type="string",
     *                 format="password",
     *                 description="Password of the user",
     *                 example="password123"
     *             ),
     *             @OA\Property(
     *                 property="password_confirmation",
     *                 type="string",
     *                 format="password",
     *                 description="Password confirmation",
     *                 example="password123"
     *             ),
     *             @OA\Property(
     *                 property="is_admin",
     *                 type="boolean",
     *                 description="Admin status of the user. Only administrators can set this field.",
     *                 example=true
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="status",
     *                 type="string",
     *                 example="success"
     *             ),
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="User created successfully. Please check your email to verify your account."
     *             ),
     *             @OA\Property(
     *                 property="data",
     *                 ref="#/components/schemas/User"
     *             ),
     *             @OA\Property(
     *                 property="errors",
     *                 type="string",
     *                 nullable=true
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad Request",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="status",
     *                 type="string",
     *                 example="error"
     *             ),
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Validation error."
     *             ),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 nullable=true
     *             ),
     *             @OA\Property(
     *                 property="errors",
     *                 type="object"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Failed to create user",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="status",
     *                 type="string",
     *                 example="error"
     *             ),
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Failed to create user."
     *             ),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 nullable=true
     *             ),
     *             @OA\Property(
     *                 property="errors",
     *                 type="string",
     *                 example="Error message"
     *             )
     *         )
     *     )
     * )
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
    /**
     * @OA\Get(
     *     path="/api/v1/users/{id}",
     *     operationId="getUserById",
     *     tags={"Users"},
     *     summary="Get user information",
     *     description="Returns user data",
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
     *         @OA\JsonContent(ref="#/components/schemas/User")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="User not found"
     *     )
     * )
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
    /**
     * @OA\Patch(
     *     path="/api/v1/users/{id}",
     *     operationId="updateUser",
     *     tags={"Users"},
     *     summary="Update an existing user",
     *     description="Updates an existing user and returns the updated data",
     *     security={{"bearerAuth": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the user to update",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/UpdateUserRequest")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="status",
     *                 type="string",
     *                 example="success"
     *             ),
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="User updated successfully."
     *             ),
     *             @OA\Property(
     *                 property="data",
     *                 ref="#/components/schemas/User"
     *             ),
     *             @OA\Property(
     *                 property="errors",
     *                 type="string",
     *                 nullable=true
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Unauthorized operation",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="status",
     *                 type="string",
     *                 example="error"
     *             ),
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="You are not authorized to update this user."
     *             ),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 nullable=true
     *             ),
     *             @OA\Property(
     *                 property="errors",
     *                 type="string",
     *                 example="Unauthorized"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Failed to update user",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="status",
     *                 type="string",
     *                 example="error"
     *             ),
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Failed to update user."
     *             ),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 nullable=true
     *             ),
     *             @OA\Property(
     *                 property="errors",
     *                 type="string",
     *                 example="Error message"
     *             )
     *         )
     *     )
     * )
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

            // Atualizar apenas os campos que foram validados
            $user->update($validatedData);

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
    /**
     * @OA\Delete(
     *     path="/api/v1/users/{id}",
     *     operationId="deleteUser",
     *     tags={"Users"},
     *     summary="Delete an existing user",
     *     description="Deletes an existing user and returns success message",
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
     *         @OA\JsonContent(ref="#/components/schemas/User")
     *     )
     * )
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
}
