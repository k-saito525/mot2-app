<?php

namespace Tests\Feature\Models;

use App\Models\Topic;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TopicModelTest extends TestCase
{
    use RefreshDatabase;

    // -------------------------------------------------------------------------
    // scopeWithAuthor
    // -------------------------------------------------------------------------

    public function test_with_author_scope_eager_loads_user(): void
    {
        $topic = Topic::factory()->create();

        $result = Topic::withAuthor()->find($topic->id);

        $this->assertTrue($result->relationLoaded('user'));
    }

    public function test_with_author_scope_does_not_narrow_results(): void
    {
        Topic::factory()->count(3)->create();

        $result = Topic::withAuthor()->get();

        $this->assertCount(3, $result);
    }
}
