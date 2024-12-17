<?php

namespace App\Http\Controllers\Documentation;

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
 *             @OA\Property(property="message", type="string", example="Registration successful. Please check your email to verify your account."),
 *             @OA\Property(property="data", ref="#/components/schemas/User"),
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
 *             @OA\Property(property="status", type="string", example="success"),
 *             @OA\Property(property="message", type="string", example="Logout successful."),
 *             @OA\Property(property="data", type="object", nullable=true),
 *             @OA\Property(property="errors", type="object", nullable=true)
 *         )
 *     ),
 *     @OA\Response(
 *         response=401,
 *         description="Token has expired or is invalid",
 *         @OA\JsonContent(
 *             @OA\Property(property="status", type="string", example="error"),
 *             @OA\Property(property="message", type="string", example="Token has expired or is invalid."),
 *             @OA\Property(property="data", type="object", nullable=true),
 *             @OA\Property(property="errors", type="object", nullable=true)
 *         )
 *     )
 * )
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
 * @OA\Post(
 *     path="/api/v1/auth/email/resend",
 *     operationId="resendVerificationEmail",
 *     tags={"Auth"},
 *     summary="Resend email verification link",
 *     description="Resends the email verification link to the user",
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             @OA\Property(property="email", type="string", format="email", example="user@example.com")
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Verification link sent",
 *         @OA\JsonContent(
 *             @OA\Property(property="status", type="string", example="success"),
 *             @OA\Property(property="message", type="string", example="Verification link sent successfully."),
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
 *     ),
 *     @OA\Response(
 *         response=400,
 *         description="Email already verified",
 *         @OA\JsonContent(
 *             @OA\Property(property="status", type="string", example="error"),
 *             @OA\Property(property="message", type="string", example="Email already verified."),
 *             @OA\Property(property="data", type="object", nullable=true),
 *             @OA\Property(property="errors", type="object", nullable=true)
 *         )
 *     )
 * )
 * @OA\Get(
 *     path="/api/v1/auth/email/verify/{id}",
 *     operationId="verifyEmail",
 *     tags={"Auth"},
 *     summary="Verify email address",
 *     description="Verify the user's email address",
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         required=true,
 *         @OA\Schema(type="string")
 *     ),
 *     @OA\Parameter(
 *         name="expires",
 *         in="query",
 *         required=true,
 *         @OA\Schema(type="integer")
 *     ),
 *     @OA\Parameter(
 *         name="signature",
 *         in="query",
 *         required=true,
 *         @OA\Schema(type="string")
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Email verified successfully",
 *         @OA\JsonContent(
 *             @OA\Property(property="status", type="string", example="success"),
 *             @OA\Property(property="message", type="string", example="Email verified successfully."),
 *             @OA\Property(property="data", type="object", nullable=true),
 *             @OA\Property(property="errors", type="object", nullable=true)
 *         )
 *     ),
 *     @OA\Response(
 *         response=400,
 *         description="Invalid or expired verification link",
 *         @OA\JsonContent(
 *             @OA\Property(property="status", type="string", example="error"),
 *             @OA\Property(property="message", type="string", example="Invalid or expired verification link."),
 *             @OA\Property(property="data", type="object", nullable=true),
 *             @OA\Property(property="errors", type="object", nullable=true)
 *         )
 *     )
 * )
 */
class AuthDocumentation {}
