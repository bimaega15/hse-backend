<?php
// app/Http/Controllers/Admin/HseKpiController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CategoryKpi;
use App\Models\HseKpi;
use App\Models\HseKpiDetail;
use App\Models\Location;
use App\Models\Project;
use App\Models\User;
use App\Support\KpiScoring;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\Facades\DataTables;

/**
 * HSE KPI (header + details) — full CRUD.
 */
class HseKpiController extends Controller
{
    public function index(): View
    {
        $categories = CategoryKpi::active()->orderBy('category_name')->get(['id', 'category_name']);
        $projects   = Project::orderBy('project_name')->get(['id', 'project_name']);
        $hseStaff   = User::where('role', 'hse_staff')->where('is_active', true)->orderBy('name')->get(['id', 'name', 'department']);

        $typeTargets = HseKpiDetail::TYPE_TARGETS;
        $defaultRumus = KpiScoring::defaultRumus();
        $bands = KpiScoring::bands();

        return view('admin.kpi.hse.index', compact('categories', 'projects', 'hseStaff', 'typeTargets', 'defaultRumus', 'bands'));
    }

    public function getData(Request $request): JsonResponse
    {
        try {
            $query = HseKpi::with(['categoryKpi:id,category_name', 'project:id,project_name'])
                ->withCount('details');

            if ($request->filled('category_kpi_id')) {
                $query->where('category_kpi_id', $request->category_kpi_id);
            }
            if ($request->filled('project_id')) {
                $query->where('project_id', $request->project_id);
            }
            if ($request->filled('month')) {
                try {
                    [$y, $m] = explode('-', $request->month);
                    $query->whereYear('report_date', $y)->whereMonth('report_date', $m);
                } catch (\Throwable $e) {
                }
            }

            $query->orderBy('report_date', 'desc');

            return DataTables::of($query)
                ->addIndexColumn()
                ->addColumn('category_name', fn($k) => $k->categoryKpi->category_name ?? '-')
                ->addColumn('project_name', fn($k) => $k->project->project_name ?? '-')
                ->addColumn('report_date_formatted', fn($k) => optional($k->report_date)->format('d M Y'))
                ->addColumn('users_display', function ($k) {
                    $users = $k->assigned_users;
                    if ($users->isEmpty()) {
                        return '<span class="text-muted">-</span>';
                    }
                    return $users->map(fn($u) => '<span class="badge bg-info-subtle text-info">' . e($u->name) . '</span>')->implode(' ');
                })
                ->addColumn('detail_count', fn($k) => '<span class="badge bg-primary-subtle text-primary">' . $k->details_count . '</span>')
                ->addColumn('average_display', fn($k) => $k->average !== null ? rtrim(rtrim(number_format($k->average, 1), '0'), '.') . '%' : '-')
                ->addColumn('nilai_badge', function ($k) {
                    $label = $k->overall_nilai;
                    if (!$label) {
                        return '<span class="text-muted">-</span>';
                    }
                    return '<span class="badge ' . $this->nilaiBadgeClass($label) . '">' . ucwords($label) . '</span>';
                })
                ->addColumn('action', function ($k) {
                    return '
                        <div class="btn-group" role="group">
                            <button type="button" class="btn btn-sm btn-soft-info" onclick="viewHseKpi(' . $k->id . ')" title="View"><i class="ri-eye-line"></i></button>
                            <button type="button" class="btn btn-sm btn-soft-warning" onclick="editHseKpi(' . $k->id . ')" title="Edit"><i class="ri-edit-line"></i></button>
                            <button type="button" class="btn btn-sm btn-soft-danger" onclick="deleteHseKpi(' . $k->id . ')" title="Delete"><i class="ri-delete-bin-line"></i></button>
                        </div>';
                })
                ->rawColumns(['users_display', 'detail_count', 'nilai_badge', 'action'])
                ->make(true);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to load: ' . $e->getMessage()], 500);
        }
    }

    public function store(Request $request): JsonResponse
    {
        return $this->save($request);
    }

    public function update(Request $request, $id): JsonResponse
    {
        return $this->save($request, $id);
    }

