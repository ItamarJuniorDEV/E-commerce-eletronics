<?php

namespace App\Support;

use App\Models\Permission;
use App\Models\User;
use Illuminate\Validation\ValidationException;

class LastAdministratorGuard
{
    private const MANAGEMENT_PERMISSIONS = [
        'roles.manage',
        'users.manage',
    ];

    public function lock(): void
    {
        Permission::query()
            ->select(['id'])
            ->whereIn('slug', self::MANAGEMENT_PERMISSIONS)
            ->orderBy('id')
            ->lockForUpdate()
            ->get();
    }

    /** @return array<string, bool> */
    public function snapshot(): array
    {
        $result = [];

        foreach (self::MANAGEMENT_PERMISSIONS as $slug) {
            $result[$slug] = $this->hasActiveManager($slug);
        }

        return $result;
    }

    /** @param array<string, bool> $before */
    public function ensureRetained(
        array $before,
        string $field
    ): void {
        foreach ($before as $permission => $hadManager) {
            if (
                $hadManager
                && ! $this->hasActiveManager($permission)
            ) {
                throw ValidationException::withMessages([
                    $field => 'Não é possível remover o último acesso administrativo ativo.',
                ]);
            }
        }
    }

    private function hasActiveManager(string $permission): bool
    {
        return User::query()
            ->where('is_staff', true)
            ->where('is_active', true)
            ->whereNotNull('email_verified_at')
            ->whereHas('roles', function ($query) use ($permission) {
                $query->whereHas(
                    'permissions',
                    function ($permissions) use ($permission) {
                        $permissions->where('slug', $permission);
                    }
                );
            })
            ->exists();
    }
}
