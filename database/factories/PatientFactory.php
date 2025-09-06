<?php

namespace Database\Factories;

use App\Models\Patient;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class PatientFactory extends Factory
{
    protected $model = Patient::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'date_of_birth' => $this->faker->date(),
            'gender' => $this->faker->randomElement(['male', 'female', 'other']), // Add gender
            'blood_type' => $this->faker->randomElement(['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-']), // Optional: good to have
            'emergency_contact_name' => $this->faker->name(), // Add emergency_contact_name
            'emergency_contact_phone' => $this->faker->phoneNumber(), // Add emergency_contact_phone
            // Removed 'address' and 'phone_number' as they are not in the patients table schema directly
            // 'allergies' and 'medical_conditions' are json/text and nullable, so can be omitted for basic factory
        ];
    }
}
