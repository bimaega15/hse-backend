<!-- Statistics Cards -->
<div class="row mb-4" id="statisticsCards">
    <div class="col-xl-3 col-md-6">
        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0">
                        <div
                            class="avatar-sm d-flex align-items-center justify-content-center bg-primary bg-gradient rounded">
                            <i class="ri-file-text-line fs-16 text-white"></i>
                        </div>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <p class="text-uppercase fw-medium text-muted mb-0">Total Reports</p>
                        <h4 class="fs-16 fw-semibold mb-0" id="totalReports">-</h4>
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
                            <i class="ri-time-line fs-16 text-white"></i>
                        </div>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <p class="text-uppercase fw-medium text-muted mb-0">Pending</p>
                        <h4 class="fs-16 fw-semibold mb-0" id="pendingReports">-</h4>
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
                            class="avatar-sm d-flex align-items-center justify-content-center bg-info bg-gradient rounded">
                            <i class="ri-refresh-line fs-16 text-white"></i>
                        </div>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <p class="text-uppercase fw-medium text-muted mb-0">In Progress</p>
                        <h4 class="fs-16 fw-semibold mb-0" id="inProgressReports">-</h4>
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
                        <p class="text-uppercase fw-medium text-muted mb-0">Completed</p>
                        <h4 class="fs-16 fw-semibold mb-0" id="completedReports">-</h4>
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
                            @if ($status === 'waiting')
                                Viewing Pending Reports
                            @elseif($status === 'in-progress')
                                Viewing Reports In Progress
                            @elseif($status === 'done')
                                Viewing Completed Reports
                            @endif
                        </h6>
                        <p class="mb-0">
                            @if ($status === 'waiting')
                                These reports are waiting for BAIK staff assignment and processing.
                            @elseif($status === 'in-progress')
                                These reports are currently being processed by BAIK staff.
                            @elseif($status === 'done')
                                These reports have been completed and closed.
                            @endif
                            <a href="{{ route('admin.reports.index') }}"
                                class="text-decoration-underline fw-medium">View all reports</a>
                        </p>
                    </div>
                    <div class="flex-shrink-0">
                        <a href="{{ route('admin.reports.index') }}?view=analytics" class="btn btn-info btn-sm">
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
                    Reports List
                    @if ($status)
                        @if ($status === 'waiting')
                            <span class="badge bg-warning ms-2">Pending</span>
                        @elseif($status === 'in-progress')
                            <span class="badge bg-info ms-2">In Progress</span>
                        @elseif($status === 'done')
                            <span class="badge bg-success ms-2">Completed</span>
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
                                <li><a class="dropdown-item" href="{{ route('admin.reports.index') }}?status=waiting">
                                        <i class="ri-time-line me-2 text-warning"></i>Pending Reports
                                    </a></li>
                                <li><a class="dropdown-item"
                                        href="{{ route('admin.reports.index') }}?status=in-progress">
                                        <i class="ri-refresh-line me-2 text-info"></i>In Progress
                                    </a></li>
                                <li><a class="dropdown-item" href="{{ route('admin.reports.index') }}?status=done">
                                        <i class="ri-check-line me-2 text-success"></i>Completed
                                    </a></li>
                                <li>
                                    <hr class="dropdown-divider">
                                </li>
                                <li><a class="dropdown-item" href="{{ route('admin.reports.index') }}?view=analytics">
                                        <i class="ri-bar-chart-line me-2 text-primary"></i>Analytics Dashboard
                                    </a></li>
                            </ul>
                        </div>
                    @endif
                    <button type="button" class="btn btn-outline-primary btn-sm" onclick="showFilters()">
                        <i class="ri-filter-line me-1"></i>Advanced Filters
                    </button>
                    <button type="button" class="btn btn-outline-success btn-sm" onclick="exportExcel()">
                        <i class="ri-file-excel-2-line me-1"></i>Export Excel
                    </button>
                    <button type="button" class="btn btn-primary" onclick="createReport()">
                        <i class="ri-add-line me-1"></i>Add New Report
                    </button>
                </div>
            </div>

            <!-- Filters Panel -->
            <div class="card-body border-bottom d-none" id="filtersPanel">
                <div class="bg-light rounded p-3">
                    <form id="filtersForm" class="row g-3">
                        <!-- Row 1: Basic Filters -->
                        <div class="col-md-3">
                            <label for="statusFilter" class="form-label fw-medium">Status</label>
                            <select class="form-select filter-select2" id="statusFilter" name="status">
                                <option value="">All Status</option>
                                <option value="waiting" {{ $status === 'waiting' ? 'selected' : '' }}>Waiting</option>
                                <option value="in-progress" {{ $status === 'in-progress' ? 'selected' : '' }}>In
                                    Progress</option>
                                <option value="done" {{ $status === 'done' ? 'selected' : '' }}>Completed</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="severityFilter" class="form-label fw-medium">Severity</label>
                            <select class="form-select filter-select2" id="severityFilter" name="severity">
                                <option value="">All Severity</option>
                                <option value="low">Low</option>
                                <option value="medium">Medium</option>
                                <option value="high">High</option>
                                <option value="critical">Critical</option>
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

                        <!-- Row 2: New Filters -->
                        <div class="col-md-3">
                            <label for="projectFilter" class="form-label fw-medium">Project</label>
                            <select class="form-select filter-select2" id="projectFilter" name="project_id">
                                <option value="">All Projects</option>
                                @if(isset($filterOptions['projects']))
                                    @foreach($filterOptions['projects'] as $project)
                                        <option value="{{ $project->id }}">{{ $project->project_name }}</option>
                                    @endforeach
                                @endif
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="categoryFilter" class="form-label fw-medium">Type of Report</label>
                            <select class="form-select filter-select2" id="categoryFilter" name="category_id">
                                <option value="">All Types</option>
                                @if(isset($filterOptions['categories']))
                                    @foreach($filterOptions['categories'] as $category)
                                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                                    @endforeach
                                @endif
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="contributingFilter" class="form-label fw-medium">Contributing Factor</label>
                            <select class="form-select filter-select2" id="contributingFilter" name="contributing_id">
                                <option value="">All Contributing Factors</option>
                                @if(isset($filterOptions['contributing_factors']))
                                    @foreach($filterOptions['contributing_factors'] as $contributing)
                                        <option value="{{ $contributing->id }}">{{ $contributing->name }}</option>
                                    @endforeach
                                @endif
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="actionFilter" class="form-label fw-medium">Action</label>
                            <select class="form-select filter-select2" id="actionFilter" name="action_id">
                                <option value="">All Actions</option>
                                @if(isset($filterOptions['actions']))
                                    @foreach($filterOptions['actions'] as $action)
                                        <option value="{{ $action->id }}" data-contributing="{{ $action->contributing_id }}">{{ $action->name }}</option>
                                    @endforeach
                                @endif
                            </select>
                        </div>

                        <!-- Action Buttons -->
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
                    <p class="mt-2 text-muted">Loading reports data...</p>
                </div>

                <!-- Data Table -->
                <div class="table-responsive">
                    <table id="reportsTable" class="table table-striped table-bordered dt-responsive nowrap"
                        style="width:100%">
                        <thead class="table-dark">
                            <tr>
                                <th width="3%" class="dtr-control">&nbsp;</th>
                                <th width="10%">Employee</th>
                                <th width="10%">BAIK Staff</th>
                                <th width="10%">Report Info</th>
                                <th width="8%">Project</th>
                                <th width="10%">Type of Report</th>
                                <th width="10%">Contributing Factor</th>
                                <th width="10%">Action</th>
                                <th width="6%">Severity</th>
                                <th width="6%">Status</th>
                                <th width="8%">CAR Progress</th>
                                <th width="8%">Created At</th>
                                <th width="2%">Actions</th>
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
                        <i class="ri-file-list-line fs-48 text-muted"></i>
                    </div>
                    <h5 class="text-muted">No Reports Found</h5>
                    <p class="text-muted mb-3">
                        @if ($status === 'waiting')
                            No pending reports at the moment.
                        @elseif($status === 'in-progress')
                            No reports currently in progress.
                        @elseif($status === 'done')
                            No completed reports found.
                        @else
                            No reports have been created yet.
                        @endif
                    </p>
                    <button type="button" class="btn btn-primary" onclick="createReport()">
                        <i class="ri-add-line me-1"></i>Create First Report
                    </button>
                </div>
            </div>

            <!-- Table Footer with Summary -->
            <div class="card-footer border-top bg-light">
                <div class="row align-items-center">
                    <div class="col-md-6">
                        <div class="text-muted small">
                            <i class="ri-information-line me-1"></i>
                            <span id="tableInfo">Showing reports data</span>
                        </div>
                    </div>
                    <div class="col-md-6 text-end">
                        <div class="d-flex justify-content-end gap-2">
                            @if (!$status)
                                <a href="{{ route('admin.reports.index') }}?status=waiting"
                                    class="btn btn-outline-warning btn-sm">
                                    <i class="ri-time-line me-1"></i>View Pending
                                </a>
                                <a href="{{ route('admin.reports.index') }}?view=analytics"
                                    class="btn btn-outline-primary btn-sm">
                                    <i class="ri-bar-chart-line me-1"></i>Analytics
                                </a>
                            @else
                                <a href="{{ route('admin.reports.index') }}"
                                    class="btn btn-outline-secondary btn-sm">
                                    <i class="ri-arrow-left-line me-1"></i>View All
                                </a>
                                <a href="{{ route('admin.reports.index') }}?view=analytics"
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
                        <a href="{{ route('admin.reports.index') }}?status=waiting"
                            class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                            <div>
                                <i class="ri-time-line me-2 text-warning"></i>Review Pending Reports
                            </div>
                            <span class="badge bg-warning" id="pendingBadge">-</span>
                        </a>
                        <a href="{{ route('admin.reports.index') }}?status=in-progress"
                            class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                            <div>
                                <i class="ri-refresh-line me-2 text-info"></i>Track In Progress
                            </div>
                            <span class="badge bg-info" id="progressBadge">-</span>
                        </a>
                        <a href="{{ route('admin.reports.index') }}?view=analytics"
                            class="list-group-item list-group-item-action">
                            <i class="ri-bar-chart-line me-2 text-primary"></i>View Analytics Dashboard
                        </a>
                        <button type="button" class="list-group-item list-group-item-action"
                            onclick="createReport()">
                            <i class="ri-add-line me-2 text-success"></i>Create New Report
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
                                <h4 class="fw-bold text-primary mb-1" id="todayReports">-</h4>
                                <p class="text-muted mb-0 small">Today</p>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="p-2">
                                <h4 class="fw-bold text-info mb-1" id="weekReports">-</h4>
                                <p class="text-muted mb-0 small">This Week</p>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="p-2">
                                <h4 class="fw-bold text-warning mb-1" id="monthReports">-</h4>
                                <p class="text-muted mb-0 small">This Month</p>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="p-2">
                                <h4 class="fw-bold text-success mb-1" id="completionRate">-</h4>
                                <p class="text-muted mb-0 small">Completion Rate</p>
                            </div>
                        </div>
                    </div>
                    <hr class="my-3">
                    <div class="text-center">
                        <p class="text-muted mb-2">Want more detailed insights?</p>
                        <a href="{{ route('admin.reports.index') }}?view=analytics" class="btn btn-primary">
                            <i class="ri-bar-chart-line me-1"></i>View Full Analytics
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endif

