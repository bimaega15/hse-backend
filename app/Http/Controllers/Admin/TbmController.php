<?php
// app/Http/Controllers/Admin/TbmController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Tbm;
use App\Models\User;
use App\Models\Project;
use App\Models\Location;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Yajra\DataTables\Facades\DataTables;

/**
 * TBM / Safety Talk — Web Admin (READ ONLY).
 *
 * The admin dashboard only displays/reports on TBM data. All create / update /
 * delete actions live in the mobile API (App\Http\Controllers\API\TbmController).
 */
class TbmController extends Controller
{
    /**
     * Display the listing page (default list view, or analytics/trending view).
     */
    public function index(Request $request)
    {
        $view = $request->get('view', 'default');

        $additionalData = [];
        $filterOptions  = [];
        $filters        = [];

        if ($view === 'analytics') {
            $filters = [
                'month'    => $request->get('month', now()->format('Y-m')),
                'project'  => $request->get('project'),
                'location' => $request->get('location'),
                'speaker'  => $request->get('speaker'),
            ];

            $additionalData = $this->getAnalyticsData($filters);
        }

        $filterOptions = [
            'speakers'  => User::where('is_active', true)->orderBy('name')->get(['id', 'name']),
            'projects'  => Project::orderBy('project_name')->get(['id', 'project_name']),
            'locations' => Location::orderBy('name')->get(['id', 'name']),
        ];

        return view('admin.tbm.index', compact('view', 'additionalData', 'filterOptions', 'filters'));
    }

