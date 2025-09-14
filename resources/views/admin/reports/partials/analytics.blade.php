<!-- Analytics Filters -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-primary text-white">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">
                        <i class="ri-filter-3-line me-2"></i>Analytics Filters
                    </h5>
                    <button type="button" class="btn btn-sm btn-light" onclick="toggleFilterCollapse()">
                        <i class="ri-eye-line me-1" id="filterToggleIcon"></i>
                        <span id="filterToggleText">Show Filters</span>
                    </button>
                </div>
            </div>
            <div class="card-body collapse" id="analyticsFilters">
                <form id="analyticsFilterForm" onsubmit="applyAnalyticsFilters(event)">
                    <div class="row g-3">
                        <!-- Date Range Filter -->
                        <div class="col-xl-3 col-md-6">
                            <label class="form-label fw-bold">
                                <i class="ri-calendar-line text-primary me-1"></i>Date Range
                            </label>
                            <div class="input-group">
                                <input type="date" class="form-control" id="filter_start_date" name="start_date"
                                    placeholder="Start Date">
                                <span class="input-group-text"><i class="ri-arrow-right-line"></i></span>
                                <input type="date" class="form-control" id="filter_end_date" name="end_date"
                                    placeholder="End Date">
                            </div>
                            <small class="text-muted">Filter by report creation date</small>
                        </div>

                        <!-- Quick Date Presets -->
                        <div class="col-xl-3 col-md-6">
                            <label class="form-label fw-bold">
                                <i class="ri-time-line text-success me-1"></i>Quick Presets
                            </label>
                            <select class="form-select" id="filter_date_preset" onchange="applyDatePreset(this.value)">
                                <option value="">Select Period</option>
                                <option value="today">Today</option>
                                <option value="yesterday">Yesterday</option>
                                <option value="last_7_days">Last 7 Days</option>
                                <option value="last_30_days">Last 30 Days</option>
                                <option value="last_90_days">Last 90 Days</option>
                                <option value="this_month">This Month</option>
                                <option value="last_month">Last Month</option>
                                <option value="this_quarter">This Quarter</option>
                                <option value="this_year">This Year</option>
                            </select>
                            <small class="text-muted">Quick date range selection</small>
                        </div>

                        <!-- Status Filter -->
                        <div class="col-xl-2 col-md-4">
                            <label class="form-label fw-bold">
                                <i class="ri-flag-line text-warning me-1"></i>Status
                            </label>
                            <select class="form-select" id="filter_status" name="status">
                                <option value="">All Status</option>
                                <option value="waiting">Waiting</option>
                                <option value="in-progress">In Progress</option>
                                <option value="done">Completed</option>
                            </select>
                            <small class="text-muted">Filter by report status</small>
                        </div>

                        <!-- Severity Filter -->
                        <div class="col-xl-2 col-md-4">
                            <label class="form-label fw-bold">
                                <i class="ri-alarm-warning-line text-danger me-1"></i>Severity
                            </label>
                            <select class="form-select" id="filter_severity" name="severity">
                                <option value="">All Severity</option>
                                <option value="low">Low</option>
                                <option value="medium">Medium</option>
                                <option value="high">High</option>
                                <option value="critical">Critical</option>
                            </select>
                            <small class="text-muted">Filter by severity level</small>
                        </div>

                        <!-- Category Filter -->
                        <div class="col-xl-2 col-md-4">
                            <label class="form-label fw-bold">
                                <i class="ri-list-check-line text-info me-1"></i>Category
                            </label>
                            <select class="form-select" id="filter_category" name="category_id">
                                <option value="">All Categories</option>
                                @if (isset($additionalData['filter_options']['categories']))
                                    @foreach ($additionalData['filter_options']['categories'] as $category)
                                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                                    @endforeach
                                @endif
                            </select>
                            <small class="text-muted">Filter by category type</small>
                        </div>
                    </div>

                    <div class="row g-3 mt-2">
                        <!-- Location Filter -->
                        <div class="col-xl-3 col-md-6">
                            <label class="form-label fw-bold">
                                <i class="ri-map-pin-line text-secondary me-1"></i>Location
                            </label>
                            <select class="form-select" id="filter_location" name="location_id">
                                <option value="">All Locations</option>
                                @if (isset($additionalData['filter_options']['locations']))
                                    @foreach ($additionalData['filter_options']['locations'] as $location)
                                        <option value="{{ $location->id }}">{{ $location->name }}</option>
                                    @endforeach
                                @endif
                            </select>
                            <small class="text-muted">Filter by location</small>
                        </div>

                        <!-- Project Filter -->
                        <div class="col-xl-3 col-md-6">
                            <label class="form-label fw-bold">
                                <i class="ri-building-line text-dark me-1"></i>Project
                            </label>
                            <select class="form-select" id="filter_project" name="project_name">
                                <option value="">All Projects</option>
                                @if (isset($additionalData['filter_options']['projects']))
                                    @foreach ($additionalData['filter_options']['projects'] as $project)
                                        <option value="{{ $project }}">{{ $project }}</option>
                                    @endforeach
                                @endif
                            </select>
                            <small class="text-muted">Filter by project name</small>
                        </div>

                        <!-- HSE Staff Filter -->
                        <div class="col-xl-3 col-md-6">
                            <label class="form-label fw-bold">
                                <i class="ri-user-settings-line text-primary me-1"></i>BAIK Staff
                            </label>
                            <select class="form-select" id="filter_hse_staff" name="hse_staff_id">
                                <option value="">All BAIK Staff</option>
                                @if (isset($additionalData['filter_options']['hse_staff']))
                                    @foreach ($additionalData['filter_options']['hse_staff'] as $staff)
                                        <option value="{{ $staff->id }}">{{ $staff->name }}</option>
                                    @endforeach
                                @endif
                            </select>
                            <small class="text-muted">Filter by assigned staff</small>
                        </div>

                        <!-- Action Buttons -->
                        <div class="col-xl-3 col-md-6">
                            <label class="form-label fw-bold text-transparent">Actions</label>
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary flex-grow-1">
                                    <i class="ri-search-line me-1"></i>Filters
                                </button>
                                <button type="button" class="btn btn-outline-secondary" onclick="clearAllFilters()">
                                    <i class="ri-refresh-line me-1"></i>Reset
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Active Filters Display -->
                    <div id="activeFiltersDisplay" class="mt-3" style="display: none;">
                        <div class="d-flex align-items-center flex-wrap gap-2">
                            <span class="fw-bold text-muted">Active Filters:</span>
                            <div id="activeFilterTags"></div>
                            <button type="button" class="btn btn-sm btn-outline-danger ms-2"
                                onclick="clearAllFilters()">
                                <i class="ri-close-line"></i> Clear All
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Analytics Dashboard -->
<style>
    .analytics-loading-overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(255, 255, 255, 0.9);
        z-index: 9999;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .filter-message {
        margin-top: 1rem;
    }

    .btn:disabled {
        opacity: 0.7;
    }

    .analytics-content-wrapper {
        position: relative;
    }

    /* Smooth transitions for updated content */
    .analytics-card {
        transition: all 0.3s ease;
    }

    .metric-value {
        transition: color 0.3s ease;
    }

    .badge {
        transition: all 0.3s ease;
    }

    /* Period Analysis Cards */
    .bg-light-success {
        background-color: rgba(40, 167, 69, 0.1) !important;
    }

    .bg-light-warning {
        background-color: rgba(255, 193, 7, 0.1) !important;
    }

    .bg-light-danger {
        background-color: rgba(220, 53, 69, 0.1) !important;
    }

    .bg-light-info {
        background-color: rgba(13, 202, 240, 0.1) !important;
    }

    .border-success {
        border-color: rgba(40, 167, 69, 0.3) !important;
    }

    .border-warning {
        border-color: rgba(255, 193, 7, 0.3) !important;
    }

    .border-danger {
        border-color: rgba(220, 53, 69, 0.3) !important;
    }

    .border-info {
        border-color: rgba(13, 202, 240, 0.3) !important;
    }

    /* Table row styling for period analysis */
    .table-success-light {
        background-color: rgba(40, 167, 69, 0.05) !important;
    }

    .table-warning-light {
        background-color: rgba(255, 193, 7, 0.05) !important;
    }

    .table-danger-light {
        background-color: rgba(220, 53, 69, 0.05) !important;
    }

    .table-info-light {
        background-color: rgba(13, 202, 240, 0.05) !important;
    }

    .table-primary {
        background-color: rgba(13, 110, 253, 0.1) !important;
    }

    /* Progress bars in tables */
    .table .progress {
        margin: 0;
    }

    .table .badge.fs-6 {
        font-size: 0.9rem !important;
        padding: 0.375rem 0.75rem;
    }
