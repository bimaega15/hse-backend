<!-- Statistics Cards -->
<div class="row mb-4" id="statisticsCards">
    <div class="col-xl-3 col-md-6">
        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0">
                        <div
                            class="avatar-sm d-flex align-items-center justify-content-center bg-primary bg-gradient rounded">
                            <i class="ri-eye-line fs-16 text-white"></i>
                        </div>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <p class="text-uppercase fw-medium text-muted mb-0">Observations</p>
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
                        <div
                            class="avatar-sm d-flex align-items-center justify-content-center bg-secondary bg-gradient rounded">
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
                        <div
                            class="avatar-sm d-flex align-items-center justify-content-center bg-warning bg-gradient rounded">
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
                        <div
                            class="avatar-sm d-flex align-items-center justify-content-center bg-success bg-gradient rounded">
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
                                These observations have been submitted and are awaiting BAIK staff review.
                            @elseif($status === 'reviewed')
                                These observations have been reviewed and completed by BAIK staff.
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
                    <button type="button" class="btn btn-outline-success btn-sm" onclick="exportObservationExcel()">
                        <i class="ri-file-excel-2-line me-1"></i>Export Excel
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
                        <!-- Filters Section -->
                        <div class="col-12">
                            <h6 class="fw-medium text-primary mb-3">
                                <i class="ri-filter-line me-1"></i>Advanced Filters
                            </h6>
                        </div>

                        <!-- Row 1: Observer, Project, Location, Date From -->
                        <div class="col-md-3">
                            <label for="observerFilter" class="form-label fw-medium">Observer</label>
                            <select class="form-select" id="observerFilter" name="observer_id">
                                <option value="">All Observers</option>
                                <!-- Options will be populated via JavaScript -->
                            </select>
                        </div>

                        <div class="col-md-3">
                            <label for="projectFilter" class="form-label fw-medium">Project</label>
                            <select class="form-select" id="projectFilter" name="project_id">
                                <option value="">All Projects</option>
                                <!-- Options will be populated via JavaScript -->
                            </select>
                        </div>

                        <div class="col-md-3">
                            <label for="locationFilter" class="form-label fw-medium">Location</label>
                            <select class="form-select" id="locationFilter" name="location_id">
                                <option value="">All Locations</option>
                                <!-- Options will be populated via JavaScript -->
                            </select>
                        </div>

                        <div class="col-md-3">
                            <label for="dateFromFilter" class="form-label fw-medium">Date From</label>
                            <input type="date" class="form-control" id="dateFromFilter" name="date_from">
                        </div>

                        <!-- Row 2: Date To, Category, Contributing Factor, Action -->
                        <div class="col-md-3">
                            <label for="dateToFilter" class="form-label fw-medium">Date To</label>
                            <input type="date" class="form-control" id="dateToFilter" name="date_to">
                        </div>

                        <div class="col-md-3">
                            <label for="categoryFilter" class="form-label fw-medium">Category</label>
                            <select class="form-select" id="categoryFilter" name="category_id">
                                <option value="">All Categories</option>
                                <!-- Options will be populated via JavaScript -->
                            </select>
                        </div>

                        <div class="col-md-3">
                            <label for="contributingFilter" class="form-label fw-medium">Contributing Factor</label>
                            <select class="form-select" id="contributingFilter" name="contributing_id">
                                <option value="">All Contributing Factors</option>
                                <!-- Options will be populated via JavaScript -->
                            </select>
                        </div>

                        <div class="col-md-3">
                            <label for="actionFilter" class="form-label fw-medium">Action</label>
                            <select class="form-select" id="actionFilter" name="action_id">
                                <option value="">All Actions</option>
                                <!-- Options will be populated via JavaScript -->
                            </select>
                        </div>

                        <!-- Filter Action Buttons -->
                        <div class="col-12">
                            <div class="d-flex gap-2 justify-content-end">
                                <button type="button" class="btn btn-secondary" onclick="clearFilters()">
                                    <i class="ri-refresh-line me-1"></i>Clear All
                                </button>
                                <button type="button" class="btn btn-primary" onclick="applyFilters()">
                                    <i class="ri-filter-line me-1"></i>Apply Filters
                                </button>
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

                <!-- Index Behavior Panel (Outside Table) -->
                <div id="indexBehaviorPanel" class="d-none mb-4">
                    <div class="card border-primary">
                        <div class="card-header bg-primary text-white">
                            <h6 class="mb-0">
                                <i class="ri-bar-chart-2-line me-2"></i>Safety Index Behavior Analysis
                            </h6>
                        </div>
                        <div class="card-body" id="indexBehaviorContent">
                            <!-- Index Behavior calculations will be loaded here -->
                        </div>
                    </div>
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

