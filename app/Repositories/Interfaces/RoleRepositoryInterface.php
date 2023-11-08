<?php

declare(strict_types=1);

namespace App\Repositories\Interfaces;

use Illuminate\Support\Collection;

interface RoleRepositoryInterface extends RepositoryInterface
{
    public function getRolesWithPermissions(): Collection;
}