</style>

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
                <div class="metric-value text-warning">{{ $additionalData['summary']['critical_incidents'] ?? 0 }}
                </div>
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

<!-- NEW: Monthly Findings Report -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card analytics-card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="ri-calendar-line me-2"></i>Monthly Findings Report (Open & Closed)
                </h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead class="table-dark">
                            <tr>
                                <th>Month</th>
                                <th>Total Findings</th>
                                <th>Closed</th>
                                <th>Open</th>
                                <th>Completion Rate</th>
                                <th>Low</th>
                                <th>Medium</th>
                                <th>High</th>
                                <th>Critical</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if (isset($additionalData['monthly_findings']) && count($additionalData['monthly_findings']) > 0)
                                @foreach ($additionalData['monthly_findings'] as $month)
                                    <tr>
                                        <td><strong>{{ $month->month_name }}</strong></td>
                                        <td>{{ $month->total_findings }}</td>
                                        <td><span class="badge bg-success">{{ $month->closed_findings }}</span></td>
                                        <td><span class="badge bg-warning">{{ $month->open_findings }}</span></td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <span class="me-2">{{ $month->completion_rate }}%</span>
                                                <div class="progress flex-grow-1"
                                                    style="height: 6px; max-width: 80px;">
                                                    <div class="progress-bar {{ $month->completion_rate >= 80 ? 'bg-success' : ($month->completion_rate >= 60 ? 'bg-warning' : 'bg-danger') }}"
                                                        style="width: {{ $month->completion_rate }}%"></div>
                                                </div>
                                            </div>
                                        </td>
                                        <td>{{ $month->low_severity }}</td>
                                        <td>{{ $month->medium_severity }}</td>
                                        <td>{{ $month->high_severity }}</td>
                                        <td>{{ $month->critical_severity }}</td>
                                    </tr>
                                @endforeach
                            @else
                                <tr>
                                    <td colspan="9" class="text-center text-muted py-4">No monthly findings data
                                        available</td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- NEW: Period-Based Reports -->
