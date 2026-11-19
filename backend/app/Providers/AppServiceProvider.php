<?php

declare(strict_types=1);

namespace App\Providers;

use App\Models\CloudActivationCode;
use App\Models\License;
use App\Models\Payment;
use App\Models\User;
use App\Policies\CloudActivationCodePolicy;
use App\Policies\LicensePolicy;
use App\Policies\PaymentPolicy;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Gate::policy(License::class, LicensePolicy::class);
        Gate::policy(Payment::class, PaymentPolicy::class);
        Gate::policy(CloudActivationCode::class, CloudActivationCodePolicy::class);

        Gate::define('access-admin', static fn (User $user): bool => $user->isAdmin());
        Gate::define('access-client-space', static fn (User $user): bool => $user->isClient());

        RateLimiter::for('auth-login', static function (Request $request): Limit {
            return Limit::perMinute(5)->by(
                strtolower((string) $request->input('email')).'|'.$request->ip()
            );
        });

        RateLimiter::for('auth-register', static function (Request $request): Limit {
            return Limit::perMinute(5)->by($request->ip());
        });
    }
}
