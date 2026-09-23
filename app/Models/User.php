<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, BelongsToTenant;

    /** Full access, including settings and team management. */
    public const ROLE_ADMIN = 'admin';

    /** Day-to-day operations; cannot change settings, team or delete financial records. */
    public const ROLE_MANAGER = 'manager';

    public const ROLES = [
        self::ROLE_ADMIN => 'Owner / Admin',
        self::ROLE_MANAGER => 'Manager',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'tenant_id',
        'name',
        'email',
        'password',
        'role',
        'is_active',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    public function isStaff(): bool
    {
        return $this->is_active !== false && array_key_exists($this->role, self::ROLES);
    }

    public function isAdmin(): bool
    {
        return $this->isStaff() && $this->role === self::ROLE_ADMIN;
    }

    public function getRoleLabelAttribute(): string
    {
        return self::ROLES[$this->role] ?? ucfirst((string) $this->role);
    }
}
