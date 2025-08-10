<!-- Analytics Dashboard -->
<div class="row mb-4">
    <!-- Summary Cards -->
    <div class="col-xl-3 col-md-6">
        <div class="card analytics-card">
            <div class="card-body analytics-metric">
                <div class="metric-value">{{ $additionalData['summary']['total_reports'] ?? 0 }}</div>
                <div class="metric-label">Total Reports</div>
                <div class="metric-change">
                    @if (isset($additionalData['summary']['this_month']) && isset($additionalData['summary']['last_month']))
                        @php
                            $change =
                                $additionalData['summary']['this_month'] - $additionalData['summary']['last_month'];
                            $changePercent =
                                $additionalData['summary']['last_month'] > 0
                                    ? round(($change / $additionalData['summary']['last_month']) * 100, 1)
                                    : 0;
                        @endphp
                        <span class="{{ $change >= 0 ? 'trend-up' : 'trend-down' }}">
                            <i class="ri-{{ $change >= 0 ? 'arrow-up' : 'arrow-down' }}-line"></i>
                            {{ abs($changePercent) }}% vs last month
                        </span>
                    @else
                        <span class="text-muted">Current period</span>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6">
        <div class="card analytics-card">
            <div class="card-body analytics-metric">
                <div class="metric-value text-warning">{{ $additionalData['summary']['critical_incidents'] ?? 0 }}</div>
                <div class="metric-label">Critical Incidents</div>
                <div class="metric-change">
                    <span class="text-muted">High & Critical Severity</span>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6">
        <div class="card analytics-card">
            <div class="card-body analytics-metric">
                <div class="metric-value text-danger">{{ $additionalData['summary']['overdue_cars'] ?? 0 }}</div>
                <div class="metric-label">Overdue CARs</div>
                <div class="metric-change">
                    <span class="text-muted">Corrective Actions</span>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6">
        <div class="card analytics-card">
            <div class="card-body analytics-metric">
                <div class="metric-value text-success">
                    {{ $additionalData['completion_metrics']['completion_rate'] ?? 0 }}%
                </div>
                <div class="metric-label">Completion Rate</div>
                <div class="metric-change">
                    <span class="text-muted">{{ $additionalData['completion_metrics']['avg_resolution_hours'] ?? 0 }}h
                        avg resolution</span>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Charts Row -->
<div class="row mb-4">
    <!-- Monthly Trends -->
    <div class="col-xl-8">
        <div class="card analytics-card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="ri-line-chart-line me-2"></i>Monthly Trends
                </h5>
            </div>
            <div class="card-body">
                <div class="chart-container">
                    <canvas id="trendsChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Severity Distribution -->
    <div class="col-xl-4">
        <div class="card analytics-card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="ri-pie-chart-line me-2"></i>Severity Analysis
                </h5>
            </div>
            <div class="card-body">
                <div class="chart-container chart-small">
                    <canvas id="severityChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Category Analysis & SLA Compliance -->
<div class="row mb-4">
    <div class="col-xl-6">
        <div class="card analytics-card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="ri-bar-chart-line me-2"></i>Category Breakdown
                </h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Category</th>
                                <th>Total</th>
                                <th>Completed</th>
                                <th>Avg Resolution</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if (isset($additionalData['categories']) && count($additionalData['categories']) > 0)
                                @foreach ($additionalData['categories'] as $category)
                                    <tr>
                                        <td>{{ $category->category ?? 'Unknown' }}</td>
                                        <td>{{ $category->total ?? 0 }}</td>
                                        <td>
                                            <span class="badge bg-success">{{ $category->completed ?? 0 }}</span>
                                        </td>
                                        <td>{{ round($category->avg_resolution_hours ?? 0, 1) }}h</td>
                                    </tr>
                                @endforeach
                            @else
                                <tr>
                                    <td colspan="4" class="text-center text-muted">No category data available</td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-6">
        <div class="card analytics-card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="ri-shield-check-line me-2"></i>SLA Compliance
                </h5>
            </div>
            <div class="card-body">
                @if (isset($additionalData['completion_metrics']['sla_compliance']) &&
                        count($additionalData['completion_metrics']['sla_compliance']) > 0)
                    @foreach ($additionalData['completion_metrics']['sla_compliance'] as $severity => $sla)
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div>
                                <span
                                    class="sla-indicator {{ ($sla['compliance_rate'] ?? 0) >= 80 ? 'sla-good' : (($sla['compliance_rate'] ?? 0) >= 60 ? 'sla-warning' : 'sla-critical') }}"></span>
                                <strong>{{ ucfirst($severity) }}</strong>
                                <small class="text-muted">({{ $sla['target_hours'] ?? 0 }}h target)</small>
                            </div>
                            <div class="text-end">
                                <div class="fw-bold">{{ $sla['compliance_rate'] ?? 0 }}%</div>
                                <small
                                    class="text-muted">{{ $sla['within_sla'] ?? 0 }}/{{ $sla['total'] ?? 0 }}</small>
                            </div>
                        </div>
                        <div class="progress mb-3" style="height: 6px;">
                            <div class="progress-bar {{ ($sla['compliance_rate'] ?? 0) >= 80 ? 'bg-success' : (($sla['compliance_rate'] ?? 0) >= 60 ? 'bg-warning' : 'bg-danger') }}"
                                style="width: {{ $sla['compliance_rate'] ?? 0 }}%"></div>
                        </div>
                    @endforeach
                @else
                    <div class="text-center text-muted py-4">
                        <i class="ri-shield-line fs-48 mb-3"></i>
                        <p>No SLA compliance data available</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- HSE Performance -->
