<?php

declare(strict_types=1);

namespace App\Enums;

enum RoleEnum: string
{
    case Admin = 'admin';
    case Manager = 'manager';

    public function permissions(): array
    {
        return PermissionEnum::permissionsFromRoleEnum($this);
    }

    public static function rolesOfPermission(PermissionEnum $permissionEnum): array
    {
        return match($permissionEnum) {
            PermissionEnum::CreateUser,
            PermissionEnum::UpdateUser,
            PermissionEnum::DeleteUser,
            PermissionEnum::ViewUser,
            PermissionEnum::ViewUsers,
            PermissionEnum::CreateTest,
            PermissionEnum::UpdateTest,
            PermissionEnum::DeleteTest,
            PermissionEnum::ViewTest,
            PermissionEnum::ViewTests => [
                self::Admin,
            ],
            PermissionEnum::ViewTests,
            PermissionEnum::UpdateTestRate => [
                self::Manager,
            ],
        };
    }
}
