<?php

namespace Database\Factories;

use App\Models\Doctor;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class DoctorFactory extends Factory
{
    protected $model = Doctor::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'specialization' => $this->faker->jobTitle,
            'qualifications' => $this->faker->sentence(3),
            'license_number' => $this->faker->unique()->numerify('MD#####'),
            'experience' => $this->faker->numberBetween(1, 30), // Add experience in years
            'is_available' => true,
        ];
    }
}
