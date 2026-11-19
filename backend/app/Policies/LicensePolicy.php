<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\License;
use App\Models\User;

class LicensePolicy
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

    public function view(User $user, License $license): bool
    {
        if ($user->isClient()) {
            return (int) $license->user_id === (int) $user->id;
        }

        if ($user->isResellerTier()) {
            return (int) $license->reseller_id === (int) $user->id;
        }

        return false;
    }

    public function create(User $user): bool
    {
        return $user->is_active && ($user->isClient() || $user->isResellerTier());
    }

    public function update(User $user, License $license): bool
    {
        return false;
    }

    public function delete(User $user, License $license): bool
    {
        return false;
    }
}
