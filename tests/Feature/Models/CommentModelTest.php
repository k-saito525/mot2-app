<?php

namespace Tests\Feature\Models;

use App\Models\Comment;
use App\Models\Topic;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CommentModelTest extends TestCase
{
    use RefreshDatabase;

    // -------------------------------------------------------------------------
    // getCommentsByTopicID
    // -------------------------------------------------------------------------

    public function test_get_comments_by_topic_id_returns_only_that_topics_comments(): void
    {
        $topic  = Topic::factory()->create();
        $other  = Topic::factory()->create();
        Comment::factory()->count(2)->create(['topic_id' => $topic->id]);
        Comment::factory()->create(['topic_id' => $other->id]);

        $result = (new Comment())->getCommentsByTopicID($topic->id);

        $this->assertCount(2, $result);
        $result->each(fn ($c) => $this->assertEquals($topic->id, $c->topic_id));
    }

    public function test_get_comments_by_topic_id_returns_oldest_first(): void
    {
        $topic = Topic::factory()->create();
        $new   = Comment::factory()->create(['topic_id' => $topic->id, 'created_at' => now()]);
        $old   = Comment::factory()->create(['topic_id' => $topic->id, 'created_at' => now()->subHour()]);

        $result = (new Comment())->getCommentsByTopicID($topic->id);

        $this->assertEquals($old->id, $result->first()->id);
        $this->assertEquals($new->id, $result->last()->id);
    }

    // -------------------------------------------------------------------------
    // deleteComments
    // -------------------------------------------------------------------------

    public function test_delete_comments_soft_deletes_and_returns_true(): void
    {
        $comment = Comment::factory()->create();

        $result = (new Comment())->deleteComments($comment->id);

        $this->assertTrue($result);
        $this->assertSoftDeleted('comments', ['id' => $comment->id]);
    }

    public function test_delete_comments_returns_true_when_not_found(): void
    {
        $result = (new Comment())->deleteComments(0);

        $this->assertTrue($result);
    }
}
