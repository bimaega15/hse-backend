<!-- Statistics Cards -->
<div class="row mb-4" id="statisticsCards">
    <div class="col-xl-3 col-md-6">
        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0">
                        <div class="avatar-sm bg-primary bg-gradient rounded">
                            <i class="ri-eye-line fs-16 text-white"></i>
                        </div>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <p class="text-uppercase fw-medium text-muted mb-0">Total Observations</p>
                        <h4 class="fs-16 fw-semibold mb-0" id="totalObservations">-</h4>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6">
        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0">
                        <div class="avatar-sm bg-secondary bg-gradient rounded">
                            <i class="ri-draft-line fs-16 text-white"></i>
                        </div>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <p class="text-uppercase fw-medium text-muted mb-0">Draft</p>
                        <h4 class="fs-16 fw-semibold mb-0" id="draftObservations">-</h4>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6">
        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0">
                        <div class="avatar-sm bg-warning bg-gradient rounded">
                            <i class="ri-send-plane-line fs-16 text-white"></i>
                        </div>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <p class="text-uppercase fw-medium text-muted mb-0">Submitted</p>
                        <h4 class="fs-16 fw-semibold mb-0" id="submittedObservations">-</h4>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6">
        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0">
                        <div class="avatar-sm bg-success bg-gradient rounded">
                            <i class="ri-check-line fs-16 text-white"></i>
                        </div>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <p class="text-uppercase fw-medium text-muted mb-0">Reviewed</p>
                        <h4 class="fs-16 fw-semibold mb-0" id="reviewedObservations">-</h4>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Quick Actions for specific status views -->
