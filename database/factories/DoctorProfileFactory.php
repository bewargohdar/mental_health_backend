<?php

namespace Database\Factories;

use App\Models\DoctorProfile;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class DoctorProfileFactory extends Factory
{
    protected $model = DoctorProfile::class;

    public function definition(): array
    {
        $isVerified = $this->faker->boolean(80);
        
        return [
            'user_id' => User::factory(),
            'specialization' => $this->faker->randomElement([
                'Clinical Psychology', 
                'Psychiatry', 
                'Cognitive Behavioral Therapy', 
                'Family Therapy', 
                'Child Psychology'
            ]),
            'license_number' => $this->faker->unique()->bothify('LIC-#####-??'),
            'bio' => $this->faker->paragraph(),
            'qualifications' => json_encode([
                'degree' => $this->faker->randomElement(['PhD', 'MD', 'PsyD', 'MA']),
                'university' => $this->faker->company(),
                'year' => $this->faker->year(),
            ]),
            'experience_years' => $this->faker->numberBetween(1, 40),
            'hourly_rate' => $this->faker->randomFloat(2, 50, 300),
            'is_verified' => $isVerified,
            'verified_at' => $isVerified ? $this->faker->dateTimeBetween('-1 year', 'now') : null,
            'languages' => json_encode($this->faker->randomElements(['English', 'Arabic', 'Kurdish'], $this->faker->numberBetween(1, 3))),
            'consultation_types' => json_encode($this->faker->randomElements(['video', 'audio', 'chat', 'in-person'], $this->faker->numberBetween(1, 4))),
        ];
    }
}
