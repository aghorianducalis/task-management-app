<?php

declare(strict_types=1);

namespace Tests\Feature\Repositories;

use App\Models\User;
use App\Repositories\UserRepository;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * @coversDefaultClass \App\Repositories\UserRepository
 */
class UserRepositoryTest extends TestCase
{
    use RefreshDatabase;

    protected UserRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = app(UserRepository::class);
        $this->seed(RolePermissionSeeder::class);
    }

    /**
     * @test
     * @covers ::find
     */
    public function test_find()
    {
        /** @var User $user */
        $user = User::factory()->create();

        $foundUser = $this->repository->find($user->id);

        $this->assertInstanceOf(User::class, $foundUser);
        $this->assertEquals($user->name, $foundUser->name);
        $this->assertEquals($user->email, $foundUser->email);
    }

    /**
     * @test
     * @covers ::find
     */
    public function test_find_non_existing_resource()
    {
        $this->expectException(ModelNotFoundException::class);
        $this->repository->find(self::NON_EXISTING_ID_INT);
    }

    /**
     * @test
     * @covers ::matching
     */
    public function test_get_all()
    {
        User::factory(5)->create();

        $users = $this->repository->matching();

        $this->assertCount(5, $users);
    }

    /**
     * @test
     * @covers ::create
     */
    public function test_create()
    {
        /** @var User $user */
        $user = User::factory()->make();
        $user->makeVisible($user->getAttributes());

        /** @var User $createdUser */
        $createdUser = $this->repository->create($user->getAttributes());

        $this->assertInstanceOf(User::class, $createdUser);
        $this->assertEquals($user->name, $createdUser->name);
        $this->assertEquals($user->email, $createdUser->email);
        $this->assertDatabaseHas($user->getTable(), [
            'name'  => $user->name,
            'email' => $user->email,
        ]);
    }

//    /**
//     * @test
//     * @covers ::attachResourceTypes
//     */
//    public function test_attach_resource_types()
//    {
//        /** @var User $user */
//        $user = User::factory()->create();
//
//        /** @var ResourceType $resourceType */
//        $resourceType = ResourceType::factory()->create();
//
//        $this->repository->attachResourceTypes($user, [$resourceType->id]);
//
//        $this->assertCount(1, $user->resourceTypes);
//        $this->assertCount(1, $resourceType->users);
//        $this->assertEquals($resourceType->id, $user->resourceTypes()->first()->id);
//        $this->assertEquals($user->id, $resourceType->users()->first()->id);
//        $this->assertDatabaseHas($user->resourceTypes()->getTable(), [
//            'user_id'          => $user->id,
//            'resource_type_id' => $resourceType->id,
//        ]);
//    }
//
//    /**
//     * @test
//     * @covers ::detachResourceTypes
//     */
//    public function test_detach_resource_types()
//    {
//        /** @var ResourceType $resourceType */
//        $resourceType = ResourceType::factory()->create();
//
//        /** @var User $user */
//        $user = User::factory()->withResourceTypes([$resourceType->id])->create();
//
//        $detachedResourceTypesCount = $this->repository->detachResourceTypes($user, [$resourceType->id]);
//
//        $this->assertEquals(1, $detachedResourceTypesCount);
//        $this->assertCount(0, $user->resourceTypes);
//        $this->assertCount(0, $resourceType->users);
//        $this->assertDatabaseMissing($user->resourceTypes()->getTable(), [
//            'user_id'          => $user->id,
//            'resource_type_id' => $resourceType->id,
//        ]);
//    }

    /**
     * @test
     * @covers ::update
     */
    public function test_update_non_existing_user()
    {
        $newData = User::factory()->make()->toArray();

        $this->expectException(ModelNotFoundException::class);
        $this->repository->update($newData, self::NON_EXISTING_ID_INT);
    }

    /**
     * @test
     * @covers ::update
     */
    public function test_update()
    {
        /** @var User $user */
        $user = User::factory()->create();
        /** @var User $newUser */
        $newUser = User::factory()->make();

        /** @var User $updatedUser */
        $updatedUser = $this->repository->update($newUser->toArray(), $user->id);

        $this->assertInstanceOf(User::class, $updatedUser);
        $this->assertEquals($newUser->name, $updatedUser->name);
        $this->assertEquals($newUser->email, $updatedUser->email);
        $this->assertDatabaseHas($user->getTable(), [
            'id'    => $user->id,
            'name'  => $newUser->name,
            'email' => $newUser->email,
        ]);
    }

    /**
     * @test
     * @covers ::delete
     */
    public function test_delete()
    {
        /** @var User $user */
        $user = User::factory()->create();

        $result = $this->repository->delete($user->id);

        $this->assertTrue($result);
        $this->assertDatabaseMissing($user->getTable(), [
            'id' => $user->id
        ]);
    }
}
