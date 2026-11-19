<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\AuditLog;
use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Protège strictement toutes les routes Filament et d'administration (/admin/*).
 * Renvoie 404 Not Found aux visiteurs anonymes et aux clients afin de ne
 * jamais révéler l'existence du panneau d'administration.
 */
class EnsureUserIsAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user instanceof User || ! $user->isAdmin()) {
            if ($user instanceof User) {
                AuditLog::create([
                    'actor_id' => $user->id,
                    'action' => 'security.unauthorized_admin_access',
                    'auditable_type' => User::class,
                    'auditable_id' => $user->id,
                    'ip_address' => $request->ip(),
                    'context' => [
                        'path' => '/'.$request->path(),
                        'role' => $user->role?->value,
                    ],
                    'created_at' => now(),
                ]);
            }

            abort(404);
        }

        return $next($request);
    }
}
