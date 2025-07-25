<?php
// database/factories/UserFactory.php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => Hash::make('password'), // Default password
            'role' => fake()->randomElement(['employee', 'hse_staff']),
            'department' => fake()->randomElement([
                'IT Department',
                'Operations',
                'Production',
                'Quality Control',
                'Maintenance',
                'Human Resources',
                'Finance',
                'Marketing',
                'Health, Safety & Environment'
            ]),
            'phone' => fake()->phoneNumber(),
            'profile_image' => null,
            'is_active' => true,
            'remember_token' => Str::random(10),
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn(array $attributes) => [
            'email_verified_at' => null,
        ]);
    }

    /**
     * Create an employee user.
     */
    public function employee(): static
    {
        return $this->state(fn(array $attributes) => [
            'role' => 'employee',
            'department' => fake()->randomElement([
                'IT Department',
                'Operations',
                'Production',
                'Quality Control',
                'Maintenance',
                'Human Resources',
                'Finance',
                'Marketing'
            ]),
        ]);
    }

    /**
     * Create an HSE staff user.
     */
    public function hseStaff(): static
    {
        return $this->state(fn(array $attributes) => [
            'role' => 'hse_staff',
            'department' => 'Health, Safety & Environment',
        ]);
    }

    /**
     * Create an inactive user.
     */
    public function inactive(): static
    {
        return $this->state(fn(array $attributes) => [
            'is_active' => false,
        ]);
    }
}
