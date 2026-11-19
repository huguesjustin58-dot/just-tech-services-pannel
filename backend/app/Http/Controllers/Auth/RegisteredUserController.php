<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * L'inscription publique est strictement verrouillée sur le rôle CLIENT.
     * Tout champ 'role' injecté manuellement dans la requête HTTP est ignoré.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'phone' => ['nullable', 'string', 'max:32'],
            'password' => ['required', 'confirmed', Password::min(8)],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'password' => Hash::make($validated['password']),
            'role' => UserRole::CLIENT,
            'is_active' => true,
            'last_login_at' => now(),
            'last_login_ip' => $request->ip(),
        ]);

        Auth::login($user);
        $request->session()->regenerate();

        AuditLog::create([
            'actor_id' => $user->id,
            'action' => 'auth.register_client',
            'auditable_type' => User::class,
            'auditable_id' => $user->id,
            'ip_address' => $request->ip(),
            'context' => ['role' => UserRole::CLIENT->value],
            'created_at' => now(),
        ]);

        return redirect()->route('client.dashboard');
    }
}
