<?php

namespace Tests\Feature\Controllers;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserIdentifierControllerTest extends TestCase
{
    use RefreshDatabase;

    // -------------------------------------------------------------------------
    // store
    // -------------------------------------------------------------------------

    public function test_store_sets_user_identifier(): void
    {
        $user = User::factory()->create(['user_identifier' => null]);

        $response = $this->withSession(['user' => $user])->post(route('identifier.store'), [
            'user_identifier' => 'newident1',
        ]);

        $response->assertOk();
        $this->assertDatabaseHas('users', ['id' => $user->id, 'user_identifier' => 'newident1']);
    }

    public function test_store_redirects_when_identifier_already_exists(): void
    {
        $user = User::factory()->create(['user_identifier' => 'existing1']);

        $response = $this->withSession(['user' => $user])->post(route('identifier.store'), [
            'user_identifier' => 'newident1',
        ]);

        $response->assertRedirect(route('login.show.form'));
        $response->assertSessionHas('flash_failed');
        $this->assertDatabaseHas('users', ['id' => $user->id, 'user_identifier' => 'existing1']);
    }

    public function test_store_fails_validation_when_identifier_is_duplicate(): void
    {
        User::factory()->create(['user_identifier' => 'taken001']);
        $user = User::factory()->create(['user_identifier' => null]);

        $response = $this->withSession(['user' => $user])->post(route('identifier.store'), [
            'user_identifier' => 'taken001',
        ]);

        $response->assertSessionHasErrors('user_identifier');
    }
}
