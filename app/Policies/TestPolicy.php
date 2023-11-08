<?php

declare(strict_types=1);

namespace App\Policies;

use App\Enums\PermissionEnum;
use App\Enums\RoleEnum;
use App\Models\User;
use App\Services\TestService;

class TestPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can(PermissionEnum::ViewTests->value);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, int $testId): bool
    {
        return $user->can(PermissionEnum::ViewTest->value) &&
            (
                $user->hasRole(RoleEnum::Admin->value) ||
                (
                    $user->hasRole(RoleEnum::Manager->value) &&
                    TestService::getInstance()->isTestBelongsToManager($testId, $user->id)
                )
            );
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can(PermissionEnum::CreateTest->value);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, int $testId): bool
    {
        return
            (
                $user->hasRole(RoleEnum::Admin->value) &&
                $user->can(PermissionEnum::UpdateTest->value)
            ) ||
            (
                $user->hasRole(RoleEnum::Manager->value) &&
                $user->can(PermissionEnum::UpdateTestRate->value) &&
                TestService::getInstance()->isTestBelongsToManager($testId, $user->id)
            );
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, int $testId): bool
    {
        return $user->can(PermissionEnum::DeleteTest->value);
    }
}