<div class="row">
    <div class="col-12">
        <div class="card analytics-card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="ri-team-line me-2"></i>HSE Staff Performance
                </h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>HSE Staff</th>
                                <th>Total Assigned</th>
                                <th>Completed</th>
                                <th>This Month</th>
                                <th>Completion Rate</th>
                                <th>Performance</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if (isset($additionalData['hse_performance']) && count($additionalData['hse_performance']) > 0)
                                @foreach ($additionalData['hse_performance'] as $staff)
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="avatar-sm bg-primary bg-gradient rounded me-2">
                                                    <span
                                                        class="avatar-title fs-14">{{ strtoupper(substr($staff->name ?? 'U', 0, 2)) }}</span>
                                                </div>
                                                <div>
                                                    <div class="fw-bold">{{ $staff->name ?? 'Unknown' }}</div>
                                                    <small class="text-muted">{{ $staff->email ?? 'No email' }}</small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>{{ $staff->assigned_reports_count ?? 0 }}</td>
                                        <td>
                                            <span
                                                class="badge bg-success">{{ $staff->completed_reports_count ?? 0 }}</span>
                                        </td>
                                        <td>{{ $staff->this_month_reports_count ?? 0 }}</td>
                                        <td>{{ $staff->completion_rate ?? 0 }}%</td>
                                        <td>
                                            <div class="progress" style="width: 100px; height: 8px;">
                                                <div class="progress-bar {{ ($staff->completion_rate ?? 0) >= 80 ? 'bg-success' : (($staff->completion_rate ?? 0) >= 60 ? 'bg-warning' : 'bg-danger') }}"
                                                    style="width: {{ $staff->completion_rate ?? 0 }}%"></div>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            @else
                                <tr>
                                    <td colspan="6" class="text-center text-muted py-4">
                                        <i class="ri-team-line fs-48 mb-3"></i>
                                        <p>No HSE staff performance data available</p>
                                    </td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Quick Actions -->
