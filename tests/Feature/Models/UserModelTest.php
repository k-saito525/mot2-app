<?php

namespace Tests\Feature\Models;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserModelTest extends TestCase
{
    use RefreshDatabase;

    // -------------------------------------------------------------------------
    // scopeApproved
    // -------------------------------------------------------------------------

    public function test_approved_scope_returns_only_approved_users(): void
    {
        User::factory()->create(['is_approved' => 1]);
        User::factory()->create(['is_approved' => 0]);

        $result = User::approved()->get();

        $this->assertCount(1, $result);
        $this->assertTrue((bool) $result->first()->is_approved);
    }

    public function test_approved_scope_returns_empty_when_no_approved_users(): void
    {
        User::factory()->create(['is_approved' => 0]);

        $result = User::approved()->get();

        $this->assertCount(0, $result);
    }

    // -------------------------------------------------------------------------
    // scopeUnapproved
    // -------------------------------------------------------------------------

    public function test_unapproved_scope_returns_only_unapproved_users(): void
    {
        User::factory()->create(['is_approved' => 0]);
        User::factory()->create(['is_approved' => 1]);

        $result = User::unapproved()->get();

        $this->assertCount(1, $result);
        $this->assertFalse((bool) $result->first()->is_approved);
    }
}
