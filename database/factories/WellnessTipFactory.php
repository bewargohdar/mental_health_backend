<?php

namespace Database\Factories;

use App\Models\WellnessTip;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class WellnessTipFactory extends Factory
{
    protected $model = WellnessTip::class;

    public function definition(): array
    {
        return [
            'title' => $this->faker->sentence(),
            'content' => $this->faker->paragraph(),
            'category' => $this->faker->randomElement(['mindfulness', 'sleep', 'exercise', 'nutrition', 'stress-management']),
            'icon' => null,
            'author_id' => User::factory(),
            'is_active' => $this->faker->boolean(90),
            'display_order' => $this->faker->numberBetween(1, 100),
            'language' => $this->faker->randomElement(['en', 'ar', 'ku']),
        ];
    }
}
