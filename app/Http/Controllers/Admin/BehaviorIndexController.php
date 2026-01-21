<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Observation;
use App\Models\ObservationDetail;
use App\Models\Category;
use App\Models\Location;
use App\Models\Project;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class BehaviorIndexController extends Controller
{
    /**
     * Display Observation Index Behavior & Trend page
     */
    public function indexTrend(Request $request)
    {
        // Get filter parameters
        $startDate = $request->get('start_date', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->get('end_date', Carbon::now()->endOfMonth()->format('Y-m-d'));
        $locationId = $request->get('location_id');

        // Base query for observation details with date filter
        $baseQuery = ObservationDetail::query()
            ->join('observations', 'observation_details.observation_id', '=', 'observations.id')
            ->whereBetween('observation_details.report_date', [$startDate, $endDate])
            ->whereNull('observations.deleted_at');

        if ($locationId) {
            $baseQuery->where('observation_details.location_id', $locationId);
        }

        // 1. Klasifikasi berdasarkan observation_type (sebagai pengganti klasifikasi insiden)
        $observationTypes = [
            'at_risk_behavior' => 'AT RISK BEHAVIOR',
            'nearmiss_incident' => 'NEARMISS INCIDENT',
            'informal_risk_mgmt' => 'INFORMAL RISK MGMT',
            'sim_k3' => 'SIM K3',
        ];

        $klasifikasiInsiden = [];
        $totalInsiden = 0;

        foreach ($observationTypes as $type => $label) {
            $count = (clone $baseQuery)->where('observation_details.observation_type', $type)->count();
            $totalInsiden += $count;
            $klasifikasiInsiden[] = [
                'laporan' => $label,
                'jumlah' => $count,
                'persen' => 0, // Will calculate after getting total
            ];
        }

        // Calculate percentages
        foreach ($klasifikasiInsiden as &$item) {
            $item['persen'] = $totalInsiden > 0 ? round(($item['jumlah'] / $totalInsiden) * 100) . '%' : '0%';
        }

        // 2. Berdasarkan Waktu Kejadian (Jam) from report_date
        $waktuKejadian = [];
        $totalWaktu = 0;

        for ($hour = 0; $hour < 24; $hour++) {
            $startHour = str_pad($hour, 2, '0', STR_PAD_LEFT) . ':01';
            $endHour = str_pad($hour + 1, 2, '0', STR_PAD_LEFT) . ':00';

            if ($hour == 23) {
                $endHour = '24:00';
            }

            $count = (clone $baseQuery)
                ->whereRaw('HOUR(observation_details.report_date) = ?', [$hour])
                ->count();

            $totalWaktu += $count;

            $waktuKejadian[] = [
                'jam' => $startHour . ' - ' . $endHour,
                'jumlah' => $count,
                'persen' => 0,
            ];
        }

        foreach ($waktuKejadian as &$item) {
            $item['persen'] = $totalWaktu > 0 ? round(($item['jumlah'] / $totalWaktu) * 100) . '%' : '0%';
        }

        // 3. Berdasarkan Hari Kejadian
        $daysOfWeek = [
            1 => 'SENIN',
            2 => 'SELASA',
            3 => 'RABU',
            4 => 'KAMIS',
            5 => 'JUMAT',
            6 => 'SABTU',
            0 => 'MINGGU', // Sunday is 0 in MySQL DAYOFWEEK but we use 7 for display
        ];

        $hariKejadian = [];
        $totalHari = 0;

        // MySQL DAYOFWEEK: 1=Sunday, 2=Monday, ..., 7=Saturday
        // We want: Monday first
        $dayMapping = [
            2 => 'SENIN',
            3 => 'SELASA',
            4 => 'RABU',
            5 => 'KAMIS',
            6 => 'JUMAT',
            7 => 'SABTU',
            1 => 'MINGGU',
        ];

        foreach ($dayMapping as $dayNum => $dayName) {
            $count = (clone $baseQuery)
                ->whereRaw('DAYOFWEEK(observation_details.report_date) = ?', [$dayNum])
                ->count();

            $totalHari += $count;

            $hariKejadian[] = [
                'hari' => $dayName,
                'jumlah' => $count,
                'persen' => 0,
            ];
        }

        foreach ($hariKejadian as &$item) {
            $item['persen'] = $totalHari > 0 ? round(($item['jumlah'] / $totalHari) * 100) . '%' : '0%';
        }

        // 4. Berdasarkan Tempat Kejadian (Location)
        $tempatKejadian = [];
        $totalTempat = 0;

        $locationStats = (clone $baseQuery)
            ->select('locations.name as location_name', DB::raw('COUNT(*) as count'))
            ->leftJoin('locations', 'observation_details.location_id', '=', 'locations.id')
            ->groupBy('observation_details.location_id', 'locations.name')
            ->orderByDesc('count')
            ->get();

        foreach ($locationStats as $stat) {
            $totalTempat += $stat->count;
            $tempatKejadian[] = [
                'tempat' => $stat->location_name ?? 'TIDAK DIKETAHUI',
                'jumlah' => $stat->count,
                'persen' => 0,
            ];
        }

        foreach ($tempatKejadian as &$item) {
            $item['persen'] = $totalTempat > 0 ? round(($item['jumlah'] / $totalTempat) * 100) . '%' : '0%';
        }

        // Get locations for filter dropdown
        $locations = Location::active()->orderBy('name')->get();

        return view('admin.behavior-index.index-trend', compact(
            'klasifikasiInsiden',
            'totalInsiden',
            'waktuKejadian',
            'totalWaktu',
            'hariKejadian',
            'totalHari',
            'tempatKejadian',
            'totalTempat',
            'locations',
            'startDate',
            'endDate',
            'locationId'
        ));
    }

    /**
     * Display Tabel Index Behavior page with 3 tabs
     */
    public function indexBehavior(Request $request)
    {
        // Get filter parameters
        $startDate = $request->get('start_date', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->get('end_date', Carbon::now()->endOfMonth()->format('Y-m-d'));
        $activeTab = $request->get('tab', 'index-behavior');

        // Get all Index Behavior data grouped by observer, project, location
        $indexBehaviorList = $this->getAllIndexBehaviorData($startDate, $endDate);

        // Trend Data for Tab 2 & 3
        $trendData = $this->getAllTrendData($startDate, $endDate);

        return view('admin.behavior-index.index-behavior', compact(
            'startDate',
            'endDate',
            'activeTab',
            'indexBehaviorList',
            'trendData'
        ));
    }

    /**
     * Get all Index Behavior data grouped by date
     */
    private function getAllIndexBehaviorData($startDate, $endDate)
    {
        $result = [];

        $start = Carbon::parse($startDate);
        $end = Carbon::parse($endDate);

        // Loop through each date in the range
        for ($date = $start->copy(); $date->lte($end); $date->addDay()) {
            $dayStr = $date->format('Y-m-d');

            // Get all observations for this date
            $observations = Observation::with(['details'])
                ->whereDate('created_at', $dayStr)
                ->get();

            $dayAtRisk = 0;
            $dayMinutes = 0;

            foreach ($observations as $observation) {
                // Calculate duration
                $duration = 0;
                if ($observation->waktu_mulai && $observation->waktu_selesai) {
                    $startTime = strtotime($observation->waktu_mulai);
                    $endTime = strtotime($observation->waktu_selesai);
                    $duration = ($endTime - $startTime) / 60;
                }

                // Count at_risk from details
                $dayAtRisk += $observation->details->where('observation_type', 'at_risk_behavior')->count();
                $dayMinutes += $duration;
            }

            // Calculate index behavior for this date
            $totalWaktuJam = $dayMinutes > 0 ? round(60 / $dayMinutes, 3) : 0;
            $atRiskPerJam = round($totalWaktuJam * $dayAtRisk, 3);
            $atRiskPerHari = round($atRiskPerJam * 8, 3);
            $atRiskPerTahun = round($atRiskPerHari * 350, 3);

            $category = $this->getIndexBehaviorCategory($atRiskPerTahun);

            $result[] = [
                'date' => $dayStr,
                'date_formatted' => $date->format('d M Y'),
                'day_name' => $date->locale('id')->translatedFormat('l'),
                'at_risk' => $dayAtRisk,
                'total_minutes' => $dayMinutes,
                'observation_count' => $observations->count(),
                'index_behavior' => $atRiskPerTahun,
                'tingkat_risiko' => $category['tingkat'],
                'zone' => $category['zone'],
                'color' => $category['color'],
                'bg_color' => $category['bg_color'],
            ];
        }

        // Sort by date ascending (oldest first)
        usort($result, function ($a, $b) {
            return strtotime($a['date']) <=> strtotime($b['date']);
        });

        return $result;
    }

    /**
     * Get all trend data for Tab 2 & 3
     */
    private function getAllTrendData($startDate, $endDate)
    {
        $trendData = [
            'labels' => [],
            'index_values' => [],
            'at_risk_counts' => [],
            'colors' => [],
            'daily_details' => [],
        ];

        $start = Carbon::parse($startDate);
        $end = Carbon::parse($endDate);

        for ($date = $start->copy(); $date->lte($end); $date->addDay()) {
            $dayStr = $date->format('Y-m-d');

            // Get all observations for this day
            $observations = Observation::with(['details'])
                ->whereDate('created_at', $dayStr)
                ->get();

            $dayAtRisk = 0;
            $dayMinutes = 0;

            foreach ($observations as $observation) {
                $duration = 0;
                if ($observation->waktu_mulai && $observation->waktu_selesai) {
                    $startTime = strtotime($observation->waktu_mulai);
                    $endTime = strtotime($observation->waktu_selesai);
                    $duration = ($endTime - $startTime) / 60;
                }

                $dayAtRisk += $observation->details->where('observation_type', 'at_risk_behavior')->count();
                $dayMinutes += $duration;
            }

            // Calculate index for this day
            $totalWaktuJam = $dayMinutes > 0 ? round(60 / $dayMinutes, 3) : 0;
            $atRiskPerJam = round($totalWaktuJam * $dayAtRisk, 3);
            $atRiskPerHari = round($atRiskPerJam * 8, 3);
            $atRiskPerTahun = round($atRiskPerHari * 350, 3);

            $category = $this->getIndexBehaviorCategory($atRiskPerTahun);

            $trendData['labels'][] = $date->format('d M');
            $trendData['index_values'][] = $atRiskPerTahun;
            $trendData['at_risk_counts'][] = $dayAtRisk;
            $trendData['colors'][] = $category['color'];
            $trendData['daily_details'][] = [
                'date' => $dayStr,
                'date_formatted' => $date->format('d M Y'),
                'index_behavior' => $atRiskPerTahun,
                'at_risk_count' => $dayAtRisk,
                'tingkat_risiko' => $category['tingkat'],
                'zone' => $category['zone'],
                'color' => $category['color'],
                'bg_color' => $category['bg_color'],
            ];
        }

        return $trendData;
    }

    /**
     * Calculate Index Behavior data for a specific observer, project, and location
     */
    private function calculateIndexBehaviorData($observerId, $projectId, $locationId, $startDate, $endDate)
    {
        $query = Observation::with(['details', 'project:id,project_name', 'location:id,name', 'user:id,name,department'])
            ->where('user_id', $observerId)
            ->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
            ->where(function ($q) use ($projectId, $locationId) {
                $q->whereHas('details', function ($detailQuery) use ($projectId, $locationId) {
                    $detailQuery->where('project_id', $projectId)
                        ->where('location_id', $locationId);
                })
                ->orWhere(function ($noDetailQ) use ($projectId, $locationId) {
                    $noDetailQ->whereDoesntHave('details')
                        ->where('observations.project_id', $projectId)
                        ->where('observations.location_id', $locationId);
                });
            });

        $observations = $query->orderBy('created_at', 'asc')->get();

        // Calculate metrics per date
        $dailyData = [];
        $totalAtRisk = 0;
        $totalNearmiss = 0;
        $totalSimK3 = 0;
        $totalRiskMgmt = 0;
        $totalMinutes = 0;

        foreach ($observations as $observation) {
            $date = $observation->created_at->format('Y-m-d');

            // Calculate duration
            $duration = 0;
            if ($observation->waktu_mulai && $observation->waktu_selesai) {
                $start = strtotime($observation->waktu_mulai);
                $end = strtotime($observation->waktu_selesai);
                $duration = ($end - $start) / 60;
            }

            // Filter details for this project/location
            $filteredDetails = $observation->details->filter(function ($detail) use ($projectId, $locationId) {
                return $detail->project_id == $projectId && $detail->location_id == $locationId;
            });

            $atRisk = $filteredDetails->where('observation_type', 'at_risk_behavior')->count();
            $nearmiss = $filteredDetails->where('observation_type', 'nearmiss_incident')->count();
            $simK3 = $filteredDetails->where('observation_type', 'sim_k3')->count();
            $riskMgmt = $filteredDetails->where('observation_type', 'informal_risk_mgmt')->count();

            if (!isset($dailyData[$date])) {
                $dailyData[$date] = [
                    'date' => $date,
                    'date_formatted' => Carbon::parse($date)->format('d M Y'),
                    'at_risk' => 0,
                    'nearmiss' => 0,
                    'sim_k3' => 0,
                    'risk_mgmt' => 0,
                    'total_minutes' => 0,
                    'observations' => [],
                ];
            }

            $dailyData[$date]['at_risk'] += $atRisk;
            $dailyData[$date]['nearmiss'] += $nearmiss;
            $dailyData[$date]['sim_k3'] += $simK3;
            $dailyData[$date]['risk_mgmt'] += $riskMgmt;
            $dailyData[$date]['total_minutes'] += $duration;
            $dailyData[$date]['observations'][] = $observation;

            $totalAtRisk += $atRisk;
            $totalNearmiss += $nearmiss;
            $totalSimK3 += $simK3;
            $totalRiskMgmt += $riskMgmt;
            $totalMinutes += $duration;
        }

        // Calculate index behavior for each day
        foreach ($dailyData as &$day) {
            $dayMinutes = $day['total_minutes'];
            $dayAtRisk = $day['at_risk'];

            $totalWaktuJam = $dayMinutes > 0 ? round(60 / $dayMinutes, 3) : 0;
            $atRiskPerJam = round($totalWaktuJam * $dayAtRisk, 3);
            $atRiskPerHari = round($atRiskPerJam * 8, 3);
            $atRiskPerTahun = round($atRiskPerHari * 350, 3);

            $indexCategory = $this->getIndexBehaviorCategory($atRiskPerTahun);

            $day['index_behavior'] = $atRiskPerTahun;
            $day['tingkat_risiko'] = $indexCategory['tingkat'];
            $day['zone'] = $indexCategory['zone'];
            $day['color'] = $indexCategory['color'];
            $day['bg_color'] = $indexCategory['bg_color'];
        }

        // Calculate overall totals
        $totalWaktuJam = $totalMinutes > 0 ? round(60 / $totalMinutes, 3) : 0;
        $atRiskPerJam = round($totalWaktuJam * $totalAtRisk, 3);
        $atRiskPerHari = round($atRiskPerJam * 8, 3);
        $atRiskPerTahun = round($atRiskPerHari * 350, 3);
        $overallCategory = $this->getIndexBehaviorCategory($atRiskPerTahun);

        return [
            'daily_data' => array_values($dailyData),
            'totals' => [
                'at_risk' => $totalAtRisk,
                'nearmiss' => $totalNearmiss,
                'sim_k3' => $totalSimK3,
                'risk_mgmt' => $totalRiskMgmt,
                'total_minutes' => $totalMinutes,
                'total_waktu_jam' => $totalWaktuJam,
                'at_risk_per_jam' => $atRiskPerJam,
                'at_risk_per_hari' => $atRiskPerHari,
                'at_risk_per_tahun' => $atRiskPerTahun,
                'tingkat_risiko' => $overallCategory['tingkat'],
                'zone' => $overallCategory['zone'],
                'color' => $overallCategory['color'],
                'bg_color' => $overallCategory['bg_color'],
            ],
            'observer' => User::find($observerId),
            'project' => Project::find($projectId),
            'location' => Location::find($locationId),
        ];
    }

    /**
     * Get trend index behavior data
     */
    private function getTrendIndexBehavior($observerId, $projectId, $locationId, $startDate, $endDate)
    {
        if (!$observerId || !$projectId || !$locationId) {
            return [
                'labels' => [],
                'index_values' => [],
                'tingkat_risiko' => [],
                'zones' => [],
                'colors' => [],
            ];
        }

        $trendData = [
            'labels' => [],
            'index_values' => [],
            'tingkat_risiko' => [],
            'zones' => [],
            'colors' => [],
            'daily_details' => [],
        ];

        // Get data for each day in the range
        $start = Carbon::parse($startDate);
        $end = Carbon::parse($endDate);

        for ($date = $start->copy(); $date->lte($end); $date->addDay()) {
            $dayStr = $date->format('Y-m-d');

            $query = Observation::with(['details'])
                ->where('user_id', $observerId)
                ->whereDate('created_at', $dayStr)
                ->where(function ($q) use ($projectId, $locationId) {
                    $q->whereHas('details', function ($detailQuery) use ($projectId, $locationId) {
                        $detailQuery->where('project_id', $projectId)
                            ->where('location_id', $locationId);
                    })
                    ->orWhere(function ($noDetailQ) use ($projectId, $locationId) {
                        $noDetailQ->whereDoesntHave('details')
                            ->where('observations.project_id', $projectId)
                            ->where('observations.location_id', $locationId);
                    });
                });

            $observations = $query->get();

            $dayAtRisk = 0;
            $dayMinutes = 0;

            foreach ($observations as $observation) {
                $duration = 0;
                if ($observation->waktu_mulai && $observation->waktu_selesai) {
                    $startTime = strtotime($observation->waktu_mulai);
                    $endTime = strtotime($observation->waktu_selesai);
                    $duration = ($endTime - $startTime) / 60;
                }

                $filteredDetails = $observation->details->filter(function ($detail) use ($projectId, $locationId) {
                    return $detail->project_id == $projectId && $detail->location_id == $locationId;
                });

                $dayAtRisk += $filteredDetails->where('observation_type', 'at_risk_behavior')->count();
                $dayMinutes += $duration;
            }

            // Calculate index for this day
            $totalWaktuJam = $dayMinutes > 0 ? round(60 / $dayMinutes, 3) : 0;
            $atRiskPerJam = round($totalWaktuJam * $dayAtRisk, 3);
            $atRiskPerHari = round($atRiskPerJam * 8, 3);
            $atRiskPerTahun = round($atRiskPerHari * 350, 3);

            $category = $this->getIndexBehaviorCategory($atRiskPerTahun);

            $trendData['labels'][] = $date->format('d M');
            $trendData['index_values'][] = $atRiskPerTahun;
            $trendData['tingkat_risiko'][] = $category['tingkat'];
            $trendData['zones'][] = $category['zone'];
            $trendData['colors'][] = $category['color'];
            $trendData['daily_details'][] = [
                'date' => $dayStr,
                'date_formatted' => $date->format('d M Y'),
                'index_behavior' => $atRiskPerTahun,
                'tingkat_risiko' => $category['tingkat'],
                'zone' => $category['zone'],
                'color' => $category['color'],
                'bg_color' => $category['bg_color'],
            ];
        }

        return $trendData;
    }

    /**
     * Get Index Behavior Category based on at risk per year value
     */
    private function getIndexBehaviorCategory($atRiskPerTahun)
    {
        if ($atRiskPerTahun < 200) {
            return [
                'tingkat' => 'RENDAH',
                'zone' => 'SAFE ZONE',
                'color' => '#28a745', // Green
                'bg_color' => '#d4edda',
                'text_color' => '#155724',
            ];
        } elseif ($atRiskPerTahun >= 200 && $atRiskPerTahun <= 20000) {
            return [
                'tingkat' => 'SEDANG',
                'zone' => 'SAFE ZONE',
                'color' => '#17a2b8', // Cyan/Info
                'bg_color' => '#d1ecf1',
                'text_color' => '#0c5460',
            ];
        } elseif ($atRiskPerTahun > 20000 && $atRiskPerTahun <= 40000) {
            return [
                'tingkat' => 'TINGGI',
                'zone' => 'CRITICAL',
                'color' => '#fd7e14', // Orange
                'bg_color' => '#fff3cd',
                'text_color' => '#856404',
            ];
        } else {
            return [
                'tingkat' => 'SANGAT TINGGI',
                'zone' => 'RISK ZONE',
                'color' => '#dc3545', // Red
                'bg_color' => '#f8d7da',
                'text_color' => '#721c24',
            ];
        }
    }

    /**
     * AJAX endpoint for getting index behavior data
     */
    public function getIndexBehaviorDataAjax(Request $request)
    {
        if (!$request->filled('observer_id') || !$request->filled('project_id') || !$request->filled('location_id')) {
            return response()->json([
                'success' => false,
                'message' => 'Observer, Project, dan Location harus dipilih'
            ]);
        }

        $startDate = $request->get('start_date', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->get('end_date', Carbon::now()->endOfMonth()->format('Y-m-d'));

        $data = $this->calculateIndexBehaviorData(
            $request->observer_id,
            $request->project_id,
            $request->location_id,
            $startDate,
            $endDate
        );

        $trendData = $this->getTrendIndexBehavior(
            $request->observer_id,
            $request->project_id,
            $request->location_id,
            $startDate,
            $endDate
        );

        return response()->json([
            'success' => true,
            'data' => $data,
            'trend' => $trendData
        ]);
    }
}
