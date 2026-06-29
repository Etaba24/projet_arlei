<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckPermission
{
    /**
     * Usage: middleware('perm:slug1,slug2')
     * Passe si l'utilisateur possède AU MOINS UNE des permissions listées.
     * Les admins (role === 'admin') passent toujours.
     */
    public function handle(Request $request, Closure $next, string ...$permissions): Response
    {
        $user = $request->user();

        if (!$user) {
            return redirect()->route('login');
        }

        foreach ($permissions as $permission) {
            if ($user->hasPermission($permission)) {
                return $next($request);
            }
        }

        abort(403, 'Vous n\'avez pas la permission d\'accéder à cette ressource.');
    }
}
