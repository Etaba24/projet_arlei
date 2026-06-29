<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    /**
     * Vérifie que l'utilisateur connecté est un administrateur.
     * Redirige vers le dashboard opérateur sinon.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!$request->user() || !$request->user()->hasAdminInterface()) {
            abort(403, 'Accès réservé aux administrateurs.');
        }

        return $next($request);
    }
}
