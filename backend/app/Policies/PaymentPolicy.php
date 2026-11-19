<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Payment;
use App\Models\User;

class PaymentPolicy
{
    public function before(User $user, string $ability): ?bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        return null;
    }

    public function viewAny(User $user): bool
    {
        return $user->is_active && ($user->isClient() || $user->isResellerTier());
    }

    public function view(User $user, Payment $payment): bool
    {
        if ($user->isClient()) {
            return (int) $payment->user_id === (int) $user->id;
        }

        if ($user->isResellerTier()) {
            return (int) $payment->reseller_id === (int) $user->id;
        }

        return false;
    }

    public function create(User $user): bool
    {
        return $user->is_active && ($user->isClient() || $user->isResellerTier());
    }

    public function verify(User $user, Payment $payment): bool
    {
        return false;
    }
}
