<?php
// app/Http/Controllers/API/ReportController.php (Updated - Added Base64 Image Support)

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Report;
use App\Models\User;
use App\Traits\ApiResponseTrait;
use App\Http\Requests\StoreReportRequest;
use App\Models\Banner;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class ReportController extends Controller
{
    use ApiResponseTrait;
    /**
     * Display a listing of reports with filtering, search, and pagination
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $query = Report::with([
            'employee',
            'hseStaff',
            'categoryMaster',
            'contributingMaster',
            'actionMaster'
        ]);

        // Filter by user role
        if ($user->role === 'employee') {
            $query->where('employee_id', $user->id);
        } elseif ($user->role === 'hse_staff') {
            // HSE staff can see all reports or assigned reports
            if ($request->filter === 'assigned') {
                $query->where('hse_staff_id', $user->id);
            }
        }

        // Filter by status
        if ($request->has('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        // Filter by severity
        if ($request->has('severity') && $request->severity !== 'all') {
            $query->where('severity_rating', $request->severity);
        }

        // Filter by category
        if ($request->has('category_id') && $request->category_id !== 'all') {
            $query->where('category_id', $request->category_id);
        }

        // Filter by contributing
        if ($request->has('contributing_id') && $request->contributing_id !== 'all') {
            $query->where('contributing_id', $request->contributing_id);
        }

        // Search functionality
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('description', 'like', "%{$search}%")
                    ->orWhere('location', 'like', "%{$search}%")
                    ->orWhere('action_taken', 'like', "%{$search}%")
                    ->orWhereHas('employee', function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%");
                    })
                    ->orWhereHas('categoryMaster', function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%");
                    })
                    ->orWhereHas('contributingMaster', function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%");
                    })
                    ->orWhereHas('actionMaster', function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%");
                    });
            });
        }

        // Sort
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $reports = $query->paginate($request->get('per_page', 10));

        return response()->json([
            'success' => true,
            'data' => $reports,
        ]);
    }

    /**
     * Store a newly created report
     */
    public function store(StoreReportRequest $request)
    {
        try {
            $user = $request->user();

            // Check if user is employee
            if ($user->role !== 'employee') {
                return response()->json([
                    'success' => false,
                    'message' => 'Hanya karyawan yang dapat membuat laporan'
                ], 403);
            }

            // Prepare report data
            $reportData = [
                'employee_id' => $user->id,
                'category_id' => $request->category_id,
                'contributing_id' => $request->contributing_id,
                'action_id' => $request->action_id,
                'severity_rating' => $request->severity_rating,
                'action_taken' => $request->action_taken,
                'description' => $request->description,
                'location' => $request->location,
                'status' => 'waiting'
            ];

            // Handle image uploads (both file uploads and base64)
            if ($request->has('images') && !empty($request->images)) {
                $imagePaths = $this->handleImages($request);
                $reportData['images'] = $imagePaths;
            }

            // Create the report
            $report = Report::create($reportData);
            $report->load([
                'employee',
                'categoryMaster',
                'contributingMaster',
                'actionMaster'
            ]);

            Log::info('Report created successfully', [
                'report_id' => $report->id,
                'employee_id' => $user->id,
                'category_id' => $request->category_id,
                'severity_rating' => $request->severity_rating,
                'image_count' => count($reportData['images'] ?? [])
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Laporan berhasil dibuat',
                'data' => $report
            ], 201);
        } catch (\Exception $e) {
            Log::error('Report creation failed', [
                'user_id' => $request->user()->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat membuat laporan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified report
     */
    public function show(Request $request, $id)
    {
        $user = $request->user();

        $report = Report::with([
            'employee',
            'hseStaff',
            'categoryMaster',
            'contributingMaster',
            'actionMaster',
            'reportDetails' => function ($query) {
                $query->with(['approvedBy', 'createdBy'])
                    ->orderBy('due_date', 'asc')
                    ->orderBy('created_at', 'desc');
            }
        ])->find($id);

        if (!$report) {
            return response()->json([
                'success' => false,
                'message' => 'Laporan tidak ditemukan',
            ], 404);
        }

        // Check access permissions
        if ($user->role === 'employee' && $report->employee_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak memiliki akses ke laporan ini',
            ], 403);
        }

        // Add computed attributes for report details
        $report->report_details_count = $report->reportDetails->count();
        $report->open_details_count = $report->reportDetails->where('status_car', 'open')->count();
        $report->in_progress_details_count = $report->reportDetails->where('status_car', 'in_progress')->count();
        $report->closed_details_count = $report->reportDetails->where('status_car', 'closed')->count();
        $report->overdue_details_count = $report->reportDetails->where('due_date', '<', now())
            ->where('status_car', '!=', 'closed')
            ->count();

        // Calculate completion percentage
        $totalDetails = $report->reportDetails->count();
        $closedDetails = $report->reportDetails->where('status_car', 'closed')->count();
        $report->completion_percentage = $totalDetails > 0 ? round(($closedDetails / $totalDetails) * 100, 2) : 0;

        // Add status badges/labels for frontend
        $report->can_have_details = $report->canHaveReportDetails();
        $report->has_overdue_details = $report->hasOverdueReportDetails();

        return response()->json([
            'success' => true,
            'data' => $report,
            'message' => 'Detail laporan berhasil diambil'
        ]);
    }

    /**
     * Update the specified report
     */
    public function update(Request $request, $id)
    {
        try {
            $report = Report::with(['employee'])->find($id);

            if (!$report) {
                return response()->json([
                    'success' => false,
                    'message' => 'Laporan tidak ditemukan'
                ], 404);
            }

            $user = $request->user();

            // Authorization check: Only employee who created the report can update
            if ($user->role !== 'employee' || $report->employee_id !== $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda hanya dapat memperbarui laporan yang Anda buat sendiri.'
                ], 403);
            }

            // Status check: Only reports with 'waiting' status can be updated
            if ($report->status !== 'waiting') {
                return response()->json([
                    'success' => false,
                    'message' => 'Laporan tidak dapat diperbarui. Status sudah berubah dari \'waiting\'.'
                ], 400);
            }

            // Enhanced validation for mixed image types
            $validator = $this->validateReportData($request->all(), true);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation Error',
                    'errors' => $validator->errors(),
                    'error_code' => 'VALIDATION_ERROR'
                ], 422);
            }

            // Prepare update data
            $updateData = $request->only([
                'category_id',
                'contributing_id',
                'action_id',
                'severity_rating',
                'action_taken',
                'description',
                'location'
            ]);

            // Handle image updates dengan logic baru
            if ($request->has('images')) {
                $updateData['images'] = $this->handleImageUpdates($request, $report);
            }

            // Update the report
            $report->update($updateData);
            $report->load([
                'employee',
                'hseStaff',
                'categoryMaster',
                'contributingMaster',
                'actionMaster'
            ]);

            Log::info('Report updated successfully', [
                'report_id' => $report->id,
                'updated_by' => $user->id,
                'updated_fields' => array_keys($updateData),
                'image_count' => count($updateData['images'] ?? [])
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Laporan berhasil diperbarui',
                'data' => $report
            ]);
        } catch (\Exception $e) {
            Log::error('Report update failed', [
                'report_id' => $id,
                'user_id' => $request->user()->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat memperbarui laporan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified report from storage
     */
    public function destroy($id)
    {
        try {
            $report = Report::with(['employee'])->find($id);

            if (!$report) {
                return response()->json([
                    'success' => false,
                    'message' => 'Laporan tidak ditemukan'
                ], 404);
            }

            $user = request()->user();

            // Authorization check: Only employee who created the report can delete
            if ($user->role !== 'employee' || $report->employee_id !== $user->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda hanya dapat menghapus laporan yang Anda buat sendiri.'
                ], 403);
            }

            // Status check: Only reports with 'waiting' status can be deleted
            if ($report->status !== 'waiting') {
                return response()->json([
                    'success' => false,
                    'message' => 'Laporan tidak dapat dihapus. Status sudah berubah dari \'waiting\'.'
                ], 400);
            }

            // Delete associated images from storage
            $this->deleteReportImages($report);

            // Delete the report from database
            $report->delete();

            Log::info('Report deleted successfully', [
                'report_id' => $report->id,
                'deleted_by' => $user->id,
                'category_id' => $report->category_id,
                'location' => $report->location
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Laporan berhasil dihapus'
            ]);
        } catch (\Exception $e) {
            Log::error('Report deletion failed', [
                'report_id' => $id,
                'user_id' => request()->user()->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat menghapus laporan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * HSE Staff starts processing a report
     */
    public function startProcess(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'action_taken' => 'nullable|string|max:1000'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation Error',
                'errors' => $validator->errors(),
            ], 422);
        }

        $report = Report::find($id);

        if (!$report) {
            return response()->json([
                'success' => false,
                'message' => 'Laporan tidak ditemukan',
            ], 404);
        }

        if ($report->status !== 'waiting') {
            return response()->json([
                'success' => false,
                'message' => 'Laporan harus dalam status waiting',
            ], 400);
        }

        if ($request->user()->role !== 'hse_staff') {
            return response()->json([
                'success' => false,
                'message' => 'Hanya HSE staff yang dapat memproses laporan',
            ], 403);
        }

        // Update report status and assign HSE staff
        $updateData = [
            'status' => 'in-progress',
            'start_process_at' => now(),
            'hse_staff_id' => $request->user()->id,
        ];

        // Add action taken if provided
        if ($request->filled('action_taken')) {
            $updateData['action_taken'] = $request->action_taken;
        }

        $report->update($updateData);
        $report->load([
            'employee',
            'hseStaff',
            'categoryMaster',
            'contributingMaster',
            'actionMaster'
        ]);

        Log::info('Report processing started', [
            'report_id' => $report->id,
            'hse_staff_id' => $request->user()->id,
            'severity_rating' => $report->severity_rating
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Laporan mulai diproses',
            'data' => $report,
        ]);
    }

    /**
     * HSE Staff completes a report
     */
    public function complete(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'action_taken' => 'required|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation Error',
                'errors' => $validator->errors(),
            ], 422);
        }

        $report = Report::find($id);

        if (!$report) {
            return response()->json([
                'success' => false,
                'message' => 'Laporan tidak ditemukan',
            ], 404);
        }

        if ($report->status !== 'in-progress') {
            return response()->json([
                'success' => false,
                'message' => 'Laporan harus dalam status in-progress',
            ], 400);
        }

        if ($request->user()->role !== 'hse_staff') {
            return response()->json([
                'success' => false,
                'message' => 'Hanya HSE staff yang dapat menyelesaikan laporan',
            ], 403);
        }

        // Update report status and action taken
        $report->update([
            'status' => 'done',
            'completed_at' => now(),
            'action_taken' => $request->action_taken,
        ]);

        $report->load([
            'employee',
            'hseStaff',
            'categoryMaster',
            'contributingMaster',
            'actionMaster'
        ]);

        Log::info('Report completed', [
            'report_id' => $report->id,
            'hse_staff_id' => $request->user()->id,
            'completion_time_hours' => $report->processing_time_hours
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Laporan berhasil diselesaikan',
            'data' => $report,
        ]);
    }

    /**
     * Get reports statistics
     */
    public function statistics(Request $request)
    {
        $user = $request->user();
        $query = Report::query();

        // Filter by user role
        if ($user->role === 'employee') {
            $query->where('employee_id', $user->id);
        }

        $totalReports = $query->count();
        $waitingReports = (clone $query)->where('status', 'waiting')->count();
        $inProgressReports = (clone $query)->where('status', 'in-progress')->count();
        $completedReports = (clone $query)->where('status', 'done')->count();

        // Severity statistics
        $severityStats = (clone $query)
            ->selectRaw('severity_rating, COUNT(*) as count')
            ->groupBy('severity_rating')
            ->pluck('count', 'severity_rating')
            ->toArray();

        // Category statistics with master data
        $categoryStats = (clone $query)
            ->join('categories', 'reports.category_id', '=', 'categories.id')
            ->selectRaw('categories.name as category_name, COUNT(*) as count')
            ->groupBy('categories.id', 'categories.name')
            ->pluck('count', 'category_name')
            ->toArray();

        // Monthly reports data
        $monthlyData = $this->getMonthlyReportsData($user);

        return response()->json([
            'success' => true,
            'data' => [
                'total_reports' => $totalReports,
                'waiting_reports' => $waitingReports,
                'in_progress_reports' => $inProgressReports,
                'completed_reports' => $completedReports,
                'completion_rate' => $totalReports > 0 ? round(($completedReports / $totalReports) * 100, 1) : 0,
                'severity_statistics' => $severityStats,
                'category_statistics' => $categoryStats,
                'monthly_data' => $monthlyData,
                'average_completion_time' => $this->getAverageCompletionTime($user),
            ],
        ]);
    }

    /**
     * Handle images (both file uploads and base64 images)
     */
    private function handleImages(Request $request): array
    {
        $imagePaths = [];
        $images = $request->images;

        if (!is_array($images)) {
            return $imagePaths;
        }

        foreach ($images as $index => $image) {
            try {
                if (is_string($image)) {
                    // Handle base64 image
                    $path = $this->saveBase64Image($image);
                    if ($path) {
                        $imagePaths[] = $path;
                    }
                } elseif ($image instanceof \Illuminate\Http\UploadedFile) {
                    // Handle file upload
                    if ($image->isValid()) {
                        $path = $this->saveUploadedFile($image);
                        if ($path) {
                            $imagePaths[] = $path;
                        }
                    }
                }
            } catch (\Exception $e) {
                Log::warning('Failed to process image', [
                    'index' => $index,
                    'type' => is_string($image) ? 'base64' : 'file',
                    'error' => $e->getMessage()
                ]);
                // Continue processing other images
            }
        }

        return $imagePaths;
    }

    /**
     * Handle image updates - mempertahankan file existing dan upload file baru
     */
    private function handleImageUpdates(Request $request, Report $report): array
    {
        $currentImages = $report->images ?? [];
        $newImages = $request->images ?? [];

        if (empty($newImages)) {
            // Jika images kosong, hapus semua file lama
            foreach ($currentImages as $image) {
                Storage::disk('public')->delete($image);
            }
            return [];
        }

        $finalImages = [];
        $filesToDelete = [];

        // Pisahkan antara file path existing dan base64 baru
        $existingPaths = [];
        $base64Images = [];

        foreach ($newImages as $image) {
            if (is_string($image)) {
                // Cek apakah ini path file existing atau base64
                if (strpos($image, 'report_images/') === 0) {
                    // Ini adalah path file existing
                    $existingPaths[] = $image;
                } elseif ($this->isBase64Image($image)) {
                    // Ini adalah base64 image
                    $base64Images[] = $image;
                }
            } elseif ($image instanceof \Illuminate\Http\UploadedFile) {
                // Handle file upload langsung
                $base64Images[] = $image;
            }
        }

        // Tentukan file mana yang perlu dihapus
        foreach ($currentImages as $currentImage) {
            if (!in_array($currentImage, $existingPaths)) {
                $filesToDelete[] = $currentImage;
            }
        }

        // Hapus file yang tidak ada di payload
        foreach ($filesToDelete as $fileToDelete) {
            Storage::disk('public')->delete($fileToDelete);
            Log::info('Deleted old image file', ['file' => $fileToDelete]);
        }

        // Tambahkan file existing yang dipertahankan
        $finalImages = array_merge($finalImages, $existingPaths);

        // Upload file/base64 baru
        foreach ($base64Images as $newImage) {
            try {
                if ($newImage instanceof \Illuminate\Http\UploadedFile) {
                    // Handle file upload
                    $path = $this->saveUploadedFile($newImage);
                    if ($path) {
                        $finalImages[] = $path;
                    }
                } else {
                    // Handle base64 image
                    $path = $this->saveBase64Image($newImage);
                    if ($path) {
                        $finalImages[] = $path;
                    }
                }
            } catch (\Exception $e) {
                Log::warning('Failed to process new image', [
                    'type' => $newImage instanceof \Illuminate\Http\UploadedFile ? 'file' : 'base64',
                    'error' => $e->getMessage()
                ]);
                // Continue processing other images
            }
        }

        return $finalImages;
    }

    /**
     * Save base64 image to storage
     */
    private function saveBase64Image(string $base64Data): ?string
    {
        try {
            // Remove data:image/...;base64, prefix if present
            if (strpos($base64Data, ',') !== false) {
                $base64Data = explode(',', $base64Data)[1];
            }

            // Decode base64
            $imageData = base64_decode($base64Data);

            if ($imageData === false) {
                throw new \Exception('Invalid base64 data');
            }

            // Validate image and get info
            $imageInfo = $this->validateBase64Image($imageData);
            if (!$imageInfo) {
                throw new \Exception('Invalid image data');
            }

            // Generate unique filename
            $extension = $imageInfo['extension'];
            $filename = 'report_' . time() . '_' . uniqid() . '.' . $extension;
            $path = 'report_images/' . $filename;

            // Save to storage
            if (Storage::disk('public')->put($path, $imageData)) {
                return $path;
            }

            throw new \Exception('Failed to save image to storage');
        } catch (\Exception $e) {
            Log::error('Failed to save base64 image', [
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Save uploaded file to storage
     */
    private function saveUploadedFile(\Illuminate\Http\UploadedFile $file): ?string
    {
        try {
            $filename = 'report_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('report_images', $filename, 'public');
            return $path;
        } catch (\Exception $e) {
            Log::error('Failed to save uploaded file', [
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Validate base64 image data
     */
    private function validateBase64Image(string $imageData): ?array
    {
        try {
            // Get image info
            $imageInfo = getimagesizefromstring($imageData);

            if ($imageInfo === false) {
                return null;
            }

            // Check mime type
            $allowedMimes = [
                'image/jpeg' => 'jpg',
                'image/png' => 'png',
                'image/gif' => 'gif',
                'image/jpg' => 'jpg'
            ];

            if (!isset($allowedMimes[$imageInfo['mime']])) {
                return null;
            }

            // Check file size (max 5MB)
            if (strlen($imageData) > 5242880) { // 5MB in bytes
                return null;
            }

            return [
                'width' => $imageInfo[0],
                'height' => $imageInfo[1],
                'mime' => $imageInfo['mime'],
                'extension' => $allowedMimes[$imageInfo['mime']],
                'size' => strlen($imageData)
            ];
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Check if string is base64 image
     */
    private function isBase64Image(string $data): bool
    {
        try {
            // Cek apakah ada prefix data:image
            if (strpos($data, 'data:image') === 0) {
                return true;
            }

            // Cek apakah string valid base64 dan bisa di-decode sebagai image
            if (strpos($data, ',') !== false) {
                $base64Data = explode(',', $data)[1];
            } else {
                $base64Data = $data;
            }

            $decoded = base64_decode($base64Data, true);
            if ($decoded === false) {
                return false;
            }

            // Cek apakah hasil decode adalah valid image
            $imageInfo = getimagesizefromstring($decoded);
            return $imageInfo !== false;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Enhanced validation for mixed image types
     */
    private function validateReportData(array $data, bool $isUpdate = false): \Illuminate\Contracts\Validation\Validator
    {
        $rules = [
            'category_id' => $isUpdate ? 'sometimes|required|exists:categories,id' : 'required|exists:categories,id',
            'contributing_id' => $isUpdate ? 'sometimes|required|exists:contributings,id' : 'required|exists:contributings,id',
            'action_id' => $isUpdate ? 'sometimes|required|exists:actions,id' : 'required|exists:actions,id',
            'severity_rating' => $isUpdate ? 'sometimes|required|in:low,medium,high,critical' : 'required|in:low,medium,high,critical',
            'action_taken' => 'nullable|string|max:1000',
            'description' => $isUpdate ? 'sometimes|required|string|max:1000' : 'required|string|max:1000',
            'location' => $isUpdate ? 'sometimes|required|string|max:255' : 'required|string|max:255',
            'images' => 'nullable|array|max:10', // Maximum 10 images
        ];

        // Custom validation for images array
        if (isset($data['images']) && is_array($data['images'])) {
            foreach ($data['images'] as $index => $image) {
                if (is_string($image)) {
                    // Cek apakah ini path existing atau base64
                    if (strpos($image, 'report_images/') === 0) {
                        // Path existing file - validasi bahwa file exists
                        $rules["images.{$index}"] = 'string';
                    } else {
                        // Base64 validation
                        $rules["images.{$index}"] = 'string|max:10485760'; // ~8MB base64
                    }
                } else {
                    // File upload validation
                    $rules["images.{$index}"] = 'image|mimes:jpeg,png,jpg,gif|max:5120'; // 5MB
                }
            }
        }

        return Validator::make($data, $rules, [
            'images.max' => 'Maksimal 10 file gambar yang dapat diupload',
            'images.*.max' => 'Ukuran file gambar maksimal 5MB',
            'images.*.image' => 'File harus berupa gambar',
            'images.*.mimes' => 'Gambar harus berformat jpeg, png, jpg, atau gif',
        ]);
    }

    /**
     * Delete report images
     */
    private function deleteReportImages(Report $report)
    {
        if ($report->images) {
            foreach ($report->images as $imagePath) {
                if (Storage::disk('public')->exists($imagePath)) {
                    Storage::disk('public')->delete($imagePath);
                }
            }
        }
    }

    /**
     * Upload report images (legacy method for backward compatibility)
     * @deprecated Use handleImages() instead
     */
    private function uploadReportImages($images)
    {
        $imagePaths = [];

        if ($images) {
            foreach ($images as $image) {
                $imageName = time() . '_' . uniqid() . '.' . $image->getClientOriginalExtension();
                $imagePath = $image->storeAs('report_images', $imageName, 'public');
                $imagePaths[] = $imagePath;
            }
        }

        return $imagePaths;
    }

    /**
     * Get monthly reports data
     */
    private function getMonthlyReportsData(User $user, int $months = 6)
    {
        $query = Report::query();

        if ($user->role === 'employee') {
            $query->where('employee_id', $user->id);
        }

        return $query
            ->selectRaw('YEAR(created_at) as year, MONTH(created_at) as month, COUNT(*) as count')
            ->where('created_at', '>=', now()->subMonths($months))
            ->groupBy('year', 'month')
            ->orderBy('year', 'desc')
            ->orderBy('month', 'desc')
            ->get()
            ->map(function ($item) {
                $item->month_name = date('F Y', mktime(0, 0, 0, $item->month, 1, $item->year));
                return $item;
            });
    }

    public function dashboard(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            $query = Report::query();

            // Filter by user role
            if ($user->role === 'employee') {
                $query->where('employee_id', $user->id);
            }

            // Get status counts
            $statusCounts = [
                'pending' => (clone $query)->where('status', 'waiting')->count(),
                'progress' => (clone $query)->where('status', 'in-progress')->count(),
                'completed' => (clone $query)->where('status', 'done')->count(),
            ];

            // Calculate total and completion rate
            $totalReports = array_sum($statusCounts);
            $completionRate = $totalReports > 0
                ? round(($statusCounts['completed'] / $totalReports) * 100, 1)
                : 0;

            // Get recent 5 reports with relationships
            $recentReports = (clone $query)
                ->with([
                    'employee:id,name,email',
                    'hseStaff:id,name,email',
                    'categoryMaster:id,name',
                    'contributingMaster:id,name',
                    'actionMaster:id,name'
                ])
                ->latest()
                ->limit(5)
                ->get()
                ->map(function ($report) {
                    return [
                        'id' => $report->id,
                        'title' => $report->title,
                        'description' => $report->description,
                        'status' => $report->status,
                        'status_label' => $this->getStatusLabel($report->status),
                        'status_color' => $this->getStatusColor($report->status),
                        'severity_rating' => $report->severity_rating,
                        'severity_label' => $report->severity_label,
                        'severity_color' => $report->severity_color,
                        'location' => $report->location,
                        'category' => $report->categoryMaster?->name,
                        'employee' => [
                            'id' => $report->employee?->id,
                            'name' => $report->employee?->name,
                        ],
                        'hse_staff' => [
                            'id' => $report->hseStaff?->id,
                            'name' => $report->hseStaff?->name,
                        ],
                        'created_at' => $report->created_at,
                        'created_at_human' => $report->created_at->diffForHumans(),
                        'completed_at' => $report->completed_at,
                        'processing_time_hours' => $report->processing_time_hours,
                    ];
                });

            // Additional dashboard metrics
            $additionalMetrics = [
                'this_week' => (clone $query)
                    ->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])
                    ->count(),
                'this_month' => (clone $query)
                    ->whereMonth('created_at', now()->month)
                    ->whereYear('created_at', now()->year)
                    ->count(),
                'average_completion_time' => $this->getAverageCompletionTime($user),
            ];

            // Get active banners for homepage
            $activeBanners = Banner::active()->ordered()->get()->map(function ($banner) {
                return [
                    'id' => $banner->id,
                    'title' => $banner->title,
                    'description' => $banner->description,
                    'icon' => $banner->icon,
                    'icon_class' => $banner->icon_class,
                    'image_url' => $banner->image_url,
                    'background_color' => $banner->background_color,
                    'text_color' => $banner->text_color,
                    'sort_order' => $banner->sort_order,
                ];
            });

            $dashboardData = [
                'status_counts' => $statusCounts,
                'total_reports' => $totalReports,
                'completion_rate' => $completionRate,
                'recent_reports' => $recentReports,
                'metrics' => $additionalMetrics,
                'banners' => $activeBanners,
            ];

            return $this->successResponse(
                $dashboardData,
                'Dashboard data retrieved successfully'
            );
        } catch (\Exception $e) {
            return $this->errorResponse(
                'Failed to retrieve dashboard data: ' . $e->getMessage(),
                null,
                500
            );
        }
    }

    /**
     * Get status label in Indonesian
     */
    private function getStatusLabel(string $status): string
    {
        return match ($status) {
            'waiting' => 'Menunggu',
            'in-progress' => 'Dalam Proses',
            'done' => 'Selesai',
            default => ucfirst($status)
        };
    }

    /**
     * Get status color for UI
     */
    private function getStatusColor(string $status): string
    {
        return match ($status) {
            'waiting' => 'warning',
            'in-progress' => 'info',
            'done' => 'success',
            default => 'secondary'
        };
    }

    /**
     * Calculate average completion time
     */
    private function getAverageCompletionTime($user): ?float
    {
        $query = Report::query()
            ->whereNotNull('start_process_at')
            ->whereNotNull('completed_at');

        if ($user->role === 'employee') {
            $query->where('employee_id', $user->id);
        }

        $reports = $query->get();

        if ($reports->isEmpty()) {
            return null;
        }

        $totalHours = $reports->sum(function ($report) {
            return $report->start_process_at->diffInHours($report->completed_at);
        });

        return round($totalHours / $reports->count(), 1);
    }
}
