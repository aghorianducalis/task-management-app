<?php

declare(strict_types=1);

namespace Tests\Feature\Repositories;

use App\Enums\RoleEnum;
use App\Models\Test;
use App\Models\User;
use App\Repositories\Filters\FilterCriteria;
use App\Repositories\TestRepository;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * @coversDefaultClass \App\Repositories\TestRepository
 */
class TestRepositoryTest extends TestCase
{
    use RefreshDatabase;

    protected TestRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = app(TestRepository::class);
        $this->seed(RolePermissionSeeder::class);
    }

    /**
     * @test
     * @covers ::find
     */
    public function test_find()
    {
        /** @var Test $test */
        $test = Test::factory()->create();

        $foundTest = $this->repository->find($test->id);

        $this->assertInstanceOf(Test::class, $foundTest);
        $this->assertEquals($test->id, $foundTest->id);
        $this->assertEquals($test->manager_id, $foundTest->manager_id);
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
     * @covers ::findByManager
     */
    public function test_find_by_user()
    {
        /** @var User $manager */
        $manager = User::factory()->withRole(RoleEnum::Manager)->create();

        /** @var Test $test */
        $test = Test::factory()->create(['manager_id' => $manager->id]);

        $foundTests = $this->repository->findByManager($manager->id);
        $this->assertCount(1, $foundTests);
        /** @var Test $foundTest */
        $foundTest = $foundTests->first();
        $this->assertEquals($test->id, $foundTest->id);
        $this->assertEquals($manager->id, $foundTest->manager->id);
    }

    /**
     * @test
     * @covers ::matching
     */
    public function test_get_all()
    {
        Test::factory(5)->create();

        $tests = $this->repository->matching();

        $this->assertCount(5, $tests);
    }

    /**
     * @test
     * @covers ::matching
     */
    public function test_matching()
    {
        /** @var Collection<int,Test> $testsCreated */
        $testsCreated = Test::factory(5)->create();

        $criteria = new FilterCriteria();
//        $criteria->push(new Filter($testsCreated->random()->title));

        $testsObtained = $this->repository->matching($criteria);

        $this->assertCount($testsCreated->count(), $testsObtained);
    }

    /**
     * @test
     * @covers ::create
     */
    public function test_create()
    {
        /** @var Test $test */
        $test = Test::factory()->make();

        /** @var Test $createdTest */
        $createdTest = $this->repository->create($test->toArray());

        $this->assertInstanceOf(Test::class, $createdTest);
        $this->assertEquals($test->location, $createdTest->location);
        $this->assertEquals($test->manager_id, $createdTest->manager_id);
        $this->assertDatabaseHas($test->getTable(), [
            'id'         => $createdTest->id,
            'location'   => $test->location,
            'manager_id' => $test->manager_id,
        ]);
    }

//    /**
//     * @test
//     * @covers ::attachUsers
//     */
//    public function test_attach_users()
//    {
//        /** @var User $user */
//        $user = User::factory()->create();
//
//        /** @var Test $test */
//        $test = Test::factory()->create();
//
//        $this->repository->attachUsers($test, [$user->id]);
//
//        $this->assertCount(1, $user->tests);
//        $this->assertCount(1, $test->users);
//        $this->assertEquals($test->id, $user->tests()->first()->id);
//        $this->assertEquals($user->id, $test->users()->first()->id);
//        $this->assertDatabaseHas($test->users()->getTable(), [
//            'resource_type_id' => $test->id,
//            'user_id'          => $user->id,
//        ]);
//    }
//
//    /**
//     * @test
//     * @covers ::detachUsers
//     */
//    public function test_detach_users()
//    {
//        /** @var User $user */
//        $user = User::factory()->create();
//
//        /** @var Test $test */
//        $test = Test::factory()->withUsers([$user->id])->create();
//
//        $detachedUsersCount = $this->repository->detachUsers($test, [$user->id]);
//
//        $this->assertEquals(1, $detachedUsersCount);
//        $this->assertCount(0, $user->tests);
//        $this->assertCount(0, $test->users);
//        $this->assertDatabaseMissing($test->users()->getTable(), [
//            'resource_type_id' => $test->id,
//            'user_id'          => $user->id,
//        ]);
//    }

    /**
     * @test
     * @covers ::update
     */
    public function test_update_non_existing_resource()
    {
        $newData = Test::factory()->make()->toArray();

        $this->expectException(ModelNotFoundException::class);
        $this->repository->update($newData, self::NON_EXISTING_ID_INT);
    }

    /**
     * @test
     * @covers ::update
     */
    public function test_update()
    {
        /** @var User $manager */
        $manager = User::factory()->create();
        /** @var Test $test */
        $test = Test::factory()->create(['manager_id' => $manager->id]);
        /** @var Test $newTest */
        $newTest = Test::factory()->make();

        $updatedTest = $this->repository->update($newTest->toArray(), $test->id);

        $this->assertInstanceOf(Test::class, $updatedTest);
        $this->assertEquals($newTest->location, $updatedTest->location);
        $this->assertEquals($newTest->manager_id, $updatedTest->manager_id);
        $this->assertDatabaseHas($test->getTable(), [
            'id'         => $test->id,
            'location'   => $updatedTest->location,
            'manager_id' => $updatedTest->manager_id,
        ]);
    }

    /**
     * @test
     * @covers ::delete
     */
    public function test_delete()
    {
        /** @var Test $test */
        $test = Test::factory()->create();

        $result = $this->repository->delete($test->id);

        $this->assertTrue($result);
        $this->assertDatabaseMissing($test->getTable(), [
            'id' => $test->id
        ]);
    }
}
