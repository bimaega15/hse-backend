@extends('admin.layouts')

@section('title', 'Tabel Index Behavior')

@push('cssSection')
    <style>
        .behavior-card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }

        .behavior-card .card-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            font-weight: bold;
            padding: 15px 20px;
            border: none;
        }

        .filter-card {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 30px;
        }

        /* Custom Tab Styling */
        .nav-tabs-custom {
            border-bottom: 2px solid #dee2e6;
        }

        .nav-tabs-custom .nav-link {
            border: none;
            border-bottom: 3px solid transparent;
            color: #6c757d;
            font-weight: 600;
            padding: 15px 25px;
            margin-bottom: -2px;
            transition: all 0.3s ease;
        }

        .nav-tabs-custom .nav-link:hover {
            border-bottom-color: #667eea;
            color: #667eea;
        }

        .nav-tabs-custom .nav-link.active {
            border-bottom-color: #667eea;
            color: #667eea;
            background: transparent;
        }

        /* Index Behavior Table */
        .index-table {
            font-size: 0.9rem;
        }

        .index-table thead th {
            background: linear-gradient(135deg, #ffc107 0%, #ff9800 100%);
            color: #000;
            font-weight: 600;
            text-align: center;
            padding: 12px;
            border: 1px solid #dee2e6;
        }

        .index-table tbody td {
            text-align: center;
            padding: 10px;
            border: 1px solid #dee2e6;
            vertical-align: middle;
        }

        .index-table tbody tr:hover {
            background-color: #f8f9fa;
        }

        /* Zone Badges */
        .zone-badge {
            padding: 5px 15px;
            border-radius: 20px;
            font-weight: 600;
            font-size: 0.85rem;
        }

        .zone-safe {
            background-color: #d4edda;
            color: #155724;
        }

        .zone-critical {
            background-color: #fff3cd;
            color: #856404;
        }

        .zone-risk {
            background-color: #f8d7da;
            color: #721c24;
        }

        /* Risk Level Colors */
        .risk-rendah {
            background-color: #d4edda !important;
            color: #155724;
        }

        .risk-sedang {
            background-color: #d1ecf1 !important;
            color: #0c5460;
        }

        .risk-tinggi {
            background-color: #fff3cd !important;
            color: #856404;
        }

        .risk-sangat-tinggi {
            background-color: #f8d7da !important;
            color: #721c24;
        }

        /* Chart Section */
        .chart-section {
            background: #fff;
            border-radius: 15px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            padding: 20px;
            margin-bottom: 30px;
        }

        /* Legend */
        .legend-item {
            display: inline-flex;
            align-items: center;
            margin-right: 20px;
            margin-bottom: 10px;
        }

        .legend-color {
            width: 20px;
            height: 20px;
            border-radius: 4px;
            margin-right: 8px;
        }

        /* DataTable styling */
        .dataTables_wrapper .dataTables_filter input {
            border-radius: 20px;
            padding: 5px 15px;
        }

        .dataTables_wrapper .dataTables_length select {
            border-radius: 10px;
        }
    </style>
@endpush

@section('content')
    <div class="page-content">
        <div class="container-fluid">
            <!-- Page Title -->
            <div class="row">
                <div class="col-12">
                    <div class="page-title-box d-flex align-items-center justify-content-between">
                        <h4 class="mb-0">Tabel Index Behavior</h4>
                        <div class="page-title-right">
                            <nav aria-label="breadcrumb">
                                <ol class="breadcrumb m-0">
                                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard.index') }}">Dashboard</a>
                                    </li>
                                    <li class="breadcrumb-item active">Index Behavior</li>
                                </ol>
                            </nav>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filter Section -->
            <div class="filter-card">
                <form id="filterForm" method="GET" action="{{ route('admin.behavior-index.table') }}">
                    <div class="row align-items-end">
                        <div class="col-md-3">
                            <label class="form-label">Periode Awal</label>
                            <input type="date" class="form-control" name="start_date" id="startDate"
                                value="{{ $startDate }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Periode Akhir</label>
                            <input type="date" class="form-control" name="end_date" id="endDate"
                                value="{{ $endDate }}">
                        </div>
                        <div class="col-md-3">
                            <button type="submit" class="btn btn-primary w-100">
                                <i data-lucide="filter" class="me-1"></i> Terapkan Filter
                            </button>
                        </div>
                        <div class="col-md-3">
                            <a href="{{ route('admin.behavior-index.table') }}" class="btn btn-outline-secondary w-100">
                                <i data-lucide="refresh-cw" class="me-1"></i> Reset
                            </a>
                        </div>
                    </div>
                    <input type="hidden" name="tab" id="activeTabInput" value="{{ $activeTab }}">
                </form>
            </div>

            <!-- Legend -->
            <div class="mb-4">
                <h6 class="mb-2">Keterangan Tingkat Risiko:</h6>
                <div class="d-flex flex-wrap">
                    <div class="legend-item">
                        <div class="legend-color" style="background-color: #d4edda;"></div>
                        <span><strong>RENDAH</strong> (&lt; 200) - SAFE ZONE</span>
                    </div>
                    <div class="legend-item">
                        <div class="legend-color" style="background-color: #d1ecf1;"></div>
                        <span><strong>SEDANG</strong> (200 - 20.000) - SAFE ZONE</span>
                    </div>
                    <div class="legend-item">
                        <div class="legend-color" style="background-color: #fff3cd;"></div>
                        <span><strong>TINGGI</strong> (20.001 - 40.000) - CRITICAL</span>
                    </div>
                    <div class="legend-item">
                        <div class="legend-color" style="background-color: #f8d7da;"></div>
                        <span><strong>SANGAT TINGGI</strong> (&gt; 40.000) - RISK ZONE</span>
                    </div>
                </div>
            </div>

            <!-- Tabs -->
            <ul class="nav nav-tabs nav-tabs-custom mb-4" id="behaviorTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link {{ $activeTab == 'index-behavior' ? 'active' : '' }}" id="index-behavior-tab"
                        data-bs-toggle="tab" data-bs-target="#index-behavior" type="button" role="tab">
                        <i data-lucide="table" class="me-2"></i> Index Behavior
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link {{ $activeTab == 'trend-temuan' ? 'active' : '' }}" id="trend-temuan-tab"
                        data-bs-toggle="tab" data-bs-target="#trend-temuan" type="button" role="tab">
                        <i data-lucide="trending-up" class="me-2"></i> Trend Temuan
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link {{ $activeTab == 'trend-closed' ? 'active' : '' }}" id="trend-closed-tab"
                        data-bs-toggle="tab" data-bs-target="#trend-closed" type="button" role="tab">
                        <i data-lucide="check-circle" class="me-2"></i> Trend Temuan Closed
                    </button>
                </li>
            </ul>

            <!-- Tab Content -->
            <div class="tab-content" id="behaviorTabsContent">
                <!-- Tab 1: Index Behavior -->
                <div class="tab-pane fade {{ $activeTab == 'index-behavior' ? 'show active' : '' }}" id="index-behavior"
                    role="tabpanel">
                    <!-- Index Behavior Table -->
                    <div class="behavior-card mb-4">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <span>
                                <i data-lucide="bar-chart-2" class="me-2"></i>
                                TABEL INDEX BEHAVIOR
                            </span>
                            <span class="badge bg-light text-dark">
                                Periode: {{ \Carbon\Carbon::parse($startDate)->format('d M Y') }} - {{ \Carbon\Carbon::parse($endDate)->format('d M Y') }}
                            </span>
                        </div>
                        <div class="card-body">
                            @if(count($indexBehaviorList) > 0)
                                <div class="table-responsive">
                                    <table class="table table-bordered index-table mb-0" id="indexBehaviorTable">
                                        <thead>
                                            <tr>
                                                <th>TANGGAL</th>
                                                <th>INDEX BEHAVIOR</th>
                                                <th>TINGKAT RISIKO</th>
                                                <th>ZONE</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($indexBehaviorList as $item)
                                                <tr>
                                                    <td>{{ $item['date_formatted'] }}</td>
                                                    <td style="background-color: {{ $item['bg_color'] }};">
                                                        <strong>{{ number_format($item['index_behavior'], 3, ',', '.') }}</strong>
                                                    </td>
                                                    <td style="background-color: {{ $item['bg_color'] }};">
                                                        {{ $item['tingkat_risiko'] }}
                                                    </td>
                                                    <td>
                                                        <span class="zone-badge {{ $item['zone'] == 'SAFE ZONE' ? 'zone-safe' : ($item['zone'] == 'CRITICAL' ? 'zone-critical' : 'zone-risk') }}">
                                                            {{ $item['zone'] }}
                                                        </span>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <div class="text-center py-5">
                                    <i data-lucide="inbox" style="width: 64px; height: 64px; color: #dee2e6;"></i>
                                    <h5 class="mt-3 text-muted">Tidak ada data</h5>
                                    <p class="text-muted">Tidak ada data observasi untuk periode yang dipilih</p>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Summary Chart -->
                    @if(count($indexBehaviorList) > 0)
                        <div class="row">
                            <div class="col-lg-8">
                                <div class="chart-section">
                                    <h5 class="mb-3 text-center">TREND INDEX BEHAVIOR</h5>
                                    <div id="chartIndexBehavior" style="height: 400px;"></div>
                                </div>
                            </div>
                            <div class="col-lg-4">
                                <div class="chart-section">
                                    <h5 class="mb-3 text-center">Distribusi Tingkat Risiko</h5>
                                    <div id="chartRiskDistribution" style="height: 400px;"></div>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>

                <!-- Tab 2: Trend Temuan -->
                <div class="tab-pane fade {{ $activeTab == 'trend-temuan' ? 'show active' : '' }}" id="trend-temuan"
                    role="tabpanel">
                    @if (!empty($trendData['labels']) && count($trendData['labels']) > 0)
                        <div class="row">
                            <div class="col-12">
                                <div class="chart-section">
                                    <h5 class="mb-3 text-center">TREND INDEX BEHAVIOR HARIAN</h5>
                                    <div id="chartTrendLine" style="height: 400px;"></div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-12">
                                <div class="chart-section">
                                    <h5 class="mb-3 text-center">JUMLAH AT RISK BEHAVIOR PER HARI</h5>
                                    <div id="chartTrendBar" style="height: 400px;"></div>
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i data-lucide="trending-up" style="width: 64px; height: 64px; color: #dee2e6;"></i>
                            <h5 class="mt-3 text-muted">Tidak ada data trend</h5>
                            <p class="text-muted">Tidak ada data observasi untuk periode yang dipilih</p>
                        </div>
                    @endif
                </div>

                <!-- Tab 3: Trend Temuan Closed -->
                <div class="tab-pane fade {{ $activeTab == 'trend-closed' ? 'show active' : '' }}" id="trend-closed"
                    role="tabpanel">
                    <div class="text-center py-5">
                        <i data-lucide="check-circle" style="width: 64px; height: 64px; color: #dee2e6;"></i>
                        <h5 class="mt-3 text-muted">Fitur Dalam Pengembangan</h5>
                        <p class="text-muted">Trend Temuan Closed akan menampilkan data observasi yang sudah ditutup/resolved.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('jsSection')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize Lucide icons
            if (typeof lucide !== 'undefined') {
                lucide.createIcons();
            }

            // Track active tab
            const tabs = document.querySelectorAll('#behaviorTabs button[data-bs-toggle="tab"]');
            tabs.forEach(tab => {
                tab.addEventListener('shown.bs.tab', function(event) {
                    document.getElementById('activeTabInput').value = event.target.getAttribute(
                        'data-bs-target').replace('#', '');
                });
            });

            // Initialize DataTable
            if ($.fn.DataTable && document.getElementById('indexBehaviorTable')) {
                $('#indexBehaviorTable').DataTable({
                    pageLength: 31,
                    paging: false,
                    searching: false,
                    info: false,
                    language: {
                        emptyTable: "Tidak ada data",
                        zeroRecords: "Tidak ada data yang cocok"
                    },
                    order: [[0, 'asc']] // Sort by date ascending
                });
            }

            @if(count($indexBehaviorList) > 0)
                // Data from controller
                const indexData = @json($indexBehaviorList);
                const trendData = @json($trendData);

                // Chart 1: Index Behavior Line Chart (Tab 1) - Trend per Tanggal
                var optionsIndexBehavior = {
                    series: [{
                        name: 'INDEX BEHAVIOR',
                        data: indexData.map(item => item.index_behavior)
                    }],
                    chart: {
                        type: 'line',
                        height: 400,
                        toolbar: {
                            show: true
                        }
                    },
                    stroke: {
                        curve: 'smooth',
                        width: 3
                    },
                    colors: ['#667eea'],
                    markers: {
                        size: 6,
                        colors: indexData.map(item => item.color),
                        strokeColors: '#fff',
                        strokeWidth: 2,
                        hover: {
                            size: 8
                        }
                    },
                    dataLabels: {
                        enabled: true,
                        formatter: function(val) {
                            return val.toLocaleString('id-ID', {
                                maximumFractionDigits: 0
                            });
                        },
                        offsetY: -10,
                        style: {
                            fontSize: '10px'
                        }
                    },
                    xaxis: {
                        categories: indexData.map(item => item.date_formatted),
                        labels: {
                            rotate: -45,
                            style: {
                                fontSize: '11px'
                            }
                        }
                    },
                    yaxis: {
                        title: {
                            text: 'Index Behavior'
                        }
                    },
                    annotations: {
                        yaxis: [
                            {
                                y: 200,
                                borderColor: '#28a745',
                                strokeDashArray: 5,
                                label: {
                                    text: 'RENDAH (200)',
                                    style: { color: '#fff', background: '#28a745' }
                                }
                            },
                            {
                                y: 20000,
                                borderColor: '#17a2b8',
                                strokeDashArray: 5,
                                label: {
                                    text: 'SEDANG (20.000)',
                                    style: { color: '#fff', background: '#17a2b8' }
                                }
                            },
                            {
                                y: 40000,
                                borderColor: '#dc3545',
                                strokeDashArray: 5,
                                label: {
                                    text: 'RISK ZONE (40.000)',
                                    style: { color: '#fff', background: '#dc3545' }
                                }
                            }
                        ]
                    },
                    tooltip: {
                        custom: function({series, seriesIndex, dataPointIndex, w}) {
                            const item = indexData[dataPointIndex];
                            return `<div class="p-2">
                                <strong>${item.date_formatted}</strong><br>
                                Index: ${item.index_behavior.toLocaleString('id-ID', {minimumFractionDigits: 3})}<br>
                                Risiko: <strong>${item.tingkat_risiko}</strong><br>
                                Zone: <strong>${item.zone}</strong>
                            </div>`;
                        }
                    }
                };

                // Only render if element exists
                if (document.querySelector("#chartIndexBehavior")) {
                    var chartIndexBehavior = new ApexCharts(document.querySelector("#chartIndexBehavior"), optionsIndexBehavior);
                    chartIndexBehavior.render();
                }

                // Chart 2: Risk Distribution Pie Chart (Tab 1)
                const riskCounts = {
                    'RENDAH': indexData.filter(item => item.tingkat_risiko === 'RENDAH').length,
                    'SEDANG': indexData.filter(item => item.tingkat_risiko === 'SEDANG').length,
                    'TINGGI': indexData.filter(item => item.tingkat_risiko === 'TINGGI').length,
                    'SANGAT TINGGI': indexData.filter(item => item.tingkat_risiko === 'SANGAT TINGGI').length
                };

                var optionsRiskDistribution = {
                    series: [riskCounts['RENDAH'], riskCounts['SEDANG'], riskCounts['TINGGI'], riskCounts['SANGAT TINGGI']],
                    chart: {
                        type: 'donut',
                        height: 400
                    },
                    labels: ['RENDAH', 'SEDANG', 'TINGGI', 'SANGAT TINGGI'],
                    colors: ['#28a745', '#17a2b8', '#fd7e14', '#dc3545'],
                    legend: {
                        position: 'bottom'
                    },
                    dataLabels: {
                        enabled: true,
                        formatter: function(val, opts) {
                            return opts.w.config.series[opts.seriesIndex] + ' (' + val.toFixed(1) + '%)';
                        }
                    },
                    plotOptions: {
                        pie: {
                            donut: {
                                labels: {
                                    show: true,
                                    total: {
                                        show: true,
                                        label: 'Total',
                                        formatter: function(w) {
                                            return w.globals.seriesTotals.reduce((a, b) => a + b, 0);
                                        }
                                    }
                                }
                            }
                        }
                    }
                };

                if (document.querySelector("#chartRiskDistribution")) {
                    var chartRiskDistribution = new ApexCharts(document.querySelector("#chartRiskDistribution"), optionsRiskDistribution);
                    chartRiskDistribution.render();
                }

                // Chart 3: Trend Line Chart (Tab 2)
                if (trendData.labels && trendData.labels.length > 0) {
                    var optionsTrendLine = {
                        series: [{
                            name: 'INDEX BEHAVIOR',
                            data: trendData.index_values
                        }],
                        chart: {
                            type: 'line',
                            height: 400,
                            toolbar: {
                                show: true
                            }
                        },
                        stroke: {
                            curve: 'smooth',
                            width: 3
                        },
                        colors: ['#667eea'],
                        markers: {
                            size: 6,
                            colors: trendData.colors,
                            strokeColors: '#fff',
                            strokeWidth: 2,
                            hover: {
                                size: 8
                            }
                        },
                        xaxis: {
                            categories: trendData.labels
                        },
                        yaxis: {
                            title: {
                                text: 'Index Behavior'
                            }
                        },
                        annotations: {
                            yaxis: [
                                {
                                    y: 200,
                                    borderColor: '#28a745',
                                    strokeDashArray: 5,
                                    label: {
                                        text: 'RENDAH (200)',
                                        style: {
                                            color: '#fff',
                                            background: '#28a745'
                                        }
                                    }
                                },
                                {
                                    y: 40000,
                                    borderColor: '#dc3545',
                                    strokeDashArray: 5,
                                    label: {
                                        text: 'RISK ZONE (40.000)',
                                        style: {
                                            color: '#fff',
                                            background: '#dc3545'
                                        }
                                    }
                                }
                            ]
                        },
                        dataLabels: {
                            enabled: true,
                            formatter: function(val) {
                                return val.toLocaleString('id-ID', {
                                    maximumFractionDigits: 0
                                });
                            },
                            offsetY: -10
                        },
                        tooltip: {
                            custom: function({series, seriesIndex, dataPointIndex, w}) {
                                const item = trendData.daily_details[dataPointIndex];
                                return `<div class="p-2">
                                    <strong>${item.date_formatted}</strong><br>
                                    Index: ${item.index_behavior.toLocaleString('id-ID', {minimumFractionDigits: 3})}<br>
                                    At Risk: ${item.at_risk_count}<br>
                                    Risiko: ${item.tingkat_risiko}<br>
                                    Zone: ${item.zone}
                                </div>`;
                            }
                        }
                    };

                    if (document.querySelector("#chartTrendLine")) {
                        var chartTrendLine = new ApexCharts(document.querySelector("#chartTrendLine"), optionsTrendLine);
                        chartTrendLine.render();
                    }

                    // Chart 4: At Risk Count Bar Chart (Tab 2)
                    var optionsTrendBar = {
                        series: [{
                            name: 'Jumlah At Risk',
                            data: trendData.at_risk_counts
                        }],
                        chart: {
                            type: 'bar',
                            height: 400,
                            toolbar: {
                                show: true
                            }
                        },
                        plotOptions: {
                            bar: {
                                horizontal: false,
                                columnWidth: '60%',
                                borderRadius: 4,
                                distributed: true
                            }
                        },
                        colors: trendData.colors,
                        dataLabels: {
                            enabled: true,
                            formatter: function(val) {
                                return val;
                            },
                            style: {
                                fontSize: '11px'
                            },
                            offsetY: -20
                        },
                        xaxis: {
                            categories: trendData.labels
                        },
                        yaxis: {
                            title: {
                                text: 'Jumlah At Risk Behavior'
                            }
                        },
                        legend: {
                            show: false
                        },
                        tooltip: {
                            custom: function({series, seriesIndex, dataPointIndex, w}) {
                                const item = trendData.daily_details[dataPointIndex];
                                return `<div class="p-2" style="background-color: ${item.bg_color};">
                                    <strong>${item.date_formatted}</strong><br>
                                    At Risk Count: ${item.at_risk_count}<br>
                                    Index: ${item.index_behavior.toLocaleString('id-ID', {minimumFractionDigits: 3})}<br>
                                    Risiko: <strong>${item.tingkat_risiko}</strong><br>
                                    Zone: <strong>${item.zone}</strong>
                                </div>`;
                            }
                        }
                    };

                    if (document.querySelector("#chartTrendBar")) {
                        var chartTrendBar = new ApexCharts(document.querySelector("#chartTrendBar"), optionsTrendBar);
                        chartTrendBar.render();
                    }
                }
            @endif
        });
    </script>
@endpush
