<?php

namespace Database\Factories;

use App\Models\Video;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class VideoFactory extends Factory
{
    protected $model = Video::class;

    public function definition(): array
    {
        return [
            'title' => $this->faker->sentence(),
            'description' => $this->faker->paragraph(),
            'url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ', // Placeholder
            'thumbnail' => null,
            'duration' => $this->faker->numberBetween(60, 3600), // seconds
            'category' => $this->faker->randomElement([
                'depression', 'anxiety', 'stress', 'mindfulness', 'sleep'
            ]),
            'author_id' => User::factory(),
            'is_published' => $this->faker->boolean(80),
            'views_count' => $this->faker->numberBetween(0, 5000),
            'tags' => json_encode($this->faker->words(3)),
        ];
    }
}
