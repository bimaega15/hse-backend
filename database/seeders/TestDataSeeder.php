<?php
// database/seeders/TestDataSeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Report;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;

class TestDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Creating test users with profile images...');

        // Create sample profile images directory
        if (!Storage::disk('public')->exists('profile_images')) {
            Storage::disk('public')->makeDirectory('profile_images');
        }

        // Create test employees
        $employees = [
            [
                'name' => 'John Doe',
                'email' => 'john.employee@test.com',
                'password' => Hash::make('password'),
                'role' => 'employee',
                'department' => 'IT Department',
                'phone' => '+62812345678',
                'is_active' => true,
            ],
            [
                'name' => 'Jane Smith',
                'email' => 'jane.employee@test.com',
                'password' => Hash::make('password'),
                'role' => 'employee',
                'department' => 'Production',
                'phone' => '+62812345679',
                'is_active' => true,
            ],
            [
                'name' => 'Bob Wilson',
                'email' => 'bob.employee@test.com',
                'password' => Hash::make('password'),
                'role' => 'employee',
                'department' => 'Quality Control',
                'phone' => '+62812345680',
                'is_active' => true,
            ],
        ];

        foreach ($employees as $index => $employeeData) {
            $user = User::create($employeeData);

            // Add sample profile image for some users
            if ($index < 2) {
                $this->createSampleProfileImage($user, $index + 1);
            }

            $this->command->info("Created employee: {$user->name}");
        }

        // Create test HSE staff
        $hseStaff = [
            [
                'name' => 'Sarah Johnson',
                'email' => 'sarah.hse@test.com',
                'password' => Hash::make('password'),
                'role' => 'hse_staff',
                'department' => 'Health, Safety & Environment',
                'phone' => '+62812345681',
                'is_active' => true,
            ],
            [
                'name' => 'Mike Brown',
                'email' => 'mike.hse@test.com',
                'password' => Hash::make('password'),
                'role' => 'hse_staff',
                'department' => 'Health, Safety & Environment',
                'phone' => '+62812345682',
                'is_active' => true,
            ],
        ];

        foreach ($hseStaff as $index => $staffData) {
            $user = User::create($staffData);

            // Add sample profile image
            $this->createSampleProfileImage($user, $index + 3);

            $this->command->info("Created HSE staff: {$user->name}");
        }

        // Create some test reports
        $this->createTestReports();

        $this->command->info('Test data seeded successfully!');
        $this->command->newLine();
        $this->command->info('Login credentials:');
        $this->command->info('Employee: john.employee@test.com / password');
        $this->command->info('HSE Staff: sarah.hse@test.com / password');
    }

    /**
     * Create sample profile image for user
     */
    private function createSampleProfileImage(User $user, int $imageNumber): void
    {
        try {
            // Create a simple colored rectangle as sample image
            $width = 400;
            $height = 400;
            $image = imagecreatetruecolor($width, $height);

            // Define colors for different users
            $colors = [
                [70, 130, 180],   // Steel Blue
                [220, 20, 60],    // Crimson
                [32, 178, 170],   // Light Sea Green
                [255, 140, 0],    // Dark Orange
                [138, 43, 226],   // Blue Violet
            ];

            $colorIndex = ($imageNumber - 1) % count($colors);
            $bgColor = imagecolorallocate($image, ...$colors[$colorIndex]);
            $textColor = imagecolorallocate($image, 255, 255, 255);

            // Fill background
            imagefill($image, 0, 0, $bgColor);

            // Add user initials
            $initials = $this->getUserInitials($user->name);
            $fontSize = 80;
            $fontPath = $this->getSystemFont();

            if ($fontPath) {
                // Calculate text position for centering
                $bbox = imagettfbbox($fontSize, 0, $fontPath, $initials);
                $x = ($width - $bbox[4]) / 2;
                $y = ($height - $bbox[5]) / 2 + $fontSize;

                imagettftext($image, $fontSize, 0, $x, $y, $textColor, $fontPath, $initials);
            } else {
                // Fallback to built-in font
                $x = ($width - strlen($initials) * 20) / 2;
                $y = ($height - 20) / 2;
                imagestring($image, 5, $x, $y, $initials, $textColor);
            }

            // Save image
            $filename = "profile_{$user->id}_" . time() . "_sample.jpg";
            $filepath = storage_path("app/public/profile_images/{$filename}");

            imagejpeg($image, $filepath, 85);
            imagedestroy($image);

            // Update user record
            $user->update([
                'profile_image' => "profile_images/{$filename}"
            ]);

            $this->command->info("  - Created profile image for {$user->name}");
        } catch (\Exception $e) {
            $this->command->warn("  - Failed to create profile image for {$user->name}: {$e->getMessage()}");
        }
    }

    /**
     * Get user initials from name
     */
    private function getUserInitials(string $name): string
    {
        $words = explode(' ', trim($name));
        $initials = '';

        foreach ($words as $word) {
            if (!empty($word)) {
                $initials .= strtoupper($word[0]);
                if (strlen($initials) >= 2) break;
            }
        }

        return $initials ?: 'U';
    }

    /**
     * Get system font path (if available)
     */
    private function getSystemFont(): ?string
    {
        $fonts = [
            '/System/Library/Fonts/Arial.ttf', // macOS
            '/usr/share/fonts/truetype/dejavu/DejaVuSans-Bold.ttf', // Ubuntu
            '/Windows/Fonts/arial.ttf', // Windows
            '/usr/share/fonts/TTF/arial.ttf', // CentOS
        ];

        foreach ($fonts as $font) {
            if (file_exists($font)) {
                return $font;
            }
        }

        return null;
    }

    /**
     * Create some test reports
     */
    private function createTestReports(): void
    {
        $employees = User::where('role', 'employee')->get();
        $hseStaff = User::where('role', 'hse_staff')->get();

        if ($employees->isEmpty() || $hseStaff->isEmpty()) {
            return;
        }

        $reportData = [
            [
                'category' => 'Life Safety Equipment',
                'equipment_type' => 'Fire Extinguisher',
                'contributing_factor' => 'Defective machinery/equipment',
                'description' => 'Fire extinguisher pressure gauge shows empty. Needs immediate replacement.',
                'location' => 'Production Floor A - Station 3',
                'status' => 'waiting',
            ],
            [
                'category' => 'PPE (Personal Protective Equipment)',
                'equipment_type' => 'Safety Helmet',
                'contributing_factor' => 'Improper use of equipment',
                'description' => 'Employee not wearing safety helmet in construction zone.',
                'location' => 'Construction Site B',
                'status' => 'in-progress',
                'hse_staff_id' => $hseStaff->first()->id,
                'start_process_at' => now()->subDays(2),
            ],
            [
                'category' => 'Work Environment',
                'equipment_type' => 'Ventilation System',
                'contributing_factor' => 'Environmental factors',
                'description' => 'Poor ventilation causing dust accumulation in workspace.',
                'location' => 'Workshop Area C',
                'status' => 'done',
                'hse_staff_id' => $hseStaff->last()->id,
                'start_process_at' => now()->subDays(5),
                'completed_at' => now()->subDays(1),
            ],
        ];

        foreach ($reportData as $index => $data) {
            $employee = $employees->random();

            Report::create([
                'employee_id' => $employee->id,
                ...$data
            ]);

            $this->command->info("Created test report: {$data['description']}");
        }
    }
}
