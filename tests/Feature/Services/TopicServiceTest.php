<?php

namespace Tests\Feature\Services;

use App\Models\Comment;
use App\Models\Topic;
use App\Models\User;
use App\Services\TopicService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TopicServiceTest extends TestCase
{
    use RefreshDatabase;

    private TopicService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new TopicService();
    }

    // -------------------------------------------------------------------------
    // create
    // -------------------------------------------------------------------------

    public function test_create_saves_topic(): void
    {
        $user = User::factory()->create();

        $result = $this->service->create($user->id, 'テストタイトル', 'テスト本文');

        $this->assertTrue($result);
        $this->assertDatabaseHas('topics', [
            'user_id' => $user->id,
            'title'   => 'テストタイトル',
            'content' => 'テスト本文',
        ]);
    }

    // -------------------------------------------------------------------------
    // updateContent
    // -------------------------------------------------------------------------

    public function test_update_content_updates_topic(): void
    {
        $topic = Topic::factory()->create(['content' => '元の本文']);

        $result = $this->service->updateContent($topic, '更新後の本文');

        $this->assertTrue($result);
        $this->assertDatabaseHas('topics', [
            'id'      => $topic->id,
            'content' => '更新後の本文',
        ]);
    }

    // -------------------------------------------------------------------------
    // delete
    // -------------------------------------------------------------------------

    public function test_delete_returns_false_when_topic_not_found(): void
    {
        $result = $this->service->delete(0);

        $this->assertFalse($result);
    }

    public function test_delete_soft_deletes_topic(): void
    {
        $topic = Topic::factory()->create();

        $result = $this->service->delete($topic->id);

        $this->assertTrue($result);
        $this->assertSoftDeleted('topics', ['id' => $topic->id]);
    }

    public function test_delete_soft_deletes_associated_comments(): void
    {
        $topic   = Topic::factory()->create();
        $comment = Comment::factory()->create(['topic_id' => $topic->id]);

        $this->service->delete($topic->id);

        $this->assertSoftDeleted('comments', ['id' => $comment->id]);
    }

    public function test_delete_succeeds_when_topic_has_no_comments(): void
    {
        $topic = Topic::factory()->create();

        $result = $this->service->delete($topic->id);

        $this->assertTrue($result);
        $this->assertSoftDeleted('topics', ['id' => $topic->id]);
    }
}