<div class="row mb-4" id="periodBasedReportsSection">
    <div class="col-12">
        <div class="card analytics-card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="ri-time-line me-2"></i>Period Analysis <span id="periodAnalysisTitle">(All Data Overview)</span>
                </h5>
            </div>
            <div class="card-body">
                <div id="periodBasedReportsContent">
                    @if (isset($additionalData['period_based_reports']) && count($additionalData['period_based_reports']) > 0)
                        @if (isset($additionalData['period_based_reports']['filtered_period']))
                            <!-- Filtered Period Table -->
                            @php $filtered = $additionalData['period_based_reports']['filtered_period']; @endphp
                            <div class="mb-3">
                                <div class="alert alert-info border-0">
                                    <div class="row align-items-center">
                                        <div class="col-md-8">
                                            <h6 class="alert-heading mb-2">
                                                <i class="ri-calendar-check-line me-2"></i>{{ $filtered['label'] }}
                                            </h6>
                                            <p class="mb-0">
                                                <strong>{{ $filtered['period']['start'] }}</strong> to
                                                <strong>{{ $filtered['period']['end'] }}</strong>
                                                <br>
                                                <small class="text-muted">{{ $filtered['period']['total_days'] }} days
                                                    | Average: {{ $filtered['avg_per_day'] }} reports/day</small>
                                            </p>
                                        </div>
                                        <div class="col-md-4 text-end">
                                            <div class="fs-2 fw-bold text-primary">
                                                {{ $filtered['data']->total_findings ?? 0 }}</div>
                                            <small class="text-muted">Total Findings</small>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="table-responsive">
                                <table class="table table-striped table-hover">
                                    <thead class="table-primary">
                                        <tr>
                                            <th>Status</th>
                                            <th>Count</th>
                                            <th>Percentage</th>
                                            <th>Progress</th>
                                            <th>Details</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @php
                                            $total = $filtered['data']->total_findings ?? 0;
                                            $closed = $filtered['data']->closed_findings ?? 0;
                                            $open = $filtered['data']->open_findings ?? 0;
                                            $critical = $filtered['data']->critical_findings ?? 0;
                                            $high = $filtered['data']->high_findings ?? 0;
                                            $medium = $filtered['data']->medium_findings ?? 0;
                                            $low = $filtered['data']->low_findings ?? 0;

                                            $closedPercent = $total > 0 ? round(($closed / $total) * 100, 1) : 0;
                                            $openPercent = $total > 0 ? round(($open / $total) * 100, 1) : 0;
                                            $criticalHighPercent =
                                                $total > 0 ? round((($critical + $high) / $total) * 100, 1) : 0;
                                            $mediumLowPercent =
                                                $total > 0 ? round((($medium + $low) / $total) * 100, 1) : 0;
                                        @endphp

                                        <tr class="table-success-light">
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <i class="ri-checkbox-circle-line text-success fs-5 me-2"></i>
                                                    <strong class="text-success">Closed Reports</strong>
                                                </div>
                                            </td>
                                            <td><span class="badge bg-success fs-6">{{ $closed }}</span></td>
                                            <td><strong class="text-success">{{ $closedPercent }}%</strong></td>
                                            <td>
                                                <div class="progress" style="height: 8px; width: 100px;">
                                                    <div class="progress-bar bg-success"
                                                        style="width: {{ $closedPercent }}%"></div>
                                                </div>
                                            </td>
                                            <td><small class="text-muted">Completed successfully</small></td>
                                        </tr>

                                        <tr class="table-warning-light">
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <i class="ri-timer-line text-warning fs-5 me-2"></i>
                                                    <strong class="text-warning">Open Reports</strong>
                                                </div>
                                            </td>
                                            <td><span class="badge bg-warning fs-6">{{ $open }}</span></td>
                                            <td><strong class="text-warning">{{ $openPercent }}%</strong></td>
                                            <td>
                                                <div class="progress" style="height: 8px; width: 100px;">
                                                    <div class="progress-bar bg-warning"
                                                        style="width: {{ $openPercent }}%"></div>
                                                </div>
                                            </td>
                                            <td><small class="text-muted">In progress or waiting</small></td>
                                        </tr>

                                        <tr class="table-danger-light">
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <i class="ri-alarm-warning-line text-danger fs-5 me-2"></i>
                                                    <strong class="text-danger">Critical & High</strong>
                                                </div>
                                            </td>
                                            <td><span class="badge bg-danger fs-6">{{ $critical + $high }}</span></td>
                                            <td><strong class="text-danger">{{ $criticalHighPercent }}%</strong></td>
                                            <td>
                                                <div class="progress" style="height: 8px; width: 100px;">
                                                    <div class="progress-bar bg-danger"
                                                        style="width: {{ $criticalHighPercent }}%"></div>
                                                </div>
                                            </td>
                                            <td><small class="text-muted">{{ $critical }} Critical,
                                                    {{ $high }} High</small></td>
                                        </tr>

                                        <tr class="table-info-light">
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <i class="ri-information-line text-info fs-5 me-2"></i>
                                                    <strong class="text-info">Medium & Low</strong>
                                                </div>
                                            </td>
                                            <td><span class="badge bg-info fs-6">{{ $medium + $low }}</span></td>
                                            <td><strong class="text-info">{{ $mediumLowPercent }}%</strong></td>
                                            <td>
                                                <div class="progress" style="height: 8px; width: 100px;">
                                                    <div class="progress-bar bg-info"
                                                        style="width: {{ $mediumLowPercent }}%"></div>
                                                </div>
                                            </td>
                                            <td><small class="text-muted">{{ $medium }} Medium,
                                                    {{ $low }} Low</small></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <!-- Default All Data Table (Same format as filtered) -->
                            @php
                                $allTotal = $additionalData['summary']['total_reports'] ?? 0;

                                // Use accurate breakdown from backend
                                $statusBreakdown = $additionalData['summary']['status_breakdown'] ?? [];
                                $severityBreakdown = $additionalData['summary']['severity_breakdown'] ?? [];

                                $allClosed = $statusBreakdown['closed'] ?? 0;
                                $allOpen = $statusBreakdown['open'] ?? 0;
                                $allCritical = $severityBreakdown['critical'] ?? 0;
                                $allHigh = $severityBreakdown['high'] ?? 0;
                                $allMedium = $severityBreakdown['medium'] ?? 0;
                                $allLow = $severityBreakdown['low'] ?? 0;

                                $allClosedPercent = $allTotal > 0 ? round(($allClosed / $allTotal) * 100, 1) : 0;
                                $allOpenPercent = $allTotal > 0 ? round(($allOpen / $allTotal) * 100, 1) : 0;
                                $allCriticalHighPercent = $allTotal > 0 ? round((($allCritical + $allHigh) / $allTotal) * 100, 1) : 0;
                                $allMediumLowPercent = $allTotal > 0 ? round((($allMedium + $allLow) / $allTotal) * 100, 1) : 0;
                            @endphp

                            <div class="mb-3">
                                <div class="alert alert-primary border-0">
                                    <div class="row align-items-center">
                                        <div class="col-md-8">
                                            <h6 class="alert-heading mb-2">
                                                <i class="ri-database-line me-2"></i>All Data Summary
                                            </h6>
                                            <p class="mb-0">
                                                <strong>Complete dataset without any filters applied</strong>
                                                <br>
                                                <small class="text-muted">Total records from all periods and categories</small>
                                            </p>
                                        </div>
                                        <div class="col-md-4 text-end">
                                            <div class="fs-2 fw-bold text-primary">{{ $allTotal }}</div>
                                            <small class="text-muted">Total Findings</small>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="table-responsive">
                                <table class="table table-striped table-hover">
                                    <thead class="table-primary">
                                        <tr>
                                            <th>Status</th>
                                            <th>Count</th>
                                            <th>Percentage</th>
                                            <th>Progress</th>
                                            <th>Details</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr class="table-success-light">
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <i class="ri-checkbox-circle-line text-success fs-5 me-2"></i>
                                                    <strong class="text-success">Closed Reports</strong>
                                                </div>
                                            </td>
                                            <td><span class="badge bg-success fs-6">{{ $allClosed }}</span></td>
                                            <td><strong class="text-success">{{ $allClosedPercent }}%</strong></td>
                                            <td>
                                                <div class="progress" style="height: 8px; width: 100px;">
                                                    <div class="progress-bar bg-success" style="width: {{ $allClosedPercent }}%"></div>
                                                </div>
                                            </td>
                                            <td><small class="text-muted">Completed successfully</small></td>
                                        </tr>

                                        <tr class="table-warning-light">
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <i class="ri-timer-line text-warning fs-5 me-2"></i>
                                                    <strong class="text-warning">Open Reports</strong>
                                                </div>
                                            </td>
                                            <td><span class="badge bg-warning fs-6">{{ $allOpen }}</span></td>
                                            <td><strong class="text-warning">{{ $allOpenPercent }}%</strong></td>
                                            <td>
                                                <div class="progress" style="height: 8px; width: 100px;">
                                                    <div class="progress-bar bg-warning" style="width: {{ $allOpenPercent }}%"></div>
                                                </div>
                                            </td>
                                            <td><small class="text-muted">In progress or waiting</small></td>
                                        </tr>

                                        <tr class="table-danger-light">
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <i class="ri-alarm-warning-line text-danger fs-5 me-2"></i>
                                                    <strong class="text-danger">Critical & High</strong>
                                                </div>
                                            </td>
                                            <td><span class="badge bg-danger fs-6">{{ $allCritical + $allHigh }}</span></td>
                                            <td><strong class="text-danger">{{ $allCriticalHighPercent }}%</strong></td>
                                            <td>
                                                <div class="progress" style="height: 8px; width: 100px;">
                                                    <div class="progress-bar bg-danger" style="width: {{ $allCriticalHighPercent }}%"></div>
                                                </div>
                                            </td>
                                            <td><small class="text-muted">{{ $allCritical }} Critical, {{ $allHigh }} High</small></td>
                                        </tr>

                                        <tr class="table-info-light">
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <i class="ri-information-line text-info fs-5 me-2"></i>
                                                    <strong class="text-info">Medium & Low</strong>
                                                </div>
                                            </td>
                                            <td><span class="badge bg-info fs-6">{{ $allMedium + $allLow }}</span></td>
                                            <td><strong class="text-info">{{ $allMediumLowPercent }}%</strong></td>
                                            <td>
                                                <div class="progress" style="height: 8px; width: 100px;">
                                                    <div class="progress-bar bg-info" style="width: {{ $allMediumLowPercent }}%"></div>
                                                </div>
                                            </td>
                                            <td><small class="text-muted">{{ $allMedium }} Medium, {{ $allLow }} Low</small></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    @else
                        <div class="text-center text-muted py-4">
                            <i class="ri-calendar-line fs-48 mb-3"></i>
                            <p>No period-based data available</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- NEW: Location & Project Reports -->
