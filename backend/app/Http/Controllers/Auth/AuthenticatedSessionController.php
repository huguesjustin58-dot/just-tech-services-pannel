<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    public function create(): View
    {
        return view('auth.login');
    }

    public function store(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ]);

        $remember = $request->boolean('remember');

        if (! Auth::attempt($credentials, $remember)) {
            throw ValidationException::withMessages([
                'email' => 'Identifiants invalides.',
            ]);
        }

        /** @var User $user */
        $user = Auth::user();

        if (! $user->is_active) {
            Auth::guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            throw ValidationException::withMessages([
                'email' => 'Ce compte est actuellement désactivé.',
            ]);
        }

        // Régénération obligatoire de l'ID de session contre la fixation de session
        $request->session()->regenerate();

        $user->forceFill([
            'last_login_at' => now(),
            'last_login_ip' => $request->ip(),
        ])->save();

        AuditLog::create([
            'actor_id' => $user->id,
            'action' => 'auth.login',
            'auditable_type' => User::class,
            'auditable_id' => $user->id,
            'ip_address' => $request->ip(),
            'context' => ['role' => $user->role->value],
            'created_at' => now(),
        ]);

        if ($user->isAdmin()) {
            $adminUrl = '/admin';
            $intended = (string) $request->session()->pull('url.intended', '');
            if ($intended !== '' && str_starts_with(parse_url($intended, PHP_URL_PATH) ?? '', '/admin')) {
                return redirect()->to($intended);
            }

            return redirect()->to($adminUrl);
        }

        $intended = (string) $request->session()->pull('url.intended', '');
        if ($intended !== '' && ! str_starts_with(parse_url($intended, PHP_URL_PATH) ?? '', '/admin')) {
            return redirect()->to($intended);
        }

        return redirect()->route('client.dashboard');
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home');
    }
}
