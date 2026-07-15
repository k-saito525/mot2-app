<?php

namespace Tests\Feature\Models;

use App\Models\Support;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SupportModelTest extends TestCase
{
    use RefreshDatabase;

    // -------------------------------------------------------------------------
    // scopeWithAuthor
    // -------------------------------------------------------------------------

    public function test_with_author_scope_eager_loads_user(): void
    {
        $support = Support::create([
            'message' => 'テストメッセージです',
            'user_id' => User::factory()->create()->id,
        ]);

        $result = Support::withAuthor()->find($support->id);

        $this->assertTrue($result->relationLoaded('user'));
    }

    public function test_with_author_scope_does_not_narrow_results(): void
    {
        $userId = User::factory()->create()->id;
        Support::create(['message' => 'メッセージ1', 'user_id' => $userId]);
        Support::create(['message' => 'メッセージ2', 'user_id' => $userId]);
        Support::create(['message' => 'メッセージ3', 'user_id' => $userId]);

        $result = Support::withAuthor()->get();

        $this->assertCount(3, $result);
    }
}
