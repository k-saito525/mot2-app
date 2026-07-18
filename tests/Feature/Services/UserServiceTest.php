<?php

namespace Tests\Feature\Services;

use App\Mail\MailChangeEmail;
use App\Models\User;
use App\Services\UserService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class UserServiceTest extends TestCase
{
    use RefreshDatabase;

    private UserService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new UserService();
    }

    // -------------------------------------------------------------------------
    // approve
    // -------------------------------------------------------------------------

    public function test_approve_sets_is_approved_to_true(): void
    {
        $user = User::factory()->create(['is_approved' => false]);

        $this->service->approve($user->id);

        $this->assertDatabaseHas('users', ['id' => $user->id, 'is_approved' => 1]);
    }

    public function test_approve_throws_when_user_not_found(): void
    {
        $this->expectException(ModelNotFoundException::class);

        $this->service->approve(0);
    }

    // -------------------------------------------------------------------------
    // updateProfile
    // -------------------------------------------------------------------------

    public function test_update_profile_updates_name(): void
    {
        $user = User::factory()->create(['name' => '旧名前']);

        $this->service->updateProfile(['name' => '新名前'], $user);

        $this->assertDatabaseHas('users', ['id' => $user->id, 'name' => '新名前']);
    }

    public function test_update_profile_returns_error_when_identifier_is_duplicate(): void
    {
        User::factory()->create(['user_identifier' => 'taken_id']);
        $user = User::factory()->create(['user_identifier' => 'my_id']);

        $result = $this->service->updateProfile(['user_identifier' => 'taken_id'], $user);

        $this->assertNotEmpty($result);
        $this->assertDatabaseHas('users', ['id' => $user->id, 'user_identifier' => 'my_id']);
    }

    public function test_update_profile_sends_mail_when_email_changes(): void
    {
        Mail::fake();
        $user = User::factory()->create(['email' => 'old@example.com']);

        $this->service->updateProfile(['email' => 'new@example.com'], $user);

        Mail::assertSent(MailChangeEmail::class, function ($mail) {
            return $mail->hasTo('new@example.com');
        });
    }

    public function test_update_profile_stores_icon_image(): void
    {
        Storage::fake('public');
        $user = User::factory()->create(['user_icon' => null]);
        $file = UploadedFile::fake()->image('icon.jpg');

        $result = $this->service->updateProfile(['user_icon' => $file], $user);

        $this->assertSame('', $result);
        $this->assertNotNull($user->fresh()->user_icon);
        Storage::disk('public')->assertExists($user->user_icon);
    }

    public function test_update_profile_deletes_old_icon_when_replaced(): void
    {
        Storage::fake('public');
        $user = User::factory()->create(['user_icon' => null]);

        $this->service->updateProfile(['user_icon' => UploadedFile::fake()->image('old.jpg')], $user);
        $oldIconPath = $user->user_icon;
        Storage::disk('public')->assertExists($oldIconPath);

        $this->service->updateProfile(['user_icon' => UploadedFile::fake()->image('new.jpg')], $user);

        Storage::disk('public')->assertMissing($oldIconPath);
        $this->assertNotSame($oldIconPath, $user->user_icon);
    }
}