@push('jsSection')
    <script>
        // Additional JavaScript for reports list specific functionality
        document.addEventListener('DOMContentLoaded', function() {
            // Update quick action badges
            updateQuickActionBadges();

            // Update recent activity summary
            updateRecentActivity();
        });

        function updateQuickActionBadges() {
            // Update badges in quick actions widget
            $('#pendingBadge').text($('#pendingReports').text());
            $('#progressBadge').text($('#inProgressReports').text());
        }

        function updateRecentActivity() {
            // This would typically come from an API call
            // For now, we'll use placeholder logic
            $.ajax({
                url: "{{ route('admin.reports.statistics.data') }}",
                type: 'GET',
                success: function(response) {
                    if (response.success) {
                        const stats = response.data;

                        // Update recent activity numbers (you would get these from backend)
                        $('#todayReports').text(stats.today_reports || '0');
                        $('#weekReports').text(stats.week_reports || '0');
                        $('#monthReports').text(stats.month_reports || '0');

                        // Calculate and display completion rate
                        const completionRate = stats.total_reports > 0 ?
                            Math.round((stats.completed_reports / stats.total_reports) * 100) :
                            0;
                        $('#completionRate').text(completionRate + '%');
                    }
                },
                error: function() {
                    // Set fallback values
                    $('#todayReports').text('0');
                    $('#weekReports').text('0');
                    $('#monthReports').text('0');
                    $('#completionRate').text('0%');
                }
            });
        }

        // Auto-refresh data every 5 minutes for real-time updates
        setInterval(function() {
            if (reportsTable) {
                reportsTable.ajax.reload(null, false); // Don't reset pagination
            }
            loadStatistics();
            updateQuickActionBadges();
            updateRecentActivity();
        }, 300000); // 5 minutes

        // Table state management
        $(document).ready(function() {
            // Show/hide empty state based on table data
            if (typeof reportsTable !== 'undefined') {
                reportsTable.on('draw', function() {
                    const info = reportsTable.page.info();
                    if (info.recordsTotal === 0) {
                        $('#emptyState').removeClass('d-none');
                        $('#reportsTable').addClass('d-none');
                    } else {
                        $('#emptyState').addClass('d-none');
                        $('#reportsTable').removeClass('d-none');

                        // Update table info
                        $('#tableInfo').text(
                            `Showing ${info.start + 1} to ${info.end} of ${info.recordsTotal} reports`);
                    }
                });
            }
        });

        // Export Excel functionality
        function exportExcel() {
            // Get the button element
            const exportButton = $('button[onclick="exportExcel()"]');

            const filters = {
                status: $('#statusFilter').val(),
                severity: $('#severityFilter').val(),
                start_date: $('#startDateFilter').val(),
                end_date: $('#endDateFilter').val(),
                // NEW: Additional filter parameters
                project_id: $('#projectFilter').val(),
                category_id: $('#categoryFilter').val(),
                contributing_id: $('#contributingFilter').val(),
                action_id: $('#actionFilter').val()
            };

            // Add URL status filter if exists
            const urlParams = new URLSearchParams(window.location.search);
            const urlStatus = urlParams.get('status');
            if (urlStatus && !filters.status) {
                filters.status = urlStatus;
            }

            // Build query string
            const params = new URLSearchParams();
            Object.keys(filters).forEach(key => {
                if (filters[key]) {
                    params.append(key, filters[key]);
                }
            });

            // Show loading indicator
            const originalText = exportButton.html();
            exportButton.html('<i class="ri-loader-2-line spinner-border spinner-border-sm me-1"></i>Exporting...');
            exportButton.prop('disabled', true);

            // Create temporary link and trigger download
            const exportUrl = `{{ route('admin.reports.export.excel') }}?${params.toString()}`;

            // Use fetch to handle the download properly
            fetch(exportUrl)
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Export failed');
                    }
                    return response.blob();
                })
                .then(blob => {
                    // Create download link with proper MIME type for Excel
                    const url = window.URL.createObjectURL(new Blob([blob], {
                        type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
                    }));
                    const a = document.createElement('a');
                    a.href = url;
                    a.download = `reports_export_${new Date().toISOString().slice(0,19).replace(/[:.]/g, '-')}.xlsx`;
                    document.body.appendChild(a);
                    a.click();
                    window.URL.revokeObjectURL(url);
                    document.body.removeChild(a);

                    // Show success message
                    showAlert('success', 'Success!', 'Excel file has been exported successfully');
                })
                .catch(error => {
                    console.error('Export error:', error);
                    showAlert('error', 'Export Failed', 'Failed to export Excel file. Please try again.');
                })
                .finally(() => {
                    // Restore button state
                    exportButton.html(originalText);
                    exportButton.prop('disabled', false);
                });
        }

        // Legacy export functionality (keeping for future enhancement)
        function exportReports(format) {
            const filters = {
                status: $('#statusFilter').val(),
                severity: $('#severityFilter').val(),
                start_date: $('#startDateFilter').val(),
                end_date: $('#endDateFilter').val()
            };

            // Build query string
            const params = new URLSearchParams();
            Object.keys(filters).forEach(key => {
                if (filters[key]) {
                    params.append(key, filters[key]);
                }
            });
            params.append('format', format);

            // Open export URL
            window.open(`/admin/reports/export?${params.toString()}`, '_blank');
        }

        // Print functionality
        function printReports() {
            window.print();
        }

        // Bulk operations (future enhancement)
        function bulkUpdateStatus(status) {
            const selectedIds = [];
            $('.report-checkbox:checked').each(function() {
                selectedIds.push($(this).val());
            });

            if (selectedIds.length === 0) {
                showAlert('warning', 'No Selection', 'Please select at least one report');
                return;
            }

            // Implement bulk update logic
            console.log('Bulk update status:', status, selectedIds);
        }

        // Advanced search functionality
        function performAdvancedSearch() {
            const searchTerm = $('#advancedSearchInput').val();
            if (searchTerm.length < 3) {
                showAlert('info', 'Search Term Too Short', 'Please enter at least 3 characters');
                return;
            }

            // Apply search to DataTable
            reportsTable.search(searchTerm).draw();
        }

        // Reset all filters and search
        function resetAllFilters() {
            $('#filtersForm')[0].reset();
            if (reportsTable) {
                reportsTable.search('').columns().search('').draw();
            }

            // Clear URL parameters
            window.history.pushState({}, '', '{{ route('admin.reports.index') }}');

            // Reload page to reset everything
            window.location.reload();
        }
    </script>
@endpush
