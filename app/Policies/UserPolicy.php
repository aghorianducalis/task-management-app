<?php

declare(strict_types=1);

namespace App\Policies;

use App\Enums\PermissionEnum;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class UserPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can(PermissionEnum::ViewUsers->value);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, int $userId): bool
    {
        return $user->can(PermissionEnum::ViewUser->value);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can(PermissionEnum::CreateUser->value);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, int $userId): bool
    {
        return $user->can(PermissionEnum::UpdateUser->value);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, int $userId): bool
    {
        return $user->can(PermissionEnum::DeleteUser->value);
    }
}
