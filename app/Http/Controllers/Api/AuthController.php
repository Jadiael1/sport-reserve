<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreUserRequest;
use App\Models\User;
use App\Notifications\CustomResetPassword;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;

class AuthController extends Controller
{
    /**
     * @OA\Post(
     *     path="/api/v1/auth/signin",
     *     operationId="signin",
     *     tags={"Auth"},
     *     summary="User login",
     *     description="Authenticate user and return user data",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="email", type="string", format="email", example="admin@admin.com"),
     *             @OA\Property(property="password", type="string", format="password", example="password")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Login successful",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Login successful."),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="user", ref="#/components/schemas/User"),
     *                 @OA\Property(property="token", type="string", example="example-token"),
     *                 @OA\Property(property="token_type", type="string", example="Bearer"),
     *                 @OA\Property(property="expires_in", type="string", example="2024-06-20 14:00:00")
     *             ),
     *             @OA\Property(property="errors", type="object", nullable=true)
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Invalid credentials",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Invalid credentials."),
     *             @OA\Property(property="data", type="object", nullable=true),
     *             @OA\Property(property="errors", type="object", nullable=true)
     *         )
     *     )
     * )
     */
    public function signin(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($credentials)) {
            /** @var \App\Models\User $user **/
            $user = Auth::user();
            $tokenResult = $user->createToken('auth_token');
            $token = $tokenResult->plainTextToken;

            // Definir expiração personalizada (em minutos)
            $expirationMinutes = 60;
            $expiration = Carbon::now()->addMinutes($expirationMinutes);

            // Salvar a expiração no token (opcional)
            $tokenResult->accessToken->expires_at = $expiration;
            $tokenResult->accessToken->save();

            $expirationInTimezone = $expiration->setTimezone('America/Recife')->toDateTimeString();

            return response()->json([
                'status' => 'success',
                'message' => 'Login successful.',
                'data' => array('user' => $user, 'token' => $token, 'token_type' => 'Bearer', 'expires_in' => $expirationInTimezone),
                'errors' => null
            ], 200);
        }

