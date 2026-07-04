<?php

namespace Tests\Feature\Controllers;

use App\Mail\MailApplyAdmin;
use App\Mail\MailApplyUser;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class ApplyControllerTest extends TestCase
{
    use RefreshDatabase;

    // -------------------------------------------------------------------------
    // check
    // -------------------------------------------------------------------------

    public function test_check_redirects_to_confirm_with_valid_data(): void
    {
        $response = $this->post(route('apply.check'), [
            'name'  => 'テストユーザー',
            'email' => 'apply@example.com',
        ]);

        $response->assertRedirect(route('apply.show.confirm'));
        $response->assertSessionHas('form_input');
    }

    public function test_check_redirects_back_when_email_is_duplicate(): void
    {
        User::factory()->create(['email' => 'taken@example.com']);

        $response = $this->post(route('apply.check'), [
            'name'  => 'テストユーザー',
            'email' => 'taken@example.com',
        ]);

        $response->assertRedirect(route('apply.form'));
        $response->assertSessionHas('flash_failed');
    }

    public function test_check_fails_validation_when_name_is_missing(): void
    {
        $response = $this->post(route('apply.check'), [
            'email' => 'apply@example.com',
        ]);

        $response->assertSessionHasErrors('name');
    }

    // -------------------------------------------------------------------------
    // store
    // -------------------------------------------------------------------------

    public function test_store_creates_user_and_sends_mail(): void
    {
        Mail::fake();

        // check でセッションにデータをセット
        $this->post(route('apply.check'), [
            'name'  => 'テストユーザー',
            'email' => 'apply@example.com',
        ]);

        // store でセッションからデータを読んで登録
        $response = $this->post(route('apply.store'));

        $response->assertRedirect(route('apply.show.complete'));
        $this->assertDatabaseHas('users', ['email' => 'apply@example.com']);
        Mail::assertSent(MailApplyUser::class);
        Mail::assertSent(MailApplyAdmin::class);
    }

    public function test_store_returns_404_when_session_is_empty(): void
    {
        $response = $this->post(route('apply.store'));

        $response->assertStatus(404);
    }
}
