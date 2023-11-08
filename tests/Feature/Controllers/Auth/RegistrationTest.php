<?php

namespace Tests\Feature\Controllers\Auth;

use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Auth\Events\Registered;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

/**
 * @coversDefaultClass \App\Http\Controllers\Auth\RegisteredUserController
 */
class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @test
     * @covers ::store
     */
    public function test_new_users_can_register(): void
    {
        $this->seed(RolePermissionSeeder::class);

        Event::fake();

        $userData = $this->getUserData();
        $response = $this->post(route('register'), $userData);

        $response->assertNoContent();

        // todo use repository instead of query builder
        /** @var User $user */
        $user = User::query()->where('email', 'test@example.com')->firstOrFail();
        $this->assertAuthenticatedAs($user);

        Event::assertDispatched(Registered::class, function ($e) use ($user) {
            return $e->user->id === $user->id;
        });
    }

    public function test_user_cannot_register_without_name()
    {
        $this->seed(RolePermissionSeeder::class);

        $userData = $this->getUserData();
        $userData['name'] = '';
        $response = $this->post(route('register'), $userData);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
        $response->assertJsonValidationErrors('name');
        $this->assertGuest();
    }

    public function test_user_cannot_register_without_email()
    {
        $this->seed(RolePermissionSeeder::class);

        $userData = $this->getUserData();
        $userData['email'] = '';
        $response = $this->post(route('register'), $userData);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
        $response->assertJsonValidationErrors('email');
        $this->assertGuest();
    }

    public function test_user_cannot_register_with_invalid_email()
    {
        $this->seed(RolePermissionSeeder::class);

        $userData = $this->getUserData();
        $userData['email'] = 'invalid-email';
        $response = $this->post(route('register'), $userData);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
        $response->assertJsonValidationErrors('email');
        $this->assertGuest();
    }

    public function test_user_cannot_register_without_password()
    {
        $this->seed(RolePermissionSeeder::class);

        $userData = $this->getUserData();
        $userData['password'] = '';
        $response = $this->post(route('register'), $userData);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
        $response->assertJsonValidationErrors('password');
        $this->assertGuest();
    }

    public function test_user_cannot_register_without_password_confirmation()
    {
        $this->seed(RolePermissionSeeder::class);

        $userData = $this->getUserData();
        $userData['password_confirmation'] = '';
        $response = $this->post(route('register'), $userData);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
        $response->assertJsonValidationErrors('password');
        $this->assertGuest();
    }

    public function test_user_cannot_register_with_passwords_not_matching()
    {
        $this->seed(RolePermissionSeeder::class);

        $userData = $this->getUserData();
        $userData['password_confirmation'] = 'wrong';
        $response = $this->post(route('register'), $userData);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
        $response->assertJsonValidationErrors('password');
        $this->assertGuest();
    }

    private function getUserData(): array
    {
        return [
            'name'                  => 'Test User',
            'email'                 => 'test@example.com',
            'password'              => 'password',
            'password_confirmation' => 'password',
        ];
    }
}
