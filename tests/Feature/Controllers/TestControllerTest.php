<?php

declare(strict_types=1);

namespace Tests\Feature\Controllers;

use App\Enums\RoleEnum;
use App\Models\Test;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Arr;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

/**
 * @coversDefaultClass \App\Http\Controllers\TestController
 */
class TestControllerTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    private User $admin;

    private User $manager;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);

        $this->admin = User::factory()->withRole(RoleEnum::Admin)->create();
        $this->manager = User::factory()->withRole(RoleEnum::Manager)->create();
    }

    /**
     * @test
     * @covers ::index
     */
    public function test_admin_can_get_all_tests()
    {
        $tests = Test::factory(3)->create();

        $response = $this->actingAs($this->admin)->get(route('tests.index'));

        $response->assertOk();
        $response->assertJsonCount($tests->count(), 'data');
        $response->assertJsonStructure(['data' => [$this->getRequiredResponseFields()]]);
        $this->assertEquals($tests->pluck('id')->toArray(), $response->json('data.*.id'));
    }

    /**
     * @test
     * @covers ::index
     */
    public function test_manager_can_get_related_tests()
    {
        $tests = Test::factory(3)->create(['manager_id' => $this->manager->id]);

        $response = $this->actingAs($this->manager)->get(route('tests.index'));

        $response->assertOk();
        $response->assertJsonCount($tests->count(), 'data');
        $response->assertJsonStructure(['data' => [$this->getRequiredResponseFields()]]);
        $this->assertEquals($tests->pluck('id')->toArray(), $response->json('data.*.id'));
    }

    /**
     * @test
     * @covers ::index
     */
    public function test_manager_cannot_get_forbidden_tests()
    {
        Test::factory(3)->create();

        $response = $this->actingAs($this->manager)->get(route('tests.index'));

        $response->assertOk();
        $response->assertJsonCount(0, 'data');
    }

    /**
     * @test
     * @covers ::store
     */
    public function test_admin_can_create_test()
    {
        $testData = Test::factory()->make()->toArray();

        $response = $this->actingAs($this->admin)->postJson(route('tests.store'), $testData);

        $response->assertStatus(Response::HTTP_CREATED);
        $response->assertJsonStructure(['data' => $this->getRequiredResponseFields()]);
        $response->assertJsonFragment($testData);
        $this->assertDatabaseHas((new Test())->getTable(), ['id' => $response->json('data.id')]);
    }

    /**
     * @test
     * @covers ::store
     */
    public function test_manager_cannot_create_test()
    {
        $testData = Test::factory()->make()->toArray();

        $response = $this->actingAs($this->manager)->postJson(route('tests.store'), $testData);

        $response->assertForbidden();
    }

    /**
     * @test
     * @covers ::show
     */
    public function test_manager_can_get_related_test()
    {
        /** @var Test $test */
        $test = Test::factory()->create(['manager_id' => $this->manager->id]);

        $response = $this->actingAs($this->manager)->get(route('tests.show', $test->id));

        $response->assertOk();
        $response->assertJsonStructure(['data' => $this->getRequiredResponseFields()]);
        $response->assertJsonFragment(['id' => $test->id]);
    }

    /**
     * @test
     * @covers ::show
     */
    public function test_manager_cannot_get_forbidden_test()
    {
        /** @var Test $test */
        $test = Test::factory()->create();

        $response = $this->actingAs($this->manager)->get(route('tests.show', $test->id));
        $response->assertForbidden();
    }

    /**
     * @test
     * @covers ::update
     */
    public function test_admin_can_update_test()
    {
        /** @var Test $test */
        $test = Test::factory()->create();
        $updatedData = Test::factory()->make()->toArray();

        $response = $this->actingAs($this->admin)->putJson(route('tests.update', $test->id), $updatedData);

        $response->assertOk();
        $response->assertJsonStructure(['data' => $this->getRequiredResponseFields()]);
        $response->assertJsonFragment($updatedData);
    }

    /**
     * @test
     * @covers ::update
     */
    public function test_manager_can_update_related_test()
    {
        /** @var Test $test */
        $test = Test::factory()->create(['manager_id' => $this->manager->id]);
        $updatedData = Test::factory()->make()->only([
            'rate',
        ]);

        $response = $this->actingAs($this->manager)->putJson(route('tests.update', $test->id), $updatedData);

        $response->assertOk();
        $response->assertJsonStructure(['data' => $this->getRequiredResponseFields()]);
        // manager can update only the 'rate' field and related 'criteria'
        // other fields are immutable
        $response->assertJsonFragment(array_merge(
            Arr::except($test->only($test->getFillable()), ['criteria']),
            $updatedData
        ));
    }

    /**
     * @test
     * @covers ::update
     */
    public function test_manager_cannot_update_forbidden_test()
    {
        /** @var Test $test */
        $test = Test::factory()->create();
        $updatedData = Test::factory()->make()->toArray();

        $response = $this->actingAs($this->manager)->putJson(route('tests.update', $test->id), $updatedData);
        $response->assertForbidden();
    }

    /**
     * @test
     * @covers ::destroy
     */
    public function test_admin_can_destroy_test()
    {
        /** @var Test $test */
        $test = Test::factory()->create();

        $response = $this->actingAs($this->admin)->deleteJson(route('tests.destroy', $test->id));

        $response->assertOk();
        $response->assertExactJson(['result' => true]);

        $this->assertDatabaseMissing($test->getTable(), ['id' => $test->id]);
    }

    /**
     * @test
     * @covers ::destroy
     */
    public function test_manager_cannot_destroy_test()
    {
        /** @var Test $test */
        $test = Test::factory()->create(['manager_id' => $this->manager->id]);

        $response = $this->actingAs($this->manager)->deleteJson(route('tests.destroy', $test->id));
        $response->assertForbidden();
    }

    private function getRequiredResponseFields(): array
    {
        return [
            'id',
            'firstname',
            'middlename',
            'lastname',
            'location',
            'rate',
            'criteria',
            'manager_id',
            'created_at',
            'updated_at',
        ];
    }
}
