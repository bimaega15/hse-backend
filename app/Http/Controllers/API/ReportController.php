<?php
// app/Http/Controllers/API/ReportController.php (Updated - Added Base64 Image Support)

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Report;
use App\Models\User;
use App\Models\Category;
use App\Models\Contributing;
use App\Models\Location;
use App\Traits\ApiResponseTrait;
use App\Http\Requests\StoreReportRequest;
use App\Models\Banner;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

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
            'locationMaster',
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
                    ->orWhere('action_taken', 'like', "%{$search}%")
                    ->orWhereHas('employee', function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%");
                    })
                    ->orWhereHas('locationMaster', function ($q) use ($search) {
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
            // if ($user->role !== 'employee') {
            //     return response()->json([
            //         'success' => false,
            //         'message' => 'Hanya karyawan yang dapat membuat laporan'
            //     ], 403);
            // }

            // Prepare report data
            $reportData = [
                'employee_id' => $user->id,
                'category_id' => $request->category_id,
                'contributing_id' => $request->contributing_id,
                'action_id' => $request->action_id,
                'severity_rating' => $request->severity_rating,
                'action_taken' => $request->action_taken,
                'description' => $request->description,
                'location_id' => $request->location_id,
                'project_name' => $request->project_name,
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
                'locationMaster',
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
            'locationMaster',
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
        $report->report_details_count = intval($report->reportDetails->count());
        $report->open_details_count = intval($report->reportDetails->where('status_car', 'open')->count());
        $report->in_progress_details_count = intval($report->reportDetails->where('status_car', 'in_progress')->count());
        $report->closed_details_count = intval($report->reportDetails->where('status_car', 'closed')->count());
        $report->overdue_details_count = intval($report->reportDetails->where('due_date', '<', now())
            ->where('status_car', '!=', 'closed')
            ->count());

        // Calculate completion percentage
        $totalDetails = $report->reportDetails->count();
        $closedDetails = $report->reportDetails->where('status_car', 'closed')->count();
        $report->completion_percentage = $totalDetails > 0 ? floatval(round(($closedDetails / $totalDetails) * 100, 2)) : 0.0;

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
                'location_id',
                'project_name'
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
                'locationMaster',
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
                'location_id' => $report->location_id
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
            'locationMaster',
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

        // Skip status check if HSE staff is completing their own report
        if ($report->status !== 'in-progress' && $report->employee_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Laporan harus dalam status in-progress',
            ], 400);
        }

        // Skip role check if HSE staff is completing their own report
        if ($request->user()->role !== 'hse_staff' && $report->employee_id !== $request->user()->id) {
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
            'locationMaster',
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

        $totalReports = intval($query->count());
        $waitingReports = intval((clone $query)->where('status', 'waiting')->count());
        $inProgressReports = intval((clone $query)->where('status', 'in-progress')->count());
        $completedReports = intval((clone $query)->where('status', 'done')->count());

        // Severity statistics
        $severityStats = (clone $query)
            ->selectRaw('severity_rating, COUNT(*) as count')
            ->groupBy('severity_rating')
            ->pluck('count', 'severity_rating')
            ->map(function ($count) {
                return intval($count);
            })
            ->toArray();

        // Category statistics with master data
        $categoryStats = (clone $query)
            ->join('categories', 'reports.category_id', '=', 'categories.id')
            ->selectRaw('categories.name as category_name, COUNT(*) as count')
            ->groupBy('categories.id', 'categories.name')
            ->pluck('count', 'category_name')
            ->map(function ($count) {
                return intval($count);
            })
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
                'completion_rate' => $totalReports > 0 ? floatval(round(($completedReports / $totalReports) * 100, 1)) : 0.0,
                'severity_statistics' => $severityStats,
                'category_statistics' => $categoryStats,
                'monthly_data' => $monthlyData,
                'average_completion_time' => floatval($this->getAverageCompletionTime($user) ?? 0),
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
            'location_id' => $isUpdate ? 'sometimes|required|exists:locations,id' : 'required|exists:locations,id',
            'project_name' => 'nullable|string|max:255',
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
                $item->year = intval($item->year);
                $item->month = intval($item->month);
                $item->count = intval($item->count);
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
                'pending' => intval((clone $query)->where('status', 'waiting')->count()),
                'progress' => intval((clone $query)->where('status', 'in-progress')->count()),
                'completed' => intval((clone $query)->where('status', 'done')->count()),
            ];

            // Calculate total and completion rate
            $totalReports = intval(array_sum($statusCounts));
            $completionRate = $totalReports > 0
                ? floatval(round(($statusCounts['completed'] / $totalReports) * 100, 1))
                : 0.0;

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
                        'id' => intval($report->id),
                        'title' => $report->title,
                        'description' => $report->description,
                        'status' => $report->status,
                        'status_label' => $this->getStatusLabel($report->status),
                        'status_color' => $this->getStatusColor($report->status),
                        'severity_rating' => $report->severity_rating,
                        'severity_label' => $report->severity_label,
                        'severity_color' => $report->severity_color,
                        'location' => $report->locationMaster?->name,
                        'category' => $report->categoryMaster?->name,
                        'employee' => [
                            'id' => $report->employee?->id ? intval($report->employee->id) : null,
                            'name' => $report->employee?->name,
                        ],
                        'hse_staff' => [
                            'id' => $report->hseStaff?->id ? intval($report->hseStaff->id) : null,
                            'name' => $report->hseStaff?->name,
                        ],
                        'created_at' => $report->created_at,
                        'created_at_human' => $report->created_at->diffForHumans(),
                        'completed_at' => $report->completed_at,
                        'processing_time_hours' => $report->processing_time_hours ? floatval($report->processing_time_hours) : null,
                    ];
                });

            // Additional dashboard metrics
            $additionalMetrics = [
                'this_week' => intval((clone $query)
                    ->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])
                    ->count()),
                'this_month' => intval((clone $query)
                    ->whereMonth('created_at', now()->month)
                    ->whereYear('created_at', now()->year)
                    ->count()),
                'average_completion_time' => floatval($this->getAverageCompletionTime($user) ?? 0),
            ];

            // Get active banners for homepage
            $activeBanners = Banner::active()->ordered()->get()->map(function ($banner) {
                return [
                    'id' => intval($banner->id),
                    'title' => $banner->title,
                    'description' => $banner->description,
                    'icon' => $banner->icon,
                    'icon_class' => $banner->icon_class,
                    'image_url' => $banner->image_url,
                    'background_color' => $banner->background_color,
                    'text_color' => $banner->text_color,
                    'sort_order' => intval($banner->sort_order),
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

        return floatval(round($totalHours / $reports->count(), 1));
    }

    /**
     * Get analytics data with filters - API endpoint
     */
    public function getAnalyticsFiltered(Request $request)
    {
        try {
            // Get filters from request
            $filters = $this->extractFilters($request);

            // Log the filters for debugging
            Log::info('API Analytics filters applied:', $filters);

            // Get analytics data with filters
            $analyticsData = $this->getAnalyticsData($filters);

            // Log the summary data for debugging
            Log::info('API Analytics summary data:', $analyticsData['summary'] ?? []);

            return response()->json([
                'success' => true,
                'data' => $analyticsData,
                'message' => 'Analytics data retrieved successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('API Analytics filter error: ' . $e->getMessage());
            Log::error('API Analytics filter stack trace: ' . $e->getTraceAsString());

            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve analytics data: ' . $e->getMessage(),
                'data' => []
            ], 500);
        }
    }

    /**
     * Extract filters from request
     */
    private function extractFilters(Request $request)
    {
        return [
            'start_date' => $request->get('start_date'),
            'end_date' => $request->get('end_date'),
            'status' => $request->get('status'),
            'severity' => $request->get('severity'),
            'category_id' => $request->get('category_id'),
            'contributing_id' => $request->get('contributing_id'),
            'location_id' => $request->get('location_id'),
            'project_name' => $request->get('project_name'),
            'hse_staff_id' => $request->get('hse_staff_id'),
            'employee_id' => auth()->id(),
        ];
    }

    /**
     * Get analytics data
     */
    private function getAnalyticsData($filters = [])
    {
        try {
            $currentMonth = now()->startOfMonth();
            $lastMonth = now()->subMonth()->startOfMonth();

            $completionMetrics = $this->getCompletionMetrics($filters);

            return [
                'summary' => [
                    'total_reports' => intval($this->getFilteredQuery($filters)->count()),
                    'this_month' => intval($this->getFilteredQuery(array_merge($filters, ['this_month' => true]))->count()),
                    'last_month' => intval($this->getFilteredQuery(array_merge($filters, ['last_month' => true]))->count()),
                    'critical_incidents' => intval($this->getFilteredQuery(array_merge($filters, ['high_critical' => true]))->count()),
                    'overdue_cars' => intval($this->getOverdueCarsCount($filters)),
                    'completion_rate' => floatval($completionMetrics['completion_rate']),
                    'avg_resolution_hours' => floatval($completionMetrics['avg_resolution_hours']),
                    // Add detailed breakdown for Period Analysis
                    'status_breakdown' => $this->getStatusBreakdown($filters),
                    'severity_breakdown' => $this->getSeverityBreakdown($filters),
                ],
                'trends' => $this->getMonthlyTrends($filters),
                'categories' => $this->getCategoryBreakdown($filters),
                'contributing_factors' => $this->getContributingBreakdown($filters),
                'severity_analysis' => $this->getSeverityAnalysis($filters),
                'completion_metrics' => $this->getCompletionMetrics($filters),
                'hse_performance' => $this->getHSEPerformance($filters),
                // Additional analytics reports
                'monthly_findings' => $this->getMonthlyFindingsReport($filters),
                'location_project_reports' => $this->getLocationProjectReports($filters),
                'category_detailed_reports' => $this->getCategoryDetailedReports($filters),
                'period_based_reports' => $this->getPeriodBasedReports($filters),
                // Filter options for dropdowns
                'filter_options' => $this->getFilterOptions(),
            ];
        } catch (\Exception $e) {
            Log::error('Failed to get analytics data: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get filtered query based on filters
     */
    private function getFilteredQuery($filters = [], $table = 'reports')
    {
        $query = Report::query();

        // Apply date range filters UNLESS we have special month filters
        if (!empty($filters['start_date']) && empty($filters['this_month']) && empty($filters['last_month'])) {
            $query->whereDate('reports.created_at', '>=', $filters['start_date']);
        }

        if (!empty($filters['end_date']) && empty($filters['this_month']) && empty($filters['last_month'])) {
            $query->whereDate('reports.created_at', '<=', $filters['end_date']);
        }

        // Apply other filters
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['severity'])) {
            $query->where('severity_rating', $filters['severity']);
        }

        if (!empty($filters['category_id'])) {
            $query->where('category_id', $filters['category_id']);
        }

        if (!empty($filters['contributing_id'])) {
            $query->where('contributing_id', $filters['contributing_id']);
        }

        if (!empty($filters['location_id'])) {
            $query->where('location_id', $filters['location_id']);
        }

        if (!empty($filters['project_name'])) {
            $query->where('project_name', $filters['project_name']);
        }

        if (!empty($filters['hse_staff_id'])) {
            $query->where('hse_staff_id', $filters['hse_staff_id']);
        }

        if (!empty($filters['employee_id'])) {
            $query->where('employee_id', $filters['employee_id']);
        }

        // Special filters for summary calculations (these take precedence over date range)
        if (!empty($filters['this_month'])) {
            $currentYear = now()->year;
            $currentMonth = now()->month;
            $query->whereYear('reports.created_at', $currentYear)
                ->whereMonth('reports.created_at', $currentMonth);
        }

        if (!empty($filters['last_month'])) {
            $lastMonth = now()->subMonth();
            $query->whereYear('reports.created_at', $lastMonth->year)
                ->whereMonth('reports.created_at', $lastMonth->month);
        }

        if (!empty($filters['high_critical'])) {
            $query->whereIn('severity_rating', ['high', 'critical']);
        }

        return $query;
    }

    /**
     * Get monthly trends
     */
    private function getMonthlyTrends($filters = [])
    {
        $query = $this->getFilteredQuery($filters);

        $monthlyData = $query->selectRaw('
                YEAR(created_at) as year,
                MONTH(created_at) as month,
                COUNT(*) as total,
                SUM(CASE WHEN status = "done" THEN 1 ELSE 0 END) as completed,
                SUM(CASE WHEN severity_rating IN ("high", "critical") THEN 1 ELSE 0 END) as critical
            ')
            ->where('created_at', '>=', now()->subMonths(12))
            ->groupBy('year', 'month')
            ->orderBy('year', 'desc')
            ->orderBy('month', 'desc')
            ->get()
            ->map(function ($item) {
                $item->month_name = date('M Y', mktime(0, 0, 0, $item->month, 1, $item->year));
                $item->completion_rate = $item->total > 0 ? floatval(round(($item->completed / $item->total) * 100, 1)) : 0.0;
                $item->year = intval($item->year);
                $item->month = intval($item->month);
                $item->total = intval($item->total);
                $item->completed = intval($item->completed);
                $item->critical = intval($item->critical);

                // Get category breakdown for this month
                $categoryBreakdown = Report::join('categories', 'reports.category_id', '=', 'categories.id')
                    ->selectRaw('categories.name as category, COUNT(*) as count')
                    ->whereYear('reports.created_at', $item->year)
                    ->whereMonth('reports.created_at', $item->month)
                    ->groupBy('categories.id', 'categories.name')
                    ->orderBy('count', 'desc')
                    ->get()
                    ->map(function ($cat) {
                        $cat->count = intval($cat->count);
                        return $cat;
                    });

                $item->categories = $categoryBreakdown;
                $item->top_category = $categoryBreakdown->first()->category ?? 'N/A';
                $item->top_category_count = intval($categoryBreakdown->first()->count ?? 0);

                return $item;
            });

        return $monthlyData;
    }

    /**
     * Get category breakdown
     */
    private function getCategoryBreakdown($filters = [])
    {
        return $this->getFilteredQuery($filters, 'reports')
            ->join('categories', 'reports.category_id', '=', 'categories.id')
            ->selectRaw('
                categories.name as category,
                COUNT(*) as total,
                SUM(CASE WHEN reports.status = "done" THEN 1 ELSE 0 END) as completed,
                AVG(CASE
                    WHEN reports.start_process_at IS NOT NULL AND reports.completed_at IS NOT NULL
                    THEN TIMESTAMPDIFF(HOUR, reports.start_process_at, reports.completed_at)
                    ELSE NULL
                END) as avg_resolution_hours
            ')
            ->groupBy('categories.id', 'categories.name')
            ->orderBy('total', 'desc')
            ->get()
            ->map(function ($item) {
                $item->total = intval($item->total);
                $item->completed = intval($item->completed);
                $item->avg_resolution_hours = $item->avg_resolution_hours ? floatval($item->avg_resolution_hours) : 0.0;
                return $item;
            });
    }

    /**
     * Get contributing factors breakdown
     */
    private function getContributingBreakdown($filters = [])
    {
        return $this->getFilteredQuery($filters, 'reports')
            ->join('contributings', 'reports.contributing_id', '=', 'contributings.id')
            ->selectRaw('
                contributings.name as contributing,
                COUNT(*) as total,
                SUM(CASE WHEN reports.status = "done" THEN 1 ELSE 0 END) as closed,
                SUM(CASE WHEN reports.status IN ("waiting", "in-progress") THEN 1 ELSE 0 END) as open,
                AVG(CASE
                    WHEN reports.start_process_at IS NOT NULL AND reports.completed_at IS NOT NULL
                    THEN TIMESTAMPDIFF(HOUR, reports.start_process_at, reports.completed_at)
                    ELSE NULL
                END) as avg_resolution_hours
            ')
            ->groupBy('contributings.id', 'contributings.name')
            ->orderBy('total', 'desc')
            ->get()
            ->map(function ($item) {
                $item->total = intval($item->total);
                $item->closed = intval($item->closed);
                $item->open = intval($item->open);
                $item->avg_resolution_hours = $item->avg_resolution_hours ? floatval($item->avg_resolution_hours) : 0.0;
                return $item;
            });
    }

    /**
     * Get severity analysis
     */
    private function getSeverityAnalysis($filters = [])
    {
        return $this->getFilteredQuery($filters)->selectRaw('
                severity_rating,
                COUNT(*) as count,
                SUM(CASE WHEN status = "done" THEN 1 ELSE 0 END) as completed,
                AVG(CASE
                    WHEN start_process_at IS NOT NULL AND completed_at IS NOT NULL
                    THEN TIMESTAMPDIFF(HOUR, start_process_at, completed_at)
                    ELSE NULL
                END) as avg_resolution_hours
            ')
            ->groupBy('severity_rating')
            ->get()
            ->map(function ($item) {
                $item->count = intval($item->count);
                $item->completed = intval($item->completed);
                $item->avg_resolution_hours = $item->avg_resolution_hours ? floatval($item->avg_resolution_hours) : 0.0;
                return $item;
            });
    }

    /**
     * Get completion metrics
     */
    private function getCompletionMetrics($filters = [])
    {
        $totalReports = $this->getFilteredQuery($filters)->count();
        $completedReports = $this->getFilteredQuery($filters)->where('status', 'done')->count();
        $avgResolutionTime = $this->getFilteredQuery($filters)->whereNotNull('start_process_at')
            ->whereNotNull('completed_at')
            ->selectRaw('AVG(TIMESTAMPDIFF(HOUR, start_process_at, completed_at)) as avg_hours')
            ->value('avg_hours');

        return [
            'total_reports' => intval($totalReports),
            'completed_reports' => intval($completedReports),
            'completion_rate' => $totalReports > 0 ? floatval(round(($completedReports / $totalReports) * 100, 1)) : 0.0,
            'avg_resolution_hours' => $avgResolutionTime ? floatval(round($avgResolutionTime, 1)) : 0.0,
            'sla_compliance' => $this->calculateSLACompliance($filters),
        ];
    }

    /**
     * Get HSE performance
     */
    private function getHSEPerformance($filters = [])
    {
        // Build constraints for assigned reports based on filters
        $constraints = [];
        if (!empty($filters['start_date'])) {
            $constraints[] = ['created_at', '>=', $filters['start_date']];
        }
        if (!empty($filters['end_date'])) {
            $constraints[] = ['created_at', '<=', $filters['end_date']];
        }
        if (!empty($filters['status'])) {
            $constraints[] = ['status', '=', $filters['status']];
        }
        if (!empty($filters['severity'])) {
            $constraints[] = ['severity_rating', '=', $filters['severity']];
        }
        if (!empty($filters['category_id'])) {
            $constraints[] = ['category_id', '=', $filters['category_id']];
        }
        if (!empty($filters['location_id'])) {
            $constraints[] = ['location_id', '=', $filters['location_id']];
        }
        if (!empty($filters['project_name'])) {
            $constraints[] = ['project_name', '=', $filters['project_name']];
        }

        return User::where('role', 'hse_staff')
            ->where('is_active', true)
            ->withCount([
                'assignedReports' => function ($query) use ($constraints) {
                    foreach ($constraints as $constraint) {
                        $query->where($constraint[0], $constraint[1], $constraint[2]);
                    }
                },
                'assignedReports as completed_reports_count' => function ($query) use ($constraints) {
                    $query->where('status', 'done');
                    foreach ($constraints as $constraint) {
                        $query->where($constraint[0], $constraint[1], $constraint[2]);
                    }
                },
                'assignedReports as this_month_reports_count' => function ($query) use ($constraints) {
                    $query->whereMonth('created_at', now()->month);
                    foreach ($constraints as $constraint) {
                        $query->where($constraint[0], $constraint[1], $constraint[2]);
                    }
                }
            ])
            ->get()
            ->map(function ($staff) {
                $staff->completion_rate = $staff->assigned_reports_count > 0
                    ? floatval(round(($staff->completed_reports_count / $staff->assigned_reports_count) * 100, 1))
                    : 0.0;
                $staff->assigned_reports_count = intval($staff->assigned_reports_count);
                $staff->completed_reports_count = intval($staff->completed_reports_count);
                $staff->this_month_reports_count = intval($staff->this_month_reports_count);
                return $staff;
            });
    }

    /**
     * Calculate SLA compliance
     */
    private function calculateSLACompliance($filters = [])
    {
        // Define SLA targets (in hours) based on severity
        $slaTargets = [
            'critical' => 4,   // 4 hours
            'high' => 24,      // 24 hours
            'medium' => 72,    // 72 hours
            'low' => 168       // 168 hours (1 week)
        ];

        $compliance = [];

        foreach ($slaTargets as $severity => $targetHours) {
            $reports = $this->getFilteredQuery($filters)
                ->where('severity_rating', $severity)
                ->whereNotNull('start_process_at')
                ->whereNotNull('completed_at')
                ->get();

            $withinSLA = $reports->filter(function ($report) use ($targetHours) {
                $resolutionHours = $report->start_process_at->diffInHours($report->completed_at);
                return $resolutionHours <= $targetHours;
            })->count();

            $compliance[$severity] = [
                'total' => intval($reports->count()),
                'within_sla' => intval($withinSLA),
                'compliance_rate' => $reports->count() > 0 ? floatval(round(($withinSLA / $reports->count()) * 100, 1)) : 0.0,
                'target_hours' => intval($targetHours)
            ];
        }

        return $compliance;
    }

    /**
     * Get monthly findings report
     */
    private function getMonthlyFindingsReport($filters = [])
    {
        $query = $this->getFilteredQuery($filters);

        return $query->selectRaw('
                YEAR(created_at) as year,
                MONTH(created_at) as month,
                COUNT(*) as total_findings,
                SUM(CASE WHEN status = "done" THEN 1 ELSE 0 END) as closed_findings,
                SUM(CASE WHEN status IN ("waiting", "in-progress") THEN 1 ELSE 0 END) as open_findings,
                COUNT(CASE WHEN severity_rating = "low" THEN 1 END) as low_severity,
                COUNT(CASE WHEN severity_rating = "medium" THEN 1 END) as medium_severity,
                COUNT(CASE WHEN severity_rating = "high" THEN 1 END) as high_severity,
                COUNT(CASE WHEN severity_rating = "critical" THEN 1 END) as critical_severity
            ')
            ->where('created_at', '>=', now()->subMonths(12))
            ->groupBy('year', 'month')
            ->orderBy('year', 'desc')
            ->orderBy('month', 'desc')
            ->get()
            ->map(function ($item) {
                $item->month_name = date('M Y', mktime(0, 0, 0, $item->month, 1, $item->year));
                $item->completion_rate = $item->total_findings > 0
                    ? floatval(round(($item->closed_findings / $item->total_findings) * 100, 1))
                    : 0.0;
                $item->year = intval($item->year);
                $item->month = intval($item->month);
                $item->total_findings = intval($item->total_findings);
                $item->closed_findings = intval($item->closed_findings);
                $item->open_findings = intval($item->open_findings);
                $item->low_severity = intval($item->low_severity);
                $item->medium_severity = intval($item->medium_severity);
                $item->high_severity = intval($item->high_severity);
                $item->critical_severity = intval($item->critical_severity);
                return $item;
            });
    }

    /**
     * Get location and project reports
     */
    private function getLocationProjectReports($filters = [])
    {
        $locationReports = $this->getFilteredQuery($filters, 'reports')
            ->join('locations', 'reports.location_id', '=', 'locations.id')
            ->selectRaw('
                locations.name as location_name,
                COUNT(*) as total_reports,
                SUM(CASE WHEN reports.status = "done" THEN 1 ELSE 0 END) as closed_reports,
                SUM(CASE WHEN reports.status IN ("waiting", "in-progress") THEN 1 ELSE 0 END) as open_reports,
                COUNT(CASE WHEN reports.severity_rating IN ("high", "critical") THEN 1 END) as critical_reports
            ')
            ->groupBy('locations.id', 'locations.name')
            ->orderBy('total_reports', 'desc')
            ->get()
            ->map(function ($item) {
                $item->total_reports = intval($item->total_reports);
                $item->closed_reports = intval($item->closed_reports);
                $item->open_reports = intval($item->open_reports);
                $item->critical_reports = intval($item->critical_reports);
                return $item;
            });

        $projectReports = $this->getFilteredQuery($filters)
            ->whereNotNull('project_name')
            ->where('project_name', '!=', '')
            ->selectRaw('
                project_name,
                COUNT(*) as total_reports,
                SUM(CASE WHEN status = "done" THEN 1 ELSE 0 END) as closed_reports,
                SUM(CASE WHEN status IN ("waiting", "in-progress") THEN 1 ELSE 0 END) as open_reports,
                COUNT(CASE WHEN severity_rating IN ("high", "critical") THEN 1 END) as critical_reports
            ')
            ->groupBy('project_name')
            ->orderBy('total_reports', 'desc')
            ->get()
            ->map(function ($item) {
                $item->total_reports = intval($item->total_reports);
                $item->closed_reports = intval($item->closed_reports);
                $item->open_reports = intval($item->open_reports);
                $item->critical_reports = intval($item->critical_reports);
                return $item;
            });

        return [
            'by_location' => $locationReports,
            'by_project' => $projectReports
        ];
    }

    /**
     * Get detailed category reports
     */
    private function getCategoryDetailedReports($filters = [])
    {
        $query = $this->getFilteredQuery($filters, 'reports')
            ->join('categories', 'reports.category_id', '=', 'categories.id')
            ->selectRaw('
                categories.name as category_name,
                categories.description as category_description,
                COUNT(*) as total_reports,
                SUM(CASE WHEN reports.status = "done" THEN 1 ELSE 0 END) as closed_reports,
                SUM(CASE WHEN reports.status IN ("waiting", "in-progress") THEN 1 ELSE 0 END) as open_reports,
                COUNT(CASE WHEN reports.severity_rating = "low" THEN 1 END) as low_severity,
                COUNT(CASE WHEN reports.severity_rating = "medium" THEN 1 END) as medium_severity,
                COUNT(CASE WHEN reports.severity_rating = "high" THEN 1 END) as high_severity,
                COUNT(CASE WHEN reports.severity_rating = "critical" THEN 1 END) as critical_severity,
                AVG(CASE
                    WHEN reports.start_process_at IS NOT NULL AND reports.completed_at IS NOT NULL
                    THEN TIMESTAMPDIFF(HOUR, reports.start_process_at, reports.completed_at)
                    ELSE NULL
                END) as avg_resolution_hours
            ')
            ->groupBy('categories.id', 'categories.name', 'categories.description')
            ->orderBy('total_reports', 'desc');

        return $query->get()->map(function ($item) {
            $item->completion_rate = $item->total_reports > 0
                ? floatval(round(($item->closed_reports / $item->total_reports) * 100, 1))
                : 0.0;
            $item->avg_resolution_hours = $item->avg_resolution_hours
                ? floatval(round($item->avg_resolution_hours, 1))
                : 0.0;
            $item->total_reports = intval($item->total_reports);
            $item->closed_reports = intval($item->closed_reports);
            $item->open_reports = intval($item->open_reports);
            $item->low_severity = intval($item->low_severity);
            $item->medium_severity = intval($item->medium_severity);
            $item->high_severity = intval($item->high_severity);
            $item->critical_severity = intval($item->critical_severity);
            return $item;
        });
    }

    /**
     * Get period-based reports
     */
    private function getPeriodBasedReports($filters = [])
    {
        // If date filters are applied, show breakdown based on those dates
        if (!empty($filters['start_date']) || !empty($filters['end_date'])) {
            return $this->getFilteredPeriodBreakdown($filters);
        }

        // Otherwise show default periods
        $periods = [
            'today' => [
                'start' => now()->startOfDay(),
                'end' => now()->endOfDay(),
                'label' => 'Today'
            ],
            'this_week' => [
                'start' => now()->startOfWeek(),
                'end' => now()->endOfWeek(),
                'label' => 'This Week'
            ],
            'this_month' => [
                'start' => now()->startOfMonth(),
                'end' => now()->endOfMonth(),
                'label' => 'This Month'
            ],
            'this_quarter' => [
                'start' => now()->startOfQuarter(),
                'end' => now()->endOfQuarter(),
                'label' => 'This Quarter'
            ],
            'this_year' => [
                'start' => now()->startOfYear(),
                'end' => now()->endOfYear(),
                'label' => 'This Year'
            ]
        ];

        $results = [];

        foreach ($periods as $key => $period) {
            // Don't override the main filters, create separate query for each period
            $basePeriodFilters = array_filter($filters, function ($value, $key) {
                return !in_array($key, ['start_date', 'end_date', 'this_month', 'last_month']);
            }, ARRAY_FILTER_USE_BOTH);

            $periodFilters = array_merge($basePeriodFilters, [
                'start_date' => $period['start']->format('Y-m-d'),
                'end_date' => $period['end']->format('Y-m-d')
            ]);

            $data = $this->getFilteredQuery($periodFilters)->selectRaw('
                    COUNT(*) as total_findings,
                    SUM(CASE WHEN status = "done" THEN 1 ELSE 0 END) as closed_findings,
                    SUM(CASE WHEN status IN ("waiting", "in-progress") THEN 1 ELSE 0 END) as open_findings,
                    COUNT(CASE WHEN severity_rating = "critical" THEN 1 END) as critical_findings,
                    COUNT(CASE WHEN severity_rating = "high" THEN 1 END) as high_findings
                ')
                ->first();

            // Type cast the data
            if ($data) {
                $data->total_findings = intval($data->total_findings);
                $data->closed_findings = intval($data->closed_findings);
                $data->open_findings = intval($data->open_findings);
                $data->critical_findings = intval($data->critical_findings);
                $data->high_findings = intval($data->high_findings);
            }

            $results[$key] = [
                'label' => $period['label'],
                'period' => [
                    'start' => $period['start']->format('d M Y'),
                    'end' => $period['end']->format('d M Y')
                ],
                'data' => $data
            ];
        }

        return $results;
    }

    /**
     * Get breakdown for filtered period
     */
    private function getFilteredPeriodBreakdown($filters = [])
    {
        $startDate = !empty($filters['start_date']) ? \Carbon\Carbon::parse($filters['start_date']) : now()->subMonth();
        $endDate = !empty($filters['end_date']) ? \Carbon\Carbon::parse($filters['end_date']) : now();

        $totalDays = $startDate->diffInDays($endDate) + 1;

        // Get basic stats for the filtered period
        $data = $this->getFilteredQuery($filters)->selectRaw('
                COUNT(*) as total_findings,
                SUM(CASE WHEN status = "done" THEN 1 ELSE 0 END) as closed_findings,
                SUM(CASE WHEN status IN ("waiting", "in-progress") THEN 1 ELSE 0 END) as open_findings,
                COUNT(CASE WHEN severity_rating = "critical" THEN 1 END) as critical_findings,
                COUNT(CASE WHEN severity_rating = "high" THEN 1 END) as high_findings,
                COUNT(CASE WHEN severity_rating = "medium" THEN 1 END) as medium_findings,
                COUNT(CASE WHEN severity_rating = "low" THEN 1 END) as low_findings
            ')
            ->first();

        // Type cast the data
        if ($data) {
            $data->total_findings = intval($data->total_findings);
            $data->closed_findings = intval($data->closed_findings);
            $data->open_findings = intval($data->open_findings);
            $data->critical_findings = intval($data->critical_findings);
            $data->high_findings = intval($data->high_findings);
            $data->medium_findings = intval($data->medium_findings);
            $data->low_findings = intval($data->low_findings);
        }

        return [
            'filtered_period' => [
                'label' => 'Filtered Period',
                'period' => [
                    'start' => $startDate->format('d M Y'),
                    'end' => $endDate->format('d M Y'),
                    'total_days' => $totalDays
                ],
                'data' => $data,
                'avg_per_day' => $totalDays > 0 ? floatval(round(($data->total_findings ?? 0) / $totalDays, 1)) : 0.0
            ]
        ];
    }

    /**
     * Get filter options
     */
    private function getFilterOptions()
    {
        try {
            return [
                'categories' => Category::where('is_active', true)->orderBy('name')->get(['id', 'name']),
                'contributing_factors' => Contributing::where('is_active', true)->orderBy('name')->get(['id', 'name']),
                'locations' => Location::where('is_active', true)->orderBy('name')->get(['id', 'name']),
                'projects' => Report::whereNotNull('project_name')
                    ->where('project_name', '!=', '')
                    ->distinct()
                    ->orderBy('project_name')
                    ->pluck('project_name'),
                'hse_staff' => User::where('role', 'hse_staff')
                    ->where('is_active', true)
                    ->orderBy('name')
                    ->get(['id', 'name'])
            ];
        } catch (\Exception $e) {
            Log::error('Failed to get filter options: ' . $e->getMessage());
            return [
                'categories' => collect(),
                'contributing_factors' => collect(),
                'locations' => collect(),
                'projects' => collect(),
                'hse_staff' => collect()
            ];
        }
    }

    /**
     * Get overdue CARs count
     */
    private function getOverdueCarsCount($filters = [])
    {
        $query = DB::table('report_details')
            ->join('reports', 'report_details.report_id', '=', 'reports.id')
            ->where('report_details.due_date', '<', now())
            ->where('report_details.status_car', '!=', 'closed');

        // Apply report-based filters
        if (!empty($filters['start_date'])) {
            $query->whereDate('reports.created_at', '>=', $filters['start_date']);
        }

        if (!empty($filters['end_date'])) {
            $query->whereDate('reports.created_at', '<=', $filters['end_date']);
        }

        if (!empty($filters['status'])) {
            $query->where('reports.status', $filters['status']);
        }

        if (!empty($filters['severity'])) {
            $query->where('reports.severity_rating', $filters['severity']);
        }

        if (!empty($filters['category_id'])) {
            $query->where('reports.category_id', $filters['category_id']);
        }

        if (!empty($filters['location_id'])) {
            $query->where('reports.location_id', $filters['location_id']);
        }

        if (!empty($filters['project_name'])) {
            $query->where('reports.project_name', $filters['project_name']);
        }

        if (!empty($filters['hse_staff_id'])) {
            $query->where('reports.hse_staff_id', $filters['hse_staff_id']);
        }

        return $query->count();
    }

    /**
     * Get accurate status breakdown
     */
    private function getStatusBreakdown($filters = [])
    {
        return [
            'closed' => intval($this->getFilteredQuery($filters)->where('status', 'done')->count()),
            'open' => intval($this->getFilteredQuery($filters)->whereIn('status', ['waiting', 'in-progress'])->count()),
            'waiting' => intval($this->getFilteredQuery($filters)->where('status', 'waiting')->count()),
            'in_progress' => intval($this->getFilteredQuery($filters)->where('status', 'in-progress')->count()),
        ];
    }

    /**
     * Get accurate severity breakdown
     */
    private function getSeverityBreakdown($filters = [])
    {
        return [
            'critical' => intval($this->getFilteredQuery($filters)->where('severity_rating', 'critical')->count()),
            'high' => intval($this->getFilteredQuery($filters)->where('severity_rating', 'high')->count()),
            'medium' => intval($this->getFilteredQuery($filters)->where('severity_rating', 'medium')->count()),
            'low' => intval($this->getFilteredQuery($filters)->where('severity_rating', 'low')->count()),
        ];
    }
}
