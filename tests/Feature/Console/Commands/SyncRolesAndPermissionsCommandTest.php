<?php

namespace Tests\Feature\Console\Commands;

use App\Console\Commands\SyncRolesAndPermissionsCommand;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * @coversDefaultClass \App\Console\Commands\SyncRolesAndPermissionsCommand
 */
class SyncRolesAndPermissionsCommandTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @test
     * @covers ::handle
     */
    public function test_command(): void
    {
        $command = new SyncRolesAndPermissionsCommand();
        $name = $command->getName();
        $this->artisan($name)->assertExitCode(0);
    }
}
