<?php

namespace Tests\Feature\Services;

use App\Models\Comment;
use App\Services\CommentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
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
