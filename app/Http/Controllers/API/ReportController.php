<?php
// app/Http/Controllers/API/ReportController.php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Report;
use App\Models\ObservationForm;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $query = Report::with(['employee', 'hseStaff', 'observationForm']);

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

        // Search functionality
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('category', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhere('location', 'like', "%{$search}%")
                    ->orWhereHas('employee', function ($q) use ($search) {
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

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'category' => 'required|string',
            'equipment_type' => 'required|string',
            'contributing_factor' => 'required|string',
            'description' => 'required|string',
            'location' => 'required|string',
            'images.*' => 'nullable|image|mimes:jpeg,png,jpg|max:5120', // 5MB max
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

        $data = $request->only(['category', 'equipment_type', 'contributing_factor', 'description', 'location']);
        $data['employee_id'] = $request->user()->id;

        // Handle image uploads
        $imagePaths = [];
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $imageName = time() . '_' . uniqid() . '.' . $image->getClientOriginalExtension();
                $imagePath = $image->storeAs('report_images', $imageName, 'public');
                $imagePaths[] = $imagePath;
            }
        }
        $data['images'] = $imagePaths;

        $report = Report::create($data);
        $report->load(['employee', 'hseStaff']);

        // Create notification for HSE staff
        $this->createReportNotification($report);

        return response()->json(
            [
                'success' => true,
                'message' => 'Laporan berhasil dibuat',
                'data' => $report,
            ],
            201,
        );
    }

    public function show($id)
    {
        $report = Report::with(['employee', 'hseStaff', 'observationForm'])->find($id);

        if (!$report) {
            return response()->json(
                [
                    'success' => false,
                    'message' => 'Laporan tidak ditemukan',
                ],
                404,
            );
        }

        // Check access permission
        $user = request()->user();
        if ($user->role === 'employee' && $report->employee_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Anda hanya dapat melihat laporan yang Anda buat sendiri.',
                'error_code' => 'FORBIDDEN'
            ], 403);
        }

        return response()->json([
            'success' => true,
            'data' => $report,
        ]);
    }

    /**
     * Update the specified report in storage.
     * Only employee who created the report can update it, and only if status is 'waiting'
     */
    public function update(Request $request, $id)
    {
        try {
            // Find the report
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

            // Validate input data
            $validator = Validator::make($request->all(), [
                'category' => 'required|string',
                'equipment_type' => 'required|string',
                'contributing_factor' => 'required|string',
                'description' => 'required|string',
                'location' => 'required|string',
                'images.*' => 'nullable|image|mimes:jpeg,png,jpg|max:5120'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation Error',
                    'errors' => $validator->errors(),
                    'error_code' => 'VALIDATION_ERROR'
                ], 422);
            }

            // Prepare update data
            $updateData = $request->only(['category', 'equipment_type', 'contributing_factor', 'description', 'location']);

            // Handle image uploads if new images are provided
            if ($request->hasFile('images')) {
                // Delete old images
                $this->deleteReportImages($report);

                // Upload new images
                $imagePaths = [];
                foreach ($request->file('images') as $image) {
                    $imageName = time() . '_' . uniqid() . '.' . $image->getClientOriginalExtension();
                    $imagePath = $image->storeAs('report_images', $imageName, 'public');
                    $imagePaths[] = $imagePath;
                }
                $updateData['images'] = $imagePaths;

                Log::info('Report images updated', [
                    'report_id' => $report->id,
                    'old_images_count' => count($report->images ?? []),
                    'new_images_count' => count($imagePaths)
                ]);
            }

            // Update the report
            $report->update($updateData);
            $report->load(['employee', 'hseStaff']);

            Log::info('Report updated successfully', [
                'report_id' => $report->id,
                'updated_by' => $user->id,
                'updated_fields' => array_keys($updateData)
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
     * Remove the specified report from storage.
     * Only employee who created the report can delete it, and only if status is 'waiting'
     */
    public function destroy($id)
    {
        try {
            // Find the report
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
                'category' => $report->category,
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

    public function startProcess(Request $request, $id)
    {
        $report = Report::find($id);

        if (!$report) {
            return response()->json(
                [
                    'success' => false,
                    'message' => 'Laporan tidak ditemukan',
                ],
                404,
            );
        }

        if ($report->status !== 'waiting') {
            return response()->json(
                [
                    'success' => false,
                    'message' => 'Laporan sudah diproses',
                ],
                400,
            );
        }

        if ($request->user()->role !== 'hse_staff') {
            return response()->json(
                [
                    'success' => false,
                    'message' => 'Hanya HSE staff yang dapat memproses laporan',
                ],
                403,
            );
        }

        $report->update([
            'status' => 'in-progress',
            'start_process_at' => now(),
            'hse_staff_id' => $request->user()->id,
        ]);

        $report->load(['employee', 'hseStaff']);

        return response()->json([
            'success' => true,
            'message' => 'Penanganan laporan dimulai',
            'data' => $report,
        ]);
    }

    public function complete(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'at_risk_behavior' => 'required|integer|min:0',
            'nearmiss_incident' => 'required|integer|min:0',
            'informasi_risk_mgmt' => 'required|integer|min:0',
            'sim_k3' => 'required|integer|min:0',
            'notes' => 'nullable|string',
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

        $report = Report::find($id);

        if (!$report) {
            return response()->json(
                [
                    'success' => false,
                    'message' => 'Laporan tidak ditemukan',
                ],
                404,
            );
        }

        if ($report->status !== 'in-progress') {
            return response()->json(
                [
                    'success' => false,
                    'message' => 'Laporan harus dalam status in-progress',
                ],
                400,
            );
        }

        if ($request->user()->role !== 'hse_staff') {
            return response()->json(
                [
                    'success' => false,
                    'message' => 'Hanya HSE staff yang dapat menyelesaikan laporan',
                ],
                403,
            );
        }

        // Update report status
        $report->update([
            'status' => 'done',
            'completed_at' => now(),
        ]);

        // Create observation form
        ObservationForm::create([
            'report_id' => $report->id,
            'at_risk_behavior' => $request->at_risk_behavior,
            'nearmiss_incident' => $request->nearmiss_incident,
            'informasi_risk_mgmt' => $request->informasi_risk_mgmt,
            'sim_k3' => $request->sim_k3,
            'notes' => $request->notes,
        ]);

        $report->load(['employee', 'hseStaff', 'observationForm']);

        return response()->json([
            'success' => true,
            'message' => 'Laporan berhasil diselesaikan',
            'data' => $report,
        ]);
    }

    public function statistics(Request $request)
    {
        $user = $request->user();
        $query = Report::query();

        // Filter by user role
        if ($user->role === 'employee') {
            $query->where('employee_id', $user->id);
        }

        $total = $query->count();
        $waiting = $query->clone()->where('status', 'waiting')->count();
        $inProgress = $query->clone()->where('status', 'in-progress')->count();
        $done = $query->clone()->where('status', 'done')->count();

        return response()->json([
            'success' => true,
            'data' => [
                'total' => $total,
                'waiting' => $waiting,
                'in_progress' => $inProgress,
                'done' => $done,
            ],
        ]);
    }

    /**
     * Helper method to delete report images from storage
     */
    private function deleteReportImages(Report $report)
    {
        if ($report->images && is_array($report->images)) {
            foreach ($report->images as $imagePath) {
                if (Storage::disk('public')->exists($imagePath)) {
                    Storage::disk('public')->delete($imagePath);
                    Log::info('Report image deleted', ['path' => $imagePath]);
                }
            }
        }
    }

    /**
     * Helper method to create notification for HSE staff when new report is created
     */
    private function createReportNotification(Report $report)
    {
        // Get all HSE staff
        $hseStaffs = User::where('role', 'hse_staff')->where('is_active', true)->get();

        foreach ($hseStaffs as $staff) {
            $staff->notifications()->create([
                'title' => 'Laporan Baru Diterima',
                'message' => "Ada laporan keselamatan baru dari {$report->employee->name} di {$report->location}",
                'type' => 'warning',
                'category' => 'reports',
                'data' => [
                    'report_id' => $report->id,
                    'action' => 'new_report',
                    'employee_name' => $report->employee->name,
                    'location' => $report->location,
                ],
            ]);
        }
    }
}
