<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Repositories\Interfaces\RoleRepositoryInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Spatie\Permission\Models\Role;

class RoleRepository extends EloquentRepository implements RoleRepositoryInterface
{
    public function getRolesWithPermissions(): Collection
    {
        return $this->query()->with('permissions')->get();
    }

    protected function query(): Builder
    {
        return Role::query();
    }
}
