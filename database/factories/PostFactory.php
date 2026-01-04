<?php

namespace Database\Factories;

use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class PostFactory extends Factory
{
    protected $model = Post::class;

    public function definition(): array
    {
        $isApproved = $this->faker->boolean(70);

        return [
            'user_id' => User::factory(),
            'title' => $this->faker->sentence(),
            'content' => $this->faker->paragraphs(2, true),
            'category' => $this->faker->randomElement([
                'depression', 'anxiety', 'relationships', 'general', 'stress'
            ]),
            'is_anonymous' => $this->faker->boolean(20),
            'is_approved' => $isApproved,
            'approved_at' => $isApproved ? $this->faker->dateTimeBetween('-1 month', 'now') : null,
            'approved_by' => $isApproved ? User::factory() : null,
            'likes_count' => $this->faker->numberBetween(0, 100),
            'comments_count' => $this->faker->numberBetween(0, 50),
        ];
    }
}
