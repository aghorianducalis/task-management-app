<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Services\RolePermissionService;
use Illuminate\Database\Seeder;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $service = RolePermissionService::getInstance();
        $service->syncRolesAndPermissions();
    }
}
