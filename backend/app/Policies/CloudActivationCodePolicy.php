<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\CloudActivationCode;
use App\Models\User;

class CloudActivationCodePolicy
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
        return $user->is_active && $user->isClient();
    }

    public function view(User $user, CloudActivationCode $cloudCode): bool
    {
        return $user->isClient() && (int) $cloudCode->user_id === (int) $user->id;
    }

    public function create(User $user): bool
    {
        return $user->is_active && $user->isClient();
    }
}
