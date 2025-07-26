<?php
// app/Http/Controllers/API/AuthController.php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\ImageUploadService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class AuthController extends Controller
{
    protected $imageUploadService;

    public function __construct(ImageUploadService $imageUploadService)
    {
        $this->imageUploadService = $imageUploadService;
    }

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string|min:6',
            'role' => 'required|in:employee,hse_staff',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation Error',
                'errors' => $validator->errors(),
            ], 422);
        }

        $credentials = $request->only('email', 'password');

        if (Auth::attempt($credentials)) {
            $user = Auth::user();

            // Check if role matches
            if ($user->role !== $request->role) {
                return response()->json([
                    'success' => false,
                    'message' => 'Role tidak sesuai',
                ], 401);
            }

            // Check if user is active
            if (!$user->is_active) {
                return response()->json([
                    'success' => false,
                    'message' => 'Akun Anda tidak aktif',
                ], 401);
            }

            $token = $user->createToken('HSE-App')->plainTextToken;

            return response()->json([
                'success' => true,
                'message' => 'Login berhasil',
                'data' => [
                    'user' => $this->formatUserData($user),
                    'token' => $token,
                ],
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Email atau password salah',
        ], 401);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Logout berhasil',
        ]);
    }

    public function profile(Request $request)
    {
        return response()->json([
            'success' => true,
            'data' => $this->formatUserData($request->user()),
        ]);
    }

    public function updateProfile(Request $request)
    {
        try {
            $user = $request->user();

            // Handle both JSON and form-data
            $inputData = $request->isJson() ? $request->json()->all() : $request->all();

            Log::info('Update Profile Request', [
                'user_id' => $user->id,
                'is_json' => $request->isJson(),
                'has_file' => $request->hasFile('profile_image'),
                'input_data' => $inputData
            ]);

            // Validate basic profile data
            $validator = Validator::make($inputData, [
                'name' => 'required|string|max:255',
                'email' => 'required|email|unique:users,email,' . $user->id,
                'phone' => 'nullable|string|max:20',
                'department' => 'nullable|string|max:100',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation Error',
                    'errors' => $validator->errors(),
                ], 422);
            }

            // Handle profile image upload if present
            $profileImagePath = $user->profile_image;
            if ($request->hasFile('profile_image')) {
                try {
                    $profileImagePath = $this->handleProfileImageUpload($request->file('profile_image'), $user);
                } catch (\Exception $e) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Gagal mengupload foto profil: ' . $e->getMessage(),
                    ], 500);
                }
            }

            // Update user data
            $user->update([
                'name' => $inputData['name'],
                'email' => $inputData['email'],
                'phone' => $inputData['phone'] ?? $user->phone,
                'department' => $inputData['department'] ?? $user->department,
                'profile_image' => $profileImagePath,
            ]);

            Log::info('Profile updated successfully', ['user_id' => $user->id]);

            return response()->json([
                'success' => true,
                'message' => 'Profil berhasil diperbarui',
                'data' => $this->formatUserData($user->fresh()),
            ]);
        } catch (\Exception $e) {
            Log::error('Update Profile Exception', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Internal server error: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Upload profile image only
     */
    public function uploadProfileImage(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'profile_image' => 'required|image|mimes:jpeg,png,jpg,webp|max:5120', // 5MB max
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation Error',
                    'errors' => $validator->errors(),
                ], 422);
            }

            $user = $request->user();

            try {
                $imagePath = $this->handleProfileImageUpload($request->file('profile_image'), $user);

                $user->update(['profile_image' => $imagePath]);

                Log::info('Profile image uploaded successfully', [
                    'user_id' => $user->id,
                    'image_path' => $imagePath
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Foto profil berhasil diupload',
                    'data' => [
                        'profile_image' => $this->getProfileImageUrl($imagePath),
                        'profile_image_path' => $imagePath,
                    ],
                ]);
            } catch (\Exception $e) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal mengupload foto profil: ' . $e->getMessage(),
                ], 500);
            }
        } catch (\Exception $e) {
            Log::error('Upload Profile Image Exception', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Internal server error: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Delete profile image
     */
    public function deleteProfileImage(Request $request)
    {
        try {
            $user = $request->user();

            if (!$user->profile_image) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tidak ada foto profil untuk dihapus',
                ], 404);
            }

            // Delete old image file
            if (Storage::disk('public')->exists($user->profile_image)) {
                Storage::disk('public')->delete($user->profile_image);
            }

            // Update user record
            $user->update(['profile_image' => null]);

            Log::info('Profile image deleted successfully', ['user_id' => $user->id]);

            return response()->json([
                'success' => true,
                'message' => 'Foto profil berhasil dihapus',
                'data' => $this->formatUserData($user->fresh()),
            ]);
        } catch (\Exception $e) {
            Log::error('Delete Profile Image Exception', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Internal server error: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function changePassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'current_password' => 'required',
            'new_password' => 'required|string|min:6|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation Error',
                'errors' => $validator->errors(),
            ], 422);
        }

        $user = $request->user();

        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Password saat ini salah',
            ], 400);
        }

        $user->update([
            'password' => Hash::make($request->new_password),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Password berhasil diubah',
        ]);
    }

    /**
     * Handle profile image upload
     */
    private function handleProfileImageUpload($file, $user)
    {
        // Validate file
        $validator = Validator::make(['profile_image' => $file], [
            'profile_image' => 'required|image|mimes:jpeg,png,jpg,webp|max:5120',
        ]);

        if ($validator->fails()) {
            throw new \Exception('File validation failed: ' . $validator->errors()->first());
        }

        // Delete old image if exists
        if ($user->profile_image && Storage::disk('public')->exists($user->profile_image)) {
            Storage::disk('public')->delete($user->profile_image);
        }

        // Upload new image using ImageUploadService
        return $this->imageUploadService->uploadImage($file, 'profile_images', $user->id);
    }

    /**
     * Format user data for response
     */
    private function formatUserData($user)
    {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role,
            'department' => $user->department,
            'phone' => $user->phone,
            'profile_image' => $this->getProfileImageUrl($user->profile_image),
            'profile_image_path' => $user->profile_image,
            'is_active' => $user->is_active,
            'created_at' => $user->created_at,
            'updated_at' => $user->updated_at,
        ];
    }

    /**
     * Get profile image URL
     */
    private function getProfileImageUrl($imagePath)
    {
        return $imagePath ? url('storage/' . $imagePath) : null;
    }
}
