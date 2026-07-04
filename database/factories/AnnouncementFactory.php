<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Announcement>
 */
class AnnouncementFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id'        => User::factory(),
            'title'          => fake()->sentence(),
            'content'        => fake()->paragraph(),
            'pub_start_at'   => now()->toDateString(),
            'pub_end_at'     => null,
            'publish_status' => 1,
        ];
    }
}
