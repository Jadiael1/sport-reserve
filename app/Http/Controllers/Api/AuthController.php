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

    public function signin(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);
        try {
            $recaptcha = new \ReCaptcha\ReCaptcha(env('GOOGLE_RECAPTCHA_V3_SECRET_KEY'));
            $response = $recaptcha->setExpectedAction('signin')
                ->setScoreThreshold(0.5)
                ->verify($request->recaptchaToken);
                
            if (!$response->isSuccess()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'reCAPTCHA validation failed.',
                    'data' => null,
                    'errors' => $response->getErrorCodes(),
                ], 403);
            }

            if ($response->getAction() !== 'signin') {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Unexpected reCAPTCHA action.',
                    'data' => null,
                    'errors' => null,
                ], 403);
            }

            if ($response->getScore() < 0.5) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Low reCAPTCHA score.',
                    'data' => null,
                    'errors' => ['score' => $response->getScore()],
                ], 403);
            }
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'reCAPTCHA verification error.',
                'data' => null,
                'errors' => $e->getMessage(),
            ], 500);
        }

        if (Auth::attempt($credentials)) {
            /** @var \App\Models\User $user **/
            $user = Auth::user();

            /*
            if (!$user->hasVerifiedEmail()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Email not verified.',
                    'data' => null,
                    'errors' => null
                ], 403);
            }
            */
            if (!$user->active) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Your account is inactive. Please contact support for assistance.',
                    'data' => null,
                    'errors' => null
                ], 403);
            }

            $tokenResult = $user->createToken('auth_token');
            $token = $tokenResult->plainTextToken;

            $expirationMinutes = 60;
            $expiration = Carbon::now()->addMinutes($expirationMinutes);

            $tokenResult->accessToken->expires_at = $expiration;
            $tokenResult->accessToken->save();

            $expirationInTimezone = $expiration->setTimezone('America/Recife')->toDateTimeString();

            return response()->json([
                'status' => 'success',
                'message' => 'Login successful.',
                'data' => ['user' => $user, 'token' => $token, 'token_type' => 'Bearer', 'expires_in' => $expirationInTimezone],
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

    public function signup(StoreUserRequest $request)
    {
        $userController = new UserController();
        return $userController->store($request);
    }

    public function signout(Request $request)
    {
        $user = $request->user();
        $token = $user ? $user->currentAccessToken() : null;

        if ($token) {
            $token->delete();
            return response()->json([
                'status' => 'success',
                'message' => 'Logout successful.',
                'data' => null,
                'errors' => null
            ], 200);
        }

        return response()->json([
            'status' => 'error',
            'message' => 'Token has expired or is invalid.',
            'data' => null,
            'errors' => null
        ], 401);
    }

    public function user(Request $request)
    {
        return response()->json([
            'status' => 'success',
            'message' => 'User successfully recovered.',
            'data' => $request->user(),
            'errors' => null
        ], 200);
    }

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

    public function resendVerificationEmail(Request $request)
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

        if ($user->hasVerifiedEmail()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Email already verified.',
                'data' => null,
                'errors' => null
            ], 400);
        }

        $user->sendEmailVerificationNotification();
        return response()->json([
            'status' => 'success',
            'message' => 'Verification link sent successfully.',
            'data' => null,
            'errors' => null
        ], 200);
    }

    public function verifyEmail(Request $request, $id)
    {
        $user = User::findOrFail($id);

        if (!hash_equals((string) $request->signature, sha1($user->getEmailForVerification()))) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid verification link.',
                'data' => null,
                'errors' => null
            ], 400);
        }

        if ($user->hasVerifiedEmail()) {
            return response()->json([
                'status' => 'success',
                'message' => 'Email already verified.',
                'data' => null,
                'errors' => null
            ], 200);
        }

        $user->markEmailAsVerified();

        return response()->json([
            'status' => 'success',
            'message' => 'Email verified successfully.',
            'data' => null,
            'errors' => null
        ], 200);
    }
}