<!-- Export Excel Modal -->
<div class="modal fade" id="exportModal" tabindex="-1" aria-labelledby="exportModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exportModalLabel">
                    <i class="ri-file-excel-2-line me-2 text-success"></i>Export Observations to Excel
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-info">
                    <i class="ri-information-line me-2"></i>
                    <strong>Export Information:</strong> Use the filters below to narrow down your export data. Select users, projects, and locations to include in your Excel
                    export. Each selected combination will create a separate sheet in the Excel file.
                </div>

                <!-- Search Filters Section -->
                <div class="card border-primary mb-4">
                    <div class="card-header bg-primary bg-gradient text-white">
                        <h6 class="mb-0">
                            <i class="ri-search-line me-2"></i>Export Filters
                        </h6>
                    </div>
                    <div class="card-body">
                        <form id="exportFiltersForm" class="row g-3" onsubmit="return false;">
                            <div class="col-12">
                                <label for="exportSearchFilter" class="form-label fw-medium">Search</label>
                                <div class="input-group">
                                    <input type="text" class="form-control" id="exportSearchFilter" name="export_search"
                                           placeholder="Search by user name, project name, or location name..."
                                           onkeypress="handleExportSearchKeypress(event)">
                                    <button type="button" class="btn btn-primary btn-sm" onclick="applyExportFilters()">
                                        <i class="ri-search-line me-1"></i>Search
                                    </button>
                                    <button type="button" class="btn btn-secondary btn-sm" onclick="clearExportFilters()">
                                        <i class="ri-refresh-line me-1"></i>Clear
                                    </button>
                                </div>
                                <div class="form-text">
                                    <i class="ri-information-line me-1"></i>Search to filter the export data below
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="mb-0">Select Data to Export:</h6>
                    <div class="btn-group btn-group-sm" role="group">
                        <button type="button" class="btn btn-outline-primary" onclick="selectAllExportItems()">
                            <i class="ri-check-double-line me-1"></i>Select All
                        </button>
                        <button type="button" class="btn btn-outline-secondary" onclick="deselectAllExportItems()">
                            <i class="ri-close-line me-1"></i>Deselect All
                        </button>
                    </div>
                </div>

                <div>
                    <div id="exportModalContent">
                        <!-- Dynamic content will be loaded here -->
                        <div class="text-center py-4">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                            <p class="mt-2 text-muted">Loading export data...</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="ri-close-line me-1"></i>Cancel
                </button>
                <button type="button" class="btn btn-success" onclick="proceedWithExport()">
                    <i class="ri-download-line me-1"></i>Export Selected
                </button>
            </div>
        </div>
    </div>
</div>

