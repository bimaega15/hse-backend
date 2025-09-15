<?php
// app/Http/Controllers/API/ReportDetailController.php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Report;
use App\Models\ReportDetail;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ReportDetailController extends Controller
{
    use ApiResponseTrait;

    /**
     * Display all report details for a specific report
     */
    public function index(Request $request, $reportId): JsonResponse
    {
        $user = $request->user();

        $report = Report::with([
            'employee',
            'hseStaff',
            'categoryMaster',
            'contributingMaster',
            'actionMaster',
            'reportDetails.approvedBy',
            'reportDetails.createdBy',
            'reportDetails.assignedUser'
        ])
            ->find($reportId);

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

        $query = $report->reportDetails()->with(['approvedBy', 'createdBy', 'assignedUser']);

        // Filter by status
        if ($request->has('status') && $request->status !== 'all') {
            $query->where('status_car', $request->status);
        }

        // Filter by overdue
        if ($request->has('overdue') && $request->overdue === 'true') {
            $query->overdue();
        }

        // Filter by approver
        if ($request->has('approved_by') && $request->approved_by !== 'all') {
            $query->where('approved_by', $request->approved_by);
        }

        // Search functionality
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('correction_action', 'like', "%{$search}%")
                    ->orWhereHas('assignedUser', function($userQuery) use ($search) {
                        $userQuery->where('name', 'like', "%{$search}%");
                    });
            });
        }

        // Sort by due_date and created_at
        $query->orderBy('due_date', 'asc')
            ->orderBy('created_at', 'desc');

        $reportDetails = $query->get();

        return response()->json([
            'success' => true,
            'data' => [
                'report' => $report,
                'report_details' => $reportDetails
            ],
            'message' => 'Detail laporan berhasil diambil'
        ]);
    }

    /**
     * Store a new report detail (HSE staff only after approving report)
     */
    public function store(Request $request, $reportId): JsonResponse
    {
        $user = $request->user();

        // Only HSE staff can create report details
        if ($user->role !== 'hse_staff') {
            return response()->json([
                'success' => false,
                'message' => 'Hanya HSE staff yang dapat membuat detail laporan',
            ], 403);
        }

        $report = Report::find($reportId);

        if (!$report) {
            return response()->json([
                'success' => false,
                'message' => 'Laporan tidak ditemukan',
            ], 404);
        }

        // Report must be in-progress or done to add details
        // Skip this check if the HSE staff is the one who created the report
        if (!$report->canHaveReportDetails() && $report->hse_staff_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Detail laporan hanya dapat ditambahkan setelah laporan diproses HSE',
            ], 400);
        }

        // Custom validation for evidences (can be file uploads or base64)
        $validator = $this->validateReportDetailData($request->all());

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation Error',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            // Prepare report detail data
            $reportDetailData = [
                'report_id' => $reportId,
                'correction_action' => $request->correction_action,
                'due_date' => $request->due_date,
                'users_id' => $request->users_id, // Employee ID as PIC
                'status_car' => $request->status_car ?? 'open',
                'approved_by' => $user->id,
                'created_by' => $user->id,
            ];

            // Handle evidence uploads (both file uploads and base64)
            if ($request->has('evidences') && !empty($request->evidences)) {
                $evidencePaths = $this->handleEvidences($request);
                $reportDetailData['evidences'] = $evidencePaths;
            }

            // Create the report detail
            $reportDetail = ReportDetail::create($reportDetailData);
            $reportDetail->load(['approvedBy', 'createdBy', 'assignedUser', 'report']);

            Log::info('Report detail created successfully', [
                'report_detail_id' => $reportDetail->id,
                'report_id' => $reportId,
                'hse_staff_id' => $user->id,
                'due_date' => $request->due_date,
                'evidence_count' => count($reportDetailData['evidences'] ?? [])
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Detail laporan berhasil dibuat',
                'data' => $reportDetail
            ], 201);
        } catch (\Exception $e) {
            Log::error('Report detail creation failed', [
                'report_id' => $reportId,
                'user_id' => $user->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat membuat detail laporan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display specific report detail
     */
    public function show(Request $request, $reportId, $detailId): JsonResponse
    {
        $user = $request->user();

        $reportDetail = ReportDetail::with(['report', 'approvedBy', 'createdBy', 'assignedUser'])
            ->where('report_id', $reportId)
            ->find($detailId);

        if (!$reportDetail) {
            return response()->json([
                'success' => false,
                'message' => 'Detail laporan tidak ditemukan',
            ], 404);
        }

        // Check access permissions
        if ($user->role === 'employee' && $reportDetail->report->employee_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak memiliki akses ke detail laporan ini',
            ], 403);
        }

        return response()->json([
            'success' => true,
            'data' => $reportDetail,
            'message' => 'Detail laporan berhasil diambil'
        ]);
    }


    /**
     * Delete report detail (HSE staff only)
     */
    public function destroy(Request $request, $reportId, $detailId): JsonResponse
    {
        $user = $request->user();

        if ($user->role !== 'hse_staff') {
            return response()->json([
                'success' => false,
                'message' => 'Hanya HSE staff yang dapat menghapus detail laporan',
            ], 403);
        }

        $reportDetail = ReportDetail::where('report_id', $reportId)->find($detailId);

        if (!$reportDetail) {
            return response()->json([
                'success' => false,
                'message' => 'Detail laporan tidak ditemukan',
            ], 404);
        }

        try {
            // Delete evidence files
            if ($reportDetail->evidences) {
                foreach ($reportDetail->evidences as $evidence) {
                    Storage::disk('public')->delete($evidence);
                }
            }

            $reportDetail->delete();

            Log::info('Report detail deleted successfully', [
                'report_detail_id' => $detailId,
                'report_id' => $reportId,
                'hse_staff_id' => $user->id
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Detail laporan berhasil dihapus'
            ]);
        } catch (\Exception $e) {
            Log::error('Report detail deletion failed', [
                'report_detail_id' => $detailId,
                'report_id' => $reportId,
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat menghapus detail laporan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update status of report detail (for quick status changes)
     */
    public function updateStatus(Request $request, $reportId, $detailId): JsonResponse
    {
        $user = $request->user();

        if ($user->role !== 'hse_staff') {
            return response()->json([
                'success' => false,
                'message' => 'Hanya HSE staff yang dapat mengupdate status detail laporan',
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'status_car' => 'required|in:open,in_progress,closed',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation Error',
                'errors' => $validator->errors(),
            ], 422);
        }

        $reportDetail = ReportDetail::where('report_id', $reportId)->find($detailId);

        if (!$reportDetail) {
            return response()->json([
                'success' => false,
                'message' => 'Detail laporan tidak ditemukan',
            ], 404);
        }

        try {
            $oldStatus = $reportDetail->status_car;
            $reportDetail->update(['status_car' => $request->status_car]);
            $reportDetail->load(['approvedBy', 'createdBy', 'assignedUser', 'report']);

            Log::info('Report detail status updated', [
                'report_detail_id' => $detailId,
                'report_id' => $reportId,
                'old_status' => $oldStatus,
                'new_status' => $request->status_car,
                'hse_staff_id' => $user->id
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Status detail laporan berhasil diupdate',
                'data' => $reportDetail
            ]);
        } catch (\Exception $e) {
            Log::error('Report detail status update failed', [
                'report_detail_id' => $detailId,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mengupdate status: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get report details statistics
     */
    public function statistics(Request $request): JsonResponse
    {
        $user = $request->user();

        if ($user->role !== 'hse_staff') {
            return response()->json([
                'success' => false,
                'message' => 'Hanya HSE staff yang dapat melihat statistik detail laporan',
            ], 403);
        }

        $query = ReportDetail::query();

        // Filter by date range if provided
        if ($request->has('start_date') && $request->has('end_date')) {
            $query->whereBetween('created_at', [
                $request->start_date . ' 00:00:00',
                $request->end_date . ' 23:59:59'
            ]);
        }

        // Filter by HSE staff if provided
        if ($request->has('approved_by') && $request->approved_by !== 'all') {
            $query->where('approved_by', $request->approved_by);
        }

        $statistics = [
            'total' => $query->count(),
            'open' => $query->where('status_car', 'open')->count(),
            'in_progress' => $query->where('status_car', 'in_progress')->count(),
            'closed' => $query->where('status_car', 'closed')->count(),
            'overdue' => $query->where('due_date', '<', now())
                ->where('status_car', '!=', 'closed')
                ->count(),
        ];

        $statistics['completion_rate'] = $statistics['total'] > 0
            ? round(($statistics['closed'] / $statistics['total']) * 100, 2)
            : 0;

        return response()->json([
            'success' => true,
            'data' => $statistics,
            'message' => 'Statistik detail laporan berhasil diambil'
        ]);
    }



    /**
     * Handle evidences (both file uploads and base64 images)
     */
    private function handleEvidences(Request $request): array
    {
        $evidencePaths = [];
        $evidences = $request->evidences;

        if (!is_array($evidences)) {
            return $evidencePaths;
        }

        foreach ($evidences as $index => $evidence) {
            try {
                if (is_string($evidence)) {
                    // Handle base64 image
                    $path = $this->saveBase64Image($evidence);
                    if ($path) {
                        $evidencePaths[] = $path;
                    }
                } elseif ($evidence instanceof \Illuminate\Http\UploadedFile) {
                    // Handle file upload
                    if ($evidence->isValid()) {
                        $path = $this->saveUploadedFile($evidence);
                        if ($path) {
                            $evidencePaths[] = $path;
                        }
                    }
                }
            } catch (\Exception $e) {
                Log::warning('Failed to process evidence', [
                    'index' => $index,
                    'type' => is_string($evidence) ? 'base64' : 'file',
                    'error' => $e->getMessage()
                ]);
                // Continue processing other evidences
            }
        }

        return $evidencePaths;
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
            $filename = 'evidence_' . time() . '_' . uniqid() . '.' . $extension;
            $path = 'report_evidences/' . $filename;

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
            $filename = 'evidence_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('report_evidences', $filename, 'public');
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
     * Upload evidence images (legacy method for backward compatibility)
     * @deprecated Use handleEvidences() instead
     */
    private function uploadEvidences(array $evidences): array
    {
        $evidencePaths = [];

        foreach ($evidences as $evidence) {
            if ($evidence->isValid()) {
                $filename = 'evidence_' . time() . '_' . uniqid() . '.' . $evidence->getClientOriginalExtension();
                $path = $evidence->storeAs('report_evidences', $filename, 'public');
                $evidencePaths[] = $path;
            }
        }

        return $evidencePaths;
    }




    public function update(Request $request, $reportId, $detailId): JsonResponse
    {
        $user = $request->user();

        if ($user->role !== 'hse_staff') {
            return response()->json([
                'success' => false,
                'message' => 'Hanya HSE staff yang dapat mengupdate detail laporan',
            ], 403);
        }

        $reportDetail = ReportDetail::where('report_id', $reportId)->find($detailId);

        if (!$reportDetail) {
            return response()->json([
                'success' => false,
                'message' => 'Detail laporan tidak ditemukan',
            ], 404);
        }

        if (!$reportDetail->canBeUpdated()) {
            return response()->json([
                'success' => false,
                'message' => 'Detail laporan yang sudah selesai tidak dapat diupdate',
            ], 400);
        }

        // Custom validation for evidences (can be file uploads or base64)
        $validator = $this->validateReportDetailData($request->all(), true);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation Error',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $updateData = $request->only([
                'correction_action',
                'due_date',
                'users_id',
                'status_car'
            ]);

            // Handle evidence updates dengan logic baru
            if ($request->has('evidences')) {
                $updateData['evidences'] = $this->handleEvidenceUpdates($request, $reportDetail);
            }

            $reportDetail->update($updateData);
            $reportDetail->load(['approvedBy', 'createdBy', 'assignedUser', 'report']);

            Log::info('Report detail updated successfully', [
                'report_detail_id' => $reportDetail->id,
                'report_id' => $reportId,
                'hse_staff_id' => $user->id,
                'updated_fields' => array_keys($updateData)
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Detail laporan berhasil diupdate',
                'data' => $reportDetail
            ]);
        } catch (\Exception $e) {
            Log::error('Report detail update failed', [
                'report_detail_id' => $detailId,
                'report_id' => $reportId,
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mengupdate detail laporan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Handle evidence updates - mempertahankan file existing dan upload file baru
     */
    private function handleEvidenceUpdates(Request $request, ReportDetail $reportDetail): array
    {
        $currentEvidences = $reportDetail->evidences ?? [];
        $newEvidences = $request->evidences ?? [];

        if (empty($newEvidences)) {
            // Jika evidences kosong, hapus semua file lama
            foreach ($currentEvidences as $evidence) {
                Storage::disk('public')->delete($evidence);
            }
            return [];
        }

        $finalEvidences = [];
        $filesToDelete = [];

        // Pisahkan antara file path existing dan base64 baru
        $existingPaths = [];
        $base64Images = [];

        foreach ($newEvidences as $evidence) {
            if (is_string($evidence)) {
                // Cek apakah ini path file existing atau base64
                if (strpos($evidence, 'report_evidences/') === 0) {
                    // Ini adalah path file existing
                    $existingPaths[] = $evidence;
                } elseif ($this->isBase64Image($evidence)) {
                    // Ini adalah base64 image
                    $base64Images[] = $evidence;
                }
            } elseif ($evidence instanceof \Illuminate\Http\UploadedFile) {
                // Handle file upload langsung
                $base64Images[] = $evidence;
            }
        }

        // Tentukan file mana yang perlu dihapus
        foreach ($currentEvidences as $currentEvidence) {
            if (!in_array($currentEvidence, $existingPaths)) {
                $filesToDelete[] = $currentEvidence;
            }
        }

        // Hapus file yang tidak ada di payload
        foreach ($filesToDelete as $fileToDelete) {
            Storage::disk('public')->delete($fileToDelete);
            Log::info('Deleted old evidence file', ['file' => $fileToDelete]);
        }

        // Tambahkan file existing yang dipertahankan
        $finalEvidences = array_merge($finalEvidences, $existingPaths);

        // Upload file/base64 baru
        foreach ($base64Images as $newImage) {
            try {
                if ($newImage instanceof \Illuminate\Http\UploadedFile) {
                    // Handle file upload
                    $path = $this->saveUploadedFile($newImage);
                    if ($path) {
                        $finalEvidences[] = $path;
                    }
                } else {
                    // Handle base64 image
                    $path = $this->saveBase64Image($newImage);
                    if ($path) {
                        $finalEvidences[] = $path;
                    }
                }
            } catch (\Exception $e) {
                Log::warning('Failed to process new evidence', [
                    'type' => $newImage instanceof \Illuminate\Http\UploadedFile ? 'file' : 'base64',
                    'error' => $e->getMessage()
                ]);
                // Continue processing other evidences
            }
        }

        return $finalEvidences;
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
     * Enhanced validation for mixed evidence types
     */
    private function validateReportDetailData(array $data, bool $isUpdate = false): \Illuminate\Contracts\Validation\Validator
    {
        $rules = [
            'correction_action' => $isUpdate ? 'sometimes|required|string|max:2000' : 'required|string|max:2000',
            'due_date' => $isUpdate ? 'sometimes|required|date|after_or_equal:today' : 'required|date|after_or_equal:today',
            'users_id' => $isUpdate ? 'sometimes|required|integer|exists:users,id' : 'required|integer|exists:users,id',
            'status_car' => 'nullable|in:open,in_progress,closed',
            'evidences' => 'nullable|array|max:10', // Maximum 10 evidences
        ];

        // Custom validation for evidences array
        if (isset($data['evidences']) && is_array($data['evidences'])) {
            foreach ($data['evidences'] as $index => $evidence) {
                if (is_string($evidence)) {
                    // Cek apakah ini path existing atau base64
                    if (strpos($evidence, 'report_evidences/') === 0) {
                        // Path existing file - validasi bahwa file exists
                        $rules["evidences.{$index}"] = 'string';
                    } else {
                        // Base64 validation
                        $rules["evidences.{$index}"] = 'string|max:10485760'; // ~8MB base64
                    }
                } else {
                    // File upload validation
                    $rules["evidences.{$index}"] = 'image|mimes:jpeg,png,jpg,gif|max:5120'; // 5MB
                }
            }
        }

        return Validator::make($data, $rules, [
            'evidences.max' => 'Maksimal 10 file evidence yang dapat diupload',
            'evidences.*.max' => 'Ukuran file evidence maksimal 5MB',
            'evidences.*.image' => 'Evidence harus berupa file gambar',
            'evidences.*.mimes' => 'Evidence harus berformat jpeg, png, jpg, atau gif',
        ]);
    }
}
