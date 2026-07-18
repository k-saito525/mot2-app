<?php

namespace Tests\Feature\Controllers\Admin;

use App\Mail\MailApprovedUser;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class ApproveControllerTest extends TestCase
{
    use RefreshDatabase;

    // -------------------------------------------------------------------------
    // showDetail
    // -------------------------------------------------------------------------

    public function test_show_detail_returns_view_with_formatted_past_join(): void
    {
        $admin     = User::factory()->create(['is_admin' => true]);
        $applicant = User::factory()->create([
            'is_approved' => 0,
            'past_join'   => ['g_2011_sum'],
        ]);

        $response = $this->actingAs($admin)->get(route('admin.show.detail', $applicant->id));

        $response->assertOk();
        $response->assertViewHas('user', fn ($u) => $u->past_join === ['多文化ぐんま2011夏']);
    }

    public function test_show_detail_returns_404_when_user_not_found(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $response = $this->actingAs($admin)->get(route('admin.show.detail', 0));

        $response->assertStatus(404);
    }

    public function test_show_detail_returns_404_when_user_already_approved(): void
    {
        $admin        = User::factory()->create(['is_admin' => true]);
        $approvedUser = User::factory()->create(['is_approved' => 1]);

        $response = $this->actingAs($admin)->get(route('admin.show.detail', $approvedUser->id));

        $response->assertStatus(404);
    }

    public function test_show_detail_returns_403_when_not_admin(): void
    {
        $user      = User::factory()->create(['is_admin' => false]);
        $applicant = User::factory()->create(['is_approved' => 0]);

        $response = $this->actingAs($user)->get(route('admin.show.detail', $applicant->id));

        $response->assertStatus(403);
    }

    // -------------------------------------------------------------------------
    // approve
    // -------------------------------------------------------------------------

    public function test_approve_approves_user_and_sends_mail(): void
    {
        Mail::fake();
        $admin     = User::factory()->create(['is_admin' => true]);
        $applicant = User::factory()->create(['is_approved' => 0]);

        $response = $this->actingAs($admin)->post(route('admin.unapproved.approve'), [
            'id' => $applicant->id,
        ]);

        $response->assertRedirect(route('admin.show.unapproved.list'));
        $this->assertDatabaseHas('users', ['id' => $applicant->id, 'is_approved' => 1]);
        Mail::assertSent(MailApprovedUser::class);
    }

    public function test_approve_redirects_when_user_not_found(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $response = $this->actingAs($admin)->post(route('admin.unapproved.approve'), [
            'id' => 0,
        ]);

        $response->assertRedirect(route('admin.show.unapproved.list'));
    }

    public function test_approve_returns_403_when_not_admin(): void
    {
        $user      = User::factory()->create(['is_admin' => false]);
        $applicant = User::factory()->create(['is_approved' => 0]);

        $response = $this->actingAs($user)->post(route('admin.unapproved.approve'), [
            'id' => $applicant->id,
        ]);

        $response->assertStatus(403);
    }
}
