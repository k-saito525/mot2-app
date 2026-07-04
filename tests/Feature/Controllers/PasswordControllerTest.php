<?php

namespace Tests\Feature\Controllers;

use App\Mail\MailPasswordResetMailCheck;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
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
        $this->assertDatabaseHas('users', [
            'id'                         => $user->id,
            'reset_password_access_key'  => $user->fresh()->reset_password_access_key,
        ]);
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
    // storeReset
    // -------------------------------------------------------------------------

    public function test_store_reset_updates_password_when_token_valid(): void
    {
        $user = User::factory()->create([
            'reset_password_access_key' => 'resetkey123',
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
}