        return response()->json([
            'status' => 'error',
            'message' => 'Invalid credentials.',
            'data' => null,
            'errors' => null
        ], 401);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/auth/signup",
     *     operationId="signup",
     *     tags={"Auth"},
     *     summary="User registration",
     *     description="Register a new user and return user data",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string", example="John Doe"),
     *             @OA\Property(property="email", type="string", format="email", example="john.doe@example.com"),
     *             @OA\Property(property="password", type="string", format="password", example="password123"),
     *             @OA\Property(property="password_confirmation", type="string", format="password", example="password123"),
     *             @OA\Property(property="cpf", type="string", example="12345678901"),
     *             @OA\Property(property="phone", type="string", example="(81) 91234-5678")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Registration successful",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Registration successful."),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="user", ref="#/components/schemas/User"),
     *                 @OA\Property(property="token", type="string", example="example-token"),
     *                 @OA\Property(property="token_type", type="string", example="Bearer"),
     *                 @OA\Property(property="expires_in", type="integer", example=3600)
     *             ),
     *             @OA\Property(property="errors", type="object", nullable=true)
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Validation error."),
     *             @OA\Property(property="data", type="object", nullable=true),
     *             @OA\Property(property="errors", type="object", nullable=true)
     *         )
     *     )
     * )
     */
    public function signup(StoreUserRequest $request)
    {
        $userController = new UserController();
        return $userController->store($request);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/auth/signout",
     *     operationId="signout",
     *     tags={"Auth"},
     *     summary="User logout",
     *     description="Logout the authenticated user",
     *     security={{"bearerAuth": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="Logout successful",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Logout successful")
     *         )
     *     )
     * )
     */
    public function signout(Request $request)
    {
        $request->user()->tokens()->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->json([
            'status' => 'success',
            'message' => 'Logout successful.',
            'data' => null,
            'errors' => null
        ], 200);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/auth/user",
     *     operationId="getUser",
     *     tags={"Auth"},
     *     summary="Get authenticated user",
     *     description="Returns the authenticated user's data",
     *     security={{"bearerAuth": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="User data retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="User successfully recovered."),
     *             @OA\Property(property="data", ref="#/components/schemas/User"),
     *             @OA\Property(property="errors", type="object", nullable=true)
     *         )
     *     )
     * )
     */
    public function user(Request $request)
    {
        return response()->json([
            'status' => 'success',
            'message' => 'User successfully recovered.',
            'data' => $request->user(),
            'errors' => null
        ], 200);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/auth/password/email",
     *     operationId="sendResetLinkEmail",
     *     tags={"Auth"},
     *     summary="Send password reset link",
     *     description="Sends a password reset link to the user's email",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="email", type="string", format="email", example="user@email.com")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Reset link sent",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="We have emailed your password reset link!"),
     *             @OA\Property(property="data", type="object", nullable=true),
     *             @OA\Property(property="errors", type="object", nullable=true)
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Email not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Email not found."),
     *             @OA\Property(property="data", type="object", nullable=true),
     *             @OA\Property(property="errors", type="object", nullable=true)
     *         )
     *     )
     * )
     */
    public function sendResetLinkEmail(Request $request)
    {
        $request->validate(['email' => 'required|email']);
        $user = User::where('email', $request->email)->first();
        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'Email not found.',
                'data' => null,
                'errors' => null
            ], 404);
        }
        $token = Password::getRepository()->create($user);
        $user->notify(new CustomResetPassword($token));
        return response()->json([
            'status' => 'success',
            'message' => 'We have emailed your password reset link!',
            'data' => null,
            'errors' => null
        ], 200);
    }


    /**
     * @OA\Post(
     *     path="/api/v1/auth/password/reset",
     *     operationId="resetPassword",
     *     tags={"Auth"},
     *     summary="Reset password",
     *     description="Resets the user's password using a valid token",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="token", type="string", example="valid-reset-token"),
     *             @OA\Property(property="email", type="string", format="email", example="user@example.com"),
     *             @OA\Property(property="password", type="string", format="password", example="newpassword123"),
     *             @OA\Property(property="password_confirmation", type="string", format="password", example="newpassword123")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Password reset successful",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Your password has been reset!"),
     *             @OA\Property(property="data", type="object", nullable=true),
     *             @OA\Property(property="errors", type="object", nullable=true)
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid token or email",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="email", type="array", @OA\Items(type="string", example="This password reset token is invalid.")),
     *             @OA\Property(property="data", type="object", nullable=true),
     *             @OA\Property(property="errors", type="object", nullable=true)
     *         )
     *     )
     * )
     */
    public function reset(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => ['required', 'confirmed', \Illuminate\Validation\Rules\Password::defaults()],
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->forceFill([
                    'password' => Hash::make($password)
                ])->save();
            }
        );

        return $status === Password::PASSWORD_RESET
            ? response()->json(['message' => __($status)], 200)
            : response()->json(['email' => [__($status)]], 400);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/auth/password/token",
     *     operationId="getResetToken",
     *     tags={"Auth"},
     *     summary="Get password reset token",
     *     description="Returns a password reset token for the given email",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="email", type="string", format="email", example="user@example.com")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Password reset token successfully recovered",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Password reset token successfully recovered."),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="token", type="string", example="valid-reset-token"),
     *                 @OA\Property(property="email", type="string", format="email", example="user@example.com")
     *             ),
     *             @OA\Property(property="errors", type="object", nullable=true)
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Email not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Email not found."),
     *             @OA\Property(property="data", type="object", nullable=true),
     *             @OA\Property(property="errors", type="object", nullable=true)
     *         )
     *     )
     * )
     */
    public function getResetToken(Request $request)
    {
        $request->validate(['email' => 'required|email']);
        $user = User::where('email', $request->email)->first();
        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'Email not found.',
                'data' => null,
                'errors' => null
            ], 404);
        }
        $token = Password::getRepository()->create($user);
        return response()->json([
            'status' => 'success',
            'message' => 'Password reset token successfully recovered.',
            'data' => array('token' => $token, 'email' => $request->email),
            'errors' => null
        ], 200);
    }
}
