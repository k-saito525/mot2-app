<?php

namespace Tests\Feature\Models;

use App\Models\Announcement;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AnnouncementModelTest extends TestCase
{
    use RefreshDatabase;

    // -------------------------------------------------------------------------
    // scopePublished
    // -------------------------------------------------------------------------

    public function test_published_scope_includes_announcement_with_no_end_date(): void
    {
        Announcement::factory()->create([
            'pub_start_at'   => now()->subDay()->toDateString(),
            'pub_end_at'     => null,
            'publish_status' => 1,
        ]);

        $result = Announcement::published()->get();

        $this->assertCount(1, $result);
    }

    public function test_published_scope_includes_announcement_ending_today(): void
    {
        Announcement::factory()->create([
            'pub_start_at'   => now()->subDay()->toDateString(),
            'pub_end_at'     => now()->toDateString(),
            'publish_status' => 1,
        ]);

        $result = Announcement::published()->get();

        $this->assertCount(1, $result);
    }

    public function test_published_scope_excludes_future_announcement(): void
    {
        Announcement::factory()->create([
            'pub_start_at'   => now()->addDay()->toDateString(),
            'pub_end_at'     => null,
            'publish_status' => 1,
        ]);

        $result = Announcement::published()->get();

        $this->assertCount(0, $result);
    }

    public function test_published_scope_excludes_ended_announcement(): void
    {
        Announcement::factory()->create([
            'pub_start_at'   => now()->subDays(5)->toDateString(),
            'pub_end_at'     => now()->subDay()->toDateString(),
            'publish_status' => 1,
        ]);

        $result = Announcement::published()->get();

        $this->assertCount(0, $result);
    }

    // -------------------------------------------------------------------------
    // pubStatus accessor
    // -------------------------------------------------------------------------

    public function test_pub_status_returns_publishing_when_active(): void
    {
        $announcement = Announcement::factory()->make([
            'pub_start_at' => now()->subDay()->toDateString(),
            'pub_end_at'   => null,
        ]);

        $this->assertSame('公開中', $announcement->pub_status);
    }

    public function test_pub_status_returns_before_publishing_when_future(): void
    {
        $announcement = Announcement::factory()->make([
            'pub_start_at' => now()->addDay()->toDateString(),
            'pub_end_at'   => null,
        ]);

        $this->assertSame('公開前', $announcement->pub_status);
    }

    public function test_pub_status_returns_ended_when_past_end_date(): void
    {
        $announcement = Announcement::factory()->make([
            'pub_start_at' => now()->subDays(5)->toDateString(),
            'pub_end_at'   => now()->subDay()->toDateString(),
        ]);

        $this->assertSame('公開終了', $announcement->pub_status);
    }

    // -------------------------------------------------------------------------
    // getAnnouncements
    // -------------------------------------------------------------------------

    public function test_get_announcements_returns_all_when_no_filter(): void
    {
        Announcement::factory()->count(3)->create();

        $result = (new Announcement())->getAnnouncements();

        $this->assertCount(3, $result);
    }

    public function test_get_announcements_filters_by_target_ids(): void
    {
        $a1 = Announcement::factory()->create();
        $a2 = Announcement::factory()->create();
        Announcement::factory()->create();

        $result = (new Announcement())->getAnnouncements(false, [$a1->id, $a2->id]);

        $this->assertCount(2, $result);
    }

    public function test_get_announcements_returns_only_ids_when_only_id_is_true(): void
    {
        Announcement::factory()->create();

        $result = (new Announcement())->getAnnouncements(true);

        $this->assertArrayHasKey('id', $result[0]);
        $this->assertArrayNotHasKey('title', $result[0]);
    }
}
