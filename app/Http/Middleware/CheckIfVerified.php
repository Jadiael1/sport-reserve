<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckIfVerified
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Verifica se o usuário está autenticado e se o email foi verificado
        if (Auth::check() && Auth::user()->email_verified_at === null) {
            return response()->json([
                'status' => 'error',
                'message' => 'Your email address is not verified.',
                'data' => null,
                'errors' => null
            ], 403);
        }

        return $next($request);
    }
}
