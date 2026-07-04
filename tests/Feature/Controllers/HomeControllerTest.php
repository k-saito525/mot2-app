<?php

namespace Tests\Feature\Controllers;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HomeControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_index_returns_view_when_authenticated(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('home.index'));

        $response->assertOk();
    }

    public function test_index_redirects_guest_to_login(): void
    {
        $response = $this->get(route('home.index'));

        $response->assertRedirect(route('login.show.form'));
    }
}
