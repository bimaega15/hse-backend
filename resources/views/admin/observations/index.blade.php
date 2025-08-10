@extends('admin.layouts')

@section('title', 'Observations Management')

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

        /* Observation details form styling */
        .observation-detail-item {
            border: 1px solid #e3e6f0;
            border-radius: 8px;
            padding: 1rem;
            margin-bottom: 1rem;
            background: #f8f9fc;
        }

        .observation-detail-item:hover {
            border-color: #667eea;
            box-shadow: 0 2px 8px rgba(102, 126, 234, 0.1);
        }

        .remove-detail-btn {
            position: absolute;
            top: -10px;
            right: -10px;
            width: 30px;
            height: 30px;
            border-radius: 50%;
            border: none;
            background: #dc3545;
            color: white;
            font-size: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .observation-type-badge {
            font-size: 0.75rem;
            padding: 0.25rem 0.5rem;
            border-radius: 12px;
        }
    </style>
@endpush

@section('content')
    <div class="page-content">
        <!-- Page Title -->
        <div class="page-title-head d-flex align-items-center gap-2">
            <div class="flex-grow-1">
                <h4 class="fs-18 fw-bold mb-0">
                    Observations Management
                    @if ($view === 'analytics')
                        - Analytics Dashboard
                    @elseif($status === 'submitted')
                        - Submitted Observations
                    @elseif($status === 'reviewed')
                        - Reviewed Observations
                    @elseif($status === 'draft')
                        - Draft Observations
                    @endif
                </h4>
            </div>
            <div class="text-end">
                <ol class="breadcrumb m-0 py-0 fs-13">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard.index') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="javascript: void(0);">HSE Management</a></li>
                    <li class="breadcrumb-item active">Observations</li>
                </ol>
            </div>
        </div>

        <div class="page-container">
            @if ($view === 'analytics')
                <!-- Analytics View -->
                @include('admin.observations.partials.analytics')
            @else
                <!-- Default Observations View -->
                @include('admin.observations.partials.observations-list')
            @endif
        </div>
    </div>

    <!-- Include Modals -->
    @include('admin.observations.partials.modals')
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

        let observationsTable;
        let isEditMode = false;
        let currentObservationId = null;
        let formData = {};
        let detailCounter = 0;

        function loadStatistics() {
            $.ajax({
                url: "{{ route('admin.observations.statistics.data') }}",
                type: 'GET',
                success: function(response) {
                    if (response.success) {
                        const stats = response.data;
                        $('#totalObservations').text(stats.total_observations);
                        $('#draftObservations').text(stats.draft_observations);
                        $('#submittedObservations').text(stats.submitted_observations);
                        $('#reviewedObservations').text(stats.reviewed_observations);
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
                'draft': 'Draft Observations',
                'submitted': 'Submitted Observations',
                'reviewed': 'Reviewed Observations'
            };

            if (titles[status]) {
                $('.page-title-head h4').text(`Observations Management - ${titles[status]}`);
            }
        }

        function initDataTable() {
            observationsTable = $('#observationsTable').DataTable({
                processing: true,
                serverSide: true,
                responsive: true,
                ajax: {
                    url: "{{ route('admin.observations.data') }}",
                    type: 'GET',
                    data: function(d) {
                        d.status = $('#statusFilter').val();
                        d.observation_type = $('#observationTypeFilter').val();
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
                        data: 'observer_info',
                        name: 'user.name'
                    },
                    {
                        data: 'observation_summary',
                        name: 'waktu_observasi'
                    },
                    {
                        data: 'observations_breakdown',
                        name: 'observations_breakdown',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'status_badge',
                        name: 'status'
                    },
                    {
                        data: 'notes_excerpt',
                        name: 'notes'
                    },
                    {
                        data: 'created_at_formatted',
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
                    [6, 'desc']
                ],
                drawCallback: function() {
                    $('[data-bs-toggle="tooltip"]').tooltip();
                }
            });
        }

        function initAnalytics() {
            // Initialize charts and analytics widgets
            console.log('Initializing observations analytics...');
        }

        function initForms() {
            $('#observationForm').on('submit', function(e) {
                e.preventDefault();
                submitObservation();
            });

            $('#statusForm').on('submit', function(e) {
                e.preventDefault();
                submitStatusUpdate();
            });

            $('#observationModal').on('hidden.bs.modal', function() {
                resetObservationForm();
            });

            $('#statusModal').on('hidden.bs.modal', function() {
                resetStatusForm();
            });
        }

        function loadFormData() {
            $.ajax({
                url: "{{ route('admin.observations.create') }}",
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
            $('#userId').empty().append('<option value="">Select Observer</option>');
            formData.users.forEach(function(user) {
                $('#userId').append(
                    `<option value="${user.id}">${user.name} (${user.role})</option>`);
            });

            // Store categories for detail forms
            window.categoriesData = formData.categories;
        }

        function showFilters() {
            $('#filtersPanel').toggleClass('d-none');
        }

        function applyFilters() {
            observationsTable.ajax.reload();
        }

        function clearFilters() {
            $('#filtersForm')[0].reset();
            observationsTable.ajax.reload();
        }

        function createObservation() {
            isEditMode = false;
            currentObservationId = null;
            $('#observationModalLabel').text('Add New Observation');
            $('#submitText').text('Save Observation');
            resetObservationForm();
            $('#observationModal').modal('show');
        }

        function editObservation(id) {
            isEditMode = true;
            currentObservationId = id;
            $('#observationModalLabel').text('Edit Observation');
            $('#submitText').text('Update Observation');

            showFormLoading(true);
            $('#observationModal').modal('show');

            $.ajax({
                url: `/admin/observations/${id}`,
                type: 'GET',
                success: function(response) {
                    if (response.success) {
                        populateObservationForm(response.data);
                    } else {
                        showAlert('error', 'Error', response.message);
                    }
                },
                error: function(xhr) {
                    const response = JSON.parse(xhr.responseText);
                    showAlert('error', 'Error', response.message || 'Failed to load observation data');
                },
                complete: function() {
                    showFormLoading(false);
                }
            });
        }

        function populateObservationForm(observation) {
            $('#observationId').val(observation.id);
            $('#userId').val(observation.user_id);
            $('#waktuObservasi').val(observation.waktu_observasi);
            $('#waktuMulai').val(observation.waktu_mulai);
            $('#waktuSelesai').val(observation.waktu_selesai);
            $('#notes').val(observation.notes);

            // Clear existing details and add observation details
            $('#observationDetails').empty();
            detailCounter = 0;

            if (observation.details && observation.details.length > 0) {
                observation.details.forEach(function(detail) {
                    addObservationDetail(detail);
                });
            }
        }

        function viewObservation(id) {
            currentObservationId = id;
            $('#observationDetailsContent').html(`
                <div class="text-center py-4">
                    <div class="spinner-border" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
            `);
            $('#viewObservationModal').modal('show');

            $.ajax({
                url: `/admin/observations/${id}`,
                type: 'GET',
                success: function(response) {
                    if (response.success) {
                        renderObservationDetails(response.data);
                    } else {
                        showAlert('error', 'Error', response.message);
                        $('#viewObservationModal').modal('hide');
                    }
                },
                error: function(xhr) {
                    const response = JSON.parse(xhr.responseText);
                    showAlert('error', 'Error', response.message || 'Failed to load observation data');
                    $('#viewObservationModal').modal('hide');
                }
            });
        }

        function renderObservationDetails(observation) {
            const statusColors = {
                'draft': 'secondary',
                'submitted': 'warning',
                'reviewed': 'success'
            };

            let detailsHtml = '';
            if (observation.details && observation.details.length > 0) {
                detailsHtml = '<div class="row">';
                observation.details.forEach(function(detail, index) {
                    const typeLabels = {
                        'at_risk_behavior': 'At Risk Behavior',
                        'nearmiss_incident': 'Near Miss Incident',
                        'informal_risk_mgmt': 'Informal Risk Management',
                        'sim_k3': 'SIM K3'
                    };

                    const severityColors = {
                        'low': 'success',
                        'medium': 'warning',
                        'high': 'danger',
                        'critical': 'dark'
                    };

                    detailsHtml += `
                        <div class="col-md-6 mb-3">
                            <div class="card h-100">
                                <div class="card-header d-flex justify-content-between align-items-center">
                                    <span class="badge bg-primary">${typeLabels[detail.observation_type] || detail.observation_type}</span>
                                    <span class="badge bg-${severityColors[detail.severity]}">${detail.severity}</span>
                                </div>
                                <div class="card-body">
                                    <h6 class="card-title">Category: ${detail.category ? detail.category.name : 'N/A'}</h6>
                                    <p class="card-text">${detail.description}</p>
                                    ${detail.action_taken ? `<p class="card-text"><small class="text-muted"><strong>Action Taken:</strong> ${detail.action_taken}</small></p>` : ''}
                                </div>
                            </div>
                        </div>
                    `;
                });
                detailsHtml += '</div>';
            } else {
                detailsHtml = '<p class="text-muted">No observation details available</p>';
            }

            const html = `
                <div class="row">
                    <div class="col-md-6">
                        <h6 class="fw-bold">Observer Information</h6>
                        <table class="table table-sm">
                            <tr><td class="fw-bold">Name:</td><td>${observation.user ? observation.user.name : 'N/A'}</td></tr>
                            <tr><td class="fw-bold">User ID:</td><td>${observation.user_id || 'N/A'}</td></tr>
                            <tr><td class="fw-bold">Email:</td><td>${observation.user ? observation.user.email : 'N/A'}</td></tr>
                            <tr><td class="fw-bold">Department:</td><td>${observation.user ? observation.user.department : 'N/A'}</td></tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <h6 class="fw-bold">Observation Summary</h6>
                        <table class="table table-sm">
                            <tr><td class="fw-bold">Status:</td><td><span class="badge bg-${statusColors[observation.status]}">${observation.status}</span></td></tr>
                            <tr><td class="fw-bold">Observation Time:</td><td>${observation.waktu_observasi || 'N/A'}</td></tr>
                            <tr><td class="fw-bold">Duration:</td><td>${observation.waktu_mulai || 'N/A'} - ${observation.waktu_selesai || 'N/A'}</td></tr>
                            <tr><td class="fw-bold">Total Observations:</td><td>${observation.total_observations || 0}</td></tr>
                        </table>
                    </div>
                </div>

                <hr>

                <div class="row">
                    <div class="col-md-12">
                        <h6 class="fw-bold">Observation Breakdown</h6>
                        <div class="row text-center mb-3">
                            <div class="col-md-3">
                                <div class="p-2">
                                    <h4 class="fw-bold text-danger mb-1">${observation.at_risk_behavior || 0}</h4>
                                    <p class="text-muted mb-0 small">At Risk Behavior</p>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="p-2">
                                    <h4 class="fw-bold text-warning mb-1">${observation.nearmiss_incident || 0}</h4>
                                    <p class="text-muted mb-0 small">Near Miss Incident</p>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="p-2">
                                    <h4 class="fw-bold text-info mb-1">${observation.informal_risk_mgmt || 0}</h4>
                                    <p class="text-muted mb-0 small">Risk Management</p>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="p-2">
                                    <h4 class="fw-bold text-primary mb-1">${observation.sim_k3 || 0}</h4>
                                    <p class="text-muted mb-0 small">SIM K3</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <hr>

                <div class="mb-3">
                    <h6 class="fw-bold">Notes</h6>
                    <p>${observation.notes || 'No notes provided'}</p>
                </div>

                <hr>

                <div class="mb-3">
                    <h6 class="fw-bold">Observation Details</h6>
                    ${detailsHtml}
                </div>
            `;

            $('#observationDetailsContent').html(html);
        }

        function editObservationFromView() {
            $('#viewObservationModal').modal('hide');
            setTimeout(() => {
                editObservation(currentObservationId);
            }, 300);
        }

        function updateObservationStatus(status) {
            $('#newStatus').val(status);
            $('#statusObservationId').val(currentObservationId);
            $('#statusDisplay').text(status.replace('_', ' ').toUpperCase());

            $('#viewObservationModal').modal('hide');
            $('#statusModal').modal('show');
        }

        function reviewObservation(id) {
            currentObservationId = id;
            updateObservationStatus('reviewed');
        }

        function deleteObservation(id) {
            Swal.fire({
                title: 'Are you sure?',
                text: "This action cannot be undone!",
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
                url: `/admin/observations/${id}?_method=delete`,
                type: 'POST',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    if (response.success) {
                        showAlert('success', 'Deleted!', response.message);
                        observationsTable.ajax.reload();
                        loadStatistics();
                    } else {
                        showAlert('error', 'Error', response.message);
                    }
                },
                error: function(xhr) {
                    const response = JSON.parse(xhr.responseText);
                    showAlert('error', 'Error', response.message || 'Failed to delete observation');
                }
            });
        }

        function submitObservation() {
            clearFormErrors();

            // Collect observation details
            const details = [];
            $('#observationDetails .observation-detail-item').each(function() {
                const detail = {
                    observation_type: $(this).find('[name$="[observation_type]"]').val(),
                    category_id: $(this).find('[name$="[category_id]"]').val(),
                    description: $(this).find('[name$="[description]"]').val(),
                    severity: $(this).find('[name$="[severity]"]').val(),
                    action_taken: $(this).find('[name$="[action_taken]"]').val()
                };
                details.push(detail);
            });

            const formData = {
                user_id: $('#userId').val(),
                waktu_observasi: $('#waktuObservasi').val(),
                waktu_mulai: $('#waktuMulai').val(),
                waktu_selesai: $('#waktuSelesai').val(),
                notes: $('#notes').val(),
                details: details
            };

            let url = isEditMode ? `/admin/observations/${currentObservationId}` : '/admin/observations';
            const method = isEditMode ? 'PUT' : 'POST';

            if (method === 'PUT') {
                formData._method = 'PUT';
            }

            showFormLoading(true);

            $.ajax({
                url: url,
                type: 'POST',
                data: formData,
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    if (response.success) {
                        showAlert('success', 'Success!', response.message);
                        $('#observationModal').modal('hide');
                        observationsTable.ajax.reload();
                        loadStatistics();
                        resetObservationForm();
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
                        showAlert('error', 'Error', response.message || 'Failed to save observation');
                    }
                },
                complete: function() {
                    showFormLoading(false);
                }
            });
        }

        function submitStatusUpdate() {
            const formData = {
                status: $('#newStatus').val()
            };

            $.ajax({
                url: `/admin/observations/${$('#statusObservationId').val()}/status?_method=PATCH`,
                type: 'POST',
                data: formData,
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    if (response.success) {
                        showAlert('success', 'Success!', response.message);
                        $('#statusModal').modal('hide');
                        observationsTable.ajax.reload();
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

        function addObservationDetail(existingDetail = null) {
            detailCounter++;
            const detailId = `detail_${detailCounter}`;

            let categoryOptions = '<option value="">Select Category</option>';
            if (window.categoriesData) {
                window.categoriesData.forEach(function(category) {
                    const selected = existingDetail && existingDetail.category_id == category.id ? 'selected' : '';
                    categoryOptions += `<option value="${category.id}" ${selected}>${category.name}</option>`;
                });
            }

            const observationTypes = {
                'at_risk_behavior': 'At Risk Behavior',
                'nearmiss_incident': 'Near Miss Incident',
                'informal_risk_mgmt': 'Informal Risk Management',
                'sim_k3': 'SIM K3'
            };

            let typeOptions = '<option value="">Select Type</option>';
            Object.keys(observationTypes).forEach(function(key) {
                const selected = existingDetail && existingDetail.observation_type === key ? 'selected' : '';
                typeOptions += `<option value="${key}" ${selected}>${observationTypes[key]}</option>`;
            });

            const severityLevels = {
                'low': 'Low',
                'medium': 'Medium',
                'high': 'High',
                'critical': 'Critical'
            };

            let severityOptions = '<option value="">Select Severity</option>';
            Object.keys(severityLevels).forEach(function(key) {
                const selected = existingDetail && existingDetail.severity === key ? 'selected' : '';
                severityOptions += `<option value="${key}" ${selected}>${severityLevels[key]}</option>`;
            });

            const detailHtml = `
                <div class="observation-detail-item position-relative" id="${detailId}">
                    <button type="button" class="remove-detail-btn" onclick="removeObservationDetail('${detailId}')">
                        <i class="ri-close-line"></i>
                    </button>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Observation Type <span class="text-danger">*</span></label>
                                <select class="form-select" name="details[${detailCounter}][observation_type]" required>
                                    ${typeOptions}
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Category <span class="text-danger">*</span></label>
                                <select class="form-select" name="details[${detailCounter}][category_id]" required>
                                    ${categoryOptions}
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Severity <span class="text-danger">*</span></label>
                                <select class="form-select" name="details[${detailCounter}][severity]" required>
                                    ${severityOptions}
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Action Taken</label>
                                <input type="text" class="form-control" name="details[${detailCounter}][action_taken]" 
                                       value="${existingDetail ? existingDetail.action_taken || '' : ''}" 
                                       placeholder="Action taken (optional)">
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Description <span class="text-danger">*</span></label>
                        <textarea class="form-control" name="details[${detailCounter}][description]" rows="3" required 
                                  placeholder="Describe the observation in detail">${existingDetail ? existingDetail.description || '' : ''}</textarea>
                    </div>
                </div>
            `;

            $('#observationDetails').append(detailHtml);
        }

        function removeObservationDetail(detailId) {
            $(`#${detailId}`).remove();
        }

        function resetObservationForm() {
            $('#observationForm')[0].reset();
            $('#observationId').val('');
            $('#observationDetails').empty();
            detailCounter = 0;
            clearFormErrors();
            showFormLoading(false);
        }

        function resetStatusForm() {
            $('#statusForm')[0].reset();
        }

        function showFormLoading(show) {
            if (show) {
                $('#submitSpinner').removeClass('d-none');
                $('#observationForm button[type="submit"]').prop('disabled', true);
            } else {
                $('#submitSpinner').addClass('d-none');
                $('#observationForm button[type="submit"]').prop('disabled', false);
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
                    input = $(`[name="${field}"]`);
                } else {
                    const camelField = field.replace(/_([a-z])/g, function(g) {
                        return g[1].toUpperCase();
                    });
                    input = $(`#${camelField}`);
                }

                if (input.length) {
                    input.addClass('is-invalid');
                    input.after(`<div class="invalid-feedback">${messages[0]}</div>`);
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