@push('jsSection')
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

        // Export Excel functionality for observations
        function exportObservationExcel() {
            // Show export modal first
            showExportModal();
        }

        function showExportModal() {
            // Copy current search value to export modal
            $('#exportSearchFilter').val($('#searchFilter').val());

            // Load grouped data and show modal
            loadExportData();
            $('#exportModal').modal('show');
        }

        function applyExportFilters() {
            // Reload export data with current filters
            loadExportData();
        }

        function clearExportFilters() {
            // Clear all export filter forms
            $('#exportFiltersForm')[0].reset();
            // Reload export data
            loadExportData();
        }

        function handleExportSearchKeypress(event) {
            // Check if Enter key was pressed
            if (event.key === 'Enter' || event.keyCode === 13) {
                // Prevent default form submission
                event.preventDefault();
                // Call the search function
                applyExportFilters();
                return false;
            }
        }

        function loadExportData() {
            // Show loading state
            $('#exportModalContent').html(`
                <div class="text-center py-4">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-2 text-muted">Loading export data...</p>
                </div>
            `);

            // Get export filter values
            const exportFilters = {
                search: $('#exportSearchFilter').val()
            };

            $.ajax({
                url: "{{ route('admin.observations.export.grouped-data') }}",
                type: 'GET',
                data: exportFilters,
                success: function(response) {
                    if (response.success) {
                        buildExportModal(response.data);
                    } else {
                        showAlert('error', 'Error', 'Failed to load export data');
                    }
                },
                error: function() {
                    showAlert('error', 'Error', 'Failed to load export data');
                }
            });
        }

        function buildExportModal(groupedData) {
            let modalContent = '';

            groupedData.forEach((user, index) => {
                const collapseId = `collapse_user_${user.user_id}`;
                modalContent += `
                    <div class="card mb-1">
                        <div class="card-header">
                            <div class="d-flex align-items-center justify-content-between">
                                <div class="form-check d-flex align-items-center">
                                    <input class="form-check-input user-checkbox me-2" type="checkbox"
                                           id="user_${user.user_id}" checked onchange="toggleUserSelection(${user.user_id})">
                                    <label class="form-check-label fw-bold" for="user_${user.user_id}">
                                        ${user.user_name} <span class="text-muted fw-normal">(${user.user_role || 'Employee'})</span>
                                    </label>
                                </div>
                                <button class="btn btn-sm btn-outline-secondary" type="button"
                                        data-bs-toggle="collapse" data-bs-target="#${collapseId}"
                                        aria-expanded="true" aria-controls="${collapseId}">
                                    <i class="ri-arrow-down-s-line"></i>
                                </button>
                            </div>
                        </div>
                        <div class="collapse show" id="${collapseId}">
                            <div class="card-body">
                                <div class="row">
                `;

                user.projects.forEach(project => {
                    modalContent += `
                        <div class="col-md-6 mb-3">
                            <div class="border rounded p-3">
                                <div class="form-check">
                                    <input class="form-check-input project-checkbox" type="checkbox"
                                           id="project_${user.user_id}_${project.project_id}"
                                           data-user-id="${user.user_id}"
                                           data-project-id="${project.project_id}"
                                           onchange="toggleProjectSelection(${user.user_id}, ${project.project_id})" checked>
                                    <label class="form-check-label fw-medium" for="project_${user.user_id}_${project.project_id}">
                                        ${project.project_name}
                                    </label>
                                </div>
                                <div class="ms-4 mt-2">
                    `;

                    project.locations.forEach(location => {
                        modalContent += `
                            <div class="form-check">
                                <input class="form-check-input location-checkbox" type="checkbox"
                                       id="location_${user.user_id}_${project.project_id}_${location.location_id}"
                                       data-user-id="${user.user_id}"
                                       data-project-id="${project.project_id}"
                                       data-location-id="${location.location_id}"
                                       value="${user.user_id}_${project.project_id}_${location.location_id}"
                                       onchange="toggleLocationSelection(${user.user_id}, ${project.project_id})" checked>
                                <label class="form-check-label text-muted" for="location_${user.user_id}_${project.project_id}_${location.location_id}">
                                    ${location.location_name} <span class="badge bg-light text-dark">${location.count}</span>
                                </label>
                            </div>
                        `;
                    });

                    modalContent += `
                                </div>
                            </div>
                        </div>
                    `;
                });

                modalContent += `
                                </div>
                            </div>
                        </div>
                    </div>
                `;
            });

            $('#exportModalContent').html(modalContent);

            // Add event listeners for collapse buttons
            $('.collapse').on('show.bs.collapse', function() {
                const button = $(`button[data-bs-target="#${this.id}"]`);
                button.find('i').removeClass('ri-arrow-down-s-line').addClass('ri-arrow-up-s-line');
            });

            $('.collapse').on('hide.bs.collapse', function() {
                const button = $(`button[data-bs-target="#${this.id}"]`);
                button.find('i').removeClass('ri-arrow-up-s-line').addClass('ri-arrow-down-s-line');
            });
        }

        function toggleUserSelection(userId) {
            const userCheckbox = $(`#user_${userId}`);
            const isChecked = userCheckbox.is(':checked');

            // Toggle all project and location checkboxes for this user
            $(`.project-checkbox[data-user-id="${userId}"]`).prop('checked', isChecked);
            $(`.location-checkbox[data-user-id="${userId}"]`).prop('checked', isChecked);
        }

        function toggleProjectSelection(userId, projectId) {
            const projectCheckbox = $(`#project_${userId}_${projectId}`);
            const isChecked = projectCheckbox.is(':checked');

            // Toggle all location checkboxes for this project
            $(`.location-checkbox[data-user-id="${userId}"][data-project-id="${projectId}"]`).prop('checked', isChecked);

            // Update user checkbox state based on project selections
            updateUserCheckboxState(userId);
        }

        function toggleLocationSelection(userId, projectId) {
            // Update project checkbox state based on location selections
            updateProjectCheckboxState(userId, projectId);

            // Update user checkbox state based on project selections
            updateUserCheckboxState(userId);
        }

        function updateProjectCheckboxState(userId, projectId) {
            const projectCheckbox = $(`#project_${userId}_${projectId}`);
            const locationCheckboxes = $(`.location-checkbox[data-user-id="${userId}"][data-project-id="${projectId}"]`);
            const checkedLocations = $(
                `.location-checkbox[data-user-id="${userId}"][data-project-id="${projectId}"]:checked`);

            // If all locations are checked, check project checkbox
            if (locationCheckboxes.length === checkedLocations.length && locationCheckboxes.length > 0) {
                projectCheckbox.prop('checked', true);
                projectCheckbox.prop('indeterminate', false);
            }
            // If no locations are checked, uncheck project checkbox
            else if (checkedLocations.length === 0) {
                projectCheckbox.prop('checked', false);
                projectCheckbox.prop('indeterminate', false);
            }
            // If some locations are checked, set indeterminate state
            else {
                projectCheckbox.prop('checked', false);
                projectCheckbox.prop('indeterminate', true);
            }
        }

        function updateUserCheckboxState(userId) {
            const userCheckbox = $(`#user_${userId}`);
            const projectCheckboxes = $(`.project-checkbox[data-user-id="${userId}"]`);
            const checkedProjects = $(`.project-checkbox[data-user-id="${userId}"]:checked`);

            // If all projects are checked, check user checkbox
            if (projectCheckboxes.length === checkedProjects.length && projectCheckboxes.length > 0) {
                userCheckbox.prop('checked', true);
                userCheckbox.prop('indeterminate', false);
            }
            // If no projects are checked, uncheck user checkbox
            else if (checkedProjects.length === 0) {
                userCheckbox.prop('checked', false);
                userCheckbox.prop('indeterminate', false);
            }
            // If some projects are checked, set indeterminate state
            else {
                userCheckbox.prop('checked', false);
                userCheckbox.prop('indeterminate', true);
            }
        }

        function selectAllExportItems() {
            $('.user-checkbox, .project-checkbox, .location-checkbox').prop('checked', true);
            $('.user-checkbox, .project-checkbox').prop('indeterminate', false);
        }

        function deselectAllExportItems() {
            $('.user-checkbox, .project-checkbox, .location-checkbox').prop('checked', false);
            $('.user-checkbox, .project-checkbox').prop('indeterminate', false);
        }

        function proceedWithExport() {
            // Get selected items
            const selectedItems = [];
            $('.location-checkbox:checked').each(function() {
                selectedItems.push($(this).val());
            });

            if (selectedItems.length === 0) {
                showAlert('warning', 'No Selection', 'Please select at least one location to export');
                return;
            }

            // Close modal
            $('#exportModal').modal('hide');

            // Get the button element
            const exportButton = $('button[onclick="exportObservationExcel()"]');

            const filters = {
                search: $('#exportSearchFilter').val() || $('#searchFilter').val(),
                selected_items: selectedItems
            };

            // Add URL status filter if exists
            const urlParams = new URLSearchParams(window.location.search);
            const urlStatus = urlParams.get('status');
            if (urlStatus && !filters.status) {
                filters.url_status = urlStatus;
            }

            // Build query string
            const params = new URLSearchParams();
            Object.keys(filters).forEach(key => {
                if (filters[key]) {
                    if (Array.isArray(filters[key])) {
                        filters[key].forEach(item => params.append(key + '[]', item));
                    } else {
                        params.append(key, filters[key]);
                    }
                }
            });

            // Show loading indicator
            const originalText = exportButton.html();
            exportButton.html('<i class="ri-loader-2-line spinner-border spinner-border-sm me-1"></i>Exporting...');
            exportButton.prop('disabled', true);

            // Create temporary link and trigger download
            const exportUrl = `{{ route('admin.observations.export.excel') }}?${params.toString()}`;

            // Use fetch to handle the download properly
            fetch(exportUrl)
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Export failed');
                    }
                    return response.blob();
                })
                .then(blob => {
                    // Create download link
                    const url = window.URL.createObjectURL(blob);
                    const a = document.createElement('a');
                    a.href = url;
                    a.download =
                        `observations_export_${new Date().toISOString().slice(0,19).replace(/[:.]/g, '-')}.xlsx`;
                    document.body.appendChild(a);
                    a.click();
                    window.URL.revokeObjectURL(url);
                    document.body.removeChild(a);

                    // Show success message
                    showAlert('success', 'Success!', 'Observations XLSX file has been exported successfully');
                })
                .catch(error => {
                    console.error('Export error:', error);
                    showAlert('error', 'Export Failed', 'Failed to export observations XLSX file. Please try again.');
                })
                .finally(() => {
                    // Restore button state
                    exportButton.html(originalText);
                    exportButton.prop('disabled', false);
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
@endpush
