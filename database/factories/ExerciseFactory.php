<?php

namespace Database\Factories;

use App\Models\Exercise;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ExerciseFactory extends Factory
{
    protected $model = Exercise::class;

    public function definition(): array
    {
        return [
            'title' => $this->faker->sentence(),
            'description' => $this->faker->paragraph(),
            'instructions' => $this->faker->paragraphs(3, true),
            'category' => $this->faker->randomElement([
                'depression', 'anxiety', 'stress', 'mindfulness', 'sleep', 'general'
            ]),
            'author_id' => User::factory(),
            'duration' => $this->faker->numberBetween(300, 1800), // seconds
            'difficulty' => $this->faker->randomElement(['beginner', 'intermediate', 'advanced']),
            'audio_url' => null,
            'image_url' => $this->faker->imageUrl(640, 480, 'sports', true),
            'is_published' => $this->faker->boolean(80),
            'completions_count' => $this->faker->numberBetween(0, 1000),
            'tags' => json_encode($this->faker->words(3)),
        ];
    }
}
