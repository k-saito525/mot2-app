<?php

namespace Tests\Feature\Models;

use App\Models\Comment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CommentModelTest extends TestCase
{
    use RefreshDatabase;

    // -------------------------------------------------------------------------
    // scopeWithAuthor
    // -------------------------------------------------------------------------

    public function test_with_author_scope_eager_loads_user(): void
    {
        $comment = Comment::factory()->create();

        $result = Comment::withAuthor()->find($comment->id);

        $this->assertTrue($result->relationLoaded('user'));
    }

    public function test_with_author_scope_does_not_narrow_results(): void
    {
        Comment::factory()->count(3)->create();

        $result = Comment::withAuthor()->get();

        $this->assertCount(3, $result);
    }
}
