<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Yajra\DataTables\Facades\DataTables;
use App\Services\ImageUploadService;

class UserController extends Controller
{
    protected $imageUploadService;

    public function __construct(ImageUploadService $imageUploadService)
    {
        $this->imageUploadService = $imageUploadService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        return view('admin.users.index');
    }

    /**
     * Get users data for DataTables
     */
    public function getData(Request $request): JsonResponse
    {
        try {
            $users = User::select(['id', 'name', 'email', 'role', 'department', 'phone', 'profile_image', 'is_active', 'created_at', 'updated_at']);

            // Filter by role if specified (from URL parameter or AJAX filter)
            $roleFilter = $request->get('role_filter') ?: $request->get('role');
            if ($roleFilter && $roleFilter !== 'all') {
                $users->where('role', $roleFilter);
            }

            // Filter by status if specified (from URL parameter or AJAX filter)
            $statusFilter = $request->get('status_filter') ?: $request->get('status');
            if ($statusFilter && $statusFilter !== 'all') {
                $users->where('is_active', $statusFilter === 'active' ? 1 : 0);
            }

            return DataTables::of($users)
                ->addIndexColumn()
                ->addColumn('avatar', function ($user) {
                    $avatarUrl = $user->profile_image
                        ? url('storage/' . $user->profile_image)
                        : asset('admin/backend/dist/assets/images/users/avatar-1.jpg');

                    return '<div class="avatar-sm">
                        <img src="' . $avatarUrl . '" alt="' . $user->name . '" class="img-fluid rounded-circle">
                    </div>';
                })
                ->addColumn('user_info', function ($user) {
                    return '<div>
                        <h6 class="mb-0">' . $user->name . '</h6>
                        <small class="text-muted">' . $user->email . '</small>
                    </div>';
                })
                ->addColumn('role_badge', function ($user) {
                    $roleConfig = [
                        'admin' => ['class' => 'danger', 'text' => 'Administrator'],
                        'hse_staff' => ['class' => 'warning', 'text' => 'HSE Staff'],
                        'employee' => ['class' => 'info', 'text' => 'Employee']
                    ];

                    $config = $roleConfig[$user->role] ?? ['class' => 'secondary', 'text' => ucfirst($user->role)];

                    return '<span class="badge bg-' . $config['class'] . '">' . $config['text'] . '</span>';
                })
                ->addColumn('department_info', function ($user) {
                    return $user->department ?? '<span class="text-muted">-</span>';
                })
                ->addColumn('contact_info', function ($user) {
                    return $user->phone ?? '<span class="text-muted">-</span>';
                })
                ->addColumn('status', function ($user) {
                    $statusClass = $user->is_active ? 'success' : 'danger';
                    $statusText = $user->is_active ? 'Active' : 'Inactive';
                    return '<span class="badge bg-' . $statusClass . '">' . $statusText . '</span>';
                })
                ->addColumn('created_at_formatted', function ($user) {
                    return $user->created_at->format('d M Y, H:i');
                })
                ->addColumn('action', function ($user) {
                    $currentUserId = auth()->id();
                    $isOwnAccount = $user->id == $currentUserId;

                    $actions = '
                        <div class="btn-group" role="group">
                            <button type="button" class="btn btn-sm btn-soft-info" onclick="viewUser(' . $user->id . ')" title="View">
                                <i class="ri-eye-line"></i>
                            </button>
                            <button type="button" class="btn btn-sm btn-soft-warning" onclick="editUser(' . $user->id . ')" title="Edit">
                                <i class="ri-edit-line"></i>
                            </button>';

                    if (!$isOwnAccount) {
                        $actions .= '
                            <button type="button" class="btn btn-sm btn-soft-' . ($user->is_active ? 'secondary' : 'success') . '" onclick="toggleUserStatus(' . $user->id . ')" title="' . ($user->is_active ? 'Deactivate' : 'Activate') . '">
                                <i class="ri-' . ($user->is_active ? 'pause' : 'play') . '-line"></i>
                            </button>
                            <button type="button" class="btn btn-sm btn-soft-danger" onclick="deleteUser(' . $user->id . ')" title="Delete">
                                <i class="ri-delete-bin-line"></i>
                            </button>';
                    }

                    $actions .= '</div>';

                    return $actions;
                })
                ->rawColumns(['avatar', 'user_info', 'role_badge', 'department_info', 'contact_info', 'status', 'action'])
                ->make(true);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to load users: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255|min:2',
                'email' => 'required|email|unique:users,email|max:255',
                'password' => 'required|string|min:6|confirmed',
                'role' => 'required|in:admin,hse_staff,employee',
                'department' => 'nullable|string|max:100',
                'phone' => 'nullable|string|max:20|regex:/^[\+]?[0-9\-\(\)\s]+$/',
                'profile_image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120',
                'is_active' => 'required|boolean'
            ], [
                'name.required' => 'Nama lengkap wajib diisi',
                'name.min' => 'Nama lengkap minimal 2 karakter',
                'name.max' => 'Nama lengkap maksimal 255 karakter',
                'email.required' => 'Email wajib diisi',
                'email.email' => 'Format email tidak valid',
                'email.unique' => 'Email sudah digunakan',
                'email.max' => 'Email maksimal 255 karakter',
                'password.required' => 'Password wajib diisi',
                'password.min' => 'Password minimal 6 karakter',
                'password.confirmed' => 'Konfirmasi password tidak cocok',
                'role.required' => 'Role wajib dipilih',
                'role.in' => 'Role tidak valid',
                'department.max' => 'Departemen maksimal 100 karakter',
                'phone.max' => 'Nomor telepon maksimal 20 karakter',
                'phone.regex' => 'Format nomor telepon tidak valid',
                'profile_image.image' => 'File harus berupa gambar',
                'profile_image.mimes' => 'Format gambar harus jpeg, png, jpg, atau webp',
                'profile_image.max' => 'Ukuran gambar maksimal 5MB',
                'is_active.required' => 'Status wajib dipilih'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation Error',
                    'errors' => $validator->errors(),
                ], 422);
            }

            $data = $validator->validated();
            $data['password'] = Hash::make($data['password']);

            // Handle profile image upload
            if ($request->hasFile('profile_image')) {
                $imagePath = $this->imageUploadService->uploadImage(
                    $request->file('profile_image'),
                    'profile_images'
                );
                $data['profile_image'] = $imagePath;
            }

            unset($data['password_confirmation']);
            $user = User::create($data);

            Log::info('User created successfully', [
                'user_id' => $user->id,
                'name' => $user->name,
                'role' => $user->role,
                'created_by' => auth()->id()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'User berhasil dibuat',
                'data' => $user
            ], 201);
        } catch (\Exception $e) {
            Log::error('User creation failed', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat membuat user: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id): JsonResponse
    {
        try {
            $user = User::find($id);

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User tidak ditemukan'
                ], 404);
            }

            // Get additional statistics
            $statistics = [
                'reports_count' => $user->reports()->count(),
                'assigned_reports_count' => $user->assignedReports()->count(),
                'unread_notifications_count' => $user->notifications()->whereNull('read_at')->count(),
            ];

            return response()->json([
                'success' => true,
                'data' => [
                    'user' => $user,
                    'statistics' => $statistics,
                    'profile_image_url' => $user->profile_image ? url('storage/' . $user->profile_image) : null,
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id): JsonResponse
    {
        try {
            $user = User::find($id);

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User tidak ditemukan'
                ], 404);
            }

            $rules = [
                'name' => 'required|string|max:255|min:2',
                'email' => 'required|email|unique:users,email,' . $id . '|max:255',
                'role' => 'required|in:admin,hse_staff,employee',
                'department' => 'nullable|string|max:100',
                'phone' => 'nullable|string|max:20|regex:/^[\+]?[0-9\-\(\)\s]+$/',
                'profile_image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120',
                'is_active' => 'required|boolean'
            ];

            // Add password validation if password is being updated
            if ($request->filled('password')) {
                $rules['password'] = 'required|string|min:6|confirmed';
            }

            $validator = Validator::make($request->all(), $rules, [
                'name.required' => 'Nama lengkap wajib diisi',
                'name.min' => 'Nama lengkap minimal 2 karakter',
                'name.max' => 'Nama lengkap maksimal 255 karakter',
                'email.required' => 'Email wajib diisi',
                'email.email' => 'Format email tidak valid',
                'email.unique' => 'Email sudah digunakan',
                'email.max' => 'Email maksimal 255 karakter',
                'password.min' => 'Password minimal 6 karakter',
                'password.confirmed' => 'Konfirmasi password tidak cocok',
                'role.required' => 'Role wajib dipilih',
                'role.in' => 'Role tidak valid',
                'department.max' => 'Departemen maksimal 100 karakter',
                'phone.max' => 'Nomor telepon maksimal 20 karakter',
                'phone.regex' => 'Format nomor telepon tidak valid',
                'profile_image.image' => 'File harus berupa gambar',
                'profile_image.mimes' => 'Format gambar harus jpeg, png, jpg, atau webp',
                'profile_image.max' => 'Ukuran gambar maksimal 5MB',
                'is_active.required' => 'Status wajib dipilih'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation Error',
                    'errors' => $validator->errors(),
                ], 422);
            }

            $data = $request->only(['name', 'email', 'role', 'department', 'phone', 'is_active']);

            // Handle password update
            if ($request->filled('password')) {
                $data['password'] = Hash::make($request->password);
            }

            // Handle profile image upload
            if ($request->hasFile('profile_image')) {
                // Delete old image if exists
                if ($user->profile_image && Storage::disk('public')->exists($user->profile_image)) {
                    Storage::disk('public')->delete($user->profile_image);
                }

                $imagePath = $this->imageUploadService->uploadImage(
                    $request->file('profile_image'),
                    'profile_images',
                    $user->id
                );
                $data['profile_image'] = $imagePath;
            }

            $user->update($data);

            Log::info('User updated successfully', [
                'user_id' => $user->id,
                'updated_by' => auth()->id()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'User berhasil diperbarui',
                'data' => $user
            ]);
        } catch (\Exception $e) {
            Log::error('User update failed', [
                'user_id' => $id,
                'error' => $e->getMessage(),
                'updated_by' => auth()->id()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat memperbarui user: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id): JsonResponse
    {
        try {
            $user = User::find($id);

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User tidak ditemukan'
                ], 404);
            }

            // Prevent deleting own account
            if ($user->id == auth()->id()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tidak dapat menghapus akun sendiri'
                ], 400);
            }

            // Check if user has related data
            $reportsCount = $user->reports()->count();
            $assignedReportsCount = $user->assignedReports()->count();

            if ($reportsCount > 0 || $assignedReportsCount > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'User tidak dapat dihapus karena masih memiliki data terkait (laporan: ' . $reportsCount . ', laporan yang ditugaskan: ' . $assignedReportsCount . ')'
                ], 400);
            }

            $userName = $user->name;

            // Delete profile image if exists
            if ($user->profile_image && Storage::disk('public')->exists($user->profile_image)) {
                Storage::disk('public')->delete($user->profile_image);
            }

            $user->delete();

            Log::info('User deleted successfully', [
                'user_id' => $id,
                'user_name' => $userName,
                'deleted_by' => auth()->id()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'User berhasil dihapus'
            ]);
        } catch (\Exception $e) {
            Log::error('User deletion failed', [
                'user_id' => $id,
                'error' => $e->getMessage(),
                'deleted_by' => auth()->id()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat menghapus user: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Toggle user status
     */
    public function toggleStatus($id): JsonResponse
    {
        try {
            $user = User::find($id);

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User tidak ditemukan'
                ], 404);
            }

            // Prevent deactivating own account
            if ($user->id == auth()->id()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tidak dapat mengubah status akun sendiri'
                ], 400);
            }

            $user->update(['is_active' => !$user->is_active]);

            $statusText = $user->is_active ? 'diaktifkan' : 'dinonaktifkan';

            Log::info('User status toggled', [
                'user_id' => $user->id,
                'new_status' => $user->is_active,
                'updated_by' => auth()->id()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Status user berhasil ' . $statusText,
                'data' => [
                    'id' => $user->id,
                    'is_active' => $user->is_active
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get users statistics
     */
    public function getStatistics(): JsonResponse
    {
        try {
            $statistics = [
                'total_users' => User::count(),
                'active_users' => User::where('is_active', true)->count(),
                'inactive_users' => User::where('is_active', false)->count(),
                'role_breakdown' => [
                    'admin' => User::where('role', 'admin')->count(),
                    'hse_staff' => User::where('role', 'hse_staff')->count(),
                    'employee' => User::where('role', 'employee')->count(),
                ],
                'recent_registrations' => User::where('created_at', '>=', now()->subDays(30))->count(),
            ];

            return response()->json([
                'success' => true,
                'data' => $statistics
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }
}
