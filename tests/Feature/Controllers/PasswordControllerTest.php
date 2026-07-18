<?php

namespace Tests\Feature\Controllers;

use App\Mail\MailPasswordResetMailCheck;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class PasswordControllerTest extends TestCase
{
    use RefreshDatabase;

    // -------------------------------------------------------------------------
    // showFormNew
    // -------------------------------------------------------------------------

    public function test_show_form_new_redirects_to_top_when_token_not_found(): void
    {
        $response = $this->get(route('password.new.show.form', ['token' => 'invalid_token']));

        $response->assertRedirect(route('top'));
    }

    public function test_show_form_new_returns_view_when_token_valid(): void
    {
        $user = User::factory()->create([
            'verify_token' => 'validtoken123',
            'password'     => null,
        ]);

        $response = $this->get(route('password.new.show.form', ['token' => $user->verify_token]));

        $response->assertOk();
        $response->assertViewHas('user');
    }

    public function test_show_form_new_redirects_to_login_when_fully_registered(): void
    {
        $user = User::factory()->create([
            'verify_token'    => 'validtoken123',
            'user_identifier' => 'testuser1',
        ]);

        $response = $this->get(route('password.new.show.form', ['token' => $user->verify_token]));

        $response->assertRedirect(route('login.show.form'));
    }

    // -------------------------------------------------------------------------
    // storeNew
    // -------------------------------------------------------------------------

    public function test_store_new_sets_password_and_redirects_to_identifier(): void
    {
        $user = User::factory()->create([
            'verify_token' => 'validtoken123',
            'password'     => null,
        ]);

        $response = $this->withSession(['user_data' => $user])->post(route('password.new.store'), [
            'password'              => 'NewPass1',
            'password_confirmation' => 'NewPass1',
        ]);

        $response->assertRedirect(route('identifier.show.form', ['token' => $user->verify_token]));
    }

    // -------------------------------------------------------------------------
    // resetSendMail
    // -------------------------------------------------------------------------

    public function test_reset_send_mail_sends_mail_when_email_matches_approved_user(): void
    {
        Mail::fake();
        $user = User::factory()->create([
            'email'       => 'user@example.com',
            'is_approved' => 1,
        ]);

        $response = $this->post(route('password.reset.send'), [
            'email' => 'user@example.com',
        ]);

        $response->assertRedirect(route('password.reset.show.send'));
        $user->refresh();
        $this->assertNotNull($user->reset_password_access_key);
        $this->assertNotNull($user->reset_password_expire_at);
        Mail::assertSent(MailPasswordResetMailCheck::class);
    }

    public function test_reset_send_mail_redirects_without_sending_when_email_not_found(): void
    {
        Mail::fake();

        $response = $this->post(route('password.reset.send'), [
            'email' => 'nobody@example.com',
        ]);

        $response->assertRedirect(route('password.reset.show.send'));
        Mail::assertNothingSent();
    }

    // -------------------------------------------------------------------------
    // showPasswordFormReset
    // -------------------------------------------------------------------------

    public function test_show_password_form_reset_returns_view_with_valid_signature(): void
    {
        $url = URL::temporarySignedRoute(
            'password.reset.show.form-password',
            Carbon::now()->addHours(24),
            ['reset_token' => 'sometoken123']
        );

        $response = $this->get($url);

        $response->assertOk();
        $this->assertSame('sometoken123', session('reset_token'));
    }

    public function test_show_password_form_reset_returns_403_when_signature_missing(): void
    {
        $response = $this->get(route('password.reset.show.form-password', ['reset_token' => 'sometoken123']));

        $response->assertStatus(403);
    }

    public function test_show_password_form_reset_returns_403_when_signature_expired(): void
    {
        $url = URL::temporarySignedRoute(
            'password.reset.show.form-password',
            Carbon::now()->subMinute(),
            ['reset_token' => 'sometoken123']
        );

        $response = $this->get($url);

        $response->assertStatus(403);
    }

    // -------------------------------------------------------------------------
    // storeReset
    // -------------------------------------------------------------------------

    public function test_store_reset_updates_password_when_token_valid(): void
    {
        $user = User::factory()->create([
            'reset_password_access_key' => 'resetkey123',
            'reset_password_expire_at'  => now()->addHours(24),
        ]);

        $response = $this->withSession(['reset_token' => 'resetkey123'])->post(route('password.reset.store'), [
            'password'              => 'NewPass1',
            'password_confirmation' => 'NewPass1',
        ]);

        $response->assertRedirect(route('password.reset.show.complete'));
    }

    public function test_store_reset_returns_404_when_token_not_found(): void
    {
        $response = $this->withSession(['reset_token' => 'invalid_key'])->post(route('password.reset.store'), [
            'password'              => 'NewPass1',
            'password_confirmation' => 'NewPass1',
        ]);

        $response->assertStatus(404);
    }

    public function test_store_reset_returns_403_when_token_expired(): void
    {
        $user = User::factory()->create([
            'reset_password_access_key' => 'resetkey123',
            'reset_password_expire_at'  => now()->subMinute(),
        ]);

        $response = $this->withSession(['reset_token' => 'resetkey123'])->post(route('password.reset.store'), [
            'password'              => 'NewPass1',
            'password_confirmation' => 'NewPass1',
        ]);

        $response->assertStatus(403);
    }

    public function test_store_reset_returns_403_when_token_has_no_expiry(): void
    {
        $user = User::factory()->create([
            'reset_password_access_key' => 'resetkey123',
            'reset_password_expire_at'  => null,
        ]);

        $response = $this->withSession(['reset_token' => 'resetkey123'])->post(route('password.reset.store'), [
            'password'              => 'NewPass1',
            'password_confirmation' => 'NewPass1',
        ]);

        $response->assertStatus(403);
    }

    public function test_store_reset_invalidates_token_after_success(): void
    {
        $user = User::factory()->create([
            'reset_password_access_key' => 'resetkey123',
            'reset_password_expire_at'  => now()->addHours(24),
        ]);

        $this->withSession(['reset_token' => 'resetkey123'])->post(route('password.reset.store'), [
            'password'              => 'NewPass1',
            'password_confirmation' => 'NewPass1',
        ]);

        $this->assertDatabaseHas('users', [
            'id'                        => $user->id,
            'reset_password_access_key' => null,
            'reset_password_expire_at'  => null,
        ]);
    }

    public function test_store_reset_rejects_replaying_same_token(): void
    {
        $user = User::factory()->create([
            'reset_password_access_key' => 'resetkey123',
            'reset_password_expire_at'  => now()->addHours(24),
        ]);

        // 1回目は成功する
        $this->withSession(['reset_token' => 'resetkey123'])->post(route('password.reset.store'), [
            'password'              => 'NewPass1',
            'password_confirmation' => 'NewPass1',
        ]);

        // 同じトークンでの再送信は失敗する(トークンが既に無効化されているため404)
        $response = $this->withSession(['reset_token' => 'resetkey123'])->post(route('password.reset.store'), [
            'password'              => 'AnotherPass1',
            'password_confirmation' => 'AnotherPass1',
        ]);

        $response->assertStatus(404);
    }
}
