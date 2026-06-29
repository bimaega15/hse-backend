@extends('admin.layouts')

@section('title', 'Laporan Pencapaian Kinerja KPI')

@php
    $badgeClass = [
        'sangat baik' => 'bg-success',
        'baik' => 'bg-primary',
        'cukup' => 'bg-info text-dark',
        'kurang' => 'bg-warning text-dark',
        'kurang baik' => 'bg-danger',
    ];
@endphp

@push('cssSection')
    <style>
        .kpi-report-table thead th { background: #fbe7d5; color: #5b3a1a; }
        .kpi-report-card { border: none; border-radius: 12px; box-shadow: 0 2px 12px rgba(0,0,0,.06); }
    </style>
@endpush

@section('content')
    <div class="page-content">
        <div class="page-title-head d-flex align-items-center gap-2">
            <div class="flex-grow-1">
                <h4 class="fs-18 fw-bold mb-0">Laporan Pencapaian Kinerja KPI</h4>
            </div>
            <div class="text-end">
                <ol class="breadcrumb m-0 py-0 fs-13">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard.index') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="javascript: void(0);">HSE Program (KPI)</a></li>
                    <li class="breadcrumb-item active">Report</li>
                </ol>
            </div>
        </div>

        <div class="page-container">
            <!-- Advance Search -->
            <div class="card mb-3">
                <div class="card-header border-bottom border-dashed d-flex align-items-center justify-content-between py-2">
                    <h6 class="card-title mb-0"><i class="ri-filter-3-line me-2"></i>Advance Search</h6>
                </div>
                <div class="card-body pb-2">
                    <form method="GET" action="{{ route('admin.kpi.report.index') }}">
                        <div class="row g-2">
                            <div class="col-md-3 col-sm-6">
                                <label class="form-label form-label-sm mb-1">Tanggal Dari</label>
                                <input type="date" name="date_from" class="form-control form-control-sm" value="{{ $filters['date_from'] }}">
                            </div>
                            <div class="col-md-3 col-sm-6">
                                <label class="form-label form-label-sm mb-1">Tanggal Sampai</label>
                                <input type="date" name="date_to" class="form-control form-control-sm" value="{{ $filters['date_to'] }}">
                            </div>
                            <div class="col-md-3 col-sm-6">
                                <label class="form-label form-label-sm mb-1">Bulan</label>
                                <input type="month" name="month" class="form-control form-control-sm" value="{{ $filters['month'] }}">
                            </div>
                            <div class="col-md-3 col-sm-6">
                                <label class="form-label form-label-sm mb-1">Project</label>
                                <select name="project_id" class="form-select form-select-sm kpi-report-select">
                                    <option value="">Semua Project</option>
                                    @foreach ($filterOptions['projects'] as $p)
                                        <option value="{{ $p->id }}" {{ $filters['project_id'] == $p->id ? 'selected' : '' }}>{{ $p->project_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3 col-sm-6">
                                <label class="form-label form-label-sm mb-1">KPI Type</label>
                                <select name="indicator_type" class="form-select form-select-sm">
                                    <option value="">Semua</option>
                                    <option value="lagging_indicator" {{ $filters['indicator_type'] === 'lagging_indicator' ? 'selected' : '' }}>Lagging Indicator</option>
                                    <option value="leading_indicator" {{ $filters['indicator_type'] === 'leading_indicator' ? 'selected' : '' }}>Leading Indicator</option>
                                </select>
                            </div>
                            <div class="col-md-3 col-sm-6">
                                <label class="form-label form-label-sm mb-1">Category</label>
                                <select name="category_kpi_id" class="form-select form-select-sm kpi-report-select">
                                    <option value="">Semua Category</option>
                                    @foreach ($filterOptions['categories'] as $c)
                                        <option value="{{ $c->id }}" {{ $filters['category_kpi_id'] == $c->id ? 'selected' : '' }}>{{ $c->category_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6 col-sm-12 d-flex align-items-end gap-2">
                                <button type="submit" class="btn btn-primary btn-sm"><i class="ri-search-line me-1"></i>Terapkan</button>
                                <a href="{{ route('admin.kpi.report.index') }}" class="btn btn-outline-secondary btn-sm"><i class="ri-refresh-line me-1"></i>Reset</a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Summary -->
            <div class="row g-3 mb-3">
                <div class="col-md-4">
                    <div class="card kpi-report-card"><div class="card-body text-center">
                        <h3 class="fw-bold text-primary mb-1">{{ $summary['total_kpi'] }}</h3>
                        <p class="text-muted mb-0 fs-13">Total Laporan KPI</p>
                    </div></div>
                </div>
                <div class="col-md-4">
                    <div class="card kpi-report-card"><div class="card-body text-center">
                        <h3 class="fw-bold text-info mb-1">{{ $summary['avg_overall'] !== null ? $summary['avg_overall'] . '%' : '-' }}</h3>
                        <p class="text-muted mb-0 fs-13">Rata-rata Pencapaian</p>
                    </div></div>
                </div>
                <div class="col-md-4">
                    <div class="card kpi-report-card"><div class="card-body text-center">
                        @if ($summary['overall_band'])
                            <h3 class="mb-1"><span class="badge {{ $badgeClass[$summary['overall_band']] ?? 'bg-secondary' }} fs-18">{{ ucwords($summary['overall_band']) }}</span></h3>
                        @else
                            <h3 class="fw-bold text-muted mb-1">-</h3>
                        @endif
                        <p class="text-muted mb-0 fs-13">Nilai Keseluruhan</p>
                    </div></div>
                </div>
            </div>

            <!-- Trending -->
            @if ($trending['ranking']->isNotEmpty())
                <div class="row mb-3">
                    <div class="col-xl-8">
                        <div class="card kpi-report-card">
                            <div class="card-header border-bottom border-dashed">
                                <h5 class="card-title mb-0"><i class="ri-bar-chart-2-line me-2"></i>Trend Pencapaian KPI (Tertinggi → Terendah / Terendah → Tertinggi)</h5>
                            </div>
                            <div class="card-body">
                                <div class="btn-group btn-group-sm mb-2" role="group">
                                    <button type="button" class="btn btn-outline-primary active" id="btnHighLow" onclick="renderRanking('desc')">Tertinggi → Terendah</button>
                                    <button type="button" class="btn btn-outline-primary" id="btnLowHigh" onclick="renderRanking('asc')">Terendah → Tertinggi</button>
                                </div>
                                <div id="kpiRankingChart" style="min-height: 320px;"></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-4">
                        <div class="card kpi-report-card">
                            <div class="card-header border-bottom border-dashed">
                                <h5 class="card-title mb-0"><i class="ri-pie-chart-2-line me-2"></i>Proyek per Nilai</h5>
                            </div>
                            <div class="card-body">
                                <div id="kpiBandChart" style="min-height: 320px;"></div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Report tables -->
            @forelse ($reports as $r)
                <div class="card kpi-report-card mb-3">
                    <div class="card-body">
                        <div class="text-center mb-3">
                            <h5 class="fw-bold mb-0">LAPORAN PENCAPAIAN KINERJA {{ strtoupper($r['indicator_key'] === 'lagging_indicator' ? 'LAGGING' : 'LEADING') }}</h5>
                            <div class="text-muted">{{ $r['project_name'] }}</div>
                            <small class="text-muted">{{ $r['category_name'] }} &bull; Periode: {{ $r['report_date'] }} @if($r['users']) &bull; Personel: {{ $r['users'] }} @endif</small>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-bordered align-middle kpi-report-table mb-0">
                                <thead>
                                    <tr>
                                        <th width="5%" class="text-center">No</th>
                                        <th>{{ $r['indicator_key'] === 'lagging_indicator' ? 'Lagging' : 'Leading' }} Indicator</th>
                                        <th width="12%" class="text-center">Target</th>
                                        <th width="12%" class="text-center">Realisasi</th>
                                        <th width="13%" class="text-center">% Pencapaian</th>
                                        <th width="13%" class="text-center">Nilai</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($r['details'] as $d)
                                        <tr>
                                            <td class="text-center">{{ $d['no'] }}</td>
                                            <td>{{ $d['activity_name'] }}</td>
                                            <td class="text-center">{{ $d['target_display'] }}</td>
                                            <td class="text-center">{{ $d['realisasi'] !== null ? rtrim(rtrim(number_format($d['realisasi'], 2, '.', ''), '0'), '.') : '-' }}</td>
                                            <td class="text-center">{{ $d['percentage'] !== null ? $d['percentage'] . '%' : '-' }}</td>
                                            <td class="text-center">
                                                @if ($d['nilai_label'])
                                                    <span class="badge {{ $badgeClass[$d['nilai_label']] ?? 'bg-secondary' }}">{{ ucwords($d['nilai_label']) }}</span>
                                                @else
                                                    -
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot>
                                    <tr class="fw-bold table-light">
                                        <td colspan="4" class="text-end">RATA RATA NILAI {{ $r['indicator_key'] === 'lagging_indicator' ? 'LAGGING' : 'LEADING' }}</td>
                                        <td class="text-center">{{ $r['average'] !== null ? $r['average'] . '%' : '-' }}</td>
                                        <td class="text-center">
                                            @if ($r['overall_nilai'])
                                                <span class="badge {{ $badgeClass[$r['overall_nilai']] ?? 'bg-secondary' }}">{{ ucwords($r['overall_nilai']) }}</span>
                                            @else
                                                -
                                            @endif
                                        </td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            @empty
                <div class="card kpi-report-card">
                    <div class="card-body text-center text-muted py-5">
                        <i class="ri-file-search-line fs-48 d-block mb-2"></i>
                        Tidak ada data KPI untuk filter yang dipilih.
                    </div>
                </div>
            @endforelse
        </div>
    </div>
@endsection

@push('jsSection')
    <script>
        const RANKING = @json($trending['ranking']);
        const BAND_DIST = @json($trending['band_distribution']);
        const BAND_COLOR = {
            'sangat baik': '#16a34a', 'baik': '#2563eb', 'cukup': '#06b6d4',
            'kurang': '#f59e0b', 'kurang baik': '#ef4444'
        };
        let rankingChart;

        $(document).ready(function() {
            $('.kpi-report-select').select2({ theme: 'bootstrap-5', width: '100%', allowClear: true });
            if (RANKING.length > 0) {
                renderRanking('desc');
                renderBandChart();
            }
        });

        function renderRanking(dir) {
            const data = [...RANKING].sort((a, b) => dir === 'desc' ? b.average - a.average : a.average - b.average);
            $('#btnHighLow').toggleClass('active', dir === 'desc');
            $('#btnLowHigh').toggleClass('active', dir === 'asc');
            const options = {
                chart: { type: 'bar', height: 320, toolbar: { show: false } },
                series: [{ name: '% Pencapaian', data: data.map(d => d.average) }],
                xaxis: { categories: data.map(d => d.label) },
                colors: [function({ dataPointIndex }) { return BAND_COLOR[data[dataPointIndex].nilai] || '#2563eb'; }],
                plotOptions: { bar: { borderRadius: 4, horizontal: true, distributed: true } },
                dataLabels: { enabled: true, formatter: v => v + '%' },
                legend: { show: false },
                tooltip: { y: { formatter: (v, { dataPointIndex }) => v + '% (' + (data[dataPointIndex].nilai || '-') + ')' } }
            };
            if (rankingChart) { rankingChart.destroy(); }
            rankingChart = new ApexCharts(document.querySelector('#kpiRankingChart'), options);
            rankingChart.render();
        }

        function renderBandChart() {
            const labels = Object.keys(BAND_DIST);
            const series = Object.values(BAND_DIST);
            if (series.reduce((a, b) => a + b, 0) === 0) {
                document.querySelector('#kpiBandChart').innerHTML = '<div class="text-center text-muted py-5">Belum ada nilai</div>';
                return;
            }
            new ApexCharts(document.querySelector('#kpiBandChart'), {
                chart: { type: 'donut', height: 320 },
                series: series,
                labels: labels.map(l => l.replace(/\b\w/g, c => c.toUpperCase())),
                colors: labels.map(l => BAND_COLOR[l] || '#6b7280'),
                legend: { position: 'bottom' },
                dataLabels: { enabled: true }
            }).render();
        }
    </script>
@endpush
