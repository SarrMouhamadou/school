<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        $user = $request->user();

        // Vérifier si l'utilisateur est authentifié
        if (!$user) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        // Vérifier si le rôle de l'utilisateur est dans la liste des rôles autorisés
        if (!in_array($user->role->name, $roles)) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        return $next($request);
    }
}
