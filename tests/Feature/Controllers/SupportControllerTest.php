<?php

namespace Tests\Feature\Controllers;

use App\Mail\MailSupportAdmin;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class SupportControllerTest extends TestCase
{
    use RefreshDatabase;

    // -------------------------------------------------------------------------
    // store
    // -------------------------------------------------------------------------

    public function test_store_saves_support_and_sends_mail(): void
    {
        Mail::fake();
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('support.store'), [
            'message' => 'テストメッセージです',
            'user_id' => $user->id,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('flash_success');
        $this->assertDatabaseHas('supports', [
            'user_id' => $user->id,
            'message' => 'テストメッセージです',
        ]);
        Mail::assertSent(MailSupportAdmin::class);
    }

    public function test_store_ignores_spoofed_user_id(): void
    {
        Mail::fake();
        $author = User::factory()->create();
        $victim = User::factory()->create();

        $response = $this->actingAs($author)->post(route('support.store'), [
            'message' => 'なりすましテスト',
            'user_id' => $victim->id,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('supports', [
            'user_id' => $author->id,
            'message' => 'なりすましテスト',
        ]);
        $this->assertDatabaseMissing('supports', [
            'user_id' => $victim->id,
            'message' => 'なりすましテスト',
        ]);
    }

    public function test_store_redirects_guest_to_login(): void
    {
        $response = $this->post(route('support.store'), [
            'message' => 'テストメッセージです',
        ]);

        $response->assertRedirect(route('login.show.form'));
    }

    public function test_store_fails_when_message_is_empty(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('support.store'), [
            'message' => '',
            'user_id' => $user->id,
        ]);

        $response->assertSessionHasErrors('message');
    }

    public function test_store_fails_when_message_exceeds_400_chars(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('support.store'), [
            'message' => str_repeat('あ', 401),
            'user_id' => $user->id,
        ]);

        $response->assertSessionHasErrors('message');
    }
}
