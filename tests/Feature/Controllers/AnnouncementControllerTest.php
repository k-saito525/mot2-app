<?php

namespace Tests\Feature\Controllers;

use App\Models\Announcement;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AnnouncementControllerTest extends TestCase
{
    use RefreshDatabase;

    private function adminUser(): User
    {
        return User::factory()->create(['is_admin' => true]);
    }

    private function generalUser(): User
    {
        return User::factory()->create(['is_admin' => false]);
    }

    private function validParams(array $overrides = []): array
    {
        return array_merge([
            'announcement_title'  => 'テストお知らせ',
            'announcement_detail' => 'テスト本文',
            'pub_start'           => now()->toDateString(),
            'pub_end'             => '',
        ], $overrides);
    }

    // -------------------------------------------------------------------------
    // store
    // -------------------------------------------------------------------------

    public function test_store_creates_announcement_as_admin(): void
    {
        $admin = $this->adminUser();

        $response = $this->actingAs($admin)->post(route('admin.announcement.store'), $this->validParams());

        $response->assertRedirect(route('admin.show.announcement.list'));
        $this->assertDatabaseHas('announcements', [
            'title'   => 'テストお知らせ',
            'content' => 'テスト本文',
        ]);
    }

    public function test_store_returns_403_when_not_admin(): void
    {
        $user = $this->generalUser();

        $response = $this->actingAs($user)->post(route('admin.announcement.store'), $this->validParams());

        $response->assertStatus(403);
    }

    public function test_store_redirects_guest_to_login(): void
    {
        $response = $this->post(route('admin.announcement.store'), $this->validParams());

        $response->assertRedirect(route('login.show.form'));
    }

    public function test_store_returns_error_when_pub_start_is_after_pub_end(): void
    {
        $admin = $this->adminUser();

        $response = $this->actingAs($admin)->post(route('admin.announcement.store'), $this->validParams([
            'pub_start' => '2026-12-31',
            'pub_end'   => '2026-01-01',
        ]));

        $response->assertRedirect();
        $response->assertSessionHas('pub_start');
    }

    // -------------------------------------------------------------------------
    // update
    // -------------------------------------------------------------------------

    public function test_update_changes_announcement_as_admin(): void
    {
        $admin        = $this->adminUser();
        $announcement = Announcement::factory()->create(['user_id' => $admin->id]);

        $response = $this->actingAs($admin)->put(
            route('admin.announcement.update', $announcement->id),
            $this->validParams(['announcement_detail' => '更新後の本文'])
        );

        $response->assertRedirect(route('admin.show.announcement.list'));
        $this->assertDatabaseHas('announcements', [
            'id'      => $announcement->id,
            'content' => '更新後の本文',
        ]);
    }

    public function test_update_returns_403_when_not_admin(): void
    {
        $user         = $this->generalUser();
        $announcement = Announcement::factory()->create();

        $response = $this->actingAs($user)->put(
            route('admin.announcement.update', $announcement->id),
            $this->validParams()
        );

        $response->assertStatus(403);
    }

    public function test_update_returns_404_when_announcement_not_found(): void
    {
        $admin = $this->adminUser();

        $response = $this->actingAs($admin)->put(
            route('admin.announcement.update', 0),
            $this->validParams()
        );

        $response->assertStatus(404);
    }

    // -------------------------------------------------------------------------
    // destroy
    // -------------------------------------------------------------------------

    public function test_destroy_deletes_announcement_as_admin(): void
    {
        $admin        = $this->adminUser();
        $announcement = Announcement::factory()->create();

        $response = $this->actingAs($admin)->delete(route('admin.announcement.destroy', $announcement->id));

        $response->assertRedirect(route('admin.show.announcement.list'));
        $this->assertSoftDeleted('announcements', ['id' => $announcement->id]);
    }

    public function test_destroy_returns_403_when_not_admin(): void
    {
        $user         = $this->generalUser();
        $announcement = Announcement::factory()->create();

        $response = $this->actingAs($user)->delete(route('admin.announcement.destroy', $announcement->id));

        $response->assertStatus(403);
    }

    public function test_destroy_returns_404_when_announcement_not_found(): void
    {
        $admin = $this->adminUser();

        $response = $this->actingAs($admin)->delete(route('admin.announcement.destroy', 0));

        $response->assertStatus(404);
    }

    // -------------------------------------------------------------------------
    // validation
    // -------------------------------------------------------------------------

    public function test_store_passes_when_title_is_exactly_50_chars(): void
    {
        $admin = $this->adminUser();

        $response = $this->actingAs($admin)->post(route('admin.announcement.store'), $this->validParams([
            'announcement_title' => str_repeat('あ', 50),
        ]));

        $response->assertSessionMissingErrors('announcement_title');
    }

    public function test_store_fails_when_title_exceeds_50_chars(): void
    {
        $admin = $this->adminUser();

        $response = $this->actingAs($admin)->post(route('admin.announcement.store'), $this->validParams([
            'announcement_title' => str_repeat('あ', 51),
        ]));

        $response->assertSessionHasErrors('announcement_title');
    }

    public function test_store_passes_when_detail_is_exactly_800_chars(): void
    {
        $admin = $this->adminUser();

        $response = $this->actingAs($admin)->post(route('admin.announcement.store'), $this->validParams([
            'announcement_detail' => str_repeat('あ', 800),
        ]));

        $response->assertSessionMissingErrors('announcement_detail');
    }

    public function test_store_fails_when_detail_exceeds_800_chars(): void
    {
        $admin = $this->adminUser();

        $response = $this->actingAs($admin)->post(route('admin.announcement.store'), $this->validParams([
            'announcement_detail' => str_repeat('あ', 801),
        ]));

        $response->assertSessionHasErrors('announcement_detail');
    }
}
