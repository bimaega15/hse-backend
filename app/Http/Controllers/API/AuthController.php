<?php
// app/Http/Controllers/API/AuthController.php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string|min:6',
            'role' => 'required|in:employee,hse_staff',
        ]);

        if ($validator->fails()) {
            return response()->json(
                [
                    'success' => false,
                    'message' => 'Validation Error',
                    'errors' => $validator->errors(),
                ],
                422,
            );
        }

        $credentials = $request->only('email', 'password');

        if (Auth::attempt($credentials)) {
            $user = Auth::user();

            // Check if role matches
            if ($user->role !== $request->role) {
                return response()->json(
                    [
                        'success' => false,
                        'message' => 'Role tidak sesuai',
                    ],
                    401,
                );
            }

            // Check if user is active
            if (!$user->is_active) {
                return response()->json(
                    [
                        'success' => false,
                        'message' => 'Akun Anda tidak aktif',
                    ],
                    401,
                );
            }

            $token = $user->createToken('HSE-App')->plainTextToken;

            return response()->json([
                'success' => true,
                'message' => 'Login berhasil',
                'data' => [
                    'user' => [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                        'role' => $user->role,
                        'department' => $user->department,
                        'phone' => $user->phone,
                        'profile_image' => $user->profile_image,
                    ],
                    'token' => $token,
                ],
            ]);
        }

        return response()->json(
            [
                'success' => false,
                'message' => 'Email atau password salah',
            ],
            401,
        );
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
            'data' => [
                'id' => $request->user()->id,
                'name' => $request->user()->name,
                'email' => $request->user()->email,
                'role' => $request->user()->role,
                'department' => $request->user()->department,
                'phone' => $request->user()->phone,
                'profile_image' => $request->user()->profile_image,
            ],
        ]);
    }

    public function updateProfile(Request $request)
    {
        try {
            // Handle both JSON and form-data
            $inputData = [];

            if ($request->isJson()) {
                // JSON request
                $inputData = $request->json()->all();
            } else {
                // Form data request
                $inputData = $request->all();
            }

            // Debug logging
            \Log::info('Update Profile Input', [
                'is_json' => $request->isJson(),
                'content_type' => $request->header('Content-Type'),
                'method' => $request->method(),
                'input_data' => $inputData,
                'has_file' => $request->hasFile('profile_image'),
            ]);

            // Validate input
            $validator = Validator::make($inputData, [
                'name' => 'required|string|max:255',
                'email' => 'required|email|unique:users,email,' . $request->user()->id,
                'phone' => 'nullable|string|max:20',
                'department' => 'nullable|string|max:100',
            ]);

            // Validate file separately if exists
            if ($request->hasFile('profile_image')) {
                $fileValidator = Validator::make($request->all(), [
                    'profile_image' => 'required|image|mimes:jpeg,png,jpg|max:2048',
                ]);

                if ($fileValidator->fails()) {
                    return response()->json(
                        [
                            'success' => false,
                            'message' => 'File validation error',
                            'errors' => $fileValidator->errors(),
                        ],
                        422,
                    );
                }
            }

            if ($validator->fails()) {
                \Log::error('Update Profile Validation Failed', [
                    'errors' => $validator->errors(),
                    'input' => $inputData,
                ]);

                return response()->json(
                    [
                        'success' => false,
                        'message' => 'Validation Error',
                        'errors' => $validator->errors(),
                    ],
                    422,
                );
            }

            $user = $request->user();

            // Get validated data
            $data = [];
            $data['name'] = $inputData['name'] ?? $user->name;
            $data['email'] = $inputData['email'] ?? $user->email;
            $data['phone'] = $inputData['phone'] ?? $user->phone;
            $data['department'] = $inputData['department'] ?? $user->department;

            // Handle profile image upload
            if ($request->hasFile('profile_image')) {
                try {
                    // Delete old image if exists
                    if ($user->profile_image && file_exists(public_path('storage/' . $user->profile_image))) {
                        unlink(public_path('storage/' . $user->profile_image));
                    }

                    $image = $request->file('profile_image');
                    $imageName = time() . '_' . $user->id . '.' . $image->getClientOriginalExtension();
                    $imagePath = $image->storeAs('profile_images', $imageName, 'public');
                    $data['profile_image'] = $imagePath;

                    \Log::info('Profile image uploaded', ['path' => $imagePath]);
                } catch (\Exception $e) {
                    \Log::error('Profile image upload failed', ['error' => $e->getMessage()]);

                    return response()->json(
                        [
                            'success' => false,
                            'message' => 'Failed to upload profile image: ' . $e->getMessage(),
                        ],
                        500,
                    );
                }
            }

            // Update user
            $user->update($data);

            \Log::info('Profile updated successfully', ['user_id' => $user->id]);

            return response()->json([
                'success' => true,
                'message' => 'Profil berhasil diperbarui',
                'data' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role,
                    'department' => $user->department,
                    'phone' => $user->phone,
                    'profile_image' => $user->profile_image ? url('storage/' . $user->profile_image) : null,
                ],
            ]);
        } catch (\Exception $e) {
            \Log::error('Update Profile Exception', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json(
                [
                    'success' => false,
                    'message' => 'Internal server error: ' . $e->getMessage(),
                ],
                500,
            );
        }
    }

    public function changePassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'current_password' => 'required',
            'new_password' => 'required|string|min:6|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json(
                [
                    'success' => false,
                    'message' => 'Validation Error',
                    'errors' => $validator->errors(),
                ],
                422,
            );
        }

        $user = $request->user();

        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json(
                [
                    'success' => false,
                    'message' => 'Password saat ini salah',
                ],
                400,
            );
        }

        $user->update([
            'password' => Hash::make($request->new_password),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Password berhasil diubah',
        ]);
    }
}
