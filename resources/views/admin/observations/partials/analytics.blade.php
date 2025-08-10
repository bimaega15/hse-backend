<!-- Analytics Dashboard -->
<div class="row mb-4">
    <!-- Summary Cards -->
    <div class="col-xl-3 col-md-6">
        <div class="card analytics-card">
            <div class="card-body analytics-metric">
                <div class="metric-value">{{ $additionalData['summary']['total_observations'] ?? 0 }}</div>
                <div class="metric-label">Total Observations</div>
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
                <div class="metric-value text-warning">{{ $additionalData['summary']['pending_review'] ?? 0 }}</div>
                <div class="metric-label">Pending Review</div>
                <div class="metric-change">
                    <span class="text-muted">Submitted Observations</span>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6">
        <div class="card analytics-card">
            <div class="card-body analytics-metric">
                <div class="metric-value text-danger">{{ $additionalData['summary']['high_severity_count'] ?? 0 }}</div>
                <div class="metric-label">High Severity</div>
                <div class="metric-change">
                    <span class="text-muted">Critical & High Risk</span>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6">
        <div class="card analytics-card">
            <div class="card-body analytics-metric">
                @php
                    $totalObs = $additionalData['summary']['total_observations'] ?? 0;
                    $reviewed = 0;
                    if (isset($additionalData['trends'])) {
                        $reviewed = $additionalData['trends']->sum('reviewed');
                    }
                    $reviewRate = $totalObs > 0 ? round(($reviewed / $totalObs) * 100, 1) : 0;
                @endphp
                <div class="metric-value text-success">{{ $reviewRate }}%</div>
                <div class="metric-label">Review Rate</div>
                <div class="metric-change">
                    <span class="text-muted">{{ $reviewed }} reviewed</span>
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
                    <canvas id="observationTrendsChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Observation Types Distribution -->
    <div class="col-xl-4">
        <div class="card analytics-card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="ri-pie-chart-line me-2"></i>Types Distribution
                </h5>
            </div>
            <div class="card-body">
                <div class="chart-container chart-small">
                    <canvas id="observationTypesChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Type Analysis & Severity Analysis -->
<div class="row mb-4">
    <div class="col-xl-6">
        <div class="card analytics-card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="ri-bar-chart-line me-2"></i>Observation Types Analysis
                </h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Type</th>
                                <th>Count</th>
                                <th>Avg Severity</th>
                                <th>Trend</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if (isset($additionalData['type_analysis']) && count($additionalData['type_analysis']) > 0)
                                @foreach ($additionalData['type_analysis'] as $type)
                                    @php
                                        $typeLabels = [
                                            'at_risk_behavior' => 'At Risk Behavior',
                                            'nearmiss_incident' => 'Near Miss Incident',
                                            'informal_risk_mgmt' => 'Risk Management',
                                            'sim_k3' => 'SIM K3',
                                        ];
                                        $typeName = $typeLabels[$type->observation_type] ?? $type->observation_type;
                                        $avgSeverity = round($type->avg_severity_score ?? 0, 1);
                                        $severityColor =
                                            $avgSeverity >= 3 ? 'danger' : ($avgSeverity >= 2 ? 'warning' : 'success');
                                    @endphp
                                    <tr>
                                        <td>{{ $typeName }}</td>
                                        <td>
                                            <span class="badge bg-primary">{{ $type->count ?? 0 }}</span>
                                        </td>
                                        <td>
                                            <span class="badge bg-{{ $severityColor }}">{{ $avgSeverity }}</span>
                                        </td>
                                        <td>
                                            <i class="ri-arrow-up-line text-success"></i>
                                        </td>
                                    </tr>
                                @endforeach
                            @else
                                <tr>
                                    <td colspan="4" class="text-center text-muted">No type analysis data available
                                    </td>
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
                    <i class="ri-shield-check-line me-2"></i>Severity Analysis
                </h5>
            </div>
            <div class="card-body">
                @if (isset($additionalData['severity_analysis']) && count($additionalData['severity_analysis']) > 0)
                    @foreach (['critical', 'high', 'medium', 'low'] as $severity)
                        @php
                            $severityData = $additionalData['severity_analysis'][$severity] ?? collect();
                            $totalCount = $severityData->sum('count');
                            $severityColors = [
                                'critical' => 'dark',
                                'high' => 'danger',
                                'medium' => 'warning',
                                'low' => 'success',
                            ];
                            $color = $severityColors[$severity];
                        @endphp
                        @if ($totalCount > 0)
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <div>
                                    <strong>{{ ucfirst($severity) }} Severity</strong>
                                    <small class="text-muted d-block">
                                        @foreach ($severityData as $item)
                                            {{ ucfirst(str_replace('_', ' ', $item->observation_type)) }}
                                            ({{ $item->count }})
                                        @endforeach
                                    </small>
                                </div>
                                <div class="text-end">
                                    <div class="fw-bold text-{{ $color }}">{{ $totalCount }}</div>
                                </div>
                            </div>
                            <div class="progress mb-3" style="height: 6px;">
                                <div class="progress-bar bg-{{ $color }}"
                                    style="width: {{ min(($totalCount / 50) * 100, 100) }}%"></div>
                            </div>
                        @endif
                    @endforeach
                @else
                    <div class="text-center text-muted py-4">
                        <i class="ri-shield-line fs-48 mb-3"></i>
                        <p>No severity analysis data available</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Observer Performance -->
