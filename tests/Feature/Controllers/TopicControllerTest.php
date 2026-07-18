<?php

namespace Tests\Feature\Controllers;

use App\Models\Comment;
use App\Models\Topic;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TopicControllerTest extends TestCase
{
    use RefreshDatabase;

    // -------------------------------------------------------------------------
    // store
    // -------------------------------------------------------------------------

    public function test_store_creates_topic_when_authenticated(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('topic.store'), [
            'topic_title'  => 'テストタイトル',
            'topic_detail' => 'テスト本文',
        ]);

        $response->assertRedirect(route('topic.show.list'));
        $this->assertDatabaseHas('topics', [
            'user_id' => $user->id,
            'title'   => 'テストタイトル',
            'content' => 'テスト本文',
        ]);
    }

    public function test_store_redirects_guest_to_login(): void
    {
        $response = $this->post(route('topic.store'), [
            'topic_title'  => 'テストタイトル',
            'topic_detail' => 'テスト本文',
        ]);

        $response->assertRedirect(route('login.show.form'));
    }

    public function test_store_fails_validation_when_title_is_missing(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('topic.store'), [
            'topic_detail' => 'テスト本文',
        ]);

        $response->assertSessionHasErrors('topic_title');
    }

    // -------------------------------------------------------------------------
    // update
    // -------------------------------------------------------------------------

    public function test_update_changes_topic_content(): void
    {
        $user  = User::factory()->create();
        $topic = Topic::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->put(route('topic.update', $topic->id), [
            'topic_title'  => $topic->title,
            'topic_detail' => '更新後の本文',
        ]);

        $response->assertRedirect(route('topic.show.list'));
        $this->assertDatabaseHas('topics', [
            'id'      => $topic->id,
            'content' => '更新後の本文',
        ]);
    }

    public function test_update_returns_403_when_not_owner(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $topic = Topic::factory()->create(['user_id' => $owner->id]);

        $response = $this->actingAs($other)->put(route('topic.update', $topic->id), [
            'topic_title'  => $topic->title,
            'topic_detail' => '書き換え試み',
        ]);

        $response->assertStatus(403);
    }

    // -------------------------------------------------------------------------
    // destroy
    // -------------------------------------------------------------------------

    public function test_destroy_soft_deletes_topic_and_comments(): void
    {
        $user    = User::factory()->create();
        $topic   = Topic::factory()->create(['user_id' => $user->id]);
        $comment = Comment::factory()->create(['topic_id' => $topic->id]);

        $response = $this->actingAs($user)->delete(route('topic.destroy', $topic->id));

        $response->assertRedirect(route('topic.show.list'));
        $this->assertSoftDeleted('topics', ['id' => $topic->id]);
        $this->assertSoftDeleted('comments', ['id' => $comment->id]);
    }

    public function test_destroy_returns_403_when_not_owner(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $topic = Topic::factory()->create(['user_id' => $owner->id]);

        $response = $this->actingAs($other)->delete(route('topic.destroy', $topic->id));

        $response->assertStatus(403);
        $this->assertDatabaseHas('topics', ['id' => $topic->id, 'deleted_at' => null]);
    }

    public function test_destroy_redirects_guest_to_login(): void
    {
        $topic = Topic::factory()->create();

        $response = $this->delete(route('topic.destroy', $topic->id));

        $response->assertRedirect(route('login.show.form'));
    }

    // -------------------------------------------------------------------------
    // showDetail
    // -------------------------------------------------------------------------

    public function test_show_detail_returns_404_when_topic_not_found(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('topic.show.detail', ['id' => 0]));

        $response->assertStatus(404);
    }

    // -------------------------------------------------------------------------
    // showEdit
    // -------------------------------------------------------------------------

    public function test_show_edit_returns_view_for_owner(): void
    {
        $user  = User::factory()->create();
        $topic = Topic::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->get(route('topic.show.edit', ['id' => $topic->id]));

        $response->assertOk();
        $response->assertViewHas('topic', fn ($t) => $t->id === $topic->id);
    }

    public function test_show_edit_redirects_when_not_owner(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $topic = Topic::factory()->create(['user_id' => $owner->id]);

        $response = $this->actingAs($other)->get(route('topic.show.edit', ['id' => $topic->id]));

        $response->assertRedirect();
    }

    public function test_show_edit_returns_404_when_topic_not_found(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('topic.show.edit', ['id' => 0]));

        $response->assertStatus(404);
    }

    // -------------------------------------------------------------------------
    // validation
    // -------------------------------------------------------------------------

    public function test_store_passes_when_title_is_exactly_50_chars(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('topic.store'), [
            'topic_title'  => str_repeat('あ', 50),
            'topic_detail' => 'テスト本文',
        ]);

        $response->assertRedirect(route('topic.show.list'));
    }

    public function test_store_fails_when_title_exceeds_50_chars(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('topic.store'), [
            'topic_title'  => str_repeat('あ', 51),
            'topic_detail' => 'テスト本文',
        ]);

        $response->assertSessionHasErrors('topic_title');
    }

    public function test_store_passes_when_detail_is_exactly_400_chars(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('topic.store'), [
            'topic_title'  => 'テストタイトル',
            'topic_detail' => str_repeat('あ', 400),
        ]);

        $response->assertRedirect(route('topic.show.list'));
    }

    public function test_store_fails_when_detail_exceeds_400_chars(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('topic.store'), [
            'topic_title'  => 'テストタイトル',
            'topic_detail' => str_repeat('あ', 401),
        ]);

        $response->assertSessionHasErrors('topic_detail');
    }

    public function test_update_passes_when_detail_is_exactly_400_chars(): void
    {
        $user  = User::factory()->create();
        $topic = Topic::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->put(route('topic.update', $topic->id), [
            'topic_title'  => $topic->title,
            'topic_detail' => str_repeat('あ', 400),
        ]);

        $response->assertRedirect(route('topic.show.list'));
    }

    public function test_update_fails_when_detail_exceeds_400_chars(): void
    {
        $user  = User::factory()->create();
        $topic = Topic::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->put(route('topic.update', $topic->id), [
            'topic_title'  => $topic->title,
            'topic_detail' => str_repeat('あ', 401),
        ]);

        $response->assertSessionHasErrors('topic_detail');
    }
}