@if ($status)
    <div class="row mb-4">
        <div class="col-12">
            <div class="alert alert-info border-0 bg-info bg-gradient bg-opacity-10 border-start border-info border-4">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0">
                        <i class="ri-information-line fs-18 text-info"></i>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <h6 class="fs-14 mb-1">
                            @if ($status === 'draft')
                                Viewing Draft Observations
                            @elseif($status === 'submitted')
                                Viewing Submitted Observations
                            @elseif($status === 'reviewed')
                                Viewing Reviewed Observations
                            @endif
                        </h6>
                        <p class="mb-0">
                            @if ($status === 'draft')
                                These observations are still being edited and haven't been submitted for review.
                            @elseif($status === 'submitted')
                                These observations have been submitted and are awaiting HSE staff review.
                            @elseif($status === 'reviewed')
                                These observations have been reviewed and completed by HSE staff.
                            @endif
                            <a href="{{ route('admin.observations.index') }}"
                                class="text-decoration-underline fw-medium">View all observations</a>
                        </p>
                    </div>
                    <div class="flex-shrink-0">
                        <a href="{{ route('admin.observations.index') }}?view=analytics" class="btn btn-info btn-sm">
                            <i class="ri-bar-chart-line me-1"></i>View Analytics
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endif

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header border-bottom border-dashed d-flex align-items-center justify-content-between">
                <h4 class="header-title mb-0">
                    Observations List
                    @if ($status)
                        @if ($status === 'draft')
                            <span class="badge bg-secondary ms-2">Draft</span>
                        @elseif($status === 'submitted')
                            <span class="badge bg-warning ms-2">Submitted</span>
                        @elseif($status === 'reviewed')
                            <span class="badge bg-success ms-2">Reviewed</span>
                        @endif
                    @endif
                </h4>
                <div class="d-flex gap-2">
                    @if (!$status)
                        <div class="btn-group" role="group">
                            <button type="button" class="btn btn-outline-secondary btn-sm dropdown-toggle"
                                data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="ri-filter-2-line me-1"></i>Quick Filter
                            </button>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item"
                                        href="{{ route('admin.observations.index') }}?status=draft">
                                        <i class="ri-draft-line me-2 text-secondary"></i>Draft Observations
                                    </a></li>
                                <li><a class="dropdown-item"
                                        href="{{ route('admin.observations.index') }}?status=submitted">
                                        <i class="ri-send-plane-line me-2 text-warning"></i>Submitted
                                    </a></li>
                                <li><a class="dropdown-item"
                                        href="{{ route('admin.observations.index') }}?status=reviewed">
                                        <i class="ri-check-line me-2 text-success"></i>Reviewed
                                    </a></li>
                                <li>
                                    <hr class="dropdown-divider">
                                </li>
                                <li><a class="dropdown-item"
                                        href="{{ route('admin.observations.index') }}?view=analytics">
                                        <i class="ri-bar-chart-line me-2 text-primary"></i>Analytics Dashboard
                                    </a></li>
                            </ul>
                        </div>
                    @endif
                    <button type="button" class="btn btn-outline-primary btn-sm" onclick="showFilters()">
                        <i class="ri-filter-line me-1"></i>Advanced Filters
                    </button>
                    <button type="button" class="btn btn-primary" onclick="createObservation()">
                        <i class="ri-add-line me-1"></i>Add New Observation
                    </button>
                </div>
            </div>

            <!-- Filters Panel -->
            <div class="card-body border-bottom d-none" id="filtersPanel">
                <div class="bg-light rounded p-3">
                    <form id="filtersForm" class="row g-3">
                        <div class="col-md-3">
                            <label for="statusFilter" class="form-label fw-medium">Status</label>
                            <select class="form-select" id="statusFilter" name="status">
                                <option value="">All Status</option>
                                <option value="draft" {{ $status === 'draft' ? 'selected' : '' }}>Draft</option>
                                <option value="submitted" {{ $status === 'submitted' ? 'selected' : '' }}>Submitted
                                </option>
                                <option value="reviewed" {{ $status === 'reviewed' ? 'selected' : '' }}>Reviewed
                                </option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="observationTypeFilter" class="form-label fw-medium">Observation Type</label>
                            <select class="form-select" id="observationTypeFilter" name="observation_type">
                                <option value="">All Types</option>
                                <option value="at_risk_behavior">At Risk Behavior</option>
                                <option value="nearmiss_incident">Near Miss Incident</option>
                                <option value="informal_risk_mgmt">Informal Risk Management</option>
                                <option value="sim_k3">SIM K3</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="startDateFilter" class="form-label fw-medium">Start Date</label>
                            <input type="date" class="form-control" id="startDateFilter" name="start_date">
                        </div>
                        <div class="col-md-3">
                            <label for="endDateFilter" class="form-label fw-medium">End Date</label>
                            <input type="date" class="form-control" id="endDateFilter" name="end_date">
                        </div>
                        <div class="col-12">
                            <div class="d-flex gap-2 align-items-center">
                                <button type="button" class="btn btn-primary" onclick="applyFilters()">
                                    <i class="ri-search-line me-1"></i>Apply Filters
                                </button>
                                <button type="button" class="btn btn-secondary" onclick="clearFilters()">
                                    <i class="ri-refresh-line me-1"></i>Clear Filters
                                </button>
                                <div class="text-muted small ms-3">
                                    <i class="ri-information-line me-1"></i>Use advanced filters to narrow down results
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card-body">
                <!-- Loading State -->
                <div id="loadingState" class="text-center py-4 d-none">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-2 text-muted">Loading observations data...</p>
                </div>

                <!-- Data Table -->
                <div class="table-responsive">
                    <table id="observationsTable" class="table table-striped table-bordered dt-responsive nowrap"
                        style="width:100%">
                        <thead class="table-dark">
                            <tr>
                                <th width="3%">#</th>
                                <th width="15%">Observer</th>
                                <th width="12%">Observation Info</th>
                                <th width="15%">Types Breakdown</th>
                                <th width="8%">Status</th>
                                <th width="25%">Notes</th>
                                <th width="12%">Created At</th>
                                <th width="10%">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Data will be loaded via DataTables -->
                        </tbody>
                    </table>
                </div>

                <!-- Empty State -->
                <div id="emptyState" class="text-center py-5 d-none">
                    <div class="mb-3">
                        <i class="ri-eye-off-line fs-48 text-muted"></i>
                    </div>
                    <h5 class="text-muted">No Observations Found</h5>
                    <p class="text-muted mb-3">
                        @if ($status === 'draft')
                            No draft observations at the moment.
                        @elseif($status === 'submitted')
                            No submitted observations waiting for review.
                        @elseif($status === 'reviewed')
                            No reviewed observations found.
                        @else
                            No observations have been created yet.
                        @endif
                    </p>
                    <button type="button" class="btn btn-primary" onclick="createObservation()">
                        <i class="ri-add-line me-1"></i>Create First Observation
                    </button>
                </div>
            </div>

            <!-- Table Footer with Summary -->
            <div class="card-footer border-top bg-light">
                <div class="row align-items-center">
                    <div class="col-md-6">
                        <div class="text-muted small">
                            <i class="ri-information-line me-1"></i>
                            <span id="tableInfo">Showing observations data</span>
                        </div>
                    </div>
                    <div class="col-md-6 text-end">
                        <div class="d-flex justify-content-end gap-2">
                            @if (!$status)
                                <a href="{{ route('admin.observations.index') }}?status=submitted"
                                    class="btn btn-outline-warning btn-sm">
                                    <i class="ri-send-plane-line me-1"></i>View Submitted
                                </a>
                                <a href="{{ route('admin.observations.index') }}?view=analytics"
                                    class="btn btn-outline-primary btn-sm">
                                    <i class="ri-bar-chart-line me-1"></i>Analytics
                                </a>
                            @else
                                <a href="{{ route('admin.observations.index') }}"
                                    class="btn btn-outline-secondary btn-sm">
                                    <i class="ri-arrow-left-line me-1"></i>View All
                                </a>
                                <a href="{{ route('admin.observations.index') }}?view=analytics"
                                    class="btn btn-outline-primary btn-sm">
                                    <i class="ri-bar-chart-line me-1"></i>Analytics
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Additional Widgets Row (if not status filtered) -->
@if (!$status)
    <div class="row">
        <div class="col-xl-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="ri-speed-line me-2 text-primary"></i>Quick Actions
                    </h5>
                </div>
                <div class="card-body">
                    <div class="list-group list-group-flush">
                        <a href="{{ route('admin.observations.index') }}?status=submitted"
                            class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                            <div>
                                <i class="ri-send-plane-line me-2 text-warning"></i>Review Submitted
                            </div>
                            <span class="badge bg-warning" id="submittedBadge">-</span>
                        </a>
                        <a href="{{ route('admin.observations.index') }}?status=draft"
                            class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                            <div>
                                <i class="ri-draft-line me-2 text-secondary"></i>View Drafts
                            </div>
                            <span class="badge bg-secondary" id="draftBadge">-</span>
                        </a>
                        <a href="{{ route('admin.observations.index') }}?view=analytics"
                            class="list-group-item list-group-item-action">
                            <i class="ri-bar-chart-line me-2 text-primary"></i>View Analytics Dashboard
                        </a>
                        <button type="button" class="list-group-item list-group-item-action"
                            onclick="createObservation()">
                            <i class="ri-add-line me-2 text-success"></i>Create New Observation
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="ri-line-chart-line me-2 text-success"></i>Recent Activity Summary
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-md-3">
                            <div class="p-2">
                                <h4 class="fw-bold text-primary mb-1" id="todayObservations">-</h4>
                                <p class="text-muted mb-0 small">Today</p>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="p-2">
                                <h4 class="fw-bold text-info mb-1" id="weekObservations">-</h4>
                                <p class="text-muted mb-0 small">This Week</p>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="p-2">
                                <h4 class="fw-bold text-warning mb-1" id="monthObservations">-</h4>
                                <p class="text-muted mb-0 small">This Month</p>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="p-2">
                                <h4 class="fw-bold text-success mb-1" id="reviewRate">-</h4>
                                <p class="text-muted mb-0 small">Review Rate</p>
                            </div>
                        </div>
                    </div>
                    <hr class="my-3">
                    <div class="text-center">
                        <p class="text-muted mb-2">Want more detailed insights?</p>
                        <a href="{{ route('admin.observations.index') }}?view=analytics" class="btn btn-primary">
                            <i class="ri-bar-chart-line me-1"></i>View Full Analytics
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endif

