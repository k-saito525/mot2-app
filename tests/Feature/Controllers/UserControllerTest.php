<?php

namespace Tests\Feature\Controllers;

use App\Models\Topic;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserControllerTest extends TestCase
{
    use RefreshDatabase;

    private function validParams(int $userId, array $overrides = []): array
    {
        return array_merge([
            'user_id'         => $userId,
            'name'            => 'テストユーザー',
            'user_identifier' => 'testuser1',
        ], $overrides);
    }

    // -------------------------------------------------------------------------
    // showList
    // -------------------------------------------------------------------------

    public function test_show_list_returns_view(): void
    {
        $viewer = User::factory()->create(['is_approved' => 1]);

        $response = $this->actingAs($viewer)->get(route('user.show.list'));

        $response->assertOk();
    }

    // -------------------------------------------------------------------------
    // showDetail
    // -------------------------------------------------------------------------

    public function test_show_detail_returns_view_when_user_approved(): void
    {
        $viewer = User::factory()->create(['is_approved' => 1]);
        $target = User::factory()->create(['is_approved' => 1]);
        $topic  = Topic::factory()->create(['user_id' => $target->id]);

        $response = $this->actingAs($viewer)->get(route('user.show.detail', ['id' => $target->id]));

        $response->assertOk();
        $response->assertViewHas('user', fn ($u) => $u->id === $target->id);
        $response->assertViewHas('topics', fn ($topics) => $topics->contains('id', $topic->id));
    }

    public function test_show_detail_redirects_to_list_when_user_not_found(): void
    {
        $viewer = User::factory()->create(['is_approved' => 1]);

        $response = $this->actingAs($viewer)->get(route('user.show.detail', ['id' => 0]));

        $response->assertRedirect(route('user.show.list'));
    }

    public function test_show_detail_redirects_to_list_when_user_not_approved(): void
    {
        $viewer = User::factory()->create(['is_approved' => 1]);
        $target = User::factory()->create(['is_approved' => 0]);

        $response = $this->actingAs($viewer)->get(route('user.show.detail', ['id' => $target->id]));

        $response->assertRedirect(route('user.show.list'));
    }

    // -------------------------------------------------------------------------
    // showEdit
    // -------------------------------------------------------------------------

    public function test_show_edit_displays_own_profile(): void
    {
        $user = User::factory()->create(['is_approved' => 1]);

        $response = $this->actingAs($user)->get(route('user.show.edit', ['id' => $user->id]));

        $response->assertOk();
        $response->assertViewHas('user', fn ($viewUser) => $viewUser->id === $user->id);
    }

    public function test_show_edit_redirects_when_not_own_profile(): void
    {
        $owner = User::factory()->create(['is_approved' => 1]);
        $other = User::factory()->create(['is_approved' => 1]);

        $response = $this->actingAs($other)->get(route('user.show.edit', ['id' => $owner->id]));

        $response->assertRedirect(route('user.show.edit', ['id' => $other->id]));
    }

    // -------------------------------------------------------------------------
    // store
    // -------------------------------------------------------------------------

    public function test_store_updates_own_profile(): void
    {
        $user = User::factory()->create(['is_approved' => 1]);

        $response = $this->actingAs($user)->post(route('user.store'), $this->validParams($user->id, [
            'name' => '更新後の名前',
        ]));

        $response->assertRedirect(route('user.show.detail', ['id' => $user->id]));
        $this->assertDatabaseHas('users', ['id' => $user->id, 'name' => '更新後の名前']);
    }

    public function test_store_returns_403_when_not_own_profile(): void
    {
        $owner = User::factory()->create(['is_approved' => 1]);
        $other = User::factory()->create(['is_approved' => 1]);

        $response = $this->actingAs($other)->post(route('user.store'), $this->validParams($owner->id));

        $response->assertStatus(403);
    }

    public function test_store_returns_404_when_user_not_approved(): void
    {
        $user = User::factory()->create(['is_approved' => 0]);

        $response = $this->actingAs($user)->post(route('user.store'), $this->validParams($user->id));

        $response->assertStatus(404);
    }

    public function test_store_fails_validation_when_email_is_duplicate(): void
    {
        User::factory()->create(['email' => 'taken@example.com', 'is_approved' => 1]);
        $user = User::factory()->create(['email' => 'own@example.com', 'is_approved' => 1]);

        $response = $this->actingAs($user)->post(route('user.store'), $this->validParams($user->id, [
            'email' => 'taken@example.com',
        ]));

        $response->assertSessionHasErrors('email');
    }

    public function test_store_fails_validation_when_identifier_is_duplicate(): void
    {
        User::factory()->create(['user_identifier' => 'taken0001', 'is_approved' => 1]);
        $user = User::factory()->create(['user_identifier' => 'myself001', 'is_approved' => 1]);

        $response = $this->actingAs($user)->post(route('user.store'), $this->validParams($user->id, [
            'user_identifier' => 'taken0001',
        ]));

        $response->assertSessionHasErrors('user_identifier');
    }

    public function test_store_allows_keeping_own_email_and_identifier(): void
    {
        $user = User::factory()->create([
            'email'           => 'own@example.com',
            'user_identifier' => 'myself001',
            'is_approved'     => 1,
        ]);

        $response = $this->actingAs($user)->post(route('user.store'), $this->validParams($user->id, [
            'email'           => 'own@example.com',
            'user_identifier' => 'myself001',
        ]));

        $response->assertRedirect(route('user.show.detail', ['id' => $user->id]));
    }

    // -------------------------------------------------------------------------
    // validation
    // -------------------------------------------------------------------------

    public function test_store_fails_when_name_is_missing(): void
    {
        $user = User::factory()->create(['is_approved' => 1]);

        $response = $this->actingAs($user)->post(route('user.store'), [
            'user_id'         => $user->id,
            'user_identifier' => 'testuser1',
        ]);

        $response->assertSessionHasErrors('name');
    }

    public function test_store_fails_when_identifier_is_too_short(): void
    {
        $user = User::factory()->create(['is_approved' => 1]);

        $response = $this->actingAs($user)->post(route('user.store'), $this->validParams($user->id, [
            'user_identifier' => 'short',
        ]));

        $response->assertSessionHasErrors('user_identifier');
    }

    public function test_store_fails_when_identifier_has_invalid_chars(): void
    {
        $user = User::factory()->create(['is_approved' => 1]);

        $response = $this->actingAs($user)->post(route('user.store'), $this->validParams($user->id, [
            'user_identifier' => 'invalid-id',
        ]));

        $response->assertSessionHasErrors('user_identifier');
    }
}
