<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\UserRole;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable implements FilamentUser
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name', 'email', 'phone', 'password', 'role',
        'reseller_id', 'parent_reseller_id', 'credit_balance_fcfa', 'gb_balance',
        'client_seat_limit', 'commission_bps', 'can_create_sub_resellers', 'is_active',
        'last_login_at', 'last_login_ip',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'role' => UserRole::class,
            'password' => 'hashed',
            'credit_balance_fcfa' => 'integer',
            'gb_balance' => 'integer',
            'client_seat_limit' => 'integer',
            'commission_bps' => 'integer',
            'can_create_sub_resellers' => 'boolean',
            'is_active' => 'boolean',
            'last_login_at' => 'datetime',
        ];
    }

    public function isAdmin(): bool
    {
        return (bool) $this->is_active
            && in_array($this->role, [UserRole::SUPER_ADMIN, UserRole::ADMIN], true);
    }

    public function isStaff(): bool
    {
        return (bool) $this->is_active && ($this->role?->isStaff() ?? false);
    }

    public function isClient(): bool
    {
        return (bool) $this->is_active && $this->role === UserRole::CLIENT;
    }

    public function isResellerTier(): bool
    {
        return (bool) $this->is_active && ($this->role?->isResellerTier() ?? false);
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return $panel->getId() === 'admin' && $this->isAdmin();
    }

    public function reseller(): BelongsTo
    {
        return $this->belongsTo(self::class, 'reseller_id');
    }

    public function licenses(): HasMany
    {
        return $this->hasMany(License::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function cloudActivationCodes(): HasMany
    {
        return $this->hasMany(CloudActivationCode::class);
    }
}