    private function save(Request $request, $id = null): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'category_kpi_id'        => 'required|exists:category_kpi,id',
            'project_id'             => 'required|exists:projects,id',
            'users_id'               => 'nullable|array',
            'users_id.*'             => 'integer|exists:users,id',
            'report_date'            => 'required|date',
            'description'            => 'nullable|string|max:2000',
            'details'                => 'required|array|min:1',
            'details.*.id'           => 'nullable|integer',
            'details.*.activity_name' => 'required|string|max:500',
            'details.*.type_target'  => 'required|in:' . implode(',', HseKpiDetail::TYPE_TARGETS),
            'details.*.target'       => 'required|numeric',
            'details.*.realisasi'    => 'nullable|numeric',
        ], [
            'details.required' => 'Minimal 1 indikator harus diisi',
            'category_kpi_id.required' => 'Kategori KPI wajib dipilih',
            'project_id.required' => 'Project wajib dipilih',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => 'Validation Error', 'errors' => $validator->errors()], 422);
        }

        try {
            DB::beginTransaction();

            // Resolve rumus: use submitted JSON if valid, else default for the chosen category
            $category = CategoryKpi::find($request->category_kpi_id);
            $key = $category->indicator_key;
            $rumus = $this->resolveRumus($request->input('rumus'), $key);

            $headerData = [
                'category_kpi_id' => $request->category_kpi_id,
                'project_id'      => $request->project_id,
                'users_id'        => $request->input('users_id', []),
                'report_date'     => $request->report_date,
                'description'     => $request->description,
                'rumus'           => $rumus,
            ];

            if ($id) {
                $kpi = HseKpi::find($id);
                if (!$kpi) {
                    DB::rollBack();
                    return response()->json(['success' => false, 'message' => 'HSE KPI tidak ditemukan'], 404);
                }
                $kpi->update($headerData);
            } else {
                $kpi = HseKpi::create($headerData);
            }

            // Sync details
            $submitted = $request->input('details', []);
            $keepIds = [];

            foreach ($submitted as $row) {
                $payload = [
                    'activity_name' => $row['activity_name'],
                    'type_target'   => $row['type_target'],
                    'target'        => $row['target'],
                    'realisasi'     => ($row['realisasi'] ?? null) === '' ? null : ($row['realisasi'] ?? null),
                ];

                if (!empty($row['id'])) {
                    $detail = HseKpiDetail::where('hse_kpi_id', $kpi->id)->find($row['id']);
                    if ($detail) {
                        $detail->update($payload);
                        $keepIds[] = $detail->id;
                        continue;
                    }
                }
                $detail = $kpi->details()->create($payload);
                $keepIds[] = $detail->id;
            }

            // Remove details no longer present
            $kpi->details()->whereNotIn('id', $keepIds)->delete();

            $kpi->load('details');
            $kpi->recalculateAverage();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => $id ? 'HSE KPI berhasil diperbarui' : 'HSE KPI berhasil dibuat',
                'data' => ['id' => $kpi->id],
            ], $id ? 200 : 201);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('HseKpi save failed', ['error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => 'Terjadi kesalahan: ' . $e->getMessage()], 500);
        }
    }

    public function show($id): JsonResponse
    {
        $kpi = HseKpi::with(['categoryKpi:id,category_name', 'project:id,project_name', 'details'])->find($id);

        if (!$kpi) {
            return response()->json(['success' => false, 'message' => 'HSE KPI tidak ditemukan'], 404);
        }

        $details = $kpi->details->map(fn(HseKpiDetail $d) => [
            'id'             => $d->id,
            'activity_name'  => $d->activity_name,
            'type_target'    => $d->type_target,
            'target'         => $d->target,
            'target_display' => $d->target_display,
            'realisasi'      => $d->realisasi,
            'percentage'     => $d->percentage,
            'nilai_label'    => $d->nilai_label,
        ]);

        return response()->json([
            'success' => true,
            'data' => [
                'id'              => $kpi->id,
                'category_kpi_id' => $kpi->category_kpi_id,
                'category_name'   => $kpi->categoryKpi->category_name ?? '-',
                'indicator_key'   => $kpi->indicator_key,
                'project_id'      => $kpi->project_id,
                'project_name'    => $kpi->project->project_name ?? '-',
                'users_id'        => $kpi->users_id ?? [],
                'assigned_users'  => $kpi->assigned_users,
                'report_date'     => optional($kpi->report_date)->format('Y-m-d'),
                'description'     => $kpi->description,
                'average'         => $kpi->average,
                'overall_nilai'   => $kpi->overall_nilai,
                'rumus'           => $kpi->rumus,
                'details'         => $details,
            ],
        ]);
    }

    public function destroy($id): JsonResponse
    {
        try {
            $kpi = HseKpi::find($id);
            if (!$kpi) {
                return response()->json(['success' => false, 'message' => 'HSE KPI tidak ditemukan'], 404);
            }
            $kpi->details()->delete();
            $kpi->delete();
            return response()->json(['success' => true, 'message' => 'HSE KPI berhasil dihapus']);
        } catch (\Exception $e) {
            Log::error('HseKpi delete failed', ['id' => $id, 'error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => 'Terjadi kesalahan: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Return default rumus for a category key (used when the category changes in the form).
     */
    public function getDefaultRumus(Request $request): JsonResponse
    {
        $categoryId = $request->get('category_kpi_id');
        $category = CategoryKpi::find($categoryId);
        $key = $category ? $category->indicator_key : 'leading_indicator';

        return response()->json([
            'success' => true,
            'data' => [
                'indicator_key' => $key,
                'rumus' => [KpiScoring::rumusFor($key)],
            ],
        ]);
    }

    private function resolveRumus($input, string $key): array
    {
        if (is_string($input) && trim($input) !== '') {
            $decoded = json_decode($input, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                return $decoded;
            }
        }
        if (is_array($input) && !empty($input)) {
            return $input;
        }
        return [KpiScoring::rumusFor($key)];
    }

    private function nilaiBadgeClass(string $label): string
    {
        return match ($label) {
            'sangat baik' => 'bg-success',
            'baik'        => 'bg-primary',
            'cukup'       => 'bg-info text-dark',
            'kurang'      => 'bg-warning text-dark',
            'kurang baik' => 'bg-danger',
            default       => 'bg-secondary',
        };
    }
}
