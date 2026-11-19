<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Protège l'Espace Client (/client/* et /dashboard).
 * - Visiteur non connecté -> redirection /login
 * - Compte désactivé -> déconnexion + 403
 * - Administrateur -> redirection propre vers /admin
 * - Tout rôle non client -> 403 Forbidden
 */
class EnsureUserIsClient
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->guest(route('login'));
        }

        if (! $user->is_active) {
            Auth::guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            abort(403, 'Votre compte est suspendu.');
        }

        if ($user->isAdmin()) {
            return redirect('/admin');
        }

        if (! $user->isClient()) {
            abort(403, 'Accès réservé aux comptes clients.');
        }

        return $next($request);
    }
}
