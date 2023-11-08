<?php

namespace Tests\Feature\Controllers\Auth;

use App\Models\User;
use App\Providers\RouteServiceProvider;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

/**
 * @coversDefaultClass \App\Http\Controllers\Auth\EmailVerificationNotificationController
 */
class EmailVerificationNotificationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @test
     * @covers ::store
     */
    public function test_send_email_verification_notification(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => null,
        ]);

        Notification::fake();

        $response = $this->actingAs($user)->postJson(route('verification.send'));

        $response->assertJson(['status' => 'verification-link-sent']);
        $response->assertStatus(Response::HTTP_OK);

        Notification::assertSentTo($user, VerifyEmail::class);
    }

    /**
     * @test
     * @covers ::store
     */
    public function test_redirect_if_email_verified(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);

        $response = $this->actingAs($user)->postJson(route('verification.send'));

        $response->assertRedirect(RouteServiceProvider::HOME);
    }
}
