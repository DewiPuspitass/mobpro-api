<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TokenAuth
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next)
    {
        $token = $request->header('Authorization');

        if (!$token) {
            return response()->json(['message' => 'Token tidak ditemukan'], 401);
        }

        $user = User::where('remember_token', $token)->first();

        if (!$user) {
            return response()->json(['message' => 'Token tidak valid'], 401);
        }

        // Simpan user ke request (jika perlu diakses di controller)
        $request->merge(['user' => $user]);

        return $next($request);
    }
}
