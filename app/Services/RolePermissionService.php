<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\PermissionEnum;
use App\Enums\RoleEnum;
use App\Models\User;
use App\Repositories\Interfaces\PermissionRepositoryInterface;
use App\Repositories\Interfaces\RoleRepositoryInterface;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolePermissionService
{
    protected RoleRepositoryInterface $roleRepository;

    protected PermissionRepositoryInterface $permissionRepository;

    public function __construct(
        RoleRepositoryInterface $roleRepository,
        PermissionRepositoryInterface $permissionRepository
    ) {
        $this->roleRepository = $roleRepository;
        $this->permissionRepository = $permissionRepository;
    }

    public function assignRoleToUser(User $user, RoleEnum $roleEnum): User
    {
        return $user->assignRole($roleEnum->value);
    }

    public function syncRolesAndPermissions()
    {
        foreach (RoleEnum::cases() as $roleEnum) {
            $role = $this->firstOrCreateRole($roleEnum->value);

            $permissionNamesToSync = collect(PermissionEnum::permissionsFromRoleEnum($roleEnum))->pluck('value');
            $permissionNames = $role->getPermissionNames();

            $permissionsToRevoke = array_diff($permissionNames->toArray(), $permissionNamesToSync->toArray());
            $permissionsToAssign = array_diff($permissionNamesToSync->toArray(), $permissionNames->toArray());

            foreach ($permissionsToRevoke as $permissionName) {
                $permission = $this->firstOrCreatePermission($permissionName);
                $role->revokePermissionTo($permission);
            }

            foreach ($permissionsToAssign as $permissionName) {
                $permission = $this->firstOrCreatePermission($permissionName);
                $role->givePermissionTo($permission);
            }
        }
    }

    protected function firstOrCreateRole(string $roleName): Role
    {
        // todo optimize this 'first-or-create' logic
        $roleFromDB = $this->roleRepository->matching()
            ->filter(fn(Role $role) => ($role->name === $roleName))
            ->first();

        if (!$roleFromDB) {
            $roleFromDB = $this->roleRepository->create(['name' => $roleName]);
        }

        return $roleFromDB;
    }

    protected function firstOrCreatePermission(string $permissionName): Permission
    {
        // todo optimize this 'first-or-create' logic
        $permissionFromDB = $this->permissionRepository->matching()
            ->filter(fn(Permission $permission) => ($permission->name === $permissionName))
            ->first();

        if (!$permissionFromDB) {
            $permissionFromDB = $this->permissionRepository->create(['name' => $permissionName]);
        }

        return $permissionFromDB;
    }

    public static function getInstance(): RolePermissionService
    {
        return app(RolePermissionService::class);
    }
}
