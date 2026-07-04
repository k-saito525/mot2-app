<?php

namespace Tests\Feature\Controllers\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminSupportControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_show_list_returns_view_when_admin(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $response = $this->actingAs($admin)->get(route('admin.show.support.list'));

        $response->assertOk();
    }

    public function test_show_list_returns_403_when_not_admin(): void
    {
        $user = User::factory()->create(['is_admin' => false]);

        $response = $this->actingAs($user)->get(route('admin.show.support.list'));

        $response->assertStatus(403);
    }

    public function test_show_list_redirects_guest_to_login(): void
    {
        $response = $this->get(route('admin.show.support.list'));

        $response->assertRedirect(route('login.show.form'));
    }
}
