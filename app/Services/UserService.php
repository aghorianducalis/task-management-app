<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\RoleEnum;
use App\Models\User;
use App\Repositories\Interfaces\UserRepositoryInterface;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

class UserService
{
    protected UserRepositoryInterface $userRepository;

    public function __construct(UserRepositoryInterface $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function getUserById(int $id): ?User
    {
        return $this->userRepository->find($id);
    }

    public function getAllUsers(): Collection
    {
        return $this->userRepository->matching();
    }

    public function createUser(array $data): User
    {
        $role = $data['role'] ?? RoleEnum::Admin;
        unset($data['role']);

        $password = Hash::make($data['password']);
        $data['password'] = $password;

        $user = $this->userRepository->create($data);

        /** @var RolePermissionService $service */
        $service = app(RolePermissionService::class);
        $service->assignRoleToUser($user, $role);

        event(new Registered($user));

        return $user;
    }

    public function updateUser(array $data, int $id): User
    {
        $password = Hash::make($data['password']);
        $data['password'] = $password;

        return $this->userRepository->update($data, $id);
    }

    public function deleteUser(int $id): bool
    {
        return $this->userRepository->delete($id);
    }

    /**
     * Attempt to reset the user's password.
     * If it is successful we will update the password on an actual user model and persist it to the database.
     *
     * @param array $data
     * @return string
     */
    public function resetPassword(array $data): string
    {
        return Password::reset(
                $data,
                function (User $user) use ($data) {
                    $this->userRepository->update([
                        'password'       => Hash::make($data['password']),
                        'remember_token' => Str::random(60),
                    ], $user->id);

                    event(new PasswordReset($user));
                }
            );
    }

    public static function getInstance(): UserService
    {
        return app(UserService::class);
    }
}