<div class="row mb-4">
    <div class="col-xl-6">
        <div class="card analytics-card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="ri-map-pin-line me-2"></i>Findings by Location (Open & Closed)
                </h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Location</th>
                                <th>Total</th>
                                <th>Closed</th>
                                <th>Open</th>
                                <th>Critical</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if (isset($additionalData['location_project_reports']['by_location']) &&
                                    count($additionalData['location_project_reports']['by_location']) > 0)
                                @foreach ($additionalData['location_project_reports']['by_location'] as $location)
                                    <tr>
                                        <td><strong>{{ $location->location_name }}</strong></td>
                                        <td>{{ $location->total_reports }}</td>
                                        <td><span class="badge bg-success">{{ $location->closed_reports }}</span></td>
                                        <td><span class="badge bg-warning">{{ $location->open_reports }}</span></td>
                                        <td><span class="badge bg-danger">{{ $location->critical_reports }}</span>
                                        </td>
                                    </tr>
                                @endforeach
                            @else
                                <tr>
                                    <td colspan="5" class="text-center text-muted">No location data available</td>
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
                    <i class="ri-building-line me-2"></i>Findings by Project (Open & Closed)
                </h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Project Name</th>
                                <th>Total</th>
                                <th>Closed</th>
                                <th>Open</th>
                                <th>Critical</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if (isset($additionalData['location_project_reports']['by_project']) &&
                                    count($additionalData['location_project_reports']['by_project']) > 0)
                                @foreach ($additionalData['location_project_reports']['by_project'] as $project)
                                    <tr>
                                        <td><strong>{{ $project->project_name }}</strong></td>
                                        <td>{{ $project->total_reports }}</td>
                                        <td><span class="badge bg-success">{{ $project->closed_reports }}</span></td>
                                        <td><span class="badge bg-warning">{{ $project->open_reports }}</span></td>
                                        <td><span class="badge bg-danger">{{ $project->critical_reports }}</span></td>
                                    </tr>
                                @endforeach
                            @else
                                <tr>
                                    <td colspan="5" class="text-center text-muted">No project data available</td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- NEW: Category Detailed Reports -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card analytics-card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="ri-list-check-line me-2"></i>Findings by Category (Unsafe Condition, etc) - Open & Closed
                </h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead class="table-dark">
                            <tr>
                                <th>Category</th>
                                <th>Description</th>
                                <th>Total</th>
                                <th>Closed</th>
                                <th>Open</th>
                                <th>Completion Rate</th>
                                <th>Low</th>
                                <th>Medium</th>
                                <th>High</th>
                                <th>Critical</th>
                                <th>Avg Resolution (h)</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if (isset($additionalData['category_detailed_reports']) && count($additionalData['category_detailed_reports']) > 0)
                                @foreach ($additionalData['category_detailed_reports'] as $category)
                                    <tr>
                                        <td><strong>{{ $category->category_name }}</strong></td>
                                        <td>
                                            <small
                                                class="text-muted">{{ Str::limit($category->category_description ?? 'No description', 50) }}</small>
                                        </td>
                                        <td>{{ $category->total_reports }}</td>
                                        <td><span class="badge bg-success">{{ $category->closed_reports }}</span></td>
                                        <td><span class="badge bg-warning">{{ $category->open_reports }}</span></td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <span class="me-2">{{ $category->completion_rate }}%</span>
                                                <div class="progress flex-grow-1"
                                                    style="height: 6px; max-width: 80px;">
                                                    <div class="progress-bar {{ $category->completion_rate >= 80 ? 'bg-success' : ($category->completion_rate >= 60 ? 'bg-warning' : 'bg-danger') }}"
                                                        style="width: {{ $category->completion_rate }}%"></div>
                                                </div>
                                            </div>
                                        </td>
                                        <td>{{ $category->low_severity }}</td>
                                        <td>{{ $category->medium_severity }}</td>
                                        <td>{{ $category->high_severity }}</td>
                                        <td>{{ $category->critical_severity }}</td>
                                        <td>{{ $category->avg_resolution_hours }}</td>
                                    </tr>
                                @endforeach
                            @else
                                <tr>
                                    <td colspan="11" class="text-center text-muted py-4">No category data available
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

