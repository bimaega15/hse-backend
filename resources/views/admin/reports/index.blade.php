@extends('admin.layouts')

@section('title', 'Reports Management')

@section('content')
    <div class="page-content">
        <!-- Page Title -->
        <div class="page-title-head d-flex align-items-center gap-2">
            <div class="flex-grow-1">
                <h4 class="fs-18 fw-bold mb-0">Reports Management</h4>
            </div>
            <div class="text-end">
                <ol class="breadcrumb m-0 py-0 fs-13">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="javascript: void(0);">HSE Management</a></li>
                    <li class="breadcrumb-item active">Reports</li>
                </ol>
            </div>
        </div>

        <div class="page-container">
            <!-- Statistics Cards -->
            <div class="row mb-4" id="statisticsCards">
                <div class="col-xl-3 col-md-6">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="flex-shrink-0">
                                    <div class="avatar-sm bg-primary bg-gradient rounded">
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
                                    <div class="avatar-sm bg-warning bg-gradient rounded">
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
                                    <div class="avatar-sm bg-info bg-gradient rounded">
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
                                    <div class="avatar-sm bg-success bg-gradient rounded">
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

            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div
                            class="card-header border-bottom border-dashed d-flex align-items-center justify-content-between">
                            <h4 class="header-title mb-0">Reports List</h4>
                            <div class="d-flex gap-2">
                                <button type="button" class="btn btn-outline-primary btn-sm" onclick="showFilters()">
                                    <i class="ri-filter-line me-1"></i>Filters
                                </button>
                                <button type="button" class="btn btn-primary" onclick="createReport()">
                                    <i class="ri-add-line me-1"></i>Add New Report
                                </button>
                            </div>
                        </div>

                        <!-- Filters Panel -->
                        <div class="card-body border-bottom d-none" id="filtersPanel">
                            <form id="filtersForm" class="row g-3">
                                <div class="col-md-3">
                                    <label for="statusFilter" class="form-label">Status</label>
                                    <select class="form-select" id="statusFilter" name="status">
                                        <option value="">All Status</option>
                                        <option value="waiting">Waiting</option>
                                        <option value="in-progress">In Progress</option>
                                        <option value="done">Completed</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label for="severityFilter" class="form-label">Severity</label>
                                    <select class="form-select" id="severityFilter" name="severity">
                                        <option value="">All Severity</option>
                                        <option value="low">Low</option>
                                        <option value="medium">Medium</option>
                                        <option value="high">High</option>
                                        <option value="critical">Critical</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label for="startDateFilter" class="form-label">Start Date</label>
                                    <input type="date" class="form-control" id="startDateFilter" name="start_date">
                                </div>
                                <div class="col-md-3">
                                    <label for="endDateFilter" class="form-label">End Date</label>
                                    <input type="date" class="form-control" id="endDateFilter" name="end_date">
                                </div>
                                <div class="col-12">
                                    <div class="d-flex gap-2">
                                        <button type="button" class="btn btn-primary" onclick="applyFilters()">
                                            <i class="ri-search-line me-1"></i>Apply Filters
                                        </button>
                                        <button type="button" class="btn btn-secondary" onclick="clearFilters()">
                                            <i class="ri-refresh-line me-1"></i>Clear Filters
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>

                        <div class="card-body">
                            <div class="table-responsive">
                                <table id="reportsTable" class="table table-striped table-bordered dt-responsive nowrap"
                                    style="width:100%">
                                    <thead class="table-dark">
                                        <tr>
                                            <th width="3%">#</th>
                                            <th width="12%">Employee</th>
                                            <th width="12%">HSE Staff</th>
                                            <th width="12%">Report Info</th>
                                            <th width="20%">Description</th>
                                            <th width="8%">Severity</th>
                                            <th width="8%">Status</th>
                                            <th width="10%">CAR Progress</th>
                                            <th width="12%">Dates</th>
                                            <th width="3%">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <!-- Data will be loaded via DataTables -->
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Create/Edit Report Modal -->
    <div class="modal fade" id="reportModal" tabindex="-1" aria-labelledby="reportModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <form id="reportForm" enctype="multipart/form-data">
                    <div class="modal-header">
                        <h5 class="modal-title" id="reportModalLabel">Add New Report</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" id="reportId" name="id">

                        <div class="row">
                            <!-- Employee Information -->
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="employeeId" class="form-label">Employee <span
                                            class="text-danger">*</span></label>
                                    <select class="form-select" id="employeeId" name="employee_id" required>
                                        <option value="">Select Employee</option>
                                    </select>
                                    <div class="invalid-feedback" id="employeeIdError"></div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="hseStaffId" class="form-label">HSE Staff</label>
                                    <select class="form-select" id="hseStaffId" name="hse_staff_id">
                                        <option value="">Select HSE Staff</option>
                                    </select>
                                    <div class="invalid-feedback" id="hseStaffIdError"></div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <!-- Report Classification -->
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="categoryId" class="form-label">Category <span
                                            class="text-danger">*</span></label>
                                    <select class="form-select" id="categoryId" name="category_id" required>
                                        <option value="">Select Category</option>
                                    </select>
                                    <div class="invalid-feedback" id="categoryIdError"></div>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="contributingId" class="form-label">Contributing Factor <span
                                            class="text-danger">*</span></label>
                                    <select class="form-select" id="contributingId" name="contributing_id" required>
                                        <option value="">Select Contributing Factor</option>
                                    </select>
                                    <div class="invalid-feedback" id="contributingIdError"></div>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="actionId" class="form-label">Action <span
                                            class="text-danger">*</span></label>
                                    <select class="form-select" id="actionId" name="action_id" required>
                                        <option value="">Select Action</option>
                                    </select>
                                    <div class="invalid-feedback" id="actionIdError"></div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="severityRating" class="form-label">Severity Rating <span
                                            class="text-danger">*</span></label>
                                    <select class="form-select" id="severityRating" name="severity_rating" required>
                                        <option value="">Select Severity</option>
                                        <option value="low">Low</option>
                                        <option value="medium">Medium</option>
                                        <option value="high">High</option>
                                        <option value="critical">Critical</option>
                                    </select>
                                    <div class="invalid-feedback" id="severityRatingError"></div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="location" class="form-label">Location <span
                                            class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="location" name="location" required
                                        maxlength="255" placeholder="Enter incident location">
                                    <div class="invalid-feedback" id="locationError"></div>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Description <span
                                    class="text-danger">*</span></label>
                            <textarea class="form-control" id="description" name="description" rows="4" required maxlength="2000"
                                placeholder="Describe the incident in detail"></textarea>
                            <div class="form-text">Maximum 2000 characters</div>
                            <div class="invalid-feedback" id="descriptionError"></div>
                        </div>

                        <div class="mb-3">
                            <label for="actionTaken" class="form-label">Action Taken</label>
                            <textarea class="form-control" id="actionTaken" name="action_taken" rows="3" maxlength="1000"
                                placeholder="Describe immediate actions taken (optional)"></textarea>
                            <div class="form-text">Maximum 1000 characters</div>
                            <div class="invalid-feedback" id="actionTakenError"></div>
                        </div>

                        <div class="mb-3">
                            <label for="images" class="form-label">Images</label>
                            <input type="file" class="form-control" id="images" name="images[]" multiple
                                accept="image/*">
                            <div class="form-text">You can upload multiple images (JPEG, PNG, JPG, GIF). Maximum 2MB per
                                file.</div>
                            <div class="invalid-feedback" id="imagesError"></div>
                            <div id="imagePreview" class="mt-2"></div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">
                            <span class="spinner-border spinner-border-sm d-none" id="submitSpinner"
                                role="status"></span>
                            <span id="submitText">Save Report</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- View Report Modal -->
    <div class="modal fade" id="viewReportModal" tabindex="-1" aria-labelledby="viewReportModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="viewReportModalLabel">Report Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <!-- Report details will be loaded here -->
                    <div id="reportDetailsContent">
                        <div class="text-center py-4">
                            <div class="spinner-border" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-warning" onclick="editReportFromView()">
                        <i class="ri-edit-line me-1"></i>Edit Report
                    </button>
                    <div class="btn-group" role="group">
                        <button type="button" class="btn btn-success dropdown-toggle" data-bs-toggle="dropdown"
                            aria-expanded="false">
                            <i class="ri-check-line me-1"></i>Update Status
                        </button>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="#" onclick="updateReportStatus('in-progress')">Mark
                                    In Progress</a></li>
                            <li><a class="dropdown-item" href="#" onclick="updateReportStatus('done')">Mark
                                    Completed</a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Status Update Modal -->
    <div class="modal fade" id="statusModal" tabindex="-1" aria-labelledby="statusModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="statusForm">
                    <div class="modal-header">
                        <h5 class="modal-title" id="statusModalLabel">Update Report Status</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" id="statusReportId" name="report_id">
                        <input type="hidden" id="newStatus" name="status">

                        <div class="mb-3">
                            <label class="form-label">New Status:</label>
                            <p class="fw-bold" id="statusDisplay"></p>
                        </div>

                        <div class="mb-3" id="hseStaffSelection" style="display: none;">
                            <label for="statusHseStaffId" class="form-label">Assign HSE Staff</label>
                            <select class="form-select" id="statusHseStaffId" name="hse_staff_id">
                                <option value="">Select HSE Staff</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Update Status</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('jsSection')
    <script>
        $(document).ready(function() {
            // Initialize
            loadStatistics();
            initDataTable();
            initForms();
            loadFormData();

            // Set filters from URL params if any
            setFiltersFromUrl();
        });

        let reportsTable;
        let isEditMode = false;
        let currentReportId = null;
        let formData = {};

        function loadStatistics() {
            $.ajax({
                url: "/admin/reports/statistics/data",
                type: 'GET',
                success: function(response) {
                    if (response.success) {
                        const stats = response.data;
                        $('#totalReports').text(stats.total_reports);
                        $('#pendingReports').text(stats.waiting_reports);
                        $('#inProgressReports').text(stats.in_progress_reports);
                        $('#completedReports').text(stats.completed_reports);
                    }
                },
                error: function(xhr) {
                    console.error('Failed to load statistics');
                }
            });
        }

        function initDataTable() {
            reportsTable = $('#reportsTable').DataTable({
                processing: true,
                serverSide: true,
                responsive: true,
                ajax: {
                    url: "/admin/reports/data",
                    type: 'GET',
                    data: function(d) {
                        d.status = $('#statusFilter').val();
                        d.severity = $('#severityFilter').val();
                        d.start_date = $('#startDateFilter').val();
                        d.end_date = $('#endDateFilter').val();
                    }
                },
                columns: [{
                        data: 'DT_RowIndex',
                        name: 'DT_RowIndex',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'employee_info',
                        name: 'employee.name'
                    },
                    {
                        data: 'hse_staff_info',
                        name: 'hseStaff.name'
                    },
                    {
                        data: 'report_info',
                        name: 'categoryMaster.name'
                    },
                    {
                        data: 'description_short',
                        name: 'description'
                    },
                    {
                        data: 'severity_badge',
                        name: 'severity_rating'
                    },
                    {
                        data: 'status_badge',
                        name: 'status'
                    },
                    {
                        data: 'report_details_count',
                        name: 'report_details_count',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'dates_info',
                        name: 'created_at'
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false
                    }
                ],
                language: {
                    processing: "Loading...",
                    search: "Search:",
                    lengthMenu: "Show _MENU_ entries",
                    info: "Showing _START_ to _END_ of _TOTAL_ entries",
                    infoEmpty: "Showing 0 to 0 of 0 entries",
                    infoFiltered: "(filtered from _MAX_ total entries)",
                    paginate: {
                        first: "First",
                        last: "Last",
                        next: "Next",
                        previous: "Previous"
                    }
                },
                pageLength: 10,
                order: [
                    [8, 'desc']
                ],
                drawCallback: function() {
                    // Reinitialize tooltips if using Bootstrap tooltips
                    $('[data-bs-toggle="tooltip"]').tooltip();
                }
            });
        }

        function initForms() {
            // Report form submission
            $('#reportForm').on('submit', function(e) {
                e.preventDefault();
                submitReport();
            });

            // Status form submission  
            $('#statusForm').on('submit', function(e) {
                e.preventDefault();
                submitStatusUpdate();
            });

            // Reset forms when modals are closed
            $('#reportModal').on('hidden.bs.modal', function() {
                resetReportForm();
            });

            $('#statusModal').on('hidden.bs.modal', function() {
                resetStatusForm();
            });

            // Handle contributing factor change to load actions
            $('#contributingId').on('change', function() {
                const contributingId = $(this).val();
                loadActionsByContributing(contributingId);
            });

            // Handle image preview
            $('#images').on('change', function() {
                previewImages(this.files);
            });
        }

        function loadFormData() {
            $.ajax({
                url: "/admin/reports/create",
                type: 'GET',
                success: function(response) {
                    if (response.success) {
                        formData = response.data;
                        populateFormSelects();
                    }
                },
                error: function(xhr) {
                    showAlert('error', 'Error', 'Failed to load form data');
                }
            });
        }

        function populateFormSelects() {
            // Populate employees
            $('#employeeId').empty().append('<option value="">Select Employee</option>');
            formData.employees.forEach(function(employee) {
                $('#employeeId').append(
                    `<option value="${employee.id}">${employee.name} (${employee.employee_id})</option>`);
            });

            // Populate HSE staff
            $('#hseStaffId, #statusHseStaffId').empty().append('<option value="">Select HSE Staff</option>');
            formData.hse_staff.forEach(function(staff) {
                console.log(staff);
                $('#hseStaffId, #statusHseStaffId').append(
                    `<option value="${staff.id}">${staff.name} (${staff.department})</option>`);
            });

            // Populate categories
            $('#categoryId').empty().append('<option value="">Select Category</option>');
            formData.categories.forEach(function(category) {
                $('#categoryId').append(`<option value="${category.id}">${category.name}</option>`);
            });

            // Populate contributing factors
            $('#contributingId').empty().append('<option value="">Select Contributing Factor</option>');
            formData.contributing_factors.forEach(function(contributing) {
                $('#contributingId').append(`<option value="${contributing.id}">${contributing.name}</option>`);
            });
        }

        function loadActionsByContributing(contributingId) {
            if (!contributingId) {
                $('#actionId').empty().append('<option value="">Select Action</option>');
                return;
            }

            $.ajax({
                url: `/admin/reports/actions/by-contributing/${contributingId}`,
                type: 'GET',
                success: function(response) {
                    if (response.success) {
                        $('#actionId').empty().append('<option value="">Select Action</option>');
                        response.data.forEach(function(action) {
                            $('#actionId').append(
                                `<option value="${action.id}">${action.name}</option>`);
                        });
                    }
                },
                error: function(xhr) {
                    console.error('Failed to load actions');
                }
            });
        }

        function setFiltersFromUrl() {
            const urlParams = new URLSearchParams(window.location.search);
            const status = urlParams.get('status');
            if (status) {
                $('#statusFilter').val(status);
                showFilters();
            }
        }

        function showFilters() {
            $('#filtersPanel').toggleClass('d-none');
        }

        function applyFilters() {
            reportsTable.ajax.reload();
        }

        function clearFilters() {
            $('#filtersForm')[0].reset();
            reportsTable.ajax.reload();
        }

        function createReport() {
            isEditMode = false;
            currentReportId = null;
            $('#reportModalLabel').text('Add New Report');
            $('#submitText').text('Save Report');
            resetReportForm();
            $('#reportModal').modal('show');
        }

        function editReport(id) {
            isEditMode = true;
            currentReportId = id;
            $('#reportModalLabel').text('Edit Report');
            $('#submitText').text('Update Report');

            showFormLoading(true);
            $('#reportModal').modal('show');

            $.ajax({
                url: `/admin/reports/${id}`,
                type: 'GET',
                success: function(response) {
                    if (response.success) {
                        populateReportForm(response.data);
                        if (response.actions) {
                            populateActions(response.actions);
                        }
                    } else {
                        showAlert('error', 'Error', response.message);
                    }
                },
                error: function(xhr) {
                    const response = JSON.parse(xhr.responseText);
                    showAlert('error', 'Error', response.message || 'Failed to load report data');
                },
                complete: function() {
                    showFormLoading(false);
                }
            });
        }

        function populateReportForm(report) {
            $('#reportId').val(report.id);
            $('#employeeId').val(report.employee_id);
            $('#hseStaffId').val(report.hse_staff_id);
            $('#categoryId').val(report.category_id);
            $('#contributingId').val(report.contributing_id);
            $('#actionId').val(report.action_id);
            $('#severityRating').val(report.severity_rating);
            $('#location').val(report.location);
            $('#description').val(report.description);
            $('#actionTaken').val(report.action_taken);
        }

        function populateActions(actions) {
            $('#actionId').empty().append('<option value="">Select Action</option>');
            actions.forEach(function(action) {
                $('#actionId').append(`<option value="${action.id}">${action.name}</option>`);
            });
        }

        function viewReport(id) {
            currentReportId = id;
            $('#reportDetailsContent').html(`
                <div class="text-center py-4">
                    <div class="spinner-border" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
            `);
            $('#viewReportModal').modal('show');

            $.ajax({
                url: `/admin/reports/${id}`,
                type: 'GET',
                success: function(response) {
                    if (response.success) {
                        renderReportDetails(response.data);
                    } else {
                        showAlert('error', 'Error', response.message);
                        $('#viewReportModal').modal('hide');
                    }
                },
                error: function(xhr) {
                    const response = JSON.parse(xhr.responseText);
                    showAlert('error', 'Error', response.message || 'Failed to load report data');
                    $('#viewReportModal').modal('hide');
                }
            });
        }

        function renderReportDetails(report) {
            const statusColors = {
                'waiting': 'warning',
                'in-progress': 'info',
                'done': 'success'
            };

            const severityColors = {
                'low': 'success',
                'medium': 'warning',
                'high': 'danger',
                'critical': 'dark'
            };

            let imagesHtml = '';
            if (report.images && report.images.length > 0) {
                imagesHtml = '<div class="row">';
                report.images.forEach(function(image) {
                    imagesHtml += `
                        <div class="col-md-3 mb-2">
                            <img src="/storage/${image}" class="img-fluid rounded" onclick="showImageModal('/storage/${image}')" style="cursor: pointer;">
                        </div>
                    `;
                });
                imagesHtml += '</div>';
            } else {
                imagesHtml = '<p class="text-muted">No images uploaded</p>';
            }

            const html = `
                <div class="row">
                    <div class="col-md-6">
                        <h6 class="fw-bold">Employee Information</h6>
                        <table class="table table-sm">
                            <tr><td class="fw-bold">Name:</td><td>${report.employee ? report.employee.name : 'N/A'}</td></tr>
                            <tr><td class="fw-bold">User ID:</td><td>${report.employee_id || 'N/A'}</td></tr>
                            <tr><td class="fw-bold">Email:</td><td>${report.employee ? report.employee.email : 'N/A'}</td></tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <h6 class="fw-bold">HSE Staff Information</h6>
                        <table class="table table-sm">
                            <tr><td class="fw-bold">Name:</td><td>${report.hse_staff ? report.hse_staff.name : 'Not Assigned'}</td></tr>
                            <tr><td class="fw-bold">User ID:</td><td>${report.hse_staff_id || 'N/A'}</td></tr>
                            <tr><td class="fw-bold">Email:</td><td>${report.hse_staff ? report.hse_staff.email : 'N/A'}</td></tr>
                        </table>
                    </div>
                </div>

                <hr>

                <div class="row">
                    <div class="col-md-4">
                        <h6 class="fw-bold">Report Classification</h6>
                        <table class="table table-sm">
                            <tr><td class="fw-bold">Category:</td><td>${report.category_master ? report.category_master.name : 'N/A'}</td></tr>
                            <tr><td class="fw-bold">Contributing Factor:</td><td>${report.contributing_master ? report.contributing_master.name : 'N/A'}</td></tr>
                            <tr><td class="fw-bold">Action:</td><td>${report.action_master ? report.action_master.name : 'N/A'}</td></tr>
                        </table>
                    </div>
                    <div class="col-md-4">
                        <h6 class="fw-bold">Status & Severity</h6>
                        <table class="table table-sm">
                            <tr><td class="fw-bold">Status:</td><td><span class="badge bg-${statusColors[report.status]}">${report.status}</span></td></tr>
                            <tr><td class="fw-bold">Severity:</td><td><span class="badge bg-${severityColors[report.severity_rating]}">${report.severity_rating}</span></td></tr>
                            <tr><td class="fw-bold">Location:</td><td>${report.location}</td></tr>
                        </table>
                    </div>
                    <div class="col-md-4">
                        <h6 class="fw-bold">Dates</h6>
                        <table class="table table-sm">
                            <tr><td class="fw-bold">Created:</td><td>${formatDateTime(report.created_at)}</td></tr>
                            <tr><td class="fw-bold">Started:</td><td>${report.start_process_at ? formatDateTime(report.start_process_at) : 'Not Started'}</td></tr>
                            <tr><td class="fw-bold">Completed:</td><td>${report.completed_at ? formatDateTime(report.completed_at) : 'Not Completed'}</td></tr>
                        </table>
                    </div>
                </div>

                <hr>

                <div class="mb-3">
                    <h6 class="fw-bold">Description</h6>
                    <p>${report.description}</p>
                </div>

                <div class="mb-3">
                    <h6 class="fw-bold">Action Taken</h6>
                    <p>${report.action_taken || 'No action taken yet'}</p>
                </div>

                <div class="mb-3">
                    <h6 class="fw-bold">Images</h6>
                    ${imagesHtml}
                </div>

                <hr>

                <div class="mb-3">
                    <h6 class="fw-bold">Corrective Action Requests (CAR)</h6>
                    ${renderReportDetails_CAR(report.report_details)}
                </div>
            `;

            $('#reportDetailsContent').html(html);
        }

        function renderReportDetails_CAR(reportDetails) {
            if (!reportDetails || reportDetails.length === 0) {
                return '<p class="text-muted">No corrective action requests yet</p>';
            }

            let html = '<div class="table-responsive"><table class="table table-sm table-bordered">';
            html +=
                '<thead class="table-light"><tr><th>Action</th><th>Due Date</th><th>PIC</th><th>Status</th><th>Approved By</th></tr></thead><tbody>';

            reportDetails.forEach(function(detail) {
                const statusColors = {
                    'open': 'danger',
                    'in_progress': 'warning',
                    'closed': 'success'
                };

                html += `
                    <tr>
                        <td>${detail.correction_action}</td>
                        <td>${formatDate(detail.due_date)}</td>
                        <td>${detail.pic}</td>
                        <td><span class="badge bg-${statusColors[detail.status_car]}">${detail.status_car.replace('_', ' ')}</span></td>
                        <td>${detail.approved_by ? detail.approved_by.name : 'N/A'}</td>
                    </tr>
                `;
            });

            html += '</tbody></table></div>';
            return html;
        }

        function editReportFromView() {
            $('#viewReportModal').modal('hide');
            setTimeout(() => {
                editReport(currentReportId);
            }, 300);
        }

        function updateReportStatus(status) {
            $('#newStatus').val(status);
            $('#statusReportId').val(currentReportId);
            $('#statusDisplay').text(status.replace('-', ' ').toUpperCase());

            if (status === 'in-progress') {
                $('#hseStaffSelection').show();
            } else {
                $('#hseStaffSelection').hide();
            }

            $('#viewReportModal').modal('hide');
            $('#statusModal').modal('show');
        }

        function updateStatus(reportId, status) {
            currentReportId = reportId;
            updateReportStatus(status);
        }

        function deleteReport(id) {
            Swal.fire({
                title: 'Are you sure?',
                text: "This action cannot be undone! All related data will also be deleted.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, delete it!',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    performDelete(id);
                }
            });
        }

        function performDelete(id) {
            $.ajax({
                url: `/admin/reports/${id}?_method=delete`,
                type: 'POST',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    if (response.success) {
                        showAlert('success', 'Deleted!', response.message);
                        reportsTable.ajax.reload();
                        loadStatistics();
                    } else {
                        showAlert('error', 'Error', response.message);
                    }
                },
                error: function(xhr) {
                    const response = JSON.parse(xhr.responseText);
                    showAlert('error', 'Error', response.message || 'Failed to delete report');
                }
            });
        }

        function submitReport() {
            clearFormErrors();

            const formData = new FormData($('#reportForm')[0]);
            let url = isEditMode ? `/admin/reports/${currentReportId}` : '/admin/reports';
            const method = isEditMode ? 'PUT' : 'POST';

            if (method === 'PUT') {
                formData.append('_method', 'PUT');
                url += '?_method=PUT';
            }

            showFormLoading(true);

            $.ajax({
                url: url,
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    if (response.success) {
                        showAlert('success', 'Success!', response.message);
                        $('#reportModal').modal('hide');
                        reportsTable.ajax.reload();
                        loadStatistics();
                        resetReportForm();
                    } else {
                        showAlert('error', 'Error', response.message);
                    }
                },
                error: function(xhr) {
                    if (xhr.status === 422) {
                        const response = JSON.parse(xhr.responseText);
                        displayFormErrors(response.errors);
                    } else {
                        const response = JSON.parse(xhr.responseText);
                        showAlert('error', 'Error', response.message || 'Failed to save report');
                    }
                },
                complete: function() {
                    showFormLoading(false);
                }
            });
        }

        function submitStatusUpdate() {
            const formData = new FormData($('#statusForm')[0]);

            $.ajax({
                url: `/admin/reports/${$('#statusReportId').val()}/status?_method=PATCH`,
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    if (response.success) {
                        showAlert('success', 'Success!', response.message);
                        $('#statusModal').modal('hide');
                        reportsTable.ajax.reload();
                        loadStatistics();
                    } else {
                        showAlert('error', 'Error', response.message);
                    }
                },
                error: function(xhr) {
                    const response = JSON.parse(xhr.responseText);
                    showAlert('error', 'Error', response.message || 'Failed to update status');
                }
            });
        }

        function previewImages(files) {
            const preview = $('#imagePreview');
            preview.empty();

            if (files.length > 0) {
                Array.from(files).forEach(function(file) {
                    if (file.type.startsWith('image/')) {
                        const reader = new FileReader();
                        reader.onload = function(e) {
                            preview.append(`
                                <div class="d-inline-block me-2 mb-2">
                                    <img src="${e.target.result}" class="rounded" style="width: 100px; height: 100px; object-fit: cover;">
                                </div>
                            `);
                        };
                        reader.readAsDataURL(file);
                    }
                });
            }
        }

        function showImageModal(imageSrc) {
            const modal = `
                <div class="modal fade" id="imageModal" tabindex="-1">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Image Preview</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body text-center">
                                <img src="${imageSrc}" class="img-fluid">
                            </div>
                        </div>
                    </div>
                </div>
            `;
            $('body').append(modal);
            $('#imageModal').modal('show').on('hidden.bs.modal', function() {
                $(this).remove();
            });
        }

        function resetReportForm() {
            $('#reportForm')[0].reset();
            $('#reportId').val('');
            $('#imagePreview').empty();
            clearFormErrors();
            showFormLoading(false);
        }

        function resetStatusForm() {
            $('#statusForm')[0].reset();
        }

        function showFormLoading(show) {
            if (show) {
                $('#submitSpinner').removeClass('d-none');
                $('#reportForm button[type="submit"]').prop('disabled', true);
            } else {
                $('#submitSpinner').addClass('d-none');
                $('#reportForm button[type="submit"]').prop('disabled', false);
            }
        }

        function clearFormErrors() {
            $('.is-invalid').removeClass('is-invalid');
            $('.invalid-feedback').text('');
        }

        function displayFormErrors(errors) {
            $.each(errors, function(field, messages) {
                let input;

                // Handle nested field names
                if (field.includes('.')) {
                    const parts = field.split('.');
                    input = $(`#${parts[0]}`);
                } else {
                    // Convert snake_case to camelCase for ID matching
                    const camelField = field.replace(/_([a-z])/g, function(g) {
                        return g[1].toUpperCase();
                    });
                    input = $(`#${camelField}`);
                }

                const errorDiv = $(`#${field.replace('.', '').replace('_', '')}Error`);

                if (input.length) {
                    input.addClass('is-invalid');
                }
                if (errorDiv.length) {
                    errorDiv.text(messages[0]);
                }
            });
        }

        function showAlert(type, title, message) {
            Swal.fire({
                icon: type,
                title: title,
                text: message,
                showConfirmButton: true,
                timer: type === 'success' ? 3000 : null
            });
        }

        function formatDateTime(dateTime) {
            const date = new Date(dateTime);
            return date.toLocaleDateString('en-GB', {
                day: '2-digit',
                month: 'short',
                year: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            });
        }

        function formatDate(date) {
            const d = new Date(date);
            return d.toLocaleDateString('en-GB', {
                day: '2-digit',
                month: 'short',
                year: 'numeric'
            });
        }
    </script>
@endpush
