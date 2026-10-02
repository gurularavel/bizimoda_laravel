<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Admin extends Authenticatable
{
    use Notifiable;

    public const ROLES = [
        'super_admin' => 'Super admin',
        'manager' => 'Menecer (sifarişlər, kataloq)',
        'content' => 'Kontent menecer (səhifələr, bloq, menyu)',
    ];

    // Hər rolun daxil ola biləcəyi admin bölmələri
    public const PERMISSIONS = [
        'manager' => ['dashboard', 'catalog', 'sales', 'customers', 'forms'],
        'content' => ['dashboard', 'content', 'forms'],
    ];

    protected $fillable = ['name', 'email', 'password', 'role', 'is_active'];

    protected $hidden = ['password', 'remember_token'];

    protected $casts = ['password' => 'hashed', 'is_active' => 'boolean'];

    public function canAccess(string $section): bool
    {
        if ($this->role === 'super_admin') {
            return true;
        }

        return in_array($section, self::PERMISSIONS[$this->role] ?? [], true);
    }
}
