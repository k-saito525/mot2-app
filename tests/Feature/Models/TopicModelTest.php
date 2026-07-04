<?php

namespace Tests\Feature\Models;

use App\Models\Topic;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
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

        $result = (new Topic())->getTopics();

        $this->assertCount(3, $result);
    }

    public function test_get_topics_respects_limit(): void
    {
        Topic::factory()->count(5)->create();

        $result = (new Topic())->getTopics(2);

        $this->assertCount(2, $result);
    }

    public function test_get_topics_returns_most_recent_first(): void
    {
        $old   = Topic::factory()->create(['created_at' => now()->subDays(2)]);
        $newer = Topic::factory()->create(['created_at' => now()->subDay()]);
        $newest = Topic::factory()->create(['created_at' => now()]);

        $result = (new Topic())->getTopics();

        $this->assertEquals($newest->id, $result->first()->id);
        $this->assertEquals($old->id, $result->last()->id);
    }

    public function test_get_topics_does_not_return_soft_deleted(): void
    {
        $active  = Topic::factory()->create();
        $deleted = Topic::factory()->create();
        $deleted->delete();

        $result = (new Topic())->getTopics();

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

        $result = (new Topic())->getTopicByUser($user->id);

        $this->assertCount(2, $result);
        $result->each(fn ($t) => $this->assertEquals($user->id, $t->user_id));
    }

    public function test_get_topic_by_user_returns_empty_for_unknown_user(): void
    {
        $result = (new Topic())->getTopicByUser(0);

        $this->assertCount(0, $result);
    }
}
