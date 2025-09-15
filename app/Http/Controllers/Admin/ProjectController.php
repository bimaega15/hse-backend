<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Yajra\DataTables\Facades\DataTables;
use Carbon\Carbon;

class ProjectController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        return view('admin.projects.index');
    }

    /**
     * Get projects data for DataTables
     */
    public function getData(Request $request): JsonResponse
    {
        try {
            $projects = Project::select(['id', 'code', 'project_name', 'start_date', 'end_date', 'durasi', 'status', 'created_at', 'updated_at']);

            return DataTables::of($projects)
                ->addIndexColumn()
                ->addColumn('status_badge', function ($project) {
                    $statusClass = $project->status === 'open' ? 'success' : 'secondary';
                    $statusText = $project->status === 'open' ? 'Open' : 'Closed';
                    return '<span class="badge bg-' . $statusClass . '">' . $statusText . '</span>';
                })
                ->addColumn('start_date_formatted', function ($project) {
                    return $project->start_date ? $project->start_date->format('d M Y') : '-';
                })
                ->addColumn('end_date_formatted', function ($project) {
                    return $project->end_date ? $project->end_date->format('d M Y') : '-';
                })
                ->addColumn('duration_days', function ($project) {
                    return $project->durasi . ' hari';
                })
                ->addColumn('created_at_formatted', function ($project) {
                    return $project->created_at->format('d M Y, H:i');
                })
                ->addColumn('action', function ($project) {
                    return '
                        <div class="btn-group" role="group">
                            <button type="button" class="btn btn-sm btn-soft-info" onclick="viewProject(' . $project->id . ')" title="View">
                                <i class="ri-eye-line"></i>
                            </button>
                            <button type="button" class="btn btn-sm btn-soft-warning" onclick="editProject(' . $project->id . ')" title="Edit">
                                <i class="ri-edit-line"></i>
                            </button>
                            <button type="button" class="btn btn-sm btn-soft-danger" onclick="deleteProject(' . $project->id . ')" title="Delete">
                            <i class="ri-delete-bin-line"></i>
                            </button>
                        </div>
                    ';
                })
                ->rawColumns(['status_badge', 'action'])
                ->make(true);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to load projects: ' . $e->getMessage()
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
                'code' => 'required|string|max:255|unique:projects,code',
                'project_name' => 'required|string|max:255',
                'start_date' => 'required|date',
                'end_date' => 'required|date|after_or_equal:start_date',
                'status' => 'required|in:open,closed'
            ], [
                'code.required' => 'Kode proyek wajib diisi',
                'code.unique' => 'Kode proyek sudah ada',
                'code.max' => 'Kode proyek maksimal 255 karakter',
                'project_name.required' => 'Nama proyek wajib diisi',
                'project_name.max' => 'Nama proyek maksimal 255 karakter',
                'start_date.required' => 'Tanggal mulai wajib diisi',
                'start_date.date' => 'Format tanggal mulai tidak valid',
                'end_date.required' => 'Tanggal selesai wajib diisi',
                'end_date.date' => 'Format tanggal selesai tidak valid',
                'end_date.after_or_equal' => 'Tanggal selesai harus setelah atau sama dengan tanggal mulai',
                'status.required' => 'Status wajib dipilih',
                'status.in' => 'Status harus open atau closed'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation Error',
                    'errors' => $validator->errors(),
                ], 422);
            }

            // Calculate duration
            $startDate = Carbon::parse($request->start_date);
            $endDate = Carbon::parse($request->end_date);
            $durasi = $startDate->diffInDays($endDate) + 1;

            $project = Project::create([
                'code' => $request->code,
                'project_name' => $request->project_name,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'durasi' => $durasi,
                'status' => $request->status
            ]);

            Log::info('Project created successfully', [
                'project_id' => $project->id,
                'code' => $project->code,
                'created_by' => auth()->id()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Proyek berhasil dibuat',
                'data' => $project
            ], 201);
        } catch (\Exception $e) {
            Log::error('Project creation failed', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat membuat proyek: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id): JsonResponse
    {
        try {
            $project = Project::find($id);

            if (!$project) {
                return response()->json([
                    'success' => false,
                    'message' => 'Proyek tidak ditemukan'
                ], 404);
            }

            // Format dates for HTML date inputs
            $projectData = $project->toArray();
            $projectData['start_date'] = $project->start_date ? $project->start_date->format('Y-m-d') : null;
            $projectData['end_date'] = $project->end_date ? $project->end_date->format('Y-m-d') : null;

            return response()->json([
                'success' => true,
                'data' => $projectData
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
            $project = Project::find($id);

            if (!$project) {
                return response()->json([
                    'success' => false,
                    'message' => 'Proyek tidak ditemukan'
                ], 404);
            }

            $validator = Validator::make($request->all(), [
                'code' => 'required|string|max:255|unique:projects,code,' . $id,
                'project_name' => 'required|string|max:255',
                'start_date' => 'required|date',
                'end_date' => 'required|date|after_or_equal:start_date',
                'status' => 'required|in:open,closed'
            ], [
                'code.required' => 'Kode proyek wajib diisi',
                'code.unique' => 'Kode proyek sudah ada',
                'code.max' => 'Kode proyek maksimal 255 karakter',
                'project_name.required' => 'Nama proyek wajib diisi',
                'project_name.max' => 'Nama proyek maksimal 255 karakter',
                'start_date.required' => 'Tanggal mulai wajib diisi',
                'start_date.date' => 'Format tanggal mulai tidak valid',
                'end_date.required' => 'Tanggal selesai wajib diisi',
                'end_date.date' => 'Format tanggal selesai tidak valid',
                'end_date.after_or_equal' => 'Tanggal selesai harus setelah atau sama dengan tanggal mulai',
                'status.required' => 'Status wajib dipilih',
                'status.in' => 'Status harus open atau closed'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation Error',
                    'errors' => $validator->errors(),
                ], 422);
            }

            // Calculate duration
            $startDate = Carbon::parse($request->start_date);
            $endDate = Carbon::parse($request->end_date);
            $durasi = $startDate->diffInDays($endDate) + 1;

            $project->update([
                'code' => $request->code,
                'project_name' => $request->project_name,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'durasi' => $durasi,
                'status' => $request->status
            ]);

            Log::info('Project updated successfully', [
                'project_id' => $project->id,
                'updated_by' => auth()->id()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Proyek berhasil diperbarui',
                'data' => $project
            ]);
        } catch (\Exception $e) {
            Log::error('Project update failed', [
                'project_id' => $id,
                'error' => $e->getMessage(),
                'user_id' => auth()->id()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat memperbarui proyek: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id): JsonResponse
    {
        try {
            $project = Project::find($id);

            if (!$project) {
                return response()->json([
                    'success' => false,
                    'message' => 'Proyek tidak ditemukan'
                ], 404);
            }

            $projectName = $project->project_name;
            $project->delete();

            Log::info('Project deleted successfully', [
                'project_id' => $id,
                'project_name' => $projectName,
                'deleted_by' => auth()->id()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Proyek berhasil dihapus'
            ]);
        } catch (\Exception $e) {
            Log::error('Project deletion failed', [
                'project_id' => $id,
                'error' => $e->getMessage(),
                'user_id' => auth()->id()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat menghapus proyek: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Toggle project status
     */
    public function toggleStatus($id): JsonResponse
    {
        try {
            $project = Project::find($id);

            if (!$project) {
                return response()->json([
                    'success' => false,
                    'message' => 'Proyek tidak ditemukan'
                ], 404);
            }

            $newStatus = $project->status === 'open' ? 'closed' : 'open';
            $project->update(['status' => $newStatus]);

            $statusText = $newStatus === 'open' ? 'dibuka' : 'ditutup';

            Log::info('Project status toggled', [
                'project_id' => $project->id,
                'new_status' => $newStatus,
                'updated_by' => auth()->id()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Status proyek berhasil ' . $statusText,
                'data' => [
                    'id' => $project->id,
                    'status' => $newStatus
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }
}