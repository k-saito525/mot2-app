<?php

namespace Tests\Feature\Controllers;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class LoginControllerTest extends TestCase
{
    use RefreshDatabase;

    // -------------------------------------------------------------------------
    // login
    // -------------------------------------------------------------------------

    public function test_login_succeeds_with_valid_credentials(): void
    {
        $user = User::factory()->create([
            'email'       => 'test@example.com',
            'password'    => Hash::make('Password1'),
            'is_approved' => 1,
        ]);

        $response = $this->post(route('login.store'), [
            'email'    => 'test@example.com',
            'password' => 'Password1',
        ]);

        $response->assertRedirect(route('home.index'));
        $this->assertAuthenticatedAs($user);
    }

    public function test_login_fails_when_user_is_not_approved(): void
    {
        User::factory()->create([
            'email'       => 'test@example.com',
            'password'    => Hash::make('Password1'),
            'is_approved' => 0,
        ]);

        $response = $this->post(route('login.store'), [
            'email'    => 'test@example.com',
            'password' => 'Password1',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('flash_failed');
        $this->assertGuest();
    }

    public function test_login_fails_with_wrong_password(): void
    {
        User::factory()->create([
            'email'       => 'test@example.com',
            'password'    => Hash::make('Password1'),
            'is_approved' => 1,
        ]);

        $response = $this->post(route('login.store'), [
            'email'    => 'test@example.com',
            'password' => 'WrongPass1',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('flash_failed');
        $this->assertGuest();
    }

    public function test_login_fails_validation_when_password_has_no_numbers(): void
    {
        $response = $this->post(route('login.store'), [
            'email'    => 'test@example.com',
            'password' => 'onlyletters',
        ]);

        $response->assertSessionHasErrors('password');
    }

    public function test_login_is_locked_out_after_five_failed_attempts(): void
    {
        User::factory()->create([
            'email'       => 'test@example.com',
            'password'    => Hash::make('Password1'),
            'is_approved' => 1,
        ]);

        for ($i = 0; $i < 5; $i++) {
            $this->post(route('login.store'), [
                'email'    => 'test@example.com',
                'password' => 'WrongPass1',
            ]);
        }

        // 6回目は正しいパスワードでもロックにより弾かれる
        $response = $this->post(route('login.store'), [
            'email'    => 'test@example.com',
            'password' => 'Password1',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('flash_failed');
        $this->assertGuest();
    }

    public function test_login_lockout_is_scoped_per_email(): void
    {
        User::factory()->create([
            'email'       => 'locked@example.com',
            'password'    => Hash::make('Password1'),
            'is_approved' => 1,
        ]);
        $other = User::factory()->create([
            'email'       => 'other@example.com',
            'password'    => Hash::make('Password1'),
            'is_approved' => 1,
        ]);

        for ($i = 0; $i < 5; $i++) {
            $this->post(route('login.store'), [
                'email'    => 'locked@example.com',
                'password' => 'WrongPass1',
            ]);
        }

        // 別のメールアドレスは同じIPからでもロックされない
        $response = $this->post(route('login.store'), [
            'email'    => 'other@example.com',
            'password' => 'Password1',
        ]);

        $response->assertRedirect(route('home.index'));
        $this->assertAuthenticatedAs($other);
    }

    // -------------------------------------------------------------------------
    // logout
    // -------------------------------------------------------------------------

    public function test_logout_clears_auth_and_returns_view(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('logout'));

        $response->assertOk();
        $this->assertGuest();
    }
}
