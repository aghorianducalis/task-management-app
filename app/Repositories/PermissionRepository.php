<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Repositories\Interfaces\PermissionRepositoryInterface;
use Illuminate\Database\Eloquent\Builder;
use Spatie\Permission\Models\Permission;

class PermissionRepository extends EloquentRepository implements PermissionRepositoryInterface
{
    protected function query(): Builder
    {
        return Permission::query();
    }
}
