<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use App\Services\ImageUploadService;
use Illuminate\Support\Facades\Log;

class ProfileController extends Controller
{
    protected $imageUploadService;

    public function __construct(ImageUploadService $imageUploadService)
    {
        $this->imageUploadService = $imageUploadService;
    }

    public function index(): View
    {
        $user = Auth::user();
        return view('admin.profile.index', compact('user'));
    }

    /**
     * Update user profile information
     */
    public function updateProfile(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();

            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'email' => 'required|email|unique:users,email,' . $user->id,
                'phone' => 'nullable|string|max:20',
                'department' => 'nullable|string|max:100',
            ], [
                'name.required' => 'Nama harus diisi',
                'name.max' => 'Nama maksimal 255 karakter',
                'email.required' => 'Email harus diisi',
                'email.email' => 'Format email tidak valid',
                'email.unique' => 'Email sudah digunakan',
                'phone.max' => 'Nomor telepon maksimal 20 karakter',
                'department.max' => 'Departemen maksimal 100 karakter',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation Error',
                    'errors' => $validator->errors(),
                ], 422);
            }

            // Update user data
            $user->update([
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'department' => $request->department,
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
                'user_id' => Auth::id(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update user password
     */
    public function updatePassword(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'current_password' => 'required',
                'new_password' => 'required|string|min:6|confirmed',
            ], [
                'current_password.required' => 'Password saat ini harus diisi',
                'new_password.required' => 'Password baru harus diisi',
                'new_password.min' => 'Password baru minimal 6 karakter',
                'new_password.confirmed' => 'Konfirmasi password tidak cocok',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation Error',
                    'errors' => $validator->errors(),
                ], 422);
            }

            $user = Auth::user();

            // Check current password
            if (!Hash::check($request->current_password, $user->password)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Password saat ini salah',
                ], 400);
            }

            // Update password
            $user->update([
                'password' => Hash::make($request->new_password),
            ]);

            Log::info('Password changed successfully', ['user_id' => $user->id]);

            return response()->json([
                'success' => true,
                'message' => 'Password berhasil diubah',
            ]);
        } catch (\Exception $e) {
            Log::error('Update Password Exception', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Upload profile image
     */
    public function uploadProfileImage(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'profile_image' => 'required|image|mimes:jpeg,png,jpg,webp|max:5120', // 5MB max
            ], [
                'profile_image.required' => 'Foto profil harus dipilih',
                'profile_image.image' => 'File harus berupa gambar',
                'profile_image.mimes' => 'Format file harus jpeg, png, jpg, atau webp',
                'profile_image.max' => 'Ukuran file maksimal 5MB',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation Error',
                    'errors' => $validator->errors(),
                ], 422);
            }

            $user = Auth::user();

            // Delete old image if exists
            if ($user->profile_image && Storage::disk('public')->exists($user->profile_image)) {
                Storage::disk('public')->delete($user->profile_image);
            }

            // Upload new image
            $imagePath = $this->imageUploadService->uploadImage(
                $request->file('profile_image'),
                'profile_images',
                $user->id
            );

            $user->update(['profile_image' => $imagePath]);

            Log::info('Profile image uploaded successfully', [
                'user_id' => $user->id,
                'image_path' => $imagePath
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Foto profil berhasil diupload',
                'data' => [
                    'profile_image_url' => $this->getProfileImageUrl($imagePath),
                    'profile_image_path' => $imagePath,
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Upload Profile Image Exception', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Gagal mengupload foto profil: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Delete profile image
     */
    public function deleteProfileImage(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();

            if (!$user->profile_image) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tidak ada foto profil untuk dihapus',
                ], 404);
            }

            // Delete image file
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
                'user_id' => Auth::id(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get user profile data
     */
    public function getProfile(): JsonResponse
    {
        try {
            $user = Auth::user();

            return response()->json([
                'success' => true,
                'data' => $this->formatUserData($user),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Format user data for response
     */
    private function formatUserData($user): array
    {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role,
            'role_display' => $user->role_display,
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
    private function getProfileImageUrl($imagePath): ?string
    {
        if (!$imagePath) {
            return asset('admin/backend/dist/assets/images/users/avatar-1.jpg'); // Default avatar
        }

        return url('storage/' . $imagePath);
    }
}