<!-- BAIK Performance -->
<div class="row">
    <div class="col-12">
        <div class="card analytics-card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="ri-team-line me-2"></i>BAIK Staff Performance
                </h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>BAIK Staff</th>
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
                                                <div
                                                    class="avatar-sm d-flex align-items-center justify-content-center bg-primary bg-gradient rounded me-2">
                                                    <span
                                                        class="avatar-title fs-14">{{ strtoupper(substr($staff->name ?? 'U', 0, 2)) }}</span>
                                                </div>
                                                <div>
                                                    <div class="fw-bold">{{ $staff->name ?? 'Unknown' }}</div>
                                                    <small
                                                        class="text-muted">{{ $staff->email ?? 'No email' }}</small>
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
                                        <p>No BAIK staff performance data available</p>
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

<!-- Include Chart.js and jQuery -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
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
                            borderWidth: 1,
                            callbacks: {
                                afterBody: function(tooltipItems) {
                                    const dataIndex = tooltipItems[0].dataIndex;
                                    const monthData = trendsData[dataIndex];

                                    if (monthData && monthData.categories && monthData.categories.length >
                                        0) {
                                        let categoryInfo = '\n\nTop Categories:';
                                        const topCategories = monthData.categories.slice(0,
                                            3); // Show top 3 categories
                                        topCategories.forEach(function(cat, index) {
                                            categoryInfo +=
                                                `\n${index + 1}. ${cat.category}: ${cat.count}`;
                                        });
                                        return categoryInfo;
                                    }
                                    return '';
                                }
                            }
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

    // Analytics Filter Functions
    function toggleFilterCollapse() {
        const filtersDiv = document.getElementById('analyticsFilters');
        const toggleIcon = document.getElementById('filterToggleIcon');
        const toggleText = document.getElementById('filterToggleText');

        if (filtersDiv.classList.contains('show')) {
            filtersDiv.classList.remove('show');
            toggleIcon.className = 'ri-eye-line me-2';
            toggleText.textContent = 'Show Filters';
        } else {
            filtersDiv.classList.add('show');
            toggleIcon.className = 'ri-eye-off-line me-2';
            toggleText.textContent = 'Hide Filters';
        }
    }

    function applyDatePreset(preset) {
        const startDateInput = document.getElementById('filter_start_date');
        const endDateInput = document.getElementById('filter_end_date');
        const today = new Date();
        let startDate, endDate;

        switch (preset) {
            case 'today':
                startDate = endDate = today;
                break;
            case 'yesterday':
                startDate = endDate = new Date(today.getTime() - 24 * 60 * 60 * 1000);
                break;
            case 'last_7_days':
                startDate = new Date(today.getTime() - 7 * 24 * 60 * 60 * 1000);
                endDate = today;
                break;
            case 'last_30_days':
                startDate = new Date(today.getTime() - 30 * 24 * 60 * 60 * 1000);
                endDate = today;
                break;
            case 'last_90_days':
                startDate = new Date(today.getTime() - 90 * 24 * 60 * 60 * 1000);
                endDate = today;
                break;
            case 'this_month':
                startDate = new Date(today.getFullYear(), today.getMonth(), 1);
                endDate = today;
                break;
            case 'last_month':
                const lastMonth = new Date(today.getFullYear(), today.getMonth() - 1, 1);
                startDate = lastMonth;
                endDate = new Date(today.getFullYear(), today.getMonth(), 0);
                break;
            case 'this_quarter':
                const quarter = Math.floor(today.getMonth() / 3);
                startDate = new Date(today.getFullYear(), quarter * 3, 1);
                endDate = today;
                break;
            case 'this_year':
                startDate = new Date(today.getFullYear(), 0, 1);
                endDate = today;
                break;
            default:
                return;
        }

        startDateInput.value = startDate.toISOString().split('T')[0];
        endDateInput.value = endDate.toISOString().split('T')[0];
    }

    function applyAnalyticsFilters(event) {
        event.preventDefault();

        const form = document.getElementById('analyticsFilterForm');
        const formData = new FormData(form);

        // Show loading state
        showFilterLoading(true);

        // Update active filters display
        updateActiveFiltersDisplay(formData);

        // Prepare AJAX data
        const filterData = {};
        for (const [key, value] of formData.entries()) {
            if (value && value.trim() !== '') {
                filterData[key] = value.trim();
            }
        }

        // Make AJAX request
        $.ajax({
            url: '{{ route('admin.reports.analytics.filter') }}',
            method: 'POST',
            data: filterData,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                'Accept': 'application/json'
            },
            success: function(response) {
                if (response.success) {
                    // Update analytics content with new data
                    updateAnalyticsContent(response.data);

                    // Update URL without reload
                    updateUrlParams(filterData);

                    // Show success message
                    showFilterMessage('Filters applied successfully!', 'success');
                } else {
                    showFilterMessage('Failed to apply filters: ' + response.message, 'error');
                }
            },
            error: function(xhr, status, error) {
                console.error('Filter AJAX Error:', error);
                let message = 'Failed to apply filters. Please try again.';

                if (xhr.responseJSON && xhr.responseJSON.message) {
                    message = xhr.responseJSON.message;
                }

                showFilterMessage(message, 'error');
            },
            complete: function() {
                showFilterLoading(false);
            }
        });
    }

    function updateActiveFiltersDisplay(formData) {
        const activeFiltersDiv = document.getElementById('activeFiltersDisplay');
        const activeFilterTags = document.getElementById('activeFilterTags');
        const filterLabels = {
            'start_date': 'Start Date',
            'end_date': 'End Date',
            'status': 'Status',
            'severity': 'Severity',
            'category_id': 'Category',
            'location_id': 'Location',
            'project_name': 'Project',
            'hse_staff_id': 'BAIK Staff'
        };

        let hasActiveFilters = false;
        let tagsHTML = '';

        for (const [key, value] of formData.entries()) {
            if (value && value.trim() !== '' && filterLabels[key]) {
                hasActiveFilters = true;
                let displayValue = value;

                // Get display text for select options
                const selectElement = document.getElementById('filter_' + key.replace('_id', ''));
                if (selectElement && selectElement.tagName === 'SELECT') {
                    const option = selectElement.querySelector(`option[value="${value}"]`);
                    if (option) {
                        displayValue = option.textContent;
                    }
                }

                tagsHTML += `<span class="badge bg-primary me-1 mb-1">
                    ${filterLabels[key]}: ${displayValue}
                    <button type="button" class="btn-close btn-close-white ms-1" style="font-size: 0.6em;"
                            onclick="removeFilter('${key}')" aria-label="Remove filter"></button>
                </span>`;
            }
        }

        if (hasActiveFilters) {
            activeFilterTags.innerHTML = tagsHTML;
            activeFiltersDiv.style.display = 'block';
        } else {
            activeFiltersDiv.style.display = 'none';
        }
    }

    function removeFilter(filterKey) {
        const element = document.getElementById('filter_' + filterKey.replace('_id', ''));
        if (element) {
            element.value = '';
        }

        // Special handling for date preset
        if (filterKey === 'start_date' || filterKey === 'end_date') {
            document.getElementById('filter_date_preset').value = '';
        }

        // Reapply filters
        document.getElementById('analyticsFilterForm').dispatchEvent(new Event('submit'));
    }

    function clearAllFilters() {
        const form = document.getElementById('analyticsFilterForm');
        const inputs = form.querySelectorAll('input, select');

        inputs.forEach(input => {
            if (input.type === 'date' || input.tagName === 'SELECT') {
                input.value = '';
            }
        });

        document.getElementById('activeFiltersDisplay').style.display = 'none';

        // Show loading and make AJAX request to clear filters
        showFilterLoading(true);

        $.ajax({
            url: '{{ route('admin.reports.analytics.filter') }}',
            method: 'POST',
            data: {},
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                'Accept': 'application/json'
            },
            success: function(response) {
                if (response.success) {
                    updateAnalyticsContent(response.data);
                    updateUrlParams({});
                    showFilterMessage('Filters cleared successfully!', 'success');
                } else {
                    showFilterMessage('Failed to clear filters: ' + response.message, 'error');
                }
            },
            error: function(xhr, status, error) {
                console.error('Clear Filter AJAX Error:', error);
                showFilterMessage('Failed to clear filters. Please try again.', 'error');
            },
            complete: function() {
                showFilterLoading(false);
            }
        });
    }

    // Loading state management
    function showFilterLoading(show) {
        const submitBtn = document.querySelector('#analyticsFilterForm button[type="submit"]');
        const resetBtn = document.querySelector('#analyticsFilterForm button[onclick="clearAllFilters()"]');

        if (show) {
            // Disable buttons and show loading
            submitBtn.disabled = true;
            resetBtn.disabled = true;
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Applying...';

            // Show loading overlay
            if (!document.getElementById('analyticsLoadingOverlay')) {
                const overlay = document.createElement('div');
                overlay.id = 'analyticsLoadingOverlay';
                overlay.style.cssText = `
                    position: fixed; top: 0; left: 0; width: 100%; height: 100%;
                    background: rgba(255, 255, 255, 0.8); z-index: 9999;
                    display: flex; align-items: center; justify-content: center;
                `;
                overlay.innerHTML = `
                    <div class="d-flex flex-column align-items-center">
                        <div class="spinner-border text-primary mb-3"></div>
                        <p class="text-muted">Updating analytics data...</p>
                    </div>
                `;
                document.body.appendChild(overlay);
            }
        } else {
            submitBtn.disabled = false;
            resetBtn.disabled = false;
            submitBtn.innerHTML = '<i class="ri-search-line me-1"></i>Filters';

            const overlay = document.getElementById('analyticsLoadingOverlay');
            if (overlay) overlay.remove();
        }
    }

    // Update analytics content with new data
    function updateAnalyticsContent(data) {
        try {
            updateSummaryCards(data.summary || {});
            updatePeriodBasedReports(data.period_based_reports || {});
            console.log('Analytics content updated successfully');
        } catch (error) {
            console.error('Error updating analytics content:', error);
            showFilterMessage('Error updating analytics display. Please refresh the page.', 'error');
        }
    }

    // Update period-based reports section
    function updatePeriodBasedReports(periodData) {
        const contentDiv = document.getElementById('periodBasedReportsContent');
        const titleSpan = document.getElementById('periodAnalysisTitle');
        if (!contentDiv) return;

        console.log('Updating period-based reports with:', periodData);

        if (!periodData || Object.keys(periodData).length === 0) {
            contentDiv.innerHTML = `
                <div class="text-center text-muted py-4">
                    <i class="ri-calendar-line fs-48 mb-3"></i>
                    <p>No period-based data available</p>
                </div>
            `;
            return;
        }

        // Check if it's filtered period data
        if (periodData.filtered_period) {
            // Update title for filtered data
            if (titleSpan) titleSpan.textContent = '(Based on Applied Filters)';

            const filtered = periodData.filtered_period;
            const total = filtered.data.total_findings || 0;

            const closedPercent = total > 0 ? Math.round(((filtered.data.closed_findings || 0) / total) * 100 * 10) /
                10 : 0;
            const openPercent = total > 0 ? Math.round(((filtered.data.open_findings || 0) / total) * 100 * 10) / 10 :
                0;
            const criticalPercent = total > 0 ? Math.round((((filtered.data.critical_findings || 0) + (filtered.data
                .high_findings || 0)) / total) * 100 * 10) / 10 : 0;
            const mediumLowPercent = total > 0 ? Math.round((((filtered.data.medium_findings || 0) + (filtered.data
                .low_findings || 0)) / total) * 100 * 10) / 10 : 0;

            contentDiv.innerHTML = `
                <div class="alert alert-info border-0 mb-4">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h6 class="alert-heading mb-2">
                                <i class="ri-calendar-check-line me-2"></i>${filtered.label}
                            </h6>
                            <p class="mb-0">
                                <strong>${filtered.period.start}</strong> to <strong>${filtered.period.end}</strong>
                                <br>
                                <small class="text-muted">${filtered.period.total_days} days | Average: ${filtered.avg_per_day} reports/day</small>
                            </p>
                        </div>
                        <div class="col-md-4 text-end">
                            <div class="fs-2 fw-bold text-primary">${total}</div>
                            <small class="text-muted">Total Findings</small>
                        </div>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead class="table-primary">
                            <tr>
                                <th>Status</th>
                                <th>Count</th>
                                <th>Percentage</th>
                                <th>Progress</th>
                                <th>Details</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr class="table-success-light">
                                <td>
                                    <i class="ri-checkbox-circle-line me-2 text-success"></i>
                                    <strong>Closed Reports</strong>
                                </td>
                                <td><span class="badge bg-success fs-6">${filtered.data.closed_findings || 0}</span></td>
                                <td><strong>${closedPercent}%</strong></td>
                                <td>
                                    <div class="progress" style="height: 8px; min-width: 120px;">
                                        <div class="progress-bar bg-success" style="width: ${closedPercent}%"></div>
                                    </div>
                                </td>
                                <td><small class="text-muted">Completed successfully</small></td>
                            </tr>
                            <tr class="table-warning-light">
                                <td>
                                    <i class="ri-timer-line me-2 text-warning"></i>
                                    <strong>Open Reports</strong>
                                </td>
                                <td><span class="badge bg-warning fs-6">${filtered.data.open_findings || 0}</span></td>
                                <td><strong>${openPercent}%</strong></td>
                                <td>
                                    <div class="progress" style="height: 8px; min-width: 120px;">
                                        <div class="progress-bar bg-warning" style="width: ${openPercent}%"></div>
                                    </div>
                                </td>
                                <td><small class="text-muted">In progress or waiting</small></td>
                            </tr>
                            <tr class="table-danger-light">
                                <td>
                                    <i class="ri-alarm-warning-line me-2 text-danger"></i>
                                    <strong>Critical & High</strong>
                                </td>
                                <td><span class="badge bg-danger fs-6">${(filtered.data.critical_findings || 0) + (filtered.data.high_findings || 0)}</span></td>
                                <td><strong>${criticalPercent}%</strong></td>
                                <td>
                                    <div class="progress" style="height: 8px; min-width: 120px;">
                                        <div class="progress-bar bg-danger" style="width: ${criticalPercent}%"></div>
                                    </div>
                                </td>
                                <td><small class="text-muted">${filtered.data.critical_findings || 0} Critical, ${filtered.data.high_findings || 0} High</small></td>
                            </tr>
                            <tr class="table-info-light">
                                <td>
                                    <i class="ri-information-line me-2 text-info"></i>
                                    <strong>Medium & Low</strong>
                                </td>
                                <td><span class="badge bg-info fs-6">${(filtered.data.medium_findings || 0) + (filtered.data.low_findings || 0)}</span></td>
                                <td><strong>${mediumLowPercent}%</strong></td>
                                <td>
                                    <div class="progress" style="height: 8px; min-width: 120px;">
                                        <div class="progress-bar bg-info" style="width: ${mediumLowPercent}%"></div>
                                    </div>
                                </td>
                                <td><small class="text-muted">${filtered.data.medium_findings || 0} Medium, ${filtered.data.low_findings || 0} Low</small></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            `;
        } else {
            // Update title for all data view
            if (titleSpan) titleSpan.textContent = '(All Data Overview)';

            // Use the accurate breakdown data from backend
            const originalSummaryData = @json($additionalData['summary'] ?? []);
            const statusBreakdown = originalSummaryData.status_breakdown || {};
            const severityBreakdown = originalSummaryData.severity_breakdown || {};

            const allTotal = parseFloat(originalSummaryData.total_reports || 0);
            const allClosed = parseFloat(statusBreakdown.closed || 0);
            const allOpen = parseFloat(statusBreakdown.open || 0);
            const allCritical = parseFloat(severityBreakdown.critical || 0);
            const allHigh = parseFloat(severityBreakdown.high || 0);
            const allMedium = parseFloat(severityBreakdown.medium || 0);
            const allLow = parseFloat(severityBreakdown.low || 0);

            const allMediumLow = allMedium + allLow;

            const closedPercent = allTotal > 0 ? Math.round((allClosed / allTotal) * 100 * 10) / 10 : 0;
            const openPercent = allTotal > 0 ? Math.round((allOpen / allTotal) * 100 * 10) / 10 : 0;
            const criticalHighPercent = allTotal > 0 ? Math.round(((allCritical + allHigh) / allTotal) * 100 * 10) / 10 : 0;
            const mediumLowPercent = allTotal > 0 ? Math.round((allMediumLow / allTotal) * 100 * 10) / 10 : 0;

            contentDiv.innerHTML = `
                <div class="alert alert-primary border-0 mb-4">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h6 class="alert-heading mb-2">
                                <i class="ri-database-line me-2"></i>All Data Summary
                            </h6>
                            <p class="mb-0">
                                <strong>Complete dataset without any filters applied</strong>
                                <br>
                                <small class="text-muted">Total records from all periods and categories</small>
                            </p>
                        </div>
                        <div class="col-md-4 text-end">
                            <div class="fs-2 fw-bold text-primary">${allTotal}</div>
                            <small class="text-muted">Total Findings</small>
                        </div>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead class="table-primary">
                            <tr>
                                <th>Status</th>
                                <th>Count</th>
                                <th>Percentage</th>
                                <th>Progress</th>
                                <th>Details</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr class="table-success-light">
                                <td>
                                    <div class="d-flex align-items-center">
                                        <i class="ri-checkbox-circle-line text-success fs-5 me-2"></i>
                                        <strong class="text-success">Closed Reports</strong>
                                    </div>
                                </td>
                                <td><span class="badge bg-success fs-6">${allClosed}</span></td>
                                <td><strong class="text-success">${closedPercent}%</strong></td>
                                <td>
                                    <div class="progress" style="height: 8px; min-width: 120px;">
                                        <div class="progress-bar bg-success" style="width: ${closedPercent}%"></div>
                                    </div>
                                </td>
                                <td><small class="text-muted">Completed successfully</small></td>
                            </tr>
                            <tr class="table-warning-light">
                                <td>
                                    <div class="d-flex align-items-center">
                                        <i class="ri-timer-line text-warning fs-5 me-2"></i>
                                        <strong class="text-warning">Open Reports</strong>
                                    </div>
                                </td>
                                <td><span class="badge bg-warning fs-6">${allOpen}</span></td>
                                <td><strong class="text-warning">${openPercent}%</strong></td>
                                <td>
                                    <div class="progress" style="height: 8px; min-width: 120px;">
                                        <div class="progress-bar bg-warning" style="width: ${openPercent}%"></div>
                                    </div>
                                </td>
                                <td><small class="text-muted">In progress or waiting</small></td>
                            </tr>
                            <tr class="table-danger-light">
                                <td>
                                    <div class="d-flex align-items-center">
                                        <i class="ri-alarm-warning-line text-danger fs-5 me-2"></i>
                                        <strong class="text-danger">Critical & High</strong>
                                    </div>
                                </td>
                                <td><span class="badge bg-danger fs-6">${allCritical + allHigh}</span></td>
                                <td><strong class="text-danger">${criticalHighPercent}%</strong></td>
                                <td>
                                    <div class="progress" style="height: 8px; min-width: 120px;">
                                        <div class="progress-bar bg-danger" style="width: ${criticalHighPercent}%"></div>
                                    </div>
                                </td>
                                <td><small class="text-muted">${allCritical} Critical, ${allHigh} High</small></td>
                            </tr>
                            <tr class="table-info-light">
                                <td>
                                    <div class="d-flex align-items-center">
                                        <i class="ri-information-line text-info fs-5 me-2"></i>
                                        <strong class="text-info">Medium & Low</strong>
                                    </div>
                                </td>
                                <td><span class="badge bg-info fs-6">${allMediumLow}</span></td>
                                <td><strong class="text-info">${mediumLowPercent}%</strong></td>
                                <td>
                                    <div class="progress" style="height: 8px; min-width: 120px;">
                                        <div class="progress-bar bg-info" style="width: ${mediumLowPercent}%"></div>
                                    </div>
                                </td>
                                <td><small class="text-muted">${allMedium} Medium, ${allLow} Low</small></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            `;
        }
    }

    // Update summary cards
    function updateSummaryCards(summary) {
        console.log('Updating summary cards with data:', summary);

        // Update Total Reports (first card, no additional class)
        const totalReportsCard = document.querySelector(
            '.metric-value:not(.text-warning):not(.text-danger):not(.text-success)');
        if (totalReportsCard) {
            totalReportsCard.textContent = summary.total_reports || 0;
            console.log('Updated total reports:', summary.total_reports);
        }

        // Update Critical Incidents (second card, text-warning)
        const criticalCard = document.querySelector('.metric-value.text-warning');
        if (criticalCard) {
            criticalCard.textContent = summary.critical_incidents || 0;
            console.log('Updated critical incidents:', summary.critical_incidents);
        }

        // Update Overdue CARs (third card, text-danger)
        const overdueCard = document.querySelector('.metric-value.text-danger');
        if (overdueCard) {
            overdueCard.textContent = summary.overdue_cars || 0;
            console.log('Updated overdue cars:', summary.overdue_cars);
        }

        // Update Completion Rate (fourth card, text-success)
        const completionCard = document.querySelector('.metric-value.text-success');
        if (completionCard && summary.completion_rate !== undefined) {
            completionCard.textContent = summary.completion_rate + '%';
            console.log('Updated completion rate:', summary.completion_rate);
        }
    }

    // Update URL without reload
    function updateUrlParams(filterData) {
        const params = new URLSearchParams();
        params.set('view', 'analytics');

        for (const [key, value] of Object.entries(filterData)) {
            if (value) params.set(key, value);
        }

        window.history.pushState({}, '', window.location.pathname + '?' + params.toString());
    }

    // Show filter messages
    function showFilterMessage(message, type = 'info') {
        document.querySelectorAll('.filter-message').forEach(msg => msg.remove());

        const alertClass = type === 'error' ? 'alert-danger' :
            type === 'success' ? 'alert-success' : 'alert-info';

        const messageDiv = document.createElement('div');
        messageDiv.className = `alert ${alertClass} alert-dismissible fade show filter-message mt-3`;
        messageDiv.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;

        const filtersCard = document.querySelector('#analyticsFilters').parentElement;
        filtersCard.insertAdjacentElement('afterend', messageDiv);

        setTimeout(() => messageDiv.remove(), 5000);
    }

    // Initialize filters on page load
    document.addEventListener('DOMContentLoaded', function() {
        const urlParams = new URLSearchParams(window.location.search);
        const form = document.getElementById('analyticsFilterForm');
        let hasFilters = false;

        // Populate form with URL parameters
        for (const [key, value] of urlParams.entries()) {
            if (key !== 'view') {
                const element = document.getElementById('filter_' + key.replace('_id', ''));
                if (element) {
                    element.value = value;
                    hasFilters = true;
                }
            }
        }

        // Show active filters if any
        if (hasFilters) {
            const formData = new FormData(form);
            updateActiveFiltersDisplay(formData);

            // Auto-expand filters if there are active ones
            const filtersDiv = document.getElementById('analyticsFilters');
            filtersDiv.classList.add('show');
            document.getElementById('filterToggleIcon').className = 'ri-eye-off-line';
            document.getElementById('filterToggleText').textContent = 'Hide Filters';
        }
    });
</script>