    /**
     * DataTables endpoint for the list view (server-side, read-only).
     */
    public function getData(Request $request): JsonResponse
    {
        try {
            $query = Tbm::with([
                'speakerUser:id,name,email,department',
                'projectData:id,project_name',
                'locationData:id,name',
            ]);

            // --- Advance search filters (see screenshot) ---

            // Per Tanggal (date range)
            if ($request->filled('date_from')) {
                $query->whereDate('date_time_tbm', '>=', $request->date_from);
            }
            if ($request->filled('date_to')) {
                $query->whereDate('date_time_tbm', '<=', $request->date_to);
            }

            // Per Bulan (YYYY-MM)
            if ($request->filled('month')) {
                try {
                    [$year, $month] = explode('-', $request->month);
                    $query->whereYear('date_time_tbm', $year)->whereMonth('date_time_tbm', $month);
                } catch (\Throwable $e) {
                    // ignore malformed month
                }
            }

            // Per Pembicara (speaker)
            if ($request->filled('speaker')) {
                $query->where('speaker', $request->speaker);
            }

            // Per Project
            if ($request->filled('project')) {
                $query->where('project', $request->project);
            }

            // Per Area Kerja (location)
            if ($request->filled('location')) {
                $query->where('location', $request->location);
            }

            $query->orderBy('date_time_tbm', 'desc');

            return DataTables::of($query)
                ->addIndexColumn()
                ->addColumn('date_time_formatted', function ($tbm) {
                    return $tbm->date_time_tbm
                        ? $tbm->date_time_tbm->format('d M Y, H:i')
                        : '-';
                })
                ->addColumn('speaker_name', function ($tbm) {
                    return $tbm->speakerUser->name ?? '-';
                })
                ->addColumn('project_name', function ($tbm) {
                    return $tbm->projectData->project_name ?? '-';
                })
                ->addColumn('location_name', function ($tbm) {
                    return $tbm->locationData->name ?? '-';
                })
                ->addColumn('participant_badge', function ($tbm) {
                    return '<span class="badge bg-primary-subtle text-primary">'
                        . (int) $tbm->participant_count . ' org</span>';
                })
                ->addColumn('summary_short', function ($tbm) {
                    return $tbm->summary_topic ? e(Str::limit($tbm->summary_topic, 60)) : '<span class="text-muted">-</span>';
                })
                ->addColumn('photos_badge', function ($tbm) {
                    $count = count($tbm->activity_picture_urls);
                    if ($count === 0) {
                        return '<span class="text-muted">-</span>';
                    }
                    return '<span class="badge bg-info-subtle text-info"><i class="ri-image-line me-1"></i>'
                        . $count . '</span>';
                })
                ->addColumn('action', function ($tbm) {
                    return '<button type="button" class="btn btn-sm btn-soft-info" onclick="viewTbm(' . $tbm->id . ')" title="View Detail">
                                <i class="ri-eye-line"></i>
                            </button>';
                })
                ->rawColumns(['participant_badge', 'summary_short', 'photos_badge', 'action'])
                ->make(true);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to load TBM data: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Summary statistics for the stat cards.
     */
    public function getStatistics(): JsonResponse
    {
        try {
            $total          = Tbm::count();
            $thisMonth      = Tbm::thisMonth()->count();
            $participants   = (int) Tbm::sum('participant_count');
            $avgParticipant = $total > 0 ? round($participants / $total, 1) : 0;
            $speakers       = Tbm::distinct('speaker')->count('speaker');

            return response()->json([
                'success' => true,
                'data' => [
                    'total_tbm'          => $total,
                    'this_month_tbm'     => $thisMonth,
                    'total_participants' => $participants,
                    'avg_participants'   => $avgParticipant,
                    'total_speakers'     => $speakers,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to load statistics: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Read-only detail of a single TBM record (used by the view modal).
     */
    public function show($id): JsonResponse
    {
        $tbm = Tbm::with([
            'speakerUser:id,name,email,department',
            'projectData:id,project_name,code',
            'locationData:id,name,city,province',
        ])->find($id);

        if (!$tbm) {
            return response()->json([
                'success' => false,
                'message' => 'TBM / Safety Talk record not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'id'                    => $tbm->id,
                'date_time_tbm'         => optional($tbm->date_time_tbm)->toIso8601String(),
                'date_time_formatted'   => optional($tbm->date_time_tbm)->format('d M Y, H:i'),
                'speaker'               => $tbm->speakerUser,
                'project'               => $tbm->projectData,
                'location'              => $tbm->locationData,
                'participant_count'     => $tbm->participant_count,
                'summary_topic'         => $tbm->summary_topic,
                'activity_picture_urls' => $tbm->activity_picture_urls,
                'created_at'            => optional($tbm->created_at)->toIso8601String(),
            ],
        ]);
    }

    /**
     * Build all the trending / analytics datasets.
     */
    private function getAnalyticsData(array $filters): array
    {
        // Resolve target month for the daily trend
        try {
            $monthDate = \Carbon\Carbon::createFromFormat('Y-m', $filters['month'] ?? now()->format('Y-m'))->startOfMonth();
        } catch (\Throwable $e) {
            $monthDate = now()->startOfMonth();
        }

        $base = function () use ($filters) {
            $q = Tbm::query();
            if (!empty($filters['project'])) {
                $q->where('project', $filters['project']);
            }
            if (!empty($filters['location'])) {
                $q->where('location', $filters['location']);
            }
            if (!empty($filters['speaker'])) {
                $q->where('speaker', $filters['speaker']);
            }
            return $q;
        };

        // --- Summary cards ---
        $monthQuery = $base()
            ->whereYear('date_time_tbm', $monthDate->year)
            ->whereMonth('date_time_tbm', $monthDate->month);

        $summary = [
            'total_tbm'          => (clone $monthQuery)->count(),
            'total_participants' => (int) (clone $monthQuery)->sum('participant_count'),
            'total_speakers'     => (clone $monthQuery)->distinct('speaker')->count('speaker'),
        ];
        $summary['avg_participants'] = $summary['total_tbm'] > 0
            ? round($summary['total_participants'] / $summary['total_tbm'], 1)
            : 0;

        // --- Daily trend (TBM per day within selected month) ---
        $daysInMonth = $monthDate->daysInMonth;
        $dailyRaw = (clone $monthQuery)
            ->selectRaw('DAY(date_time_tbm) as d, COUNT(*) as total')
            ->groupBy('d')
            ->pluck('total', 'd')
            ->toArray();

        $dailyTrend = [];
        for ($d = 1; $d <= $daysInMonth; $d++) {
            $dailyTrend[] = [
                'label' => str_pad($d, 2, '0', STR_PAD_LEFT),
                'total' => (int) ($dailyRaw[$d] ?? 0),
            ];
        }

        // --- Trend per Project ---
        $byProject = $base()
            ->select('project', DB::raw('COUNT(*) as total'))
            ->with('projectData:id,project_name')
            ->groupBy('project')
            ->orderByDesc('total')
            ->limit(10)
            ->get()
            ->map(fn($row) => [
                'label' => $row->projectData->project_name ?? ('Project #' . $row->project),
                'total' => (int) $row->total,
            ]);

        // --- Trend per Area Kerja (location) ---
        $byLocation = $base()
            ->select('location', DB::raw('COUNT(*) as total'))
            ->with('locationData:id,name')
            ->groupBy('location')
            ->orderByDesc('total')
            ->limit(10)
            ->get()
            ->map(fn($row) => [
                'label' => $row->locationData->name ?? ('Location #' . $row->location),
                'total' => (int) $row->total,
            ]);

        // --- Trend per Pembicara (speaker) ---
        $bySpeaker = $base()
            ->select('speaker', DB::raw('COUNT(*) as total'))
            ->with('speakerUser:id,name')
            ->groupBy('speaker')
            ->orderByDesc('total')
            ->limit(10)
            ->get()
            ->map(fn($row) => [
                'label' => $row->speakerUser->name ?? ('User #' . $row->speaker),
                'total' => (int) $row->total,
            ]);

        return [
            'month_label' => $monthDate->translatedFormat('F Y'),
            'summary'     => $summary,
            'daily_trend' => $dailyTrend,
            'by_project'  => $byProject,
            'by_location' => $byLocation,
            'by_speaker'  => $bySpeaker,
        ];
    }
}
