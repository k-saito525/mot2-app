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
}
