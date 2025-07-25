<?php
// database/factories/ReportFactory.php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\User;
use App\Helpers\HSEConstants;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Report>
 */
class ReportFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $status = fake()->randomElement(['waiting', 'in-progress', 'done']);
        $createdAt = fake()->dateTimeBetween('-30 days', 'now');

        $startProcessAt = null;
        $completedAt = null;
        $hseStaffId = null;

        // Set dates based on status
        if ($status === 'in-progress' || $status === 'done') {
            $startProcessAt = fake()->dateTimeBetween($createdAt, 'now');
            $hseStaffId = User::where('role', 'hse_staff')->inRandomOrder()->first()?->id;
        }

        if ($status === 'done') {
            $completedAt = fake()->dateTimeBetween($startProcessAt, 'now');
        }

        return [
            'employee_id' => User::where('role', 'employee')->inRandomOrder()->first()?->id
                ?? User::factory()->employee()->create()->id,
            'category' => fake()->randomElement(HSEConstants::CATEGORIES),
            'equipment_type' => fake()->randomElement(HSEConstants::EQUIPMENT_TYPES),
            'contributing_factor' => fake()->randomElement(HSEConstants::CONTRIBUTING_FACTORS),
            'description' => fake()->realText(200),
            'location' => fake()->randomElement([
                'Building A - Floor 1',
                'Building A - Floor 2',
                'Building A - Floor 3',
                'Building B - Floor 1',
                'Building B - Floor 2',
                'Building C - Ground Floor',
                'Building C - Basement',
                'Parking Area',
                'Main Entrance',
                'Emergency Exit',
                'Cafeteria',
                'Storage Room',
                'Server Room',
                'Laboratory',
                'Production Line 1',
                'Production Line 2',
                'Quality Control Room',
                'Maintenance Workshop'
            ]),
            'status' => $status,
            'images' => fake()->optional(0.7)->randomElements([
                'report_images/sample1.jpg',
                'report_images/sample2.jpg',
                'report_images/sample3.jpg'
            ], fake()->numberBetween(1, 3)),
            'start_process_at' => $startProcessAt,
            'completed_at' => $completedAt,
            'hse_staff_id' => $hseStaffId,
            'created_at' => $createdAt,
            'updated_at' => fake()->dateTimeBetween($createdAt, 'now'),
        ];
    }

    /**
     * Create a waiting report.
     */
    public function waiting(): static
    {
        return $this->state(fn(array $attributes) => [
            'status' => 'waiting',
            'start_process_at' => null,
            'completed_at' => null,
            'hse_staff_id' => null,
        ]);
    }

    /**
     * Create an in-progress report.
     */
    public function inProgress(): static
    {
        return $this->state(function (array $attributes) {
            $startProcessAt = fake()->dateTimeBetween($attributes['created_at'] ?? '-7 days', 'now');

            return [
                'status' => 'in-progress',
                'start_process_at' => $startProcessAt,
                'completed_at' => null,
                'hse_staff_id' => User::where('role', 'hse_staff')->inRandomOrder()->first()?->id,
            ];
        });
    }

    /**
     * Create a completed report.
     */
    public function completed(): static
    {
        return $this->state(function (array $attributes) {
            $startProcessAt = fake()->dateTimeBetween($attributes['created_at'] ?? '-7 days', '-1 day');
            $completedAt = fake()->dateTimeBetween($startProcessAt, 'now');

            return [
                'status' => 'done',
                'start_process_at' => $startProcessAt,
                'completed_at' => $completedAt,
                'hse_staff_id' => User::where('role', 'hse_staff')->inRandomOrder()->first()?->id,
            ];
        });
    }

    /**
     * Create a report with specific category.
     */
    public function category(string $category): static
    {
        return $this->state(fn(array $attributes) => [
            'category' => $category,
        ]);
    }

    /**
     * Create a report with images.
     */
    public function withImages(int $count = 2): static
    {
        $images = [];
        for ($i = 1; $i <= $count; $i++) {
            $images[] = "report_images/sample_{$i}.jpg";
        }

        return $this->state(fn(array $attributes) => [
            'images' => $images,
        ]);
    }

    /**
     * Create an urgent report (fire safety related).
     */
    public function urgent(): static
    {
        return $this->state(fn(array $attributes) => [
            'category' => 'Life Safety Equipment',
            'equipment_type' => fake()->randomElement(['Fire Extinguisher', 'Fire Alarm']),
            'contributing_factor' => 'Defective machinery/equipment',
            'description' => fake()->randomElement([
                'Fire extinguisher is empty and needs immediate replacement',
                'Fire alarm system is not working properly',
                'Emergency exit is blocked',
                'Smoke detector battery is low'
            ]),
        ]);
    }
}
