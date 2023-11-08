<?php

declare(strict_types=1);

namespace Tests\Feature\Services;

use App\Models\Test;
use App\Models\User;
use App\Repositories\Interfaces\TestRepositoryInterface;
use App\Services\TestService;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery\MockInterface;
use Tests\TestCase;

/**
 * @coversDefaultClass \App\Services\TestService
 */
class TestServiceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
    }

    /**
     * @test
     * @covers ::getAllTests
     */
    public function test_get_all_tests()
    {
        $tests = Test::factory(5)->create();

        /** @var TestRepositoryInterface $repositoryMock */
        $repositoryMock = $this->mock(TestRepositoryInterface::class, function (MockInterface $mock) use ($tests) {
            $mock->shouldReceive('matching')->once()->andReturn($tests);
        });
        $service = $this->makeService($repositoryMock);

        $tests = $service->getAllTests();

        $this->assertCount(5, $tests);
    }

    /**
     * @test
     * @covers ::getTestById
     */
    public function test_get_test_by_id()
    {
        /** @var Test $test */
        $test = Test::factory()->create();

        /** @var TestRepositoryInterface $repositoryMock */
        $repositoryMock = $this->mock(TestRepositoryInterface::class, function (MockInterface $mock) use ($test) {
            $mock->shouldReceive('find')->once()->with($test->id)->andReturn($test);
        });
        $service = $this->makeService($repositoryMock);

        $foundTest = $service->getTestById($test->id);

        $this->assertInstanceOf(Test::class, $foundTest);
        $this->assertEquals($test->location, $foundTest->location);
        $this->assertEquals($test->rate, $foundTest->rate);
    }

    /**
     * @test
     * @covers ::getTestsByManager
     */
    public function test_get_test_by_manager()
    {
        /** @var User $manager */
        $manager = User::factory()->create();
        $managerId = $manager->id;

        /** @var Test $test */
        $test = Test::factory()->create(['manager_id' => $manager->id]);

        /** @var TestRepositoryInterface $repositoryMock */
        $repositoryMock = $this->partialMock(TestRepositoryInterface::class, function (MockInterface $mock) use ($managerId, $test) {
            $mock->shouldReceive('findByManager')->once()->with($managerId)->andReturn(collect([$test]));
        });
        $service = $this->makeService($repositoryMock);

        $foundTests = $service->getTestsByManager($managerId);

        $this->assertCount(1, $foundTests);
        /** @var Test $foundTest */
        $foundTest = $foundTests->first();
        $this->assertEquals($test->id, $foundTest->id);
        $this->assertEquals($managerId, $foundTest->manager->id);
    }

    /**
     * @test
     * @covers ::isTestBelongsToManager
     */
    public function test_is_test_belongs_to_manager()
    {
        /** @var User $manager */
        $manager = User::factory()->create();
        /** @var User $anotherUser */
        $anotherUser = User::factory()->create();
        /** @var Test $test */
        $test = Test::factory()->create(['manager_id' => $manager->id]);

        $service = $this->makeService();

        $result = $service->isTestBelongsToManager($test->id, $manager->id);

        $this->assertTrue($result);

        $result = $service->isTestBelongsToManager($test->id, $anotherUser->id);

        $this->assertFalse($result);
    }

    /**
     * @test
     * @covers ::createTest
     */
    public function test_create()
    {
        /** @var Test $test */
        $test = Test::factory()->make();

        $createdTest = $this->makeService()->createTest($test->toArray());

        $this->assertInstanceOf(Test::class, $createdTest);
        $this->assertEquals($test->location, $createdTest->location);
        $this->assertEquals($test->rate, $createdTest->rate);
        $this->assertDatabaseHas($test->getTable(), [
            'id'       => $createdTest->id,
            'location' => $test->location,
            'rate'     => $test->rate,
        ]);
    }

    /**
     * @test
     * @covers ::updateTest
     */
    public function test_update()
    {
        /** @var Test $test */
        $test = Test::factory()->create();
        /** @var Test $newTest */
        $newTest = Test::factory()->make();

        $updatedTest = $this->makeService()->updateTest($newTest->toArray(), $test->id);

        $this->assertInstanceOf(Test::class, $updatedTest);
        $this->assertEquals($newTest->location, $updatedTest->location);
        $this->assertEquals($newTest->rate, $updatedTest->rate);
        $this->assertDatabaseHas($test->getTable(), [
            'id'       => $test->id,
            'location' => $updatedTest->location,
            'rate'     => $updatedTest->rate,
        ]);
    }

    /**
     * @test
     * @covers ::deleteTest
     */
    public function test_delete()
    {
        $test = Test::factory()->create();

        $result = $this->makeService()->deleteTest($test->id);

        $this->assertTrue($result);
        $this->assertDatabaseMissing($test->getTable(), [
            'id' => $test->id
        ]);
    }

    /**
     * @test
     * @covers ::getInstance
     */
    public function test_get_instance()
    {
        $service = TestService::getInstance();

        $this->assertInstanceOf(TestService::class, $service);

        $service2 = TestService::getInstance();

        $this->assertInstanceOf(TestService::class, $service2);
        $this->assertSame($service, $service2);
    }

    protected function makeService(TestRepositoryInterface $repositoryMock = null): TestService
    {
        if ($repositoryMock) {
            $this->app->bind(TestService::class, function (Application $app) use ($repositoryMock) {
                return new TestService($repositoryMock);
            });
        }

        return app()->make(TestService::class);
    }
}
