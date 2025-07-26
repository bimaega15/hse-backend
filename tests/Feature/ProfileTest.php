<?php
// tests/Feature/ProfileTest.php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
    }

    /** @test */
    public function user_can_get_their_profile()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->getJson('/api/profile');

        $response->assertOk()
            ->assertJsonStructure([
                'success',
                'data' => [
                    'id',
                    'name',
                    'email',
                    'role',
                    'department',
                    'phone',
                    'profile_image',
                    'profile_image_path',
                    'is_active',
                    'created_at',
                    'updated_at'
                ]
            ])
            ->assertJson([
                'success' => true,
                'data' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                ]
            ]);
    }

    /** @test */
    public function user_can_update_profile_without_image()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $newData = [
            'name' => 'Updated Name',
            'email' => 'updated@example.com',
            'phone' => '+628123456789',
            'department' => 'Updated Department'
        ];

        $response = $this->putJson('/api/profile', $newData);

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'message' => 'Profil berhasil diperbarui',
                'data' => [
                    'name' => 'Updated Name',
                    'email' => 'updated@example.com',
                    'phone' => '+628123456789',
                    'department' => 'Updated Department'
                ]
            ]);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'Updated Name',
            'email' => 'updated@example.com',
        ]);
    }

    /** @test */
    public function user_can_update_profile_with_image()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $image = UploadedFile::fake()->image('profile.jpg', 500, 500)->size(1000);

        $response = $this->postJson('/api/profile', [
            'name' => 'Updated Name',
            'email' => 'updated@example.com',
            'phone' => '+628123456789',
            'department' => 'Updated Department',
            'profile_image' => $image
        ]);

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'message' => 'Profil berhasil diperbarui'
            ])
            ->assertJsonStructure([
                'data' => [
                    'profile_image',
                    'profile_image_path'
                ]
            ]);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'Updated Name',
            'email' => 'updated@example.com',
        ]);

        // Assert image was stored
        $updatedUser = $user->fresh();
        $this->assertNotNull($updatedUser->profile_image);
        Storage::disk('public')->assertExists($updatedUser->profile_image);
    }

    /** @test */
    public function user_can_upload_profile_image_separately()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $image = UploadedFile::fake()->image('profile.jpg', 500, 500)->size(1000);

        $response = $this->postJson('/api/profile/image/upload', [
            'profile_image' => $image
        ]);

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'message' => 'Foto profil berhasil diupload'
            ])
            ->assertJsonStructure([
                'data' => [
                    'profile_image',
                    'profile_image_path'
                ]
            ]);

        $updatedUser = $user->fresh();
        $this->assertNotNull($updatedUser->profile_image);
        Storage::disk('public')->assertExists($updatedUser->profile_image);
    }

    /** @test */
    public function user_can_delete_profile_image()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        // First upload an image
        $image = UploadedFile::fake()->image('profile.jpg', 500, 500);
        $this->postJson('/api/profile/image/upload', [
            'profile_image' => $image
        ]);

        $user = $user->fresh();
        $this->assertNotNull($user->profile_image);

        // Then delete it
        $response = $this->deleteJson('/api/profile/image');

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'message' => 'Foto profil berhasil dihapus'
            ]);

        $user = $user->fresh();
        $this->assertNull($user->profile_image);
    }

    /** @test */
    public function cannot_delete_profile_image_when_none_exists()
    {
        $user = User::factory()->create(['profile_image' => null]);
        Sanctum::actingAs($user);

        $response = $this->deleteJson('/api/profile/image');

        $response->assertNotFound()
            ->assertJson([
                'success' => false,
                'message' => 'Tidak ada foto profil untuk dihapus'
            ]);
    }

    /** @test */
    public function profile_update_validates_required_fields()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->putJson('/api/profile', [
            'name' => '',
            'email' => 'invalid-email'
        ]);

        $response->assertUnprocessable()
            ->assertJson([
                'success' => false,
                'message' => 'Validation Error'
            ])
            ->assertJsonValidationErrors(['name', 'email']);
    }

    /** @test */
    public function profile_image_upload_validates_file_type()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $file = UploadedFile::fake()->create('document.pdf', 1000);

        $response = $this->postJson('/api/profile/image/upload', [
            'profile_image' => $file
        ]);

        $response->assertUnprocessable()
            ->assertJson([
                'success' => false,
                'message' => 'Validation Error'
            ])
            ->assertJsonValidationErrors(['profile_image']);
    }

    /** @test */
    public function profile_image_upload_validates_file_size()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        // Create a file larger than 5MB
        $largeImage = UploadedFile::fake()->image('large.jpg')->size(6000);

        $response = $this->postJson('/api/profile/image/upload', [
            'profile_image' => $largeImage
        ]);

        $response->assertUnprocessable()
            ->assertJson([
                'success' => false,
                'message' => 'Validation Error'
            ])
            ->assertJsonValidationErrors(['profile_image']);
    }

    /** @test */
    public function profile_image_upload_validates_image_dimensions()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        // Create an image smaller than minimum dimensions
        $smallImage = UploadedFile::fake()->image('small.jpg', 50, 50);

        $response = $this->postJson('/api/profile/image/upload', [
            'profile_image' => $smallImage
        ]);

        $response->assertUnprocessable()
            ->assertJson([
                'success' => false,
                'message' => 'Validation Error'
            ])
            ->assertJsonValidationErrors(['profile_image']);
    }

    /** @test */
    public function user_can_change_password()
    {
        $user = User::factory()->create([
            'password' => bcrypt('oldpassword')
        ]);
        Sanctum::actingAs($user);

        $response = $this->putJson('/api/change-password', [
            'current_password' => 'oldpassword',
            'new_password' => 'newpassword123',
            'new_password_confirmation' => 'newpassword123'
        ]);

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'message' => 'Password berhasil diubah'
            ]);
    }

    /** @test */
    public function change_password_validates_current_password()
    {
        $user = User::factory()->create([
            'password' => bcrypt('oldpassword')
        ]);
        Sanctum::actingAs($user);

        $response = $this->putJson('/api/change-password', [
            'current_password' => 'wrongpassword',
            'new_password' => 'newpassword123',
            'new_password_confirmation' => 'newpassword123'
        ]);

        $response->assertStatus(400)
            ->assertJson([
                'success' => false,
                'message' => 'Password saat ini salah'
            ]);
    }

    /** @test */
    public function old_profile_image_is_deleted_when_uploading_new_one()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        // Upload first image
        $firstImage = UploadedFile::fake()->image('first.jpg', 500, 500);
        $this->postJson('/api/profile/image/upload', [
            'profile_image' => $firstImage
        ]);

        $user = $user->fresh();
        $firstImagePath = $user->profile_image;
        Storage::disk('public')->assertExists($firstImagePath);

        // Upload second image
        $secondImage = UploadedFile::fake()->image('second.jpg', 500, 500);
        $this->postJson('/api/profile/image/upload', [
            'profile_image' => $secondImage
        ]);

        $user = $user->fresh();
        $secondImagePath = $user->profile_image;

        // Assert first image was deleted and second image exists
        Storage::disk('public')->assertMissing($firstImagePath);
        Storage::disk('public')->assertExists($secondImagePath);
        $this->assertNotEquals($firstImagePath, $secondImagePath);
    }

    /** @test */
    public function unauthenticated_user_cannot_access_profile_endpoints()
    {
        $endpoints = [
            ['GET', '/api/profile'],
            ['PUT', '/api/profile'],
            ['POST', '/api/profile/image/upload'],
            ['DELETE', '/api/profile/image'],
            ['PUT', '/api/change-password'],
        ];

        foreach ($endpoints as [$method, $endpoint]) {
            $response = $this->json($method, $endpoint);
            $response->assertUnauthorized();
        }
    }

    /** @test */
    public function user_cannot_update_email_to_existing_email()
    {
        $existingUser = User::factory()->create(['email' => 'existing@example.com']);
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->putJson('/api/profile', [
            'name' => 'Test User',
            'email' => 'existing@example.com',
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['email']);
    }
}