<div class="row mt-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Quick Actions</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3">
                        <a href="{{ route('admin.reports.index') }}?status=waiting"
                            class="btn btn-outline-warning w-100 mb-2">
                            <i class="ri-time-line me-2"></i>View Pending Reports
                        </a>
                    </div>
                    <div class="col-md-3">
                        <a href="{{ route('admin.reports.index') }}?status=in-progress"
                            class="btn btn-outline-info w-100 mb-2">
                            <i class="ri-refresh-line me-2"></i>View In Progress
                        </a>
                    </div>
                    <div class="col-md-3">
                        <button type="button" class="btn btn-outline-primary w-100 mb-2" onclick="createReport()">
                            <i class="ri-add-line me-2"></i>Create New Report
                        </button>
                    </div>
                    <div class="col-md-3">
                        <a href="{{ route('admin.reports.index') }}" class="btn btn-outline-secondary w-100 mb-2">
                            <i class="ri-list-line me-2"></i>View All Reports
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Include Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize charts with actual data
        initAnalyticsCharts();
    });

    function initAnalyticsCharts() {
        // Get data safely
        const trendsData = @json($additionalData['trends'] ?? []);
        const severityData = @json($additionalData['severity_analysis'] ?? []);

        // Trends Chart
        const trendsCtx = document.getElementById('trendsChart');
        if (trendsCtx && trendsData.length > 0) {
            new Chart(trendsCtx, {
                type: 'line',
                data: {
                    labels: trendsData.map(item => item.month_name || 'Unknown'),
                    datasets: [{
                        label: 'Total Reports',
                        data: trendsData.map(item => item.total || 0),
                        borderColor: '#667eea',
                        backgroundColor: 'rgba(102, 126, 234, 0.1)',
                        tension: 0.4,
                        fill: true
                    }, {
                        label: 'Completed',
                        data: trendsData.map(item => item.completed || 0),
                        borderColor: '#28a745',
                        backgroundColor: 'rgba(40, 167, 69, 0.1)',
                        tension: 0.4,
                        fill: true
                    }, {
                        label: 'Critical',
                        data: trendsData.map(item => item.critical || 0),
                        borderColor: '#dc3545',
                        backgroundColor: 'rgba(220, 53, 69, 0.1)',
                        tension: 0.4,
                        fill: true
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    interaction: {
                        intersect: false,
                        mode: 'index'
                    },
                    plugins: {
                        legend: {
                            position: 'top',
                        },
                        tooltip: {
                            backgroundColor: 'rgba(0, 0, 0, 0.8)',
                            titleColor: '#fff',
                            bodyColor: '#fff',
                            borderColor: '#667eea',
                            borderWidth: 1
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: {
                                color: 'rgba(0, 0, 0, 0.1)'
                            }
                        },
                        x: {
                            grid: {
                                color: 'rgba(0, 0, 0, 0.1)'
                            }
                        }
                    }
                }
            });
        } else if (trendsCtx) {
            // Show empty state for trends chart
            trendsCtx.getContext('2d').fillText('No trends data available', 10, 50);
        }

        // Severity Chart
        const severityCtx = document.getElementById('severityChart');
        if (severityCtx && severityData.length > 0) {
            new Chart(severityCtx, {
                type: 'doughnut',
                data: {
                    labels: severityData.map(item => {
                        const severity = item.severity_rating || 'unknown';
                        return severity.charAt(0).toUpperCase() + severity.slice(1);
                    }),
                    datasets: [{
                        data: severityData.map(item => item.count || 0),
                        backgroundColor: [
                            '#28a745', // low - green
                            '#ffc107', // medium - yellow
                            '#fd7e14', // high - orange  
                            '#dc3545' // critical - red
                        ],
                        borderWidth: 2,
                        borderColor: '#fff'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                usePointStyle: true,
                                padding: 20
                            }
                        },
                        tooltip: {
                            backgroundColor: 'rgba(0, 0, 0, 0.8)',
                            titleColor: '#fff',
                            bodyColor: '#fff',
                            callbacks: {
                                label: function(context) {
                                    const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                    const percentage = total > 0 ? ((context.parsed / total) * 100).toFixed(
                                        1) : 0;
                                    return `${context.label}: ${context.parsed} (${percentage}%)`;
                                }
                            }
                        }
                    },
                    cutout: '60%'
                }
            });
        } else if (severityCtx) {
            // Show empty state for severity chart
            const ctx = severityCtx.getContext('2d');
            ctx.fillStyle = '#6c757d';
            ctx.font = '14px Arial';
            ctx.textAlign = 'center';
            ctx.fillText('No severity data available', severityCtx.width / 2, severityCtx.height / 2);
        }
    }

    // Error handling for chart initialization
    window.addEventListener('error', function(e) {
        if (e.message.includes('Chart')) {
            console.warn('Chart.js error:', e.message);
            // Fallback: hide chart containers and show message
            document.querySelectorAll('.chart-container').forEach(container => {
                container.innerHTML =
                    '<div class="text-center text-muted py-4"><i class="ri-bar-chart-line fs-48"></i><p>Chart data unavailable</p></div>';
            });
        }
    });

    // Refresh analytics data every 5 minutes
    setInterval(function() {
        if (typeof window.location !== 'undefined' && window.location.search.includes('view=analytics')) {
            window.location.reload();
        }
    }, 300000); // 5 minutes
</script>
