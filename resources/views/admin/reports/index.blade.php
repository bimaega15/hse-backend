@extends('admin.layouts')

@section('title', 'Reports Management')

@push('cssSection')
    <style>
        /* Analytics Cards */
        .analytics-card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            overflow: hidden;
        }

        .analytics-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.15);
        }

        .analytics-card .card-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            padding: 1rem 1.5rem;
        }

        .analytics-metric {
            text-align: center;
            padding: 1rem;
        }

        .analytics-metric .metric-value {
            font-size: 2.5rem;
            font-weight: 700;
            color: #495057;
            margin-bottom: 0.5rem;
        }

        .analytics-metric .metric-label {
            font-size: 0.9rem;
            color: #6c757d;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .analytics-metric .metric-change {
            font-size: 0.8rem;
            margin-top: 0.5rem;
        }

        .trend-up {
            color: #28a745;
        }

        .trend-down {
            color: #dc3545;
        }

        .trend-neutral {
            color: #6c757d;
        }

        /* Chart containers */
        .chart-container {
            position: relative;
            height: 300px;
        }

        .chart-small {
            height: 200px;
        }

        /* SLA indicator */
        .sla-indicator {
            display: inline-block;
            width: 12px;
            height: 12px;
            border-radius: 50%;
            margin-right: 8px;
        }

        .sla-good {
            background-color: #28a745;
        }

        .sla-warning {
            background-color: #ffc107;
        }

        .sla-critical {
            background-color: #dc3545;
        }

        /* Tab styling */
        .nav-tabs .nav-link {
            border: none;
            border-radius: 25px;
            margin-right: 10px;
            transition: all 0.3s ease;
        }

        .nav-tabs .nav-link.active {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        /* Detail Timeline Styles for Modal */
        .detail-timeline {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            border-radius: 15px;
            padding: 1.5rem;
            margin: 1rem 0;
            position: relative;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        .detail-timeline::before {
            content: '';
            position: absolute;
            top: 33%;
            left: 8%;
            right: 8%;
            height: 4px;
            background: #e9ecef;
            border-radius: 2px;
            z-index: 1;
        }

        .detail-timeline.progress-1::before {
            background: linear-gradient(to right, #28a745 33%, #e9ecef 33%);
        }

        .detail-timeline.progress-2::before {
            background: linear-gradient(to right, #28a745 67%, #e9ecef 67%);
        }

        .detail-timeline.progress-3::before {
            background: #28a745;
        }

        .detail-timeline-step {
            display: flex;
            flex-direction: column;
            align-items: center;
            position: relative;
            z-index: 2;
            flex: 1;
            max-width: 150px;
        }

        .detail-timeline-icon {
            width: 55px;
            height: 55px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            margin-bottom: 0.75rem;
            border: 4px solid transparent;
            transition: all 0.3s ease;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }

        .detail-timeline-step.completed .detail-timeline-icon {
            background: linear-gradient(135deg, #2850a7, #206fc9);
            color: white;
            border-color: #2883a7;
            transform: scale(1.05);
        }

        .detail-timeline-step.current .detail-timeline-icon {
            background: linear-gradient(135deg, #ffc107, #fd7e14);
            color: #212529;
            border-color: #ffc107;
            animation: pulse 2s infinite;
            transform: scale(1.1);
        }

        .detail-timeline-step .detail-timeline-icon {
            background: linear-gradient(135deg, #28a745, #20c997);
            color: #6c757d;
            border-color: #18bd78;
        }

        @keyframes pulse {
            0% {
                box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15), 0 0 0 0 rgba(255, 193, 7, 0.7);
            }

            70% {
                box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15), 0 0 0 10px rgba(255, 193, 7, 0);
            }

            100% {
                box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15), 0 0 0 0 rgba(255, 193, 7, 0);
            }
        }

        .detail-timeline-title {
            font-weight: 700;
            font-size: 0.9rem;
            color: #495057;
            margin-bottom: 0.25rem;
            text-align: center;
            transition: color 0.3s ease;
        }

        .detail-timeline-date {
            font-size: 0.8rem;
            color: #6c757d;
            text-align: center;
            font-weight: 500;
            line-height: 1.2;
            transition: color 0.3s ease;
        }

        .detail-timeline-step.completed .detail-timeline-title {
            color: #28a745;
            font-weight: 800;
        }

        .detail-timeline-step.current .detail-timeline-title {
            color: #fd7e14;
            font-weight: 800;
        }

        .detail-timeline-step.completed .detail-timeline-date {
            color: #28a745;
            font-weight: 600;
        }

        .detail-timeline-step.current .detail-timeline-date {
            color: #fd7e14;
            font-weight: 600;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .detail-timeline {
                flex-direction: column;
                gap: 1.5rem;
                padding: 1rem;
            }

            .detail-timeline::before {
                display: none;
            }

            .detail-timeline-step {
                max-width: none;
                width: 100%;
                flex-direction: row;
                text-align: left;
            }

            .detail-timeline-icon {
                margin-bottom: 0;
                margin-right: 1rem;
                width: 45px;
                height: 45px;
                font-size: 20px;
            }

            .detail-timeline-content {
                flex: 1;
            }

            .detail-timeline-title,
            .detail-timeline-date {
                text-align: left;
            }
        }

        .detail-timeline-step:hover .detail-timeline-icon {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.2);
        }

        .detail-timeline-step.completed:hover .detail-timeline-icon {
            transform: scale(1.1) translateY(-2px);
        }

        .detail-timeline-step.current:hover .detail-timeline-icon {
            transform: scale(1.15) translateY(-2px);
        }

        .timeline-header {
            display: flex;
            align-items: center;
            margin-bottom: 1rem;
        }

        .timeline-header i {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            font-size: 1.2rem;
            margin-right: 0.5rem;
        }
    </style>
@endpush

@section('content')
    <div class="page-content">
        <!-- Page Title -->
        <div class="page-title-head d-flex align-items-center gap-2">
            <div class="flex-grow-1">
                <h4 class="fs-18 fw-bold mb-0">
                    Reports Management
                    @if ($view === 'analytics')
                        - Analytics Dashboard
                    @elseif($status === 'waiting')
                        - Pending Reports
                    @elseif($status === 'in-progress')
                        - Reports In Progress
                    @elseif($status === 'done')
                        - Completed Reports
                    @endif
                </h4>
            </div>
            <div class="text-end">
                <ol class="breadcrumb m-0 py-0 fs-13">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard.index') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="javascript: void(0);">BAIK Management</a></li>
                    <li class="breadcrumb-item active">Reports</li>
                </ol>
            </div>
        </div>

        <div class="page-container">
            @if ($view === 'analytics')
                <!-- Analytics View -->
                @include('admin.reports.partials.analytics')
            @else
                <!-- Default Reports View -->
                @include('admin.reports.partials.reports-list')
            @endif
        </div>
    </div>

    <!-- Include Modals -->
    @include('admin.reports.partials.modals')
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

            // Initialize analytics if on analytics page
            @if ($view === 'analytics')
                initAnalytics();
            @endif
        });

        let reportsTable;
        let isEditMode = false;
        let currentReportId = null;
        let formData = {};

        function loadStatistics() {
            $.ajax({
                url: "{{ route('admin.reports.statistics.data') }}",
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

        function setFiltersFromUrl() {
            const urlParams = new URLSearchParams(window.location.search);
            const status = urlParams.get('status');
            const view = urlParams.get('view');

            if (status && view !== 'analytics') {
                $('#statusFilter').val(status);
                showFilters();
                updatePageTitle(status);
            }
        }

        function updatePageTitle(status) {
            const titles = {
                'waiting': 'Pending Reports',
                'in-progress': 'Reports In Progress',
                'done': 'Completed Reports'
            };

            if (titles[status]) {
                $('.page-title-head h4').text(`Reports Management - ${titles[status]}`);
            }
        }

        function initDataTable() {
            reportsTable = $('#reportsTable').DataTable({
                processing: true,
                serverSide: true,
                responsive: true,
                ajax: {
                    url: "{{ route('admin.reports.data') }}",
                    type: 'GET',
                    data: function(d) {
                        d.status = $('#statusFilter').val();
                        d.severity = $('#severityFilter').val();
                        d.start_date = $('#startDateFilter').val();
                        d.end_date = $('#endDateFilter').val();

                        // Add URL status filter
                        const urlParams = new URLSearchParams(window.location.search);
                        const urlStatus = urlParams.get('status');
                        if (urlStatus && !d.status) {
                            d.url_status = urlStatus;
                        }
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
                    $('[data-bs-toggle="tooltip"]').tooltip();
                }
            });
        }

        function initAnalytics() {
            // Initialize charts and analytics widgets
            console.log('Initializing analytics...');
        }

        function initForms() {
            $('#reportForm').on('submit', function(e) {
                e.preventDefault();
                submitReport();
            });

            $('#statusForm').on('submit', function(e) {
                e.preventDefault();
                submitStatusUpdate();
            });

            $('#reportModal').on('hidden.bs.modal', function() {
                resetReportForm();
            });

            $('#statusModal').on('hidden.bs.modal', function() {
                resetStatusForm();
            });

            $('#contributingId').on('change', function() {
                const contributingId = $(this).val();
                loadActionsByContributing(contributingId);
            });

            $('#images').on('change', function() {
                previewImages(this.files);
            });
        }

        function loadFormData() {
            $.ajax({
                url: "{{ route('admin.reports.create') }}",
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
            $('#employeeId').empty().append('<option value="">Select Employee</option>');
            formData.employees.forEach(function(employee) {
                $('#employeeId').append(
                    `<option value="${employee.id}">${employee.name}</option>`);
            });

            $('#hseStaffId, #statusHseStaffId').empty().append('<option value="">Select BAIK Staff</option>');
            formData.hse_staff.forEach(function(staff) {
                $('#hseStaffId, #statusHseStaffId').append(
                    `<option value="${staff.id}">${staff.name}</option>`);
            });

            $('#categoryId').empty().append('<option value="">Select Category</option>');
            formData.categories.forEach(function(category) {
                $('#categoryId').append(`<option value="${category.id}">${category.name}</option>`);
            });

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

        function renderDetailTimeline(report) {
            const created = report.created_at ? formatDateTime(report.created_at) : 'N/A';
            const started = report.start_process_at ? formatDateTime(report.start_process_at) : 'Pending';
            const completed = report.completed_at ? formatDateTime(report.completed_at) : 'Pending';

            let progressClass = 'progress-1';
            if (report.status === 'in-progress') progressClass = 'progress-2';
            if (report.status === 'done') progressClass = 'progress-3';

            let createdClass = 'completed';
            let startedClass = report.status === 'in-progress' || report.status === 'done' ? 'completed' : (report
                .status === 'waiting' ? 'current' : '');
            let completedClass = report.status === 'done' ? 'completed' : (report.status === 'in-progress' ? 'current' :
                '');

            return `
                <div class="detail-timeline ${progressClass}">
                    <div class="detail-timeline-step ${createdClass}">
                        <div class="detail-timeline-icon">
                            <i class="ri-file-add-line"></i>
                        </div>
                        <div class="detail-timeline-content">
                            <div class="detail-timeline-title">Created</div>
                            <div class="detail-timeline-date">${created}</div>
                        </div>
                    </div>
                    
                    <div class="detail-timeline-step ${startedClass}">
                        <div class="detail-timeline-icon">
                            <i class="ri-play-circle-line"></i>
                        </div>
                        <div class="detail-timeline-content">
                            <div class="detail-timeline-title">Started</div>
                            <div class="detail-timeline-date">${started}</div>
                        </div>
                    </div>
                    
                    <div class="detail-timeline-step ${completedClass}">
                        <div class="detail-timeline-icon">
                            <i class="ri-check-line text-white"></i>
                        </div>
                        <div class="detail-timeline-content">
                            <div class="detail-timeline-title">Completed</div>
                            <div class="detail-timeline-date">${completed}</div>
                        </div>
                    </div>
                </div>
            `;
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
                            <img src="{{ asset('storage/' . ${image}) }}" class="img-fluid rounded" onclick="showImageModal('{{ asset('storage/' . ${image}) }}')" style="cursor: pointer;">
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
                        <h6 class="fw-bold">BAIK Staff Information</h6>
                        <table class="table table-sm">
                            <tr><td class="fw-bold">Name:</td><td>${report.hse_staff ? report.hse_staff.name : 'Not Assigned'}</td></tr>
                            <tr><td class="fw-bold">User ID:</td><td>${report.hse_staff_id || 'N/A'}</td></tr>
                            <tr><td class="fw-bold">Email:</td><td>${report.hse_staff ? report.hse_staff.email : 'N/A'}</td></tr>
                        </table>
                    </div>
                </div>

                <hr>

                <div class="row">
                    <div class="col-md-6">
                        <h6 class="fw-bold">Report Classification</h6>
                        <table class="table table-sm">
                            <tr><td class="fw-bold">Category:</td><td>${report.category_master ? report.category_master.name : 'N/A'}</td></tr>
                            <tr><td class="fw-bold">Contributing Factor:</td><td>${report.contributing_master ? report.contributing_master.name : 'N/A'}</td></tr>
                            <tr><td class="fw-bold">Action:</td><td>${report.action_master ? report.action_master.name : 'N/A'}</td></tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <h6 class="fw-bold">Status & Severity</h6>
                        <table class="table table-sm">
                            <tr><td class="fw-bold">Status:</td><td><span class="badge bg-${statusColors[report.status]}">${report.status}</span></td></tr>
                            <tr><td class="fw-bold">Severity:</td><td><span class="badge bg-${severityColors[report.severity_rating]}">${report.severity_rating}</span></td></tr>
                            <tr><td class="fw-bold">Location:</td><td>${report.location}</td></tr>
                        </table>
                    </div>
                </div>

                <hr>

                <div class="row">
                    <div class="col-md-12">
                        <div class="timeline-header">
                            <i class="ri-time-line"></i>
                            <h6 class="fw-bold mb-0">Progress Timeline</h6>
                        </div>
                        ${renderDetailTimeline(report)}
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
                '<thead class="table-light"><tr><th>Action</th><th>Due Date</th><th>PIC</th><th>Status</th><th>Approved By</th><th>Images</th></tr></thead><tbody>';

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
                        <td>
                        ${detail.evidences.length > 0 ? `<div class="d-flex gap-1">
                                        ${detail.evidences.map(img => `
                                                                            <a href="javascript:void(0);" class="avatar-md" onclick="showImageModal('{{ asset('storage/') }}/${img}')">
                                                                                <img src="{{ asset('storage/') }}/${img}" alt="Report Image" class="img-fluid rounded" >
                                                                            </a>
                                                                        `).join('')}
                                    </div>` : '<p class="text-muted">No images available</p>'}
                        </td>
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

                if (field.includes('.')) {
                    const parts = field.split('.');
                    input = $(`#${parts[0]}`);
                } else {
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
