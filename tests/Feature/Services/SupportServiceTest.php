<?php

namespace Tests\Feature\Services;

use App\Mail\MailSupportAdmin;
use App\Models\User;
use App\Services\SupportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class SupportServiceTest extends TestCase
{
    use RefreshDatabase;

    private SupportService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new SupportService();
    }

    // -------------------------------------------------------------------------
    // create
    // -------------------------------------------------------------------------

    public function test_create_saves_support_and_sends_mail(): void
    {
        Mail::fake();
        $user = User::factory()->create();

        $result = $this->service->create([
            'message' => 'テストメッセージです',
            'user_id' => $user->id,
        ]);

        $this->assertTrue($result);
        $this->assertDatabaseHas('supports', [
            'user_id' => $user->id,
            'message' => 'テストメッセージです',
        ]);
        Mail::assertSent(MailSupportAdmin::class);
    }
}
