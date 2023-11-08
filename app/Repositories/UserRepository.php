<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\User;
use App\Repositories\Interfaces\UserRepositoryInterface;
use Illuminate\Database\Eloquent\Builder;

class UserRepository extends EloquentRepository implements UserRepositoryInterface
{
    public function create(array $data): User
    {
        $model = new User();
        $model->fill($data);
        $model->email_verified_at = $data['email_verified_at'] ?? null;
        $model->save();

        return $model;
    }

    public function update(array $data, $id): User
    {
        /** @var User $model */
        $model = $this->find($id);
        $model->fill($data);
        $model->email_verified_at = $data['email_verified_at'] ?? $model->email_verified_at;
        $model->remember_token = $data['remember_token'] ?? $model->remember_token;
        $model->save();
        $model->refresh();

        return $model;
    }

//    public function attachResourceTypes(User $user, array $resourceTypeIds): void
//    {
//        $user->resourceTypes()->attach($resourceTypeIds);
//    }
//
//    public function detachResourceTypes(User $user, array $resourceTypeIds): int
//    {
//        return $user->resourceTypes()->detach($resourceTypeIds);
//    }

    protected function query(): Builder
    {
        return User::query();
    }
}
