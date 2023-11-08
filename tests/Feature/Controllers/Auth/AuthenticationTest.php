<?php

namespace Tests\Feature\Controllers\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

/**
 * @coversDefaultClass \App\Http\Controllers\Auth\AuthenticatedSessionController
 */
class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @test
     * @covers ::store
     */
    public function test_users_can_authenticate_with_correct_credentials(): void
    {
        $password = 'test1234';

        /** @var User $user */
        $user = User::factory()->create(['password' => Hash::make($password)]);

        $response = $this->post(route('login'), [
            'email'    => $user->email,
            'password' => $password,
        ]);

        $this->assertAuthenticatedAs($user);
        $response->assertNoContent();
    }

    /**
     * @test
     * @covers ::store
     */
    public function test_users_can_not_authenticate_with_invalid_password(): void
    {
        /** @var User $user */
        $user = User::factory()->create(['password' => Hash::make('test1234')]);

        $response = $this->post(route('login'), [
            'email'    => $user->email,
            'password' => 'wrong-password',
        ]);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
        $response->assertJsonValidationErrors('email');
        $this->assertGuest();
    }

    /**
     * @test
     * @covers ::store
     */
    public function test_users_can_not_authenticate_with_invalid_email(): void
    {
        $password = 'test1234';

        User::factory()->create(['password' => Hash::make($password)]);

        $response = $this->post(route('login'), [
            'email'    => 'wrong@email.com',
            'password' => $password,
        ]);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
        $response->assertJsonValidationErrors('email');
        $this->assertGuest();
    }

    /**
     * @test
     * @covers ::store
     */
    public function user_cannot_attempt_to_login_more_than_five_times_in_one_minute(): void
    {
        /** @var User $user */
        $user = User::factory()->create();

        RateLimiter::clear($user->email. '|' .$this->app['request']->ip());

        foreach (range(1, 5) as $attempt) {
            $response = $this->postJson(route('login'), [
                'email'    => $user->email,
                'password' => 'invalid-password',
            ]);

            $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
            $response->assertJsonValidationErrors('email');
        }

        $response = $this->postJson(route('login'), [
            'email'    => $user->email,
            'password' => 'invalid-password',
        ]);

        $response->assertStatus(Response::HTTP_TOO_MANY_REQUESTS);
    }

    /**
     * @test
     * @covers ::destroy
     */
    public function test_users_can_logout(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('logout'));

        $this->assertGuest();
        $response->assertNoContent();
    }
}
