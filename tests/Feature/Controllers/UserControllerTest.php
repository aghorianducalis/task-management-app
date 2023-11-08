<?php

declare(strict_types=1);

namespace Tests\Feature\Controllers;

use App\Enums\PermissionEnum;
use App\Enums\RoleEnum;
use App\Models\User;
use App\Services\UserService;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Arr;
use Tests\TestCase;

/**
 * @coversDefaultClass \App\Http\Controllers\UserController
 */
class UserControllerTest extends TestCase
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
    public function test_admin_can_view_users()
    {
        $users = User::factory(3)->create();
        $allUsers = $users->merge([$this->admin, $this->manager]);

        $response = $this->actingAs($this->admin)->get(route('users.index'));

        $response->assertOk();
        $response->assertJsonCount($allUsers->count(), 'data');
        $response->assertJsonStructure(['data' => [$this->getRequiredResponseFields()]]);
        $this->assertEquals($allUsers->sortBy('id')->pluck('id')->toArray(), $response->json('data.*.id'));
    }

    /**
     * @test
     * @covers ::index
     */
    public function test_manager_cannot_view_users()
    {
        $response = $this->actingAs($this->manager)->get(route('users.index'));

        $response->assertForbidden();
    }

    /**
     * @test
     * @covers ::store
     */
    public function test_admin_can_create_user()
    {
        $roleEnum = RoleEnum::Manager;
        $roleName = $roleEnum->value;
        $permissionNames = PermissionEnum::permissionsFromRoleEnum($roleEnum);
        $password = 'password';

        /** @var User $model */
        $model = User::factory()->make();
        $model->makeVisible($model->getAttributes());
        $data = $model->getAttributes();
        $data['email_verified_at'] = $data['email_verified_at']->toDateTimeString();
        $data['password'] = $password;
        $data['password_confirmation'] = $password;

        $response = $this->actingAs($this->admin)->postJson(route('users.store'), $data);

        $response->assertStatus(201);
        $response->assertJsonStructure(['data' => $this->getRequiredResponseFields()]);
        $response->assertJsonFragment(Arr::except($data, ['password', 'password_confirmation', 'remember_token']));

        $createdUserFromDB = UserService::getInstance()->getUserById($response->json('data.id'));

        $this->assertCount(1, $createdUserFromDB->roles);
        $this->assertEquals($roleName, $createdUserFromDB->roles->first()->name);
        $this->assertCount(sizeof($permissionNames), $createdUserFromDB->getPermissionsViaRoles());
        $this->assertEquals(
            collect($permissionNames)->pluck('value')->sort()->toArray(),
            $createdUserFromDB->getPermissionsViaRoles()->pluck('name')->sort()->toArray()
        );
    }

    /**
     * @test
     * @covers ::store
     */
    public function test_manager_cannot_create_user()
    {
        $password = 'password';
        /** @var User $model */
        $model = User::factory()->make();
        $model->makeVisible($model->getAttributes());
        $data = $model->getAttributes();
        $data['email_verified_at'] = $data['email_verified_at']->toDateTimeString();
        $data['password'] = $password;
        $data['password_confirmation'] = $password;

        $response = $this->actingAs($this->manager)->postJson(route('users.store'), $data);

        $response->assertForbidden();
    }

    /**
     * @test
     * @covers ::show
     */
    public function test_admin_can_view_user()
    {
        /** @var User $user */
        $user = User::factory()->create();

        $response = $this->actingAs($this->admin)->get(route('users.show', $user->id));

        $response->assertOk();
        $response->assertJsonStructure(['data' => $this->getRequiredResponseFields()]);
        $response->assertJsonFragment(['id' => $user->id]);
    }

    /**
     * @test
     * @covers ::show
     */
    public function test_manager_cannot_view_user()
    {
        /** @var User $user */
        $user = User::factory()->create();

        $response = $this->actingAs($this->manager)->get(route('users.show', $user->id));

        $response->assertForbidden();
    }

    /**
     * @test
     * @covers ::update
     */
    public function test_admin_can_update_user()
    {
        /** @var User $user */
        $user = User::factory()->create();
        $updatedData = User::factory()->make()->only([
            'name',
            'email',
            'password',
            'email_verified_at',
        ]);
        $updatedData['email_verified_at'] = $updatedData['email_verified_at']->toDateTimeString();

        $response = $this->actingAs($this->admin)->putJson(route('users.update', $user->id), $updatedData);

        $response->assertOk();
        $response->assertJsonStructure(['data' => $this->getRequiredResponseFields()]);
        $response->assertJsonFragment(Arr::except($updatedData, ['password']));
    }

    /**
     * @test
     * @covers ::update
     */
    public function test_manager_cannot_update_user()
    {
        /** @var User $user */
        $user = User::factory()->create();
        $updatedData = User::factory()->make()->only([
            'name',
            'email',
            'password',
            'email_verified_at',
        ]);
        $updatedData['email_verified_at'] = $updatedData['email_verified_at']->toDateTimeString();

        $response = $this->actingAs($this->manager)->get(route('users.update', $user->id), $updatedData);

        $response->assertForbidden();
    }

    /**
     * @test
     * @covers ::destroy
     */
    public function test_admin_can_destroy_user()
    {
        /** @var User $user */
        $user = User::factory()->create();

        $response = $this->actingAs($this->admin)->deleteJson(route('users.destroy', $user->id));

        $response->assertOk();
        $response->assertExactJson(['result' => true]);

        $this->assertDatabaseMissing('users', ['id' => $user->id]);
    }

    /**
     * @test
     * @covers ::destroy
     */
    public function test_manager_cannot_destroy_user()
    {
        /** @var User $user */
        $user = User::factory()->create();

        $response = $this->actingAs($this->manager)->deleteJson(route('users.destroy', $user->id));

        $response->assertForbidden();
    }

    private function getRequiredResponseFields(): array
    {
        return [
            'id',
            'name',
            'email',
            'email_verified_at',
            'created_at',
            'updated_at',
        ];
    }
}
