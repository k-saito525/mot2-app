<?php

namespace Tests\Feature\Models;

use App\Models\Topic;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Pagination\LengthAwarePaginator;
use Tests\TestCase;

class TopicModelTest extends TestCase
{
    use RefreshDatabase;

    // -------------------------------------------------------------------------
    // getTopics
    // -------------------------------------------------------------------------

    public function test_get_topics_returns_all_topics_when_no_limit(): void
    {
        Topic::factory()->count(3)->create();

        $result = new Topic()->getTopics();

        $this->assertCount(3, $result);
    }

    public function test_get_topics_respects_limit(): void
    {
        Topic::factory()->count(5)->create();

        $result = new Topic()->getTopics(2);

        $this->assertCount(2, $result);
    }

    public function test_get_topics_returns_most_recent_first(): void
    {
        $old   = Topic::factory()->create(['created_at' => now()->subDays(2)]);
        $newer = Topic::factory()->create(['created_at' => now()->subDay()]);
        $newest = Topic::factory()->create(['created_at' => now()]);

        $result = new Topic()->getTopics();

        $this->assertEquals($newest->id, $result->first()->id);
        $this->assertEquals($old->id, $result->last()->id);
    }

    public function test_get_topics_does_not_return_soft_deleted(): void
    {
        $active  = Topic::factory()->create();
        $deleted = Topic::factory()->create();
        $deleted->delete();

        $result = new Topic()->getTopics();

        $this->assertCount(1, $result);
        $this->assertEquals($active->id, $result->first()->id);
    }

    // -------------------------------------------------------------------------
    // getTopicByUser
    // -------------------------------------------------------------------------

    public function test_get_topic_by_user_returns_only_that_users_topics(): void
    {
        $user  = User::factory()->create();
        $other = User::factory()->create();
        Topic::factory()->count(2)->create(['user_id' => $user->id]);
        Topic::factory()->create(['user_id' => $other->id]);

        $result = new Topic()->getTopicByUser($user->id);

        $this->assertCount(2, $result);
        $result->each(fn ($t) => $this->assertEquals($user->id, $t->user_id));
    }

    public function test_get_topic_by_user_returns_empty_for_unknown_user(): void
    {
        $result = new Topic()->getTopicByUser(0);

        $this->assertCount(0, $result);
    }

    // -------------------------------------------------------------------------
    // getTopicById
    // -------------------------------------------------------------------------

    public function test_get_topic_by_id_returns_topic_when_found(): void
    {
        $topic = Topic::factory()->create();

        $result = new Topic()->getTopicById($topic->id);

        $this->assertNotNull($result);
        $this->assertEquals($topic->id, $result->id);
    }

    public function test_get_topic_by_id_returns_null_when_not_found(): void
    {
        $result = new Topic()->getTopicById(0);

        $this->assertNull($result);
    }

    // -------------------------------------------------------------------------
    // getTopicsList
    // -------------------------------------------------------------------------

    public function test_get_topics_list_returns_paginator_with_correct_count(): void
    {
        Topic::factory()->count(5)->create();

        $result = new Topic()->getTopicsList(3, 1);

        $this->assertInstanceOf(LengthAwarePaginator::class, $result);
        $this->assertCount(3, $result->items());
        $this->assertEquals(5, $result->total());
    }

    public function test_get_topics_list_returns_second_page(): void
    {
        Topic::factory()->count(5)->create();

        $result = new Topic()->getTopicsList(3, 2);

        $this->assertCount(2, $result->items());
    }
}
