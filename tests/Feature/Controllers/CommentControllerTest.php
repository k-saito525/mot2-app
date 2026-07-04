<?php

namespace Tests\Feature\Controllers;

use App\Mail\MailComment;
use App\Models\Comment;
use App\Models\Topic;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class CommentControllerTest extends TestCase
{
    use RefreshDatabase;

    // -------------------------------------------------------------------------
    // store
    // -------------------------------------------------------------------------

    public function test_store_creates_comment(): void
    {
        Mail::fake();

        $topicAuthor = User::factory()->create(['is_approved' => 1]);
        $commenter   = User::factory()->create(['is_approved' => 1]);
        $topic       = Topic::factory()->create(['user_id' => $topicAuthor->id]);

        $response = $this->actingAs($commenter)->post(route('comment.store'), [
            'topic_id' => $topic->id,
            'comment'  => 'テストコメント',
        ]);

        $response->assertRedirect(route('topic.show.detail', ['id' => $topic->id]));
        $this->assertDatabaseHas('comments', [
            'topic_id' => $topic->id,
            'user_id'  => $commenter->id,
            'comment'  => 'テストコメント',
        ]);
    }

    public function test_store_sends_mail_to_topic_author_when_commenter_is_not_author(): void
    {
        Mail::fake();

        $topicAuthor = User::factory()->create(['is_approved' => 1]);
        $commenter   = User::factory()->create(['is_approved' => 1]);
        $topic       = Topic::factory()->create(['user_id' => $topicAuthor->id]);

        $this->actingAs($commenter)->post(route('comment.store'), [
            'topic_id' => $topic->id,
            'comment'  => 'テストコメント',
        ]);

        Mail::assertSent(MailComment::class, function ($mail) use ($topicAuthor) {
            return $mail->hasTo($topicAuthor->email);
        });
    }

    public function test_store_does_not_send_mail_when_commenter_is_topic_author(): void
    {
        Mail::fake();

        $author = User::factory()->create(['is_approved' => 1]);
        $topic  = Topic::factory()->create(['user_id' => $author->id]);

        $this->actingAs($author)->post(route('comment.store'), [
            'topic_id' => $topic->id,
            'comment'  => '自分のトピックへのコメント',
        ]);

        Mail::assertNothingSent();
    }

    public function test_store_redirects_to_list_when_topic_not_found(): void
    {
        Mail::fake();

        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('comment.store'), [
            'topic_id' => 0,
            'comment'  => 'テストコメント',
        ]);

        $response->assertRedirect(route('topic.show.list'));
    }

    public function test_store_fails_validation_when_comment_is_empty(): void
    {
        $user  = User::factory()->create();
        $topic = Topic::factory()->create();

        $response = $this->actingAs($user)->post(route('comment.store'), [
            'topic_id' => $topic->id,
            'comment'  => '',
        ]);

        $response->assertSessionHasErrors('comment');
    }

    public function test_store_redirects_guest_to_login(): void
    {
        $response = $this->post(route('comment.store'), [
            'topic_id' => 1,
            'comment'  => 'テストコメント',
        ]);

        $response->assertRedirect(route('login'));
    }

    // -------------------------------------------------------------------------
    // update
    // -------------------------------------------------------------------------

    public function test_update_changes_comment_content(): void
    {
        $user    = User::factory()->create();
        $topic   = Topic::factory()->create();
        $comment = Comment::factory()->create([
            'user_id'  => $user->id,
            'topic_id' => $topic->id,
        ]);

        $response = $this->actingAs($user)->put(route('comment.update', $comment->id), [
            'comment' => '更新後のコメント',
        ]);

        $response->assertRedirect(route('topic.show.detail', ['id' => $topic->id]));
        $this->assertDatabaseHas('comments', [
            'id'      => $comment->id,
            'comment' => '更新後のコメント',
        ]);
    }

    public function test_update_returns_403_when_not_owner(): void
    {
        $owner   = User::factory()->create();
        $other   = User::factory()->create();
        $topic   = Topic::factory()->create();
        $comment = Comment::factory()->create([
            'user_id'  => $owner->id,
            'topic_id' => $topic->id,
        ]);

        $response = $this->actingAs($other)->put(route('comment.update', $comment->id), [
            'comment' => '書き換え試み',
        ]);

        $response->assertStatus(403);
    }

    public function test_update_returns_403_when_comment_not_found(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->put(route('comment.update', 0), [
            'comment' => '更新試み',
        ]);

        // CommentRequest::authorize() がコントローラーより先に動くため 403
        $response->assertStatus(403);
    }

    // -------------------------------------------------------------------------
    // validation
    // -------------------------------------------------------------------------

    public function test_store_passes_when_comment_is_exactly_200_chars(): void
    {
        Mail::fake();
        $user  = User::factory()->create();
        $topic = Topic::factory()->create();

        $response = $this->actingAs($user)->post(route('comment.store'), [
            'topic_id' => $topic->id,
            'comment'  => str_repeat('あ', 200),
        ]);

        $response->assertSessionMissingErrors('comment');
    }

    public function test_store_fails_when_comment_exceeds_200_chars(): void
    {
        $user  = User::factory()->create();
        $topic = Topic::factory()->create();

        $response = $this->actingAs($user)->post(route('comment.store'), [
            'topic_id' => $topic->id,
            'comment'  => str_repeat('あ', 201),
        ]);

        $response->assertSessionHasErrors('comment');
    }
}
