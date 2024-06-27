<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckAccountIsActive
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Verifica se o usuário está autenticado
        if (Auth::check()) {
            $user = Auth::user();

            // Verifica se a conta do usuário está ativa
            if (!$user->is_active) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Your account is not active.',
                    'data' => null,
                    'errors' => null
                ], 403);
            }
        }

        return $next($request);
    }
}
