<?php

declare(strict_types=1);

namespace App\Enums;

enum PermissionEnum: string
{
    case CreateUser = 'create_user';
    case UpdateUser = 'update_user';
    case DeleteUser = 'delete_user';
    case ViewUser = 'view_user';
    case ViewUsers = 'view_users';
    case CreateTest = 'create_test';
    case UpdateTest = 'update_test';
    case UpdateTestRate = 'update_test_rate';
    case DeleteTest = 'delete_test';
    case ViewTest = 'view_test';
    case ViewTests = 'view_tests';

    public static function permissionsFromRoleEnum(RoleEnum $roleEnum): array
    {
        return match($roleEnum) {
            RoleEnum::Admin => [
                self::ViewUsers,
                self::ViewUser,
                self::CreateUser,
                self::UpdateUser,
                self::DeleteUser,
                self::ViewTests,
                self::ViewTest,
                self::CreateTest,
                self::UpdateTest,
                self::UpdateTestRate,
                self::DeleteTest,
            ],
            RoleEnum::Manager => [
                self::ViewTests,
                self::ViewTest,
                self::UpdateTestRate,
            ],
        };
    }
}
