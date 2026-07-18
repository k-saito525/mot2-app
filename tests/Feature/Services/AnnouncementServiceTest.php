<?php

namespace Tests\Feature\Services;

use App\Models\Announcement;
use App\Models\AnnouncementRead;
use App\Models\User;
use App\Services\AnnouncementService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AnnouncementServiceTest extends TestCase
{
    use RefreshDatabase;

    private AnnouncementService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new AnnouncementService();
    }

    // -------------------------------------------------------------------------
    // delete
    // -------------------------------------------------------------------------

    public function test_delete_returns_false_when_announcement_not_found(): void
    {
        $result = $this->service->delete(0);

        $this->assertFalse($result);
    }

    public function test_delete_soft_deletes_announcement(): void
    {
        $announcement = Announcement::factory()->create();

        $result = $this->service->delete($announcement->id);

        $this->assertTrue($result);
        $this->assertSoftDeleted('announcements', ['id' => $announcement->id]);
    }

    public function test_delete_removes_associated_reads(): void
    {
        $user         = User::factory()->create();
        $announcement = Announcement::factory()->create();
        AnnouncementRead::create([
            'user_id'         => $user->id,
            'announcement_id' => $announcement->id,
        ]);

        $this->service->delete($announcement->id);

        $this->assertDatabaseMissing('announcement_reads', [
            'announcement_id' => $announcement->id,
        ]);
    }

    // -------------------------------------------------------------------------
    // saveAndSyncReads
    // -------------------------------------------------------------------------

    public function test_save_and_sync_reads_saves_announcement(): void
    {
        $announcement = Announcement::factory()->make([
            'user_id'      => User::factory()->create()->id,
            'pub_start_at' => now()->toDateString(),
        ]);

        $result = $this->service->saveAndSyncReads($announcement);

        $this->assertTrue($result);
        $this->assertDatabaseHas('announcements', ['title' => $announcement->title]);
    }

    public function test_save_and_sync_reads_deletes_reads_when_not_yet_published(): void
    {
        $user         = User::factory()->create();
        $announcement = Announcement::factory()->create([
            'user_id'      => $user->id,
            'pub_start_at' => now()->addDay()->toDateString(),
        ]);
        AnnouncementRead::create([
            'user_id'         => $user->id,
            'announcement_id' => $announcement->id,
        ]);

        // 公開前日付に変更して再保存
        $announcement->pub_start_at = now()->addDays(2)->toDateString();
        $this->service->saveAndSyncReads($announcement);

        $this->assertDatabaseMissing('announcement_reads', [
            'announcement_id' => $announcement->id,
        ]);
    }

    // -------------------------------------------------------------------------
    // markAsRead
    // -------------------------------------------------------------------------

    public function test_mark_as_read_creates_read_record(): void
    {
        $user         = User::factory()->create();
        $announcement = Announcement::factory()->create();

        $result = $this->service->markAsRead($user->id, $announcement->id);

        $this->assertTrue($result);
        $this->assertDatabaseHas('announcement_reads', [
            'user_id'         => $user->id,
            'announcement_id' => $announcement->id,
        ]);
    }

    public function test_mark_as_read_is_idempotent_when_already_read(): void
    {
        $user         = User::factory()->create();
        $announcement = Announcement::factory()->create();
        AnnouncementRead::create([
            'user_id'         => $user->id,
            'announcement_id' => $announcement->id,
        ]);

        $result = $this->service->markAsRead($user->id, $announcement->id);

        $this->assertTrue($result);
        $this->assertDatabaseCount('announcement_reads', 1);
    }

    // -------------------------------------------------------------------------
    // getAnnouncements
    // -------------------------------------------------------------------------

    public function test_get_announcements_returns_all_when_no_filter(): void
    {
        Announcement::factory()->count(3)->create();

        $result = $this->service->getAnnouncements();

        $this->assertCount(3, $result);
    }

    public function test_get_announcements_filters_by_target_ids(): void
    {
        $a1 = Announcement::factory()->create();
        $a2 = Announcement::factory()->create();
        Announcement::factory()->create();

        $result = $this->service->getAnnouncements(false, [$a1->id, $a2->id]);

        $this->assertCount(2, $result);
    }

    public function test_get_announcements_returns_only_ids_when_only_id_is_true(): void
    {
        Announcement::factory()->create();

        $result = $this->service->getAnnouncements(true);

        $this->assertArrayHasKey('id', $result[0]);
        $this->assertArrayNotHasKey('title', $result[0]);
    }

    // -------------------------------------------------------------------------
    // getStatusRead
    // -------------------------------------------------------------------------

    public function test_get_status_read_returns_zero_when_no_published_announcements(): void
    {
        $user = User::factory()->create();

        $result = $this->service->getStatusRead($user->id);

        $this->assertSame(0, $result['unread_count']);
        $this->assertSame('', $result['announcement']);
    }

    public function test_get_status_read_flags_read_announcement_as_is_read(): void
    {
        $user         = User::factory()->create();
        $announcement = Announcement::factory()->create();
        AnnouncementRead::create([
            'user_id'         => $user->id,
            'announcement_id' => $announcement->id,
        ]);

        $result = $this->service->getStatusRead($user->id);

        $target = collect($result['announcement'])->firstWhere('id', $announcement->id);
        $this->assertTrue($target->is_read);
        $this->assertSame(0, $result['unread_count']);
    }

    public function test_get_status_read_does_not_flag_unread_announcement(): void
    {
        $user         = User::factory()->create();
        $announcement = Announcement::factory()->create();

        $result = $this->service->getStatusRead($user->id);

        $target = collect($result['announcement'])->firstWhere('id', $announcement->id);
        $this->assertFalse((bool) $target->is_read);
        $this->assertSame(1, $result['unread_count']);
    }
}
