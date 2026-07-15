<?php

namespace Tests\Feature\Services;

use App\Mail\MailComment;
use App\Models\Comment;
use App\Models\Topic;
use App\Models\User;
use App\Services\CommentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class CommentServiceTest extends TestCase
{
    use RefreshDatabase;

    private CommentService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new CommentService();
    }

    // -------------------------------------------------------------------------
    // create
    // -------------------------------------------------------------------------

    public function test_create_saves_comment(): void
    {
        Mail::fake();
        $author = User::factory()->create(['is_approved' => 1]);
        $topic  = Topic::factory()->create(['user_id' => $author->id]);

        $result = $this->service->create($topic, $author, 'テストコメント');

        $this->assertTrue($result);
        $this->assertDatabaseHas('comments', [
            'topic_id' => $topic->id,
            'user_id'  => $author->id,
            'comment'  => 'テストコメント',
        ]);
    }

    public function test_create_sends_mail_to_topic_author_when_commenter_is_not_author(): void
    {
        Mail::fake();
        $topicAuthor = User::factory()->create(['is_approved' => 1]);
        $commenter   = User::factory()->create(['is_approved' => 1]);
        $topic       = Topic::factory()->create(['user_id' => $topicAuthor->id]);

        $this->service->create($topic, $commenter, 'テストコメント');

        Mail::assertSent(MailComment::class, function ($mail) use ($topicAuthor) {
            return $mail->hasTo($topicAuthor->email);
        });
    }

    public function test_create_does_not_send_mail_when_commenter_is_topic_author(): void
    {
        Mail::fake();
        $author = User::factory()->create(['is_approved' => 1]);
        $topic  = Topic::factory()->create(['user_id' => $author->id]);

        $this->service->create($topic, $author, '自分のトピックへのコメント');

        Mail::assertNothingSent();
    }

    // -------------------------------------------------------------------------
    // updateComment
    // -------------------------------------------------------------------------

    public function test_update_comment_updates_content(): void
    {
        $comment = Comment::factory()->create(['comment' => '元のコメント']);

        $result = $this->service->updateComment($comment, '更新後のコメント');

        $this->assertTrue($result);
        $this->assertDatabaseHas('comments', [
            'id'      => $comment->id,
            'comment' => '更新後のコメント',
        ]);
    }

    // -------------------------------------------------------------------------
    // delete
    // -------------------------------------------------------------------------

    public function test_delete_soft_deletes_and_returns_true(): void
    {
        $comment = Comment::factory()->create();

        $result = $this->service->delete($comment->id);

        $this->assertTrue($result);
        $this->assertSoftDeleted('comments', ['id' => $comment->id]);
    }

    public function test_delete_returns_true_when_not_found(): void
    {
        $result = $this->service->delete(0);

        $this->assertTrue($result);
    }
}
