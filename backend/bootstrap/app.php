<?php

declare(strict_types=1);

use App\Http\Middleware\EnsureInfraVisibleRole;
use App\Http\Middleware\EnsureUserIsAdmin;
use App\Http\Middleware\EnsureUserIsClient;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'admin' => EnsureUserIsAdmin::class,
            'client' => EnsureUserIsClient::class,
            'infra.visible' => EnsureInfraVisibleRole::class,
        ]);

        // Redirection des utilisateurs déjà authentifiés qui visitent /login ou /register
        $middleware->redirectGuestsTo(fn () => route('login'));
        $middleware->redirectUsersTo(function ($request) {
            $user = $request->user();

            return ($user && method_exists($user, 'isAdmin') && $user->isAdmin())
                ? '/admin'
                : route('client.dashboard');
        });
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })
    ->create();
