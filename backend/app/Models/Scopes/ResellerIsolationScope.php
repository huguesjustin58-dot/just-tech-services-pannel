<?php

declare(strict_types=1);

namespace App\Models\Scopes;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Support\Facades\Auth;

class ResellerIsolationScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        $user = Auth::user();
        if (! $user instanceof User) {
            return;
        }

        if ($user->role === UserRole::SUPER_ADMIN || $user->role->isStaff()) {
            return;
        }

        $table = $model->getTable();
        if ($user->role === UserRole::RESELLER) {
            $subIds = User::withoutGlobalScopes()
                ->where('parent_reseller_id', $user->id)
                ->pluck('id')
                ->all();
            $allowedResellerIds = array_merge([$user->id], $subIds);
            $builder->whereIn("{$table}.reseller_id", $allowedResellerIds);
            return;
        }

        if ($user->role === UserRole::SUB_RESELLER) {
            $builder->where("{$table}.reseller_id", $user->id);
            return;
        }

        if ($user->role === UserRole::CLIENT) {
            $col = $table === 'users' ? 'id' : 'user_id';
            $builder->where("{$table}.{$col}", $user->id);
        }
    }
}