<script>
    // Additional JavaScript for observations list specific functionality
    document.addEventListener('DOMContentLoaded', function() {
        // Update quick action badges
        updateQuickActionBadges();

        // Update recent activity summary
        updateRecentActivity();
    });

    function updateQuickActionBadges() {
        // Update badges in quick actions widget
        $('#submittedBadge').text($('#submittedObservations').text());
        $('#draftBadge').text($('#draftObservations').text());
    }

    function updateRecentActivity() {
        // This would typically come from an API call
        // For now, we'll use placeholder logic
        $.ajax({
            url: "{{ route('admin.observations.statistics.data') }}",
            type: 'GET',
            success: function(response) {
                if (response.success) {
                    const stats = response.data;

                    // Update recent activity numbers (you would get these from backend)
                    $('#todayObservations').text(stats.today_observations || '0');
                    $('#weekObservations').text(stats.week_observations || '0');
                    $('#monthObservations').text(stats.month_observations || '0');

                    // Calculate and display review rate
                    const reviewRate = stats.total_observations > 0 ?
                        Math.round((stats.reviewed_observations / stats.total_observations) * 100) :
                        0;
                    $('#reviewRate').text(reviewRate + '%');
                }
            },
            error: function() {
                // Set fallback values
                $('#todayObservations').text('0');
                $('#weekObservations').text('0');
                $('#monthObservations').text('0');
                $('#reviewRate').text('0%');
            }
        });
    }

    // Auto-refresh data every 5 minutes for real-time updates
    setInterval(function() {
        if (observationsTable) {
            observationsTable.ajax.reload(null, false); // Don't reset pagination
        }
        loadStatistics();
        updateQuickActionBadges();
        updateRecentActivity();
    }, 300000); // 5 minutes

    // Table state management
    $(document).ready(function() {
        // Show/hide empty state based on table data
        if (typeof observationsTable !== 'undefined') {
            observationsTable.on('draw', function() {
                const info = observationsTable.page.info();
                if (info.recordsTotal === 0) {
                    $('#emptyState').removeClass('d-none');
                    $('#observationsTable').addClass('d-none');
                } else {
                    $('#emptyState').addClass('d-none');
                    $('#observationsTable').removeClass('d-none');

                    // Update table info
                    $('#tableInfo').text(
                        `Showing ${info.start + 1} to ${info.end} of ${info.recordsTotal} observations`
                        );
                }
            });
        }
    });
</script>
