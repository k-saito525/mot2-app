<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Topic>
 */
class TopicFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'title'   => fake()->sentence(),
            'content' => fake()->paragraph(),
        ];
    }
}