<div class="row">
    <div class="col-12">
        <div class="card analytics-card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="ri-team-line me-2"></i>Observer Performance
                </h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Observer</th>
                                <th>Total Observations</th>
                                <th>Reviewed</th>
                                <th>This Month</th>
                                <th>Review Rate</th>
                                <th>Performance</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if (isset($additionalData['observer_performance']) && count($additionalData['observer_performance']) > 0)
                                @foreach ($additionalData['observer_performance'] as $observer)
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="avatar-sm bg-primary bg-gradient rounded me-2">
                                                    <span
                                                        class="avatar-title fs-14">{{ strtoupper(substr($observer->name ?? 'U', 0, 2)) }}</span>
                                                </div>
                                                <div>
                                                    <div class="fw-bold">{{ $observer->name ?? 'Unknown' }}</div>
                                                    <small
                                                        class="text-muted">{{ $observer->email ?? 'No email' }}</small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>{{ $observer->observations_count ?? 0 }}</td>
                                        <td>
                                            <span
                                                class="badge bg-success">{{ $observer->reviewed_observations_count ?? 0 }}</span>
                                        </td>
                                        <td>{{ $observer->this_month_observations_count ?? 0 }}</td>
                                        <td>{{ $observer->review_rate ?? 0 }}%</td>
                                        <td>
                                            <div class="progress" style="width: 100px; height: 8px;">
                                                <div class="progress-bar {{ ($observer->review_rate ?? 0) >= 80 ? 'bg-success' : (($observer->review_rate ?? 0) >= 60 ? 'bg-warning' : 'bg-danger') }}"
                                                    style="width: {{ $observer->review_rate ?? 0 }}%"></div>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            @else
                                <tr>
                                    <td colspan="6" class="text-center text-muted py-4">
                                        <i class="ri-team-line fs-48 mb-3"></i>
                                        <p>No observer performance data available</p>
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
                        <a href="{{ route('admin.observations.index') }}?status=submitted"
                            class="btn btn-outline-warning w-100 mb-2">
                            <i class="ri-send-plane-line me-2"></i>Review Submitted
                        </a>
                    </div>
                    <div class="col-md-3">
                        <a href="{{ route('admin.observations.index') }}?status=draft"
                            class="btn btn-outline-secondary w-100 mb-2">
                            <i class="ri-draft-line me-2"></i>View Drafts
                        </a>
                    </div>
                    <div class="col-md-3">
                        <button type="button" class="btn btn-outline-primary w-100 mb-2"
                            onclick="createObservation()">
                            <i class="ri-add-line me-2"></i>Create New Observation
                        </button>
                    </div>
                    <div class="col-md-3">
                        <a href="{{ route('admin.observations.index') }}" class="btn btn-outline-info w-100 mb-2">
                            <i class="ri-list-line me-2"></i>View All Observations
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
        initObservationAnalyticsCharts();
    });

    function initObservationAnalyticsCharts() {
        // Get data safely
        const trendsData = @json($additionalData['trends'] ?? []);
        const typeAnalysisData = @json($additionalData['type_analysis'] ?? []);

        // Trends Chart
        const trendsCtx = document.getElementById('observationTrendsChart');
        if (trendsCtx && trendsData.length > 0) {
            new Chart(trendsCtx, {
                type: 'line',
                data: {
                    labels: trendsData.map(item => item.month_name || 'Unknown'),
                    datasets: [{
                        label: 'Total Observations',
                        data: trendsData.map(item => item.total || 0),
                        borderColor: '#667eea',
                        backgroundColor: 'rgba(102, 126, 234, 0.1)',
                        tension: 0.4,
                        fill: true
                    }, {
                        label: 'Reviewed',
                        data: trendsData.map(item => item.reviewed || 0),
                        borderColor: '#28a745',
                        backgroundColor: 'rgba(40, 167, 69, 0.1)',
                        tension: 0.4,
                        fill: true
                    }, {
                        label: 'Total Details',
                        data: trendsData.map(item => item.total_details || 0),
                        borderColor: '#ffc107',
                        backgroundColor: 'rgba(255, 193, 7, 0.1)',
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
            // Show empty state for trends chart with proper centering
            trendsCtx.parentElement.innerHTML = `
                <div class="d-flex align-items-center justify-content-center h-100" style="min-height: 300px;">
                    <div class="text-center text-muted">
                        <i class="ri-line-chart-line fs-48 mb-3"></i>
                        <p class="mb-0">No trends data available</p>
                        <small>Data will appear when observations are created</small>
                    </div>
                </div>
            `;
        }

        // Observation Types Chart
        const typesCtx = document.getElementById('observationTypesChart');
        if (typesCtx && typeAnalysisData.length > 0) {
            const typeLabels = {
                'at_risk_behavior': 'At Risk Behavior',
                'nearmiss_incident': 'Near Miss Incident',
                'informal_risk_mgmt': 'Risk Management',
                'sim_k3': 'SIM K3'
            };

            new Chart(typesCtx, {
                type: 'doughnut',
                data: {
                    labels: typeAnalysisData.map(item => typeLabels[item.observation_type] || item
                        .observation_type),
                    datasets: [{
                        data: typeAnalysisData.map(item => item.count || 0),
                        backgroundColor: [
                            '#dc3545', // At Risk - red
                            '#ffc107', // Near Miss - yellow
                            '#17a2b8', // Risk Mgmt - info
                            '#6f42c1' // SIM K3 - purple
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
        } else if (typesCtx) {
            // Show empty state for types chart with proper centering
            typesCtx.parentElement.innerHTML = `
                <div class="d-flex align-items-center justify-content-center h-100" style="min-height: 200px;">
                    <div class="text-center text-muted">
                        <i class="ri-pie-chart-line fs-48 mb-3"></i>
                        <p class="mb-0">No types data available</p>
                        <small>Chart will appear when observations are added</small>
                    </div>
                </div>
            `;
        }
    }

    // Error handling for chart initialization
    window.addEventListener('error', function(e) {
        if (e.message.includes('Chart')) {
            console.warn('Chart.js error:', e.message);
            // Fallback: hide chart containers and show message
            document.querySelectorAll('.chart-container').forEach(container => {
                container.innerHTML = `
                    <div class="d-flex align-items-center justify-content-center h-100" style="min-height: 250px;">
                        <div class="text-center text-muted">
                            <i class="ri-bar-chart-line fs-48 mb-3"></i>
                            <p class="mb-0">Chart data unavailable</p>
                            <small>Please try refreshing the page</small>
                        </div>
                    </div>
                `;
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
