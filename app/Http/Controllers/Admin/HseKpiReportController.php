<?php
// app/Http/Controllers/Admin/HseKpiReportController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CategoryKpi;
use App\Models\HseKpi;
use App\Models\HseKpiDetail;
use App\Models\Project;
use App\Support\KpiScoring;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * HSE KPI — Report (Laporan Pencapaian Kinerja) with advance search + trending.
 */
class HseKpiReportController extends Controller
{
    public function index(Request $request): View
    {
        $filters = [
            'date_from'       => $request->get('date_from'),
            'date_to'         => $request->get('date_to'),
            'month'           => $request->get('month'),
            'project_id'      => $request->get('project_id'),
            'category_kpi_id' => $request->get('category_kpi_id'),
            'indicator_type'  => $request->get('indicator_type'), // lagging_indicator | leading_indicator
        ];

        $query = HseKpi::with([
            'categoryKpi:id,category_name',
            'project:id,project_name',
            'details',
        ]);

        if (!empty($filters['date_from'])) {
            $query->whereDate('report_date', '>=', $filters['date_from']);
        }
        if (!empty($filters['date_to'])) {
            $query->whereDate('report_date', '<=', $filters['date_to']);
        }
        if (!empty($filters['month'])) {
            try {
                [$y, $m] = explode('-', $filters['month']);
                $query->whereYear('report_date', $y)->whereMonth('report_date', $m);
            } catch (\Throwable $e) {
            }
        }
        if (!empty($filters['project_id'])) {
            $query->where('project_id', $filters['project_id']);
        }
        if (!empty($filters['category_kpi_id'])) {
            $query->where('category_kpi_id', $filters['category_kpi_id']);
        }
        if (!empty($filters['indicator_type'])) {
            $catIds = CategoryKpi::all()
                ->filter(fn($c) => $c->indicator_key === $filters['indicator_type'])
                ->pluck('id')->toArray();
            $query->whereIn('category_kpi_id', $catIds ?: [0]);
        }

        $kpis = $query->orderBy('report_date', 'desc')->get();

        // Build report rows + trending datasets
        $reports = $kpis->map(function (HseKpi $kpi) {
            $no = 0;
            $details = $kpi->details->map(function (HseKpiDetail $d) use (&$no) {
                $no++;
                return [
                    'no'             => $no,
                    'activity_name'  => $d->activity_name,
                    'target_display' => $d->target_display,
                    'realisasi'      => $d->realisasi,
                    'percentage'     => $d->percentage,
                    'nilai_label'    => $d->nilai_label,
                ];
            });

            return [
                'id'            => $kpi->id,
                'project_name'  => $kpi->project->project_name ?? '-',
                'category_name' => $kpi->categoryKpi->category_name ?? '-',
                'indicator_key' => $kpi->indicator_key,
                'report_date'   => optional($kpi->report_date)->format('d M Y'),
                'users'         => $kpi->assigned_users->pluck('name')->implode(', '),
                'average'       => $kpi->average,
                'overall_nilai' => $kpi->overall_nilai,
                'details'       => $details,
            ];
        });

        // Trending: ranking by average + band distribution
        $ranking = $reports
            ->filter(fn($r) => $r['average'] !== null)
            ->map(fn($r) => [
                'label'   => $r['project_name'] . ' — ' . $r['category_name'],
                'average' => (float) $r['average'],
                'nilai'   => $r['overall_nilai'],
            ])
            ->sortByDesc('average')
            ->values();

        $bandDistribution = [];
        foreach (KpiScoring::bands() as $band) {
            $bandDistribution[$band] = $reports->where('overall_nilai', $band)->count();
        }

        $filterOptions = [
            'projects'   => Project::orderBy('project_name')->get(['id', 'project_name']),
            'categories' => CategoryKpi::orderBy('category_name')->get(['id', 'category_name']),
        ];

        $trending = [
            'ranking'           => $ranking,
            'band_distribution' => $bandDistribution,
        ];

        $summary = [
            'total_kpi'   => $reports->count(),
            'avg_overall' => $reports->whereNotNull('average')->avg('average'),
        ];
        $summary['avg_overall'] = $summary['avg_overall'] !== null ? round($summary['avg_overall'], 1) : null;
        $summary['overall_band'] = KpiScoring::overallBand($summary['avg_overall']);

        return view('admin.kpi.report.index', compact('reports', 'trending', 'filters', 'filterOptions', 'summary'));
    }
}
