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
            'reportDetails.createdBy'
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

        $query = $report->reportDetails()->with(['approvedBy', 'createdBy']);

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
                    ->orWhere('pic', 'like', "%{$search}%");
            });
        }

        // Sort by due_date and created_at
        $query->orderBy('due_date', 'asc')
            ->orderBy('created_at', 'desc');

        $reportDetails = $query->paginate($request->get('per_page', 10));

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
        if (!$report->canHaveReportDetails()) {
            return response()->json([
                'success' => false,
                'message' => 'Detail laporan hanya dapat ditambahkan setelah laporan diproses HSE',
            ], 400);
        }

        $validator = Validator::make($request->all(), [
            'correction_action' => 'required|string|max:2000',
            'due_date' => 'required|date|after_or_equal:today',
            'pic' => 'nullable|string|max:255',
            'status_car' => 'nullable|in:open,in_progress,closed',
            'evidences' => 'nullable|array',
            'evidences.*' => 'image|mimes:jpeg,png,jpg,gif|max:5120', // 5MB
        ]);

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
                'pic' => $request->pic ?? $user->name, // Default to current user
                'status_car' => $request->status_car ?? 'open',
                'approved_by' => $user->id,
                'created_by' => $user->id,
            ];

            // Handle evidence uploads
            if ($request->hasFile('evidences')) {
                $evidencePaths = $this->uploadEvidences($request->file('evidences'));
                $reportDetailData['evidences'] = $evidencePaths;
            }

            // Create the report detail
            $reportDetail = ReportDetail::create($reportDetailData);
            $reportDetail->load(['approvedBy', 'createdBy', 'report']);

            Log::info('Report detail created successfully', [
                'report_detail_id' => $reportDetail->id,
                'report_id' => $reportId,
                'hse_staff_id' => $user->id,
                'due_date' => $request->due_date
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

        $reportDetail = ReportDetail::with(['report', 'approvedBy', 'createdBy'])
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
     * Update report detail (HSE staff only)
     */
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

        $validator = Validator::make($request->all(), [
            'correction_action' => 'sometimes|required|string|max:2000',
            'due_date' => 'sometimes|required|date|after_or_equal:today',
            'pic' => 'sometimes|nullable|string|max:255',
            'status_car' => 'sometimes|nullable|in:open,in_progress,closed',
            'evidences' => 'nullable|array',
            'evidences.*' => 'image|mimes:jpeg,png,jpg,gif|max:5120', // 5MB
        ]);

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
                'pic',
                'status_car'
            ]);

            // Handle evidence uploads
            if ($request->hasFile('evidences')) {
                // Delete old evidences
                if ($reportDetail->evidences) {
                    foreach ($reportDetail->evidences as $evidence) {
                        Storage::disk('public')->delete($evidence);
                    }
                }

                $evidencePaths = $this->uploadEvidences($request->file('evidences'));
                $updateData['evidences'] = $evidencePaths;
            }

            $reportDetail->update($updateData);
            $reportDetail->load(['approvedBy', 'createdBy', 'report']);

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
            $reportDetail->load(['approvedBy', 'createdBy', 'report']);

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
     * Upload evidence images
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
}
