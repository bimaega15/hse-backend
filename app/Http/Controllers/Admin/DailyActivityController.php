<?php
// app/Http/Controllers/Admin/DailyActivityController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DailyActivity;
use App\Models\DailyActivityDetail;
use App\Models\User;
use App\Models\Project;
use App\Models\Location;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Yajra\DataTables\Facades\DataTables;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

/**
 * Daily Activity (web admin).
 *
 *  - Header (daily_activities): full CRUD by admin (assigns an hse_staff user).
 *  - Detail (daily_activity_details): READ ONLY here — filled by hse_staff via
 *    the mobile API.
 */
class DailyActivityController extends Controller
{
    public function index(): View
    {
        $hseStaff  = User::where('role', 'hse_staff')->where('is_active', true)->orderBy('name')->get(['id', 'name', 'department']);
        $projects  = Project::orderBy('project_name')->get(['id', 'project_name']);
        $locations = Location::orderBy('name')->get(['id', 'name']);

        return view('admin.daily-activities.index', compact('hseStaff', 'projects', 'locations'));
    }

    public function getData(Request $request): JsonResponse
    {
        try {
            $query = DailyActivity::with(['user:id,name,department', 'project:id,project_name', 'location:id,name'])
                ->withCount('details');

            // Advance search — per tanggal (range)
            if ($request->filled('date_from')) {
                $query->whereDate('datetime_activity', '>=', $request->date_from);
            }
            if ($request->filled('date_to')) {
                $query->whereDate('datetime_activity', '<=', $request->date_to);
            }

            // Per bulan (YYYY-MM)
            if ($request->filled('month')) {
                try {
                    [$year, $month] = explode('-', $request->month);
                    $query->whereYear('datetime_activity', $year)->whereMonth('datetime_activity', $month);
                } catch (\Throwable $e) {
                    // ignore malformed month
                }
            }

            // Per personel (assigned hse_staff)
            if ($request->filled('user_id')) {
                $query->where('user_id', $request->user_id);
            }

            // Per project / location
            if ($request->filled('project_id')) {
                $query->where('project_id', $request->project_id);
            }
            if ($request->filled('location_id')) {
                $query->where('location_id', $request->location_id);
            }

            $query->orderBy('datetime_activity', 'desc');

            return DataTables::of($query)
                ->addIndexColumn()
                ->addColumn('datetime_formatted', fn($d) => $d->datetime_activity ? $d->datetime_activity->format('d M Y, H:i') : '-')
                ->addColumn('personel_name', fn($d) => $d->user->name ?? '-')
                ->addColumn('project_name', fn($d) => $d->project->project_name ?? '-')
                ->addColumn('location_name', fn($d) => $d->location->name ?? '-')
                ->addColumn('description_short', fn($d) => $d->description ? e(Str::limit($d->description, 50)) : '<span class="text-muted">-</span>')
                ->addColumn('todo_count', function ($d) {
                    return '<span class="badge bg-primary-subtle text-primary"><i class="ri-list-check-2 me-1"></i>'
                        . $d->details_count . ' to-do</span>';
                })
                ->addColumn('action', function ($d) {
                    return '
                        <div class="btn-group" role="group">
                            <button type="button" class="btn btn-sm btn-soft-info" onclick="viewDailyActivity(' . $d->id . ')" title="View To-do List">
                                <i class="ri-eye-line"></i>
                            </button>
                            <button type="button" class="btn btn-sm btn-soft-warning" onclick="editDailyActivity(' . $d->id . ')" title="Edit">
                                <i class="ri-edit-line"></i>
                            </button>
                            <button type="button" class="btn btn-sm btn-soft-danger" onclick="deleteDailyActivity(' . $d->id . ')" title="Delete">
                                <i class="ri-delete-bin-line"></i>
                            </button>
                        </div>
                    ';
                })
                ->rawColumns(['description_short', 'todo_count', 'action'])
                ->make(true);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to load daily activities: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function getStatistics(): JsonResponse
    {
        try {
            return response()->json([
                'success' => true,
                'data' => [
                    'total'      => DailyActivity::count(),
                    'this_month' => DailyActivity::thisMonth()->count(),
                    'total_todo' => DailyActivityDetail::count(),
                    'personel'   => DailyActivity::distinct('user_id')->count('user_id'),
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to load statistics: ' . $e->getMessage()], 500);
        }
    }

    public function store(Request $request): JsonResponse
    {
        try {
            $validator = $this->validateHeader($request);

            if ($validator->fails()) {
                return response()->json(['success' => false, 'message' => 'Validation Error', 'errors' => $validator->errors()], 422);
            }

            $daily = DailyActivity::create($request->only([
                'user_id', 'datetime_activity', 'project_id', 'location_id', 'description',
            ]));

            Log::info('DailyActivity header created', ['id' => $daily->id, 'created_by' => auth()->id()]);

            return response()->json(['success' => true, 'message' => 'Daily Activity berhasil dibuat', 'data' => $daily], 201);
        } catch (\Exception $e) {
            Log::error('DailyActivity store failed', ['error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => 'Terjadi kesalahan: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Header detail + its to-do list (read-only) for the view modal.
     * When called for the edit form it still returns the raw header fields.
     */
    public function show($id): JsonResponse
    {
        $daily = DailyActivity::with([
            'user:id,name,email,department',
            'project:id,project_name',
            'location:id,name',
            'details.activity:id,name',
            'details.user:id,name',
        ])->find($id);

        if (!$daily) {
            return response()->json(['success' => false, 'message' => 'Daily Activity tidak ditemukan'], 404);
        }

        $details = $daily->details->map(function (DailyActivityDetail $d) {
            return [
                'id'                   => $d->id,
                'activity'             => $d->activity->name ?? '-',
                'todolist'             => $d->todolist,
                'activity_datetime'    => optional($d->activity_datetime)->format('d M Y, H:i'),
                'status'               => $d->status,
                'status_label'         => $d->status_label,
                'description_status'   => $d->description_status,
                'realization_datetime' => optional($d->realization_datetime)->format('d M Y, H:i'),
                'picture_urls'         => $d->picture_urls,
                'user'                 => $d->user->name ?? '-',
            ];
        });

        return response()->json([
            'success' => true,
            'data' => [
                'id'                 => $daily->id,
                'user_id'            => $daily->user_id,
                'project_id'         => $daily->project_id,
                'location_id'        => $daily->location_id,
                'datetime_activity'  => optional($daily->datetime_activity)->format('Y-m-d\TH:i'),
                'datetime_formatted' => optional($daily->datetime_activity)->format('d M Y, H:i'),
                'description'        => $daily->description,
                'personel'          => $daily->user,
                'project'           => $daily->project,
                'location'          => $daily->location,
                'details'           => $details,
            ],
        ]);
    }

    public function update(Request $request, $id): JsonResponse
    {
        try {
            $daily = DailyActivity::find($id);

            if (!$daily) {
                return response()->json(['success' => false, 'message' => 'Daily Activity tidak ditemukan'], 404);
            }

            $validator = $this->validateHeader($request);

            if ($validator->fails()) {
                return response()->json(['success' => false, 'message' => 'Validation Error', 'errors' => $validator->errors()], 422);
            }

            $daily->update($request->only([
                'user_id', 'datetime_activity', 'project_id', 'location_id', 'description',
            ]));

            Log::info('DailyActivity header updated', ['id' => $daily->id, 'updated_by' => auth()->id()]);

            return response()->json(['success' => true, 'message' => 'Daily Activity berhasil diperbarui', 'data' => $daily]);
        } catch (\Exception $e) {
            Log::error('DailyActivity update failed', ['id' => $id, 'error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => 'Terjadi kesalahan: ' . $e->getMessage()], 500);
        }
    }

    public function destroy($id): JsonResponse
    {
        try {
            $daily = DailyActivity::find($id);

            if (!$daily) {
                return response()->json(['success' => false, 'message' => 'Daily Activity tidak ditemukan'], 404);
            }

            $daily->delete(); // soft delete header (details remain linked / soft-deleted via app logic)

            Log::info('DailyActivity deleted', ['id' => $id, 'deleted_by' => auth()->id()]);

            return response()->json(['success' => true, 'message' => 'Daily Activity berhasil dihapus']);
        } catch (\Exception $e) {
            Log::error('DailyActivity delete failed', ['id' => $id, 'error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => 'Terjadi kesalahan: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Export the (filtered) Daily Activity data to Excel.
     *
     * Layout mirrors "LAPORAN AKTIFITAS HARIAN HSE PERSONNEL". When no specific
     * personnel is filtered, each HSE personnel gets its own sheet.
     */
    public function exportExcel(Request $request)
    {
        try {
            $query = DailyActivity::with([
                'user:id,name,email,department,phone,profile_image',
                'project:id,project_name',
                'location:id,name',
                'details' => fn($q) => $q->orderBy('activity_datetime'),
                'details.activity:id,name',
            ]);

            // Same advance-search filters as the list
            if ($request->filled('date_from')) {
                $query->whereDate('datetime_activity', '>=', $request->date_from);
            }
            if ($request->filled('date_to')) {
                $query->whereDate('datetime_activity', '<=', $request->date_to);
            }
            if ($request->filled('month')) {
                try {
                    [$year, $month] = explode('-', $request->month);
                    $query->whereYear('datetime_activity', $year)->whereMonth('datetime_activity', $month);
                } catch (\Throwable $e) {
                }
            }
            if ($request->filled('user_id')) {
                $query->where('user_id', $request->user_id);
            }
            if ($request->filled('project_id')) {
                $query->where('project_id', $request->project_id);
            }
            if ($request->filled('location_id')) {
                $query->where('location_id', $request->location_id);
            }

            $activities = $query->orderBy('datetime_activity')->get();

            // Group by assigned personnel -> one sheet each
            $grouped = $activities->groupBy('user_id');

            $spreadsheet = new Spreadsheet();
            $spreadsheet->removeSheetByIndex(0); // start clean; we add named sheets

            $usedTitles = [];

            if ($grouped->isEmpty()) {
                $sheet = $spreadsheet->createSheet();
                $sheet->setTitle('Tidak Ada Data');
                $sheet->setCellValue('A1', 'LAPORAN AKTIFITAS HARIAN HSE PERSONNEL');
                $sheet->mergeCells('A1:K1');
                $sheet->setCellValue('A3', 'Tidak ada data untuk filter yang dipilih.');
            } else {
                foreach ($grouped as $userId => $userActivities) {
                    $user = $userActivities->first()->user;
                    $title = $this->uniqueSheetTitle($user->name ?? ('Personel ' . $userId), $usedTitles);

                    $sheet = $spreadsheet->createSheet();
                    $sheet->setTitle($title);
                    $this->buildPersonnelSheet($sheet, $user, $userActivities);
                }
            }

            $spreadsheet->setActiveSheetIndex(0);

            $writer = new Xlsx($spreadsheet);
            $writer->setIncludeCharts(false);

            $filename = 'laporan_aktifitas_harian_hse_' . date('Y-m-d_H-i-s') . '.xlsx';
            $tempFile = tempnam(sys_get_temp_dir(), 'da_excel');
            $writer->save($tempFile);

            return response()->download($tempFile, $filename, [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
                'Cache-Control' => 'max-age=0',
            ])->deleteFileAfterSend(true);
        } catch (\Exception $e) {
            Log::error('Daily Activity export failed', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Gagal export data: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Render one personnel sheet (header block + activity table).
     */
    private function buildPersonnelSheet(Worksheet $sheet, ?User $user, $activities): void
    {
        $headerFill = 'FCE4D6'; // light orange (matches the sample report)

        // --- Title ---
        $sheet->setCellValue('A1', 'LAPORAN AKTIFITAS HARIAN HSE PERSONNEL');
        $sheet->mergeCells('A1:K1');
        $sheet->getStyle('A1')->applyFromArray([
            'font' => ['bold' => true, 'size' => 14],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ]);
        $sheet->getRowDimension(1)->setRowHeight(24);

        // --- Personnel info block ---
        $nip = $user ? str_pad((string) $user->id, 3, '0', STR_PAD_LEFT) . '-05-' . date('Y') : '-';
        $jabatan = $user ? ($user->department ?: ucfirst(str_replace('_', ' ', $user->role ?? 'HSE Staff'))) : '-';

        $info = [
            ['NIP', $nip],
            ['NAMA PERSONEL', $user->name ?? '-'],
            ['JABATAN', $jabatan],
        ];
        $r = 2;
        foreach ($info as [$label, $value]) {
            $sheet->setCellValue('A' . $r, $label);
            $sheet->setCellValue('B' . $r, ': ' . $value);
            $sheet->getStyle('A' . $r)->getFont()->setBold(true);
            $sheet->mergeCells('B' . $r . ':E' . $r);
            $r++;
        }

        // Personnel photo (top-right) if available
        $this->embedPersonnelPhoto($sheet, $user);

        // --- Table header ---
        $headerRow = 6;
        $headers = ['NO', 'KEGIATAN', 'PROJECT', 'LOKASI', 'TGL Kegiatan', 'JAM', 'To do list', 'TGL realisasi', 'STATUS', 'Deskripsi / Keterangan', 'Foto'];
        $col = 'A';
        foreach ($headers as $h) {
            $sheet->setCellValue($col . $headerRow, $h);
            $col++;
        }
        $sheet->getStyle('A' . $headerRow . ':K' . $headerRow)->applyFromArray([
            'font' => ['bold' => true],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $headerFill]],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
        ]);
        $sheet->getRowDimension($headerRow)->setRowHeight(28);

        // --- Data rows: flatten each activity's details ---
        $statusLabels = DailyActivityDetail::STATUSES;
        $rowNum = $headerRow + 1;
        $no = 0;

        foreach ($activities as $activity) {
            $projectName = $activity->project->project_name ?? '-';
            $locationName = $activity->location->name ?? '-';

            $details = $activity->details ?? collect();

            if ($details->isEmpty()) {
                continue;
            }

            foreach ($details as $detail) {
                $no++;
                $hasPhoto = !empty($detail->pictures_activity) && is_array($detail->pictures_activity);

                $sheet->setCellValue('A' . $rowNum, $no);
                $sheet->setCellValue('B' . $rowNum, $detail->activity->name ?? '-');
                $sheet->setCellValue('C' . $rowNum, $projectName);
                $sheet->setCellValue('D' . $rowNum, $locationName);
                $sheet->setCellValue('E' . $rowNum, optional($detail->activity_datetime)->format('d/m/Y') ?? '-');
                $sheet->setCellValue('F' . $rowNum, optional($detail->activity_datetime)->format('H:i') ?? '-');
                $sheet->setCellValue('G' . $rowNum, $detail->todolist ?? '-');
                $sheet->setCellValue('H' . $rowNum, optional($detail->realization_datetime)->format('d/m/Y H:i') ?? '-');
                $sheet->setCellValue('I' . $rowNum, $statusLabels[$detail->status] ?? $detail->status);
                $sheet->setCellValue('J' . $rowNum, $detail->description_status ?? '-');

                // Foto (embed first picture)
                if ($hasPhoto) {
                    $this->embedDetailPhoto($sheet, $detail, $rowNum);
                } else {
                    $sheet->setCellValue('K' . $rowNum, '-');
                }

                // status cell coloring
                $sheet->getStyle('I' . $rowNum)->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()->setRGB($this->statusColor($detail->status));

                $rowNum++;
            }
        }

        // No detail rows fallback
        if ($no === 0) {
            $sheet->setCellValue('A' . $rowNum, 'Belum ada to-do list dari personel');
            $sheet->mergeCells('A' . $rowNum . ':K' . $rowNum);
            $sheet->getStyle('A' . $rowNum)->getFont()->setItalic(true);
            $rowNum++;
        }

        // --- Borders + alignment for the table body ---
        $lastRow = max($rowNum - 1, $headerRow + 1);
        $sheet->getStyle('A' . ($headerRow + 1) . ':K' . $lastRow)->applyFromArray([
            'alignment' => ['vertical' => Alignment::VERTICAL_TOP, 'wrapText' => true],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
        ]);
        $sheet->getStyle('A' . ($headerRow + 1) . ':A' . $lastRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('E' . ($headerRow + 1) . ':F' . $lastRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // --- Column widths ---
        foreach (['A' => 5, 'B' => 22, 'C' => 22, 'D' => 16, 'E' => 12, 'F' => 8, 'G' => 30, 'H' => 16, 'I' => 14, 'J' => 28, 'K' => 18] as $c => $w) {
            $sheet->getColumnDimension($c)->setWidth($w);
        }
    }

    private function embedDetailPhoto(Worksheet $sheet, DailyActivityDetail $detail, int $rowNum): void
    {
        $images = is_array($detail->pictures_activity) ? $detail->pictures_activity : [];
        foreach ($images as $rel) {
            if (!is_string($rel) || $rel === '') {
                continue;
            }
            foreach ([public_path('storage/' . $rel), storage_path('app/public/' . $rel)] as $absPath) {
                if (is_file($absPath) && is_readable($absPath)) {
                    try {
                        $size = @getimagesize($absPath);
                        $maxW = 110;
                        $maxH = 90;
                        $scale = min($maxW / max($size[0] ?? $maxW, 1), $maxH / max($size[1] ?? $maxH, 1), 1);
                        $sheet->getRowDimension($rowNum)->setRowHeight($maxH, 'px');

                        $drawing = new Drawing();
                        $drawing->setName('Foto');
                        $drawing->setPath($absPath);
                        $drawing->setResizeProportional(false);
                        $drawing->setWidth((int) round(($size[0] ?? $maxW) * $scale));
                        $drawing->setHeight((int) round(($size[1] ?? $maxH) * $scale));
                        $drawing->setOffsetX(4);
                        $drawing->setOffsetY(4);
                        $drawing->setCoordinates('K' . $rowNum);
                        $drawing->setWorksheet($sheet);

                        if (count($images) > 1) {
                            $sheet->setCellValue('K' . $rowNum, count($images) . ' foto');
                            $sheet->getStyle('K' . $rowNum)->applyFromArray([
                                'font' => ['size' => 7, 'color' => ['rgb' => '666666']],
                                'alignment' => ['vertical' => Alignment::VERTICAL_BOTTOM],
                            ]);
                        }
                        return;
                    } catch (\Exception $e) {
                        Log::warning('Failed to embed daily activity photo: ' . $absPath . ' — ' . $e->getMessage());
                    }
                }
            }
        }
        $sheet->setCellValue('K' . $rowNum, 'Y');
    }

    private function embedPersonnelPhoto(Worksheet $sheet, ?User $user): void
    {
        if (!$user || empty($user->profile_image)) {
            return;
        }
        $rel = ltrim($user->profile_image, '/');
        foreach ([public_path('storage/' . $rel), storage_path('app/public/' . $rel), public_path($rel)] as $absPath) {
            if (is_file($absPath) && is_readable($absPath)) {
                try {
                    $drawing = new Drawing();
                    $drawing->setName('Foto Personel');
                    $drawing->setPath($absPath);
                    $drawing->setHeight(70);
                    $drawing->setOffsetX(5);
                    $drawing->setOffsetY(2);
                    $drawing->setCoordinates('I2');
                    $drawing->setWorksheet($sheet);
                    return;
                } catch (\Exception $e) {
                    // ignore photo failure
                }
            }
        }
    }

    private function statusColor(?string $status): string
    {
        return match ($status) {
            'done'        => 'D1E7DD',
            'in_progress' => 'CFE2FF',
            'pending'     => 'FFF3CD',
            'cancel'      => 'E2E3E5',
            'rejected'    => 'F8D7DA',
            default       => 'FFFFFF',
        };
    }

    /**
     * Build a valid, unique Excel sheet title (max 31 chars, no special chars).
     */
    private function uniqueSheetTitle(string $name, array &$used): string
    {
        $clean = preg_replace('/[\\\\\/\?\*\[\]\:]/', ' ', $name);
        $clean = trim(Str::limit($clean, 28, ''));
        if ($clean === '') {
            $clean = 'Personel';
        }

        $title = $clean;
        $i = 2;
        while (in_array(strtolower($title), $used, true)) {
            $title = Str::limit($clean, 27, '') . ' ' . $i;
            $i++;
        }
        $used[] = strtolower($title);
        return $title;
    }

    private function validateHeader(Request $request): \Illuminate\Validation\Validator
    {
        return Validator::make($request->all(), [
            'user_id'           => 'required|exists:users,id',
            'datetime_activity' => 'required|date',
            'project_id'        => 'required|exists:projects,id',
            'location_id'       => 'required|exists:locations,id',
            'description'       => 'nullable|string|max:2000',
        ], [
            'user_id.required'           => 'Personel (HSE Staff) wajib dipilih',
            'user_id.exists'             => 'Personel tidak valid',
            'datetime_activity.required' => 'Tanggal & jam aktivitas wajib diisi',
            'project_id.required'        => 'Project wajib dipilih',
            'location_id.required'       => 'Lokasi wajib dipilih',
        ]);
    }
}
