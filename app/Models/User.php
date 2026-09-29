<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Traits\HasRoles;

#[Fillable(['name', 'email', 'password', 'role', 'client_id', 'active'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, HasRoles, Notifiable;

    /** Papéis que não podem ser renomeados/removidos (usados no middleware role:). */
    public const CORE_ROLES = ['admin', 'gerente', 'cliente', 'financeiro'];

    protected static function booted(): void
    {
        // Mantém o papel Spatie sincronizado com a coluna role (Kanban/permissões).
        static::saved(function (User $user) {
            if ($user->role && Role::where('name', $user->role)->exists()) {
                $user->syncRoles([$user->role]);
            }
        });
    }

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'active' => 'boolean',
        ];
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isCoreRole(): bool
    {
        return in_array($this->role, self::CORE_ROLES, true);
    }
}
