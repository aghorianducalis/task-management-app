<?php

namespace App\Providers;

use App\Repositories\Interfaces\PermissionRepositoryInterface;
use App\Repositories\Interfaces\RoleRepositoryInterface;
use App\Repositories\Interfaces\TestRepositoryInterface;
use App\Repositories\Interfaces\UserRepositoryInterface;
use App\Repositories\PermissionRepository;
use App\Repositories\RoleRepository;
use App\Repositories\TestRepository;
use App\Repositories\UserRepository;
use App\Services\RolePermissionService;
use App\Services\TestService;
use App\Services\UserService;
use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * All of the container bindings that should be registered.
     *
     * @var array
     */
    public array $bindings = [
        PermissionRepositoryInterface::class => PermissionRepository::class,
        RoleRepositoryInterface::class       => RoleRepository::class,
        TestRepositoryInterface::class       => TestRepository::class,
        UserRepositoryInterface::class       => UserRepository::class,
    ];

    /**
     * All of the container singletons that should be registered.
     *
     * @var array
     */
    public array $singletons = [
        // repositories
        PermissionRepositoryInterface::class => PermissionRepository::class,
        RoleRepositoryInterface::class       => RoleRepository::class,
        TestRepositoryInterface::class       => TestRepository::class,
        UserRepositoryInterface::class       => UserRepository::class,

        // services
        RolePermissionService::class => RolePermissionService::class,
        TestService::class           => TestService::class,
        UserService::class           => UserService::class,
    ];

    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
