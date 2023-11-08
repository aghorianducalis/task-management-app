<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\RolePermissionService;
use Illuminate\Console\Command;
use Illuminate\Contracts\Console\Isolatable;

class SyncRolesAndPermissionsCommand extends Command implements Isolatable
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'roles-permissions:sync';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync role and permission names between PHP code and database.';

    /**
     * Execute the console command.
     */
    public function handle(RolePermissionService $service): int
    {
        $service->syncRolesAndPermissions();
        $this->info('Roles and permissions successfully synced.');
        $this->call('cache:clear');

        return self::SUCCESS;
    }
}
