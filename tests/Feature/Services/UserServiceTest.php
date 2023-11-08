<?php

declare(strict_types=1);

namespace Tests\Feature\Services;

use App\Enums\PermissionEnum;
use App\Enums\RoleEnum;
use App\Models\User;
use App\Services\UserService;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * @coversDefaultClass \App\Services\UserService
 */
class UserServiceTest extends TestCase
{
    use RefreshDatabase;

    protected UserService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(UserService::class);
        $this->seed(RolePermissionSeeder::class);
    }

    /**
     * @test
     * @covers ::getUserById
     */
    public function test_get_user_by_id()
    {
        /** @var User $user */
        $user = User::factory()->create();

        $foundUser = $this->service->getUserById($user->id);

        $this->assertInstanceOf(User::class, $foundUser);
        $this->assertEquals($user->name, $foundUser->name);
        $this->assertEquals($user->email, $foundUser->email);
    }

    /**
     * @test
     * @covers ::getAllUsers
     */
    public function test_get_all_users()
    {
        User::factory(5)->create();

        $users = $this->service->getAllUsers();

        $this->assertCount(5, $users);
    }

    /**
     * @test
     * @covers ::createUser
     */
    public function test_create()
    {
        /** @var User $user */
        $user = User::factory()->make();
        $user->makeVisible($user->getAttributes());

        $createdUser = $this->service->createUser($user->getAttributes());

        $this->assertInstanceOf(User::class, $createdUser);
        $this->assertEquals($user->name, $createdUser->name);
        $this->assertEquals($user->email, $createdUser->email);
        $this->assertDatabaseHas($createdUser->getTable(), [
            'name'  => $user->name,
            'email' => $user->email,
        ]);
    }

    /**
     * @test
     * @covers ::createUser
     * @dataProvider roleEnums
     */
    public function test_create_with_role(RoleEnum $roleEnum)
    {
        $permissionNames = PermissionEnum::permissionsFromRoleEnum($roleEnum);

        /** @var User $user */
        $user = User::factory()->make();
        $user->makeVisible($user->getAttributes());
        $data = array_merge($user->getAttributes(), ['role' => $roleEnum]);

        $createdUser = $this->service->createUser($data);

        $createdUserFromDB = $this->service->getUserById($createdUser->id);

        $this->assertCount(1, $createdUserFromDB->roles);
        $this->assertEquals($roleEnum->value, $createdUserFromDB->roles->first()->name);
        $this->assertCount(sizeof($permissionNames), $createdUserFromDB->getPermissionsViaRoles());
        $this->assertEquals(
            collect($permissionNames)->pluck('value')->sort()->toArray(),
            $createdUserFromDB->getPermissionsViaRoles()->pluck('name')->sort()->toArray()
        );
    }

    /**
     * @test
     * @covers ::updateUser
     */
    public function test_update()
    {
        /** @var User $user */
        $user = User::factory()->create();
        /** @var User $newUser */
        $newUser = User::factory()->make();
        $newUser->makeVisible($newUser->getAttributes());
        $newData = $newUser->getAttributes();
        $newData['email_verified_at'] = $newData['email_verified_at']->toDateTimeString();

        $updatedUser = $this->service->updateUser($newData, $user->id);

        $this->assertInstanceOf(User::class, $updatedUser);
        $this->assertEquals($newUser->name, $updatedUser->name);
        $this->assertEquals($newUser->email, $updatedUser->email);
        $this->assertEquals($newData['email_verified_at'], $updatedUser->email_verified_at);
        $this->assertDatabaseHas($user->getTable(), [
            'id'    => $user->id,
            'name'  => $newUser->name,
            'email' => $newUser->email,
        ]);
    }

    /**
     * @test
     * @covers ::deleteUser
     */
    public function test_delete()
    {
        /** @var User $user */
        $user = User::factory()->create();

        $result = $this->service->deleteUser($user->id);

        $this->assertTrue($result);
        $this->assertDatabaseMissing($user->getTable(), [
            'id' => $user->id
        ]);
    }

    /**
     * @test
     * @covers ::getInstance
     */
    public function test_get_instance()
    {
        $service = UserService::getInstance();

        $this->assertInstanceOf(UserService::class, $service);

        $service2 = UserService::getInstance();

        $this->assertInstanceOf(UserService::class, $service2);
        $this->assertSame($service, $service2);
    }

    public static function roleEnums(): array
    {
        return [
            'admin role' => [
                RoleEnum::Admin,
            ],
            'manager role' => [
                'user role' => RoleEnum::Manager,
            ],
        ];
    }
}
