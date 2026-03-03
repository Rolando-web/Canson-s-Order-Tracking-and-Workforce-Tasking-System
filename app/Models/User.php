<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $primaryKey = 'User_Id';

    const ROLE_EMPLOYEE = 'employee';
    const ROLE_ADMIN = 'admin';
    const ROLE_SUPER_ADMIN = 'super_admin';

    protected $fillable = [
        'name',
        'password',
        'role',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
        ];
    }

    /**
     * Tell Laravel auth which column is the unique identifier.
     */
    public function getAuthIdentifierName(): string
    {
        return 'User_Id';
    }

    // ─── Role helpers ────────────────────────────

    public function isEmployee(): bool
    {
        return $this->role === self::ROLE_EMPLOYEE;
    }

    public function isAdmin(): bool
    {
        return $this->role === self::ROLE_ADMIN;
    }

    public function isSuperAdmin(): bool
    {
        return $this->role === self::ROLE_SUPER_ADMIN;
    }

    public function isAdminOrAbove(): bool
    {
        return in_array($this->role, [self::ROLE_ADMIN, self::ROLE_SUPER_ADMIN]);
    }

    public function hasRole(string $role): bool
    {
        return $this->role === $role;
    }

    public function hasAnyRole(array $roles): bool
    {
        return in_array($this->role, $roles);
    }

    // ─── Relationships ───────────────────────────

    public function assignments()
    {
        return $this->hasMany(Assignment::class, 'employee_id', 'User_Id');
    }

    public function activeAssignments()
    {
        return $this->assignments()->whereIn('status', ['pending', 'in_progress']);
    }

    public function createdOrders()
    {
        return $this->hasMany(Order::class, 'created_by', 'User_Id');
    }

    /**
     * Get display initial(s) for avatars.
     */
    public function getInitialAttribute(): string
    {
        $parts = explode(' ', $this->name);
        if (count($parts) >= 2) {
            return strtoupper(substr($parts[0], 0, 1) . substr($parts[1], 0, 1));
        }
        return strtoupper(substr($this->name, 0, 1));
    }
}