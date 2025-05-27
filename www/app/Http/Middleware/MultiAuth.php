<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class MultiAuth
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next)
    {
        // Buscar tokens en cookies separadas
        $studentToken = $request->cookie('student_token');
        $adminToken = $request->cookie('admin_token');

        $token = $studentToken ?? $adminToken;

        if (!$token) {
            return response()->json(['message' => 'Token no encontrado'], 401);
        }

        // Agregar manualmente el header Authorization para Sanctum
        $request->headers->set('Authorization', 'Bearer ' . $token);

        return $next($request);
    }
}
