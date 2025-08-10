@extends('admin.layouts')

@section('title', 'HSE Dashboard')

@section('content')
    <div class="page-content">
        <div class="page-title-head d-flex align-items-center gap-2">
            <div class="flex-grow-1">
                <h4 class="fs-18 fw-bold mb-0">HSE Dashboard</h4>
                <p class="text-muted mb-0">Monitoring & Overview Health, Safety & Environment</p>
            </div>

            <div class="text-end">
                <ol class="breadcrumb m-0 py-0 fs-13">
                    <li class="breadcrumb-item"><a href="javascript: void(0);">HSE Admin</a></li>
                    <li class="breadcrumb-item active">Dashboard</li>
                </ol>
            </div>
        </div>

        <div class="page-container">
            <!-- Statistics Cards -->
            <div class="row row-cols-xxl-4 row-cols-md-2 row-cols-1">
                <div class="col">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex align-items-start gap-2 justify-content-between">
                                <div>
                                    <h5 class="text-muted fs-13 fw-bold text-uppercase">Total Reports</h5>
                                    <h3 class="mt-2 mb-1 fw-bold" id="total-reports">125</h3>
                                    <p class="mb-0 text-muted">
                                        <span class="text-success me-1"><i class="ri-arrow-up-line"></i>
                                            8.5%</span>
                                        <span class="text-nowrap">Since last month</span>
                                    </p>
                                </div>
                                <div class="avatar-lg flex-shrink-0">
                                    <span class="avatar-title bg-primary-subtle text-primary rounded fs-28">
                                        <iconify-icon icon="solar:document-bold-duotone"></iconify-icon>
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="apex-charts" id="chart-reports"></div>
                    </div>
                </div>

                <div class="col">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex align-items-start gap-2 justify-content-between">
                                <div>
                                    <h5 class="text-muted fs-13 fw-bold text-uppercase">Pending Reports</h5>
                                    <h3 class="mt-2 mb-1 fw-bold text-warning" id="pending-reports">12</h3>
                                    <p class="mb-0 text-muted">
                                        <span class="text-warning me-1"><i class="ri-time-line"></i>
                                            Need Action</span>
                                        <span class="text-nowrap">Requires review</span>
                                    </p>
                                </div>
                                <div class="avatar-lg flex-shrink-0">
                                    <span class="avatar-title bg-warning-subtle text-warning rounded fs-28">
                                        <iconify-icon icon="solar:clock-circle-bold-duotone"></iconify-icon>
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="apex-charts" id="chart-pending"></div>
                    </div>
                </div>

                <div class="col">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex align-items-start gap-2 justify-content-between">
                                <div>
                                    <h5 class="text-muted fs-13 fw-bold text-uppercase">Critical Incidents</h5>
                                    <h3 class="mt-2 mb-1 fw-bold text-danger" id="critical-incidents">3</h3>
                                    <p class="mb-0 text-muted">
                                        <span class="text-danger me-1"><i class="ri-alert-line"></i>
                                            High Priority</span>
                                        <span class="text-nowrap">This month</span>
                                    </p>
                                </div>
                                <div class="avatar-lg flex-shrink-0">
                                    <span class="avatar-title bg-danger-subtle text-danger rounded fs-28">
                                        <iconify-icon icon="solar:danger-triangle-bold-duotone"></iconify-icon>
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="apex-charts" id="chart-critical"></div>
                    </div>
                </div>

                <div class="col">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex align-items-start gap-2 justify-content-between">
                                <div>
                                    <h5 class="text-muted fs-13 fw-bold text-uppercase">Completion Rate</h5>
                                    <h3 class="mt-2 mb-1 fw-bold text-success" id="completion-rate">89.5%</h3>
                                    <p class="mb-0 text-muted">
                                        <span class="text-success me-1"><i class="ri-arrow-up-line"></i>
                                            12.3%</span>
                                        <span class="text-nowrap">Since last month</span>
                                    </p>
                                </div>
                                <div class="avatar-lg flex-shrink-0">
                                    <span class="avatar-title bg-success-subtle text-success rounded fs-28">
                                        <iconify-icon icon="solar:check-circle-bold-duotone"></iconify-icon>
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="apex-charts" id="chart-completion"></div>
                    </div>
                </div>
            </div>

            <!-- Charts Section -->
            <div class="row">
                <div class="col-xl-8">
                    <div class="card">
                        <div class="d-flex card-header justify-content-between align-items-center">
                            <div>
                                <h4 class="header-title">HSE Reports Overview</h4>
                                <p class="text-muted mb-0">Monthly trend of safety reports and incidents</p>
                            </div>
                            <div class="dropdown">
                                <a href="#" class="dropdown-toggle drop-arrow-none card-drop"
                                    data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="ri-more-2-fill fs-18"></i>
                                </a>
                                <div class="dropdown-menu dropdown-menu-end">
                                    <a href="javascript:void(0);" class="dropdown-item">Download Report</a>
                                    <a href="javascript:void(0);" class="dropdown-item">Export Data</a>
                                    <a href="javascript:void(0);" class="dropdown-item">View Details</a>
                                </div>
                            </div>
                        </div>

                        <div class="card-body px-0 pt-0">
                            <div class="bg-light bg-opacity-50">
                                <div class="row text-center">
                                    <div class="col-md-3 col-6">
                                        <p class="text-muted mt-3 mb-1">Waiting Reports</p>
                                        <h4 class="mb-3">
                                            <span class="text-warning me-1"><i class="ri-time-line"></i></span>
                                            <span id="waiting-count">12</span>
                                        </h4>
                                    </div>
                                    <div class="col-md-3 col-6">
                                        <p class="text-muted mt-3 mb-1">In Progress</p>
                                        <h4 class="mb-3">
                                            <span class="text-info me-1"><i class="ri-play-circle-line"></i></span>
                                            <span id="progress-count">8</span>
                                        </h4>
                                    </div>
                                    <div class="col-md-3 col-6">
                                        <p class="text-muted mt-3 mb-1">Completed</p>
                                        <h4 class="mb-3">
                                            <span class="text-success me-1"><i class="ri-check-circle-line"></i></span>
                                            <span id="completed-count">105</span>
                                        </h4>
                                    </div>
                                    <div class="col-md-3 col-6">
                                        <p class="text-muted mt-3 mb-1">Avg. Resolution</p>
                                        <h4 class="mb-3">
                                            <span class="text-primary me-1"><i class="ri-timer-line"></i></span>
                                            <span id="avg-resolution">2.5 days</span>
                                        </h4>
                                    </div>
                                </div>
                            </div>

                            <div dir="ltr" class="px-1 mt-2">
                                <div id="reports-trend-chart" class="apex-charts" data-colors="#ff6b6b,#4ecdc4,#45b7d1">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-4">
                    <div class="card">
                        <div class="d-flex card-header justify-content-between align-items-center">
                            <div>
                                <h4 class="header-title">Severity Distribution</h4>
                                <p class="text-muted mb-0">Current month breakdown</p>
                            </div>
                        </div>

                        <div class="card-body px-0 pt-0">
                            <div class="border-top border-bottom border-light border-dashed">
                                <div class="row text-center align-items-center">
                                    <div class="col-6">
                                        <p class="text-muted mt-3 mb-1">High/Critical</p>
                                        <h4 class="mb-3">
                                            <span class="text-danger me-1"><i class="ri-error-warning-line"></i></span>
                                            <span id="high-critical">15</span>
                                        </h4>
                                    </div>
                                    <div class="col-6 border-start border-light border-dashed">
                                        <p class="text-muted mt-3 mb-1">Low/Medium</p>
                                        <h4 class="mb-3">
                                            <span class="text-success me-1"><i class="ri-shield-check-line"></i></span>
                                            <span id="low-medium">110</span>
                                        </h4>
                                    </div>
                                </div>
                            </div>

                            <div dir="ltr" class="px-2">
                                <div id="severity-chart" class="apex-charts"
                                    data-colors="#e74c3c,#f39c12,#f1c40f,#27ae60"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tables Section -->
            <div class="row">
                <div class="col-xxl-6">
                    <div class="card">
                        <div class="card-header d-flex flex-wrap align-items-center gap-2">
                            <h4 class="header-title me-auto">Recent HSE Reports</h4>
                            <div class="d-flex gap-2 justify-content-end text-end">
                                <a href="{{ route('admin.reports.index') }}" class="btn btn-sm btn-light">View All</a>
                                <a href="javascript:void(0);" class="btn btn-sm btn-primary" onclick="createReport()">New
                                    Report</a>
                            </div>
                        </div>

                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-custom table-centered table-sm table-nowrap table-hover mb-0">
                                    <thead class="bg-light">
                                        <tr>
                                            <th>Employee</th>
                                            <th>Category</th>
                                            <th>Severity</th>
                                            <th>Status</th>
                                            <th>Date</th>
                                        </tr>
                                    </thead>
                                    <tbody id="recent-reports">
                                        <!-- Data will be loaded via AJAX -->
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xxl-6">
                    <div class="card">
                        <div class="card-header d-flex flex-wrap align-items-center gap-2">
                            <h4 class="header-title me-auto">Recent Observations</h4>
                            <div class="d-flex gap-2 justify-content-end text-end">
                                <a href="{{ route('admin.observations.index') }}" class="btn btn-sm btn-light">View
                                    All</a>
                                <a href="javascript:void(0);" class="btn btn-sm btn-primary"
                                    onclick="createObservation()">New Observation</a>
                            </div>
                        </div>

                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-custom table-centered table-sm table-nowrap table-hover mb-0">
                                    <thead class="bg-light">
                                        <tr>
                                            <th>Observer</th>
                                            <th>Type</th>
                                            <th>Count</th>
                                            <th>Status</th>
                                            <th>Date</th>
                                        </tr>
                                    </thead>
                                    <tbody id="recent-observations">
                                        <!-- Data will be loaded via AJAX -->
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Footer Start -->
        <footer class="footer">
            <div class="page-container">
                <div class="row">
                    <div class="col-md-6 text-center text-md-start">
                        <script>
                            document.write(new Date().getFullYear())
                        </script> © HSE Management System -
                        <span class="fw-bold text-decoration-underline text-uppercase text-reset fs-12">Safety First</span>
                    </div>
                    <div class="col-md-6">
                        <div class="text-md-end footer-links d-none d-md-block">
                            <a href="javascript: void(0);">Documentation</a>
                            <a href="javascript: void(0);">Support</a>
                            <a href="javascript: void(0);">Contact HSE Team</a>
                        </div>
                    </div>
                </div>
            </div>
        </footer>
    </div>

    @push('jsSection')
        <script>
            // SOLUSI: Deklarasikan chart variables di global scope
            let reportsTrendChart;
            let severityChart;
            let reportsCardChart;
            let pendingCardChart;
            let criticalCardChart;
            let completionCardChart;

            document.addEventListener('DOMContentLoaded', function() {
                // Reports Trend Chart
                var reportsTrendOptions = {
                    series: [{
                        name: 'Completed',
                        data: [31, 40, 28, 51, 42, 85, 77, 92, 68, 85, 105, 98]
                    }, {
                        name: 'In Progress',
                        data: [11, 22, 18, 31, 32, 25, 17, 22, 18, 25, 8, 12]
                    }, {
                        name: 'Waiting',
                        data: [15, 11, 32, 18, 9, 24, 11, 18, 14, 21, 12, 15]
                    }],
                    chart: {
                        height: 350,
                        type: 'area',
                        toolbar: {
                            show: false
                        }
                    },
                    dataLabels: {
                        enabled: false
                    },
                    stroke: {
                        curve: 'smooth',
                        width: 2
                    },
                    xaxis: {
                        categories: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov',
                            'Dec'
                        ]
                    },
                    fill: {
                        type: 'gradient',
                        gradient: {
                            shadeIntensity: 1,
                            inverseColors: false,
                            opacityFrom: 0.45,
                            opacityTo: 0.05,
                            stops: [20, 100, 100, 100]
                        }
                    },
                    colors: ['#28a745', '#17a2b8', '#ffc107'],
                    legend: {
                        position: 'top',
                        horizontalAlign: 'right'
                    }
                };

                // Assign ke global variable
                reportsTrendChart = new ApexCharts(document.querySelector("#reports-trend-chart"), reportsTrendOptions);
                reportsTrendChart.render();

                // Severity Distribution Chart
                var severityOptions = {
                    series: [25, 45, 35, 15],
                    chart: {
                        type: 'donut',
                        height: 300
                    },
                    labels: ['Critical', 'High', 'Medium', 'Low'],
                    colors: ['#dc3545', '#fd7e14', '#ffc107', '#28a745'],
                    legend: {
                        position: 'bottom'
                    },
                    plotOptions: {
                        pie: {
                            donut: {
                                size: '70%'
                            }
                        }
                    },
                    dataLabels: {
                        enabled: true,
                        formatter: function(val) {
                            return Math.round(val) + "%";
                        }
                    }
                };

                // Assign ke global variable
                severityChart = new ApexCharts(document.querySelector("#severity-chart"), severityOptions);
                severityChart.render();

                // Small Charts for Cards
                var cardChartOptions = {
                    chart: {
                        type: 'line',
                        height: 60,
                        sparkline: {
                            enabled: true
                        }
                    },
                    stroke: {
                        curve: 'smooth',
                        width: 2
                    },
                    markers: {
                        size: 0
                    }
                };

                // Reports Card Chart
                reportsCardChart = new ApexCharts(document.querySelector("#chart-reports"), {
                    ...cardChartOptions,
                    series: [{
                        data: [12, 14, 18, 17, 13, 22, 25, 29, 26, 31, 27, 28]
                    }],
                    colors: ['#0d6efd']
                });
                reportsCardChart.render();

                // Pending Card Chart
                pendingCardChart = new ApexCharts(document.querySelector("#chart-pending"), {
                    ...cardChartOptions,
                    series: [{
                        data: [8, 12, 10, 15, 12, 8, 6, 9, 12, 10, 8, 12]
                    }],
                    colors: ['#ffc107']
                });
                pendingCardChart.render();

                // Critical Card Chart
                criticalCardChart = new ApexCharts(document.querySelector("#chart-critical"), {
                    ...cardChartOptions,
                    series: [{
                        data: [2, 1, 3, 4, 2, 1, 2, 3, 2, 1, 3, 2]
                    }],
                    colors: ['#dc3545']
                });
                criticalCardChart.render();

                // Completion Card Chart
                completionCardChart = new ApexCharts(document.querySelector("#chart-completion"), {
                    ...cardChartOptions,
                    series: [{
                        data: [85, 87, 83, 89, 92, 88, 85, 90, 88, 92, 89, 91]
                    }],
                    colors: ['#28a745']
                });
                completionCardChart.render();

                // Load dashboard data via AJAX
                loadDashboardData();
            });

            function loadDashboardData() {
                // Load dashboard data from Laravel backend
                fetch('{{ route('admin.dashboard.data') }}')
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            updateDashboardStats(data.data.statistics);
                            updateCharts(data.data.charts);
                            loadRecentReports();
                            loadRecentObservations();
                        } else {
                            console.error('Failed to load dashboard data:', data.message);
                            showErrorNotification('Failed to load dashboard data');
                        }
                    })
                    .catch(error => {
                        console.error('Error loading dashboard data:', error);
                        showErrorNotification('Error loading dashboard data');
                    });
            }

            function loadRecentReports() {
                fetch('{{ route('admin.dashboard.recent-reports') }}')
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            updateReportsTable(data.data);
                        } else {
                            console.error('Failed to load recent reports:', data.message);
                            loadSampleReports();
                        }
                    })
                    .catch(error => {
                        console.error('Error loading recent reports:', error);
                        loadSampleReports();
                    });
            }

            function loadRecentObservations() {
                // Load recent observations from API
                fetch('/admin/observations/recent')
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            updateObservationsTable(data.data);
                        } else {
                            console.error('Failed to load recent observations:', data.message);
                            loadSampleObservations();
                        }
                    })
                    .catch(error => {
                        console.error('Error loading recent observations:', error);
                        loadSampleObservations();
                    });
            }

            function loadSampleReports() {
                const sampleReports = [{
                        id: 1,
                        reporter: 'John Doe',
                        department: 'Safety Officer',
                        category: 'Near Miss',
                        severity: 'Medium',
                        status: 'Completed',
                        date: '2 hours ago',
                        avatarClass: 'bg-primary-subtle'
                    },
                    {
                        id: 2,
                        reporter: 'Jane Smith',
                        department: 'Production',
                        category: 'Accident',
                        severity: 'Critical',
                        status: 'In Progress',
                        date: '4 hours ago',
                        avatarClass: 'bg-warning-subtle'
                    },
                    {
                        id: 3,
                        reporter: 'Mike Wilson',
                        department: 'Maintenance',
                        category: 'Hazard',
                        severity: 'Medium',
                        status: 'Waiting',
                        date: '1 day ago',
                        avatarClass: 'bg-success-subtle'
                    }
                ];

                updateReportsTable(sampleReports);
            }

            function loadSampleObservations() {
                const sampleObservations = [{
                        id: 1,
                        observer: 'Alice Brown',
                        department: 'HSE Team',
                        type: 'At Risk',
                        count: 3,
                        status: 'Reviewed',
                        date: '1 hour ago',
                        avatarClass: 'bg-info-subtle'
                    },
                    {
                        id: 2,
                        observer: 'Bob Johnson',
                        department: 'Production',
                        type: 'Near Miss',
                        count: 1,
                        status: 'Submitted',
                        date: '3 hours ago',
                        avatarClass: 'bg-secondary-subtle'
                    },
                    {
                        id: 3,
                        observer: 'Carol Davis',
                        department: 'Engineering',
                        type: 'Risk Mgmt',
                        count: 2,
                        status: 'Draft',
                        date: '6 hours ago',
                        avatarClass: 'bg-primary-subtle'
                    }
                ];

                updateObservationsTable(sampleObservations);
            }

            function updateDashboardStats(stats) {
                // Safely update elements if they exist
                const updateElement = (id, value) => {
                    const element = document.getElementById(id);
                    if (element) {
                        element.textContent = value;
                    }
                };

                updateElement('total-reports', stats.total_reports || 0);
                updateElement('pending-reports', stats.pending_reports || 0);
                updateElement('critical-incidents', stats.critical_incidents || 0);
                updateElement('completion-rate', (stats.completion_rate || 0) + '%');
                updateElement('waiting-count', stats.pending_reports || 0);
                updateElement('progress-count', stats.in_progress_reports || 0);
                updateElement('completed-count', stats.completed_reports || 0);
                updateElement('avg-resolution', stats.avg_resolution_time || 'N/A');

                // Calculate high/critical vs low/medium
                var highCritical = stats.critical_incidents || 0;
                var lowMedium = (stats.total_reports || 0) - highCritical;
                updateElement('high-critical', highCritical);
                updateElement('low-medium', lowMedium);
            }

            function updateCharts(chartsData) {
                try {
                    // Update severity chart with real data
                    if (chartsData.severity_distribution && severityChart) {
                        var severityData = [
                            chartsData.severity_distribution.critical || 0,
                            chartsData.severity_distribution.high || 0,
                            chartsData.severity_distribution.medium || 0,
                            chartsData.severity_distribution.low || 0
                        ];
                        severityChart.updateSeries(severityData);
                    }

                    // Update monthly trend chart
                    if (chartsData.monthly_trend && reportsTrendChart) {
                        reportsTrendChart.updateSeries([{
                            name: 'Completed',
                            data: chartsData.monthly_trend.completed || []
                        }, {
                            name: 'In Progress',
                            data: chartsData.monthly_trend.in_progress || []
                        }, {
                            name: 'Waiting',
                            data: chartsData.monthly_trend.waiting || []
                        }]);
                    }
                } catch (error) {
                    console.error('Error updating charts:', error);
                }
            }

            function updateReportsTable(reports) {
                var tbody = document.getElementById('recent-reports');
                if (!tbody) return;

                tbody.innerHTML = '';

                reports.forEach(function(report) {
                    var statusClass = getStatusClass(report.status);
                    var severityClass = getSeverityClass(report.severity);
                    var categoryClass = getCategoryClass(report.category);

                    var row = `
                        <tr>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="avatar-sm flex-shrink-0 me-2">
                                        <span class="avatar-title ${report.avatarClass} rounded-circle">
                                            <i class="ri-user-line"></i>
                                        </span>
                                    </div>
                                    <div>
                                        <h6 class="fs-14 mt-1 mb-0">${report.reporter}</h6>
                                        <small class="text-muted">${report.department}</small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="badge ${categoryClass}">${report.category}</span>
                            </td>
                            <td>
                                <span class="badge ${severityClass}">${report.severity}</span>
                            </td>
                            <td>
                                <span class="badge ${statusClass}">
                                    ${getStatusIcon(report.status)}${report.status}
                                </span>
                            </td>
                            <td>
                                <small class="text-muted">${report.date}</small>
                            </td>
                        </tr>
                    `;
                    tbody.innerHTML += row;
                });
            }

            function updateObservationsTable(observations) {
                var tbody = document.getElementById('recent-observations');
                if (!tbody) return;

                tbody.innerHTML = '';

                observations.forEach(function(observation) {
                    var statusClass = getObservationStatusClass(observation.status);
                    var typeClass = getObservationTypeClass(observation.type);

                    var row = `
                        <tr>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="avatar-sm flex-shrink-0 me-2">
                                        <span class="avatar-title ${observation.avatarClass} rounded-circle">
                                            <i class="ri-eye-line"></i>
                                        </span>
                                    </div>
                                    <div>
                                        <h6 class="fs-14 mt-1 mb-0">${observation.observer}</h6>
                                        <small class="text-muted">${observation.department}</small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="badge ${typeClass}">${observation.type}</span>
                            </td>
                            <td>
                                <span class="fw-bold text-primary">${observation.count}</span>
                            </td>
                            <td>
                                <span class="badge ${statusClass}">
                                    ${getObservationStatusIcon(observation.status)}${observation.status}
                                </span>
                            </td>
                            <td>
                                <small class="text-muted">${observation.date}</small>
                            </td>
                        </tr>
                    `;
                    tbody.innerHTML += row;
                });
            }

            function getStatusClass(status) {
                switch (status) {
                    case 'Completed':
                        return 'bg-success-subtle text-success';
                    case 'In Progress':
                        return 'bg-info-subtle text-info';
                    case 'Waiting':
                        return 'bg-warning-subtle text-warning';
                    default:
                        return 'bg-secondary-subtle text-secondary';
                }
            }

            function getSeverityClass(severity) {
                switch (severity) {
                    case 'Critical':
                        return 'bg-danger-subtle text-danger';
                    case 'High':
                        return 'bg-danger-subtle text-danger';
                    case 'Medium':
                        return 'bg-warning-subtle text-warning';
                    case 'Low':
                        return 'bg-success-subtle text-success';
                    default:
                        return 'bg-secondary-subtle text-secondary';
                }
            }

            function getCategoryClass(category) {
                switch (category) {
                    case 'Accident':
                        return 'bg-danger-subtle text-danger';
                    case 'Near Miss':
                        return 'bg-info-subtle text-info';
                    case 'Hazard':
                        return 'bg-warning-subtle text-warning';
                    default:
                        return 'bg-secondary-subtle text-secondary';
                }
            }

            function getObservationStatusClass(status) {
                switch (status) {
                    case 'Reviewed':
                        return 'bg-success-subtle text-success';
                    case 'Submitted':
                        return 'bg-warning-subtle text-warning';
                    case 'Draft':
                        return 'bg-secondary-subtle text-secondary';
                    default:
                        return 'bg-secondary-subtle text-secondary';
                }
            }

            function getObservationTypeClass(type) {
                switch (type) {
                    case 'At Risk':
                        return 'bg-danger-subtle text-danger';
                    case 'Near Miss':
                        return 'bg-warning-subtle text-warning';
                    case 'Risk Mgmt':
                        return 'bg-info-subtle text-info';
                    case 'SIM K3':
                        return 'bg-primary-subtle text-primary';
                    default:
                        return 'bg-secondary-subtle text-secondary';
                }
            }

            function getStatusIcon(status) {
                switch (status) {
                    case 'Completed':
                        return '<i class="ri-check-line me-1"></i>';
                    case 'In Progress':
                        return '<i class="ri-play-line me-1"></i>';
                    case 'Waiting':
                        return '<i class="ri-time-line me-1"></i>';
                    default:
                        return '';
                }
            }

            function getObservationStatusIcon(status) {
                switch (status) {
                    case 'Reviewed':
                        return '<i class="ri-check-line me-1"></i>';
                    case 'Submitted':
                        return '<i class="ri-send-plane-line me-1"></i>';
                    case 'Draft':
                        return '<i class="ri-draft-line me-1"></i>';
                    default:
                        return '';
                }
            }

            function showErrorNotification(message) {
                // Simple error notification
                var notification = document.createElement('div');
                notification.className = 'alert alert-danger position-fixed top-0 end-0 m-3';
                notification.style.zIndex = '9999';
                notification.innerHTML = `
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    ${message}
                `;
                document.body.appendChild(notification);

                // Auto remove after 5 seconds
                setTimeout(function() {
                    if (notification.parentNode) {
                        notification.parentNode.removeChild(notification);
                    }
                }, 5000);
            }

            // Function untuk redirect ke halaman reports/observations
            function createReport() {
                window.location.href = '{{ route('admin.reports.index') }}';
            }

            function createObservation() {
                window.location.href = '{{ route('admin.observations.index') }}';
            }

            function viewReport(id) {
                window.location.href = `/admin/reports/${id}`;
            }

            function editReport(id) {
                window.location.href = `/admin/reports/${id}/edit`;
            }

            function viewObservation(id) {
                window.location.href = `/admin/observations/${id}`;
            }

            function editObservation(id) {
                window.location.href = `/admin/observations/${id}/edit`;
            }

            // Real-time updates simulation
            setInterval(function() {
                // Simulate real-time updates to pending reports count
                var pendingElement = document.getElementById('pending-reports');
                if (pendingElement) {
                    var currentPending = parseInt(pendingElement.textContent);
                    var newPending = Math.max(0, currentPending + Math.floor(Math.random() * 3) - 1);
                    pendingElement.textContent = newPending;
                }
            }, 30000); // Update every 30 seconds

            // Auto refresh recent data every 5 minutes
            setInterval(function() {
                loadRecentReports();
                loadRecentObservations();
            }, 300000); // 5 minutes
        </script>
    @endpush
@endsection
