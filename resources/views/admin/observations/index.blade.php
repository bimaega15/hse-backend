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
                    <li class="breadcrumb-item"><a href="javascript: void(0);">BAIK Management</a></li>
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
                        searchable: false,
                        responsivePriority: 1
                    },
                    {
                        data: 'observer_info',
                        name: 'user.name',
                        responsivePriority: 2
                    },
                    {
                        data: 'observation_summary',
                        name: 'waktu_observasi',
                        responsivePriority: 5
                    },
                    {
                        data: 'observations_breakdown',
                        name: 'observations_breakdown',
                        orderable: false,
                        searchable: false,
                        responsivePriority: 6
                    },
                    {
                        data: 'status_badge',
                        name: 'status',
                        responsivePriority: 4
                    },
                    {
                        data: 'notes_excerpt',
                        name: 'notes',
                        responsivePriority: 7
                    },
                    {
                        data: 'created_at_formatted',
                        name: 'created_at',
                        responsivePriority: 3
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false,
                        responsivePriority: 1
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

            // Initialize Select2 for Observer dropdown
            $('#userId').select2({
                dropdownParent: $('#observationModal'),
                theme: 'bootstrap-5',
                placeholder: 'Select Observer',
                allowClear: true,
                width: '100%'
            });

            // Store all master data for detail forms
            window.categoriesData = formData.categories || [];
            window.contributingsData = formData.contributings || [];
            window.actionsData = formData.actions || [];
            window.locationsData = formData.locations || [];
            window.projectsData = formData.projects || [];
            window.activatorsData = formData.activators || [];
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
                url: `{{ url('/') }}/admin/observations/${id}`,
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
                url: `{{ url('/') }}/admin/observations/${id}`,
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
                url: `{{ url('/') }}/admin/observations/${id}?_method=delete`,
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

        async function submitObservation() {
            clearFormErrors();

            // Collect observation details with all fields including images
            const details = [];
            const detailPromises = [];

            $('#observationDetails .observation-detail-item').each(function(index) {
                const $detail = $(this);

                const detailPromise = new Promise(async (resolve) => {
                    // Collect all form fields
                    const detail = {
                        observation_type: $detail.find('[name$="[observation_type]"]').val(),
                        category_id: $detail.find('[name$="[category_id]"]').val(),
                        contributing_id: $detail.find('[name$="[contributing_id]"]').val(),
                        action_id: $detail.find('[name$="[action_id]"]').val(),
                        location_id: $detail.find('[name$="[location_id]"]').val(),
                        project_id: $detail.find('[name$="[project_id]"]').val(),
                        activator_id: $detail.find('[name$="[activator_id]"]').val(),
                        report_date: $detail.find('[name$="[report_date]"]').val(),
                        severity: $detail.find('[name$="[severity]"]').val(),
                        description: $detail.find('[name$="[description]"]').val(),
                        action_taken: $detail.find('[name$="[action_taken]"]').val(),
                        images: []
                    };

                    // Process images to base64
                    const fileInput = $detail.find('input[type="file"]')[0];
                    if (fileInput && fileInput.files.length > 0) {
                        const imagePromises = [];

                        for (let i = 0; i < fileInput.files.length; i++) {
                            const file = fileInput.files[i];
                            if (file.type.startsWith('image/')) {
                                const imagePromise = new Promise((imageResolve) => {
                                    const reader = new FileReader();
                                    reader.onload = function(e) {
                                        detail.images.push({
                                            name: file.name,
                                            type: file.type,
                                            size: file.size,
                                            data: e.target.result // base64 string with data:image prefix
                                        });
                                        imageResolve();
                                    };
                                    reader.readAsDataURL(file);
                                });
                                imagePromises.push(imagePromise);
                            }
                        }

                        await Promise.all(imagePromises);
                    }

                    resolve(detail);
                });

                detailPromises.push(detailPromise);
            });

            // Wait for all details (including images) to be processed
            showFormLoading(true);

            try {
                const allDetails = await Promise.all(detailPromises);

                const formData = {
                    user_id: $('#userId').val(),
                    waktu_observasi: $('#waktuObservasi').val(),
                    waktu_mulai: $('#waktuMulai').val(),
                    waktu_selesai: $('#waktuSelesai').val(),
                    notes: $('#notes').val(),
                    details: allDetails
                };

                let url = isEditMode ? `{{ url('/') }}/admin/observations/${currentObservationId}` :
                    '{{ url('/') }}/admin/observations';
                const method = isEditMode ? 'PUT' : 'POST';

                if (method === 'PUT') {
                    formData._method = 'PUT';
                }

                console.log('Submitting form data:', formData); // Debug log

                $.ajax({
                    url: url,
                    type: 'POST',
                    data: JSON.stringify(formData),
                    contentType: 'application/json',
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

            } catch (error) {
                console.error('Error processing form data:', error);
                showAlert('error', 'Error', 'Failed to process form data');
                showFormLoading(false);
            }
        }

        function submitStatusUpdate() {
            const formData = {
                status: $('#newStatus').val()
            };

            $.ajax({
                url: `{{ url('/') }}/admin/observations/${$('#statusObservationId').val()}/status?_method=PATCH`,
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

            // Get current datetime for default report date
            const now = new Date();
            const localISOTime = new Date(now.getTime() - (now.getTimezoneOffset() * 60000)).toISOString().slice(0, 16);

            // Build dropdown options
            let categoryOptions = '<option value="">Select Category</option>';
            if (window.categoriesData) {
                window.categoriesData.forEach(function(category) {
                    const selected = existingDetail && existingDetail.category_id == category.id ? 'selected' : '';
                    categoryOptions += `<option value="${category.id}" ${selected}>${category.name}</option>`;
                });
            }

            let contributingOptions = '<option value="">Select Contributing Factor</option>';
            if (window.contributingsData) {
                window.contributingsData.forEach(function(contributing) {
                    const selected = existingDetail && existingDetail.contributing_id == contributing.id ?
                        'selected' : '';
                    contributingOptions +=
                        `<option value="${contributing.id}" ${selected}>${contributing.name}</option>`;
                });
            }

            let actionOptions = '<option value="">Select Action</option>';
            if (window.actionsData) {
                window.actionsData.forEach(function(action) {
                    const selected = existingDetail && existingDetail.action_id == action.id ? 'selected' : '';
                    actionOptions += `<option value="${action.id}" ${selected}>${action.name}</option>`;
                });
            }

            let locationOptions = '<option value="">Select Location</option>';
            if (window.locationsData) {
                window.locationsData.forEach(function(location) {
                    const selected = existingDetail && existingDetail.location_id == location.id ? 'selected' : '';
                    locationOptions += `<option value="${location.id}" ${selected}>${location.name}</option>`;
                });
            }

            let projectOptions = '<option value="">Select Project (Optional)</option>';
            if (window.projectsData) {
                window.projectsData.forEach(function(project) {
                    const selected = existingDetail && existingDetail.project_id == project.id ? 'selected' : '';
                    projectOptions += `<option value="${project.id}" ${selected}>${project.project_name}</option>`;
                });
            }

            let activatorOptions = '<option value="">Select Activator</option>';
            if (window.activatorsData) {
                window.activatorsData.forEach(function(activator) {
                    const selected = existingDetail && existingDetail.activator_id == activator.id ? 'selected' :
                        '';
                    activatorOptions += `<option value="${activator.id}" ${selected}>${activator.name}</option>`;
                });
            }

            const observationTypes = {
                'at_risk_behavior': 'At Risk Behavior',
                'nearmiss_incident': 'Near Miss Incident',
                'informal_risk_mgmt': 'Informal Risk Management',
                'sim_k3': 'SIM K3'
            };

            let typeOptions = '<option value="">Select Observation Type</option>';
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
                <div class="observation-detail-item border rounded p-3 mb-3 position-relative" id="${detailId}" data-detail-index="${detailCounter}">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="fw-bold mb-0">Detail ${detailCounter}</h6>
                        <button type="button" class="btn btn-danger btn-sm" onclick="removeObservationDetail('${detailId}')">
                            <i class="ri-delete-bin-line me-1"></i>Remove
                        </button>
                    </div>

                    <div class="row">
                        <!-- Observation Type -->
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">Observation Type <span class="text-danger">*</span></label>
                                <select class="form-select observation-type-select" name="details[${detailCounter}][observation_type]" required onchange="handleObservationTypeChangeIndex(this, '${detailId}')">
                                    ${typeOptions}
                                </select>
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>

                        <!-- Category -->
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">Category <span class="text-danger">*</span></label>
                                <select class="form-select" name="details[${detailCounter}][category_id]" required>
                                    ${categoryOptions}
                                </select>
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>

                        <!-- Contributing Factor -->
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">Contributing Factor <span class="text-danger">*</span></label>
                                <select class="form-select" name="details[${detailCounter}][contributing_id]" required>
                                    ${contributingOptions}
                                </select>
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <!-- Action -->
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">Action <span class="text-danger">*</span></label>
                                <select class="form-select" name="details[${detailCounter}][action_id]" required>
                                    ${actionOptions}
                                </select>
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>

                        <!-- Severity Rating -->
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">Severity Rating <span class="text-danger">*</span></label>
                                <select class="form-select" name="details[${detailCounter}][severity]" required>
                                    ${severityOptions}
                                </select>
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>

                        <!-- Location -->
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">Location <span class="text-danger">*</span></label>
                                <select class="form-select" name="details[${detailCounter}][location_id]" required>
                                    ${locationOptions}
                                </select>
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <!-- Report Date -->
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Report Date <span class="text-danger">*</span></label>
                                <input type="datetime-local" class="form-control" name="details[${detailCounter}][report_date]" required value="${existingDetail && existingDetail.report_date ? existingDetail.report_date : localISOTime}">
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>

                        <!-- Project -->
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Project</label>
                                <select class="form-select" name="details[${detailCounter}][project_id]">
                                    ${projectOptions}
                                </select>
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Activator Field (only for At Risk Behavior) -->
                    <div class="row activator-row" id="activator-row-${detailId}" style="display: ${existingDetail && existingDetail.observation_type === 'at_risk_behavior' ? 'block' : 'none'};">
                        <div class="col-md-12">
                            <div class="mb-3">
                                <label class="form-label">Activator <span class="text-danger">*</span></label>
                                <select class="form-select" name="details[${detailCounter}][activator_id]" ${existingDetail && existingDetail.observation_type === 'at_risk_behavior' ? 'required' : ''}>
                                    ${activatorOptions}
                                </select>
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Description -->
                    <div class="mb-3">
                        <label class="form-label">Description <span class="text-danger">*</span></label>
                        <textarea class="form-control" name="details[${detailCounter}][description]" rows="4" required maxlength="2000" placeholder="Describe the observation in detail">${existingDetail ? existingDetail.description || '' : ''}</textarea>
                        <div class="form-text">Maximum 2000 characters</div>
                        <div class="invalid-feedback"></div>
                    </div>

                    <!-- Action Taken -->
                    <div class="mb-3">
                        <label class="form-label">Action Taken</label>
                        <textarea class="form-control" name="details[${detailCounter}][action_taken]" rows="3" maxlength="1000" placeholder="Describe immediate actions taken (optional)">${existingDetail ? existingDetail.action_taken || '' : ''}</textarea>
                        <div class="form-text">Maximum 1000 characters</div>
                        <div class="invalid-feedback"></div>
                    </div>

                    <!-- Images -->
                    <div class="mb-3">
                        <label class="form-label">Images</label>
                        <input type="file" class="form-control" name="details[${detailCounter}][images][]" multiple accept="image/*" onchange="previewImages(this, '${detailId}')">
                        <div class="form-text">You can upload multiple images (JPEG, PNG, JPG, GIF). Maximum 2MB per file.</div>
                        <div class="invalid-feedback"></div>
                        <div class="image-preview mt-2" id="image-preview-${detailId}"></div>
                    </div>
                </div>
            `;

            $('#observationDetails').append(detailHtml);

            // Initialize Select2 for all dropdowns in this detail
            initializeSelect2ForDetail(detailId, detailCounter);

            // Show bottom add button and floating button if more than 0 details
            updateAddDetailButtons();
        }

        // Update visibility of add detail buttons
        function updateAddDetailButtons() {
            const detailCount = $('#observationDetails .observation-detail-item').length;

            if (detailCount > 0) {
                $('#bottomAddDetailButton').show();
                $('#floatingAddDetailBtn').show();
            } else {
                $('#bottomAddDetailButton').hide();
                $('#floatingAddDetailBtn').hide();
            }
        }

        // Initialize Select2 for all select elements in a detail item
        function initializeSelect2ForDetail(detailId, detailCounter) {
            const detailElement = document.getElementById(detailId);

            // Initialize Select2 for all select elements
            $(detailElement).find('select').each(function() {
                const $select = $(this);
                const fieldName = $select.attr('name');
                let placeholder = 'Select option';

                // Set specific placeholders based on field name
                if (fieldName.includes('observation_type')) {
                    placeholder = 'Select Observation Type';
                } else if (fieldName.includes('category_id')) {
                    placeholder = 'Select Category';
                } else if (fieldName.includes('contributing_id')) {
                    placeholder = 'Select Contributing Factor';
                } else if (fieldName.includes('action_id')) {
                    placeholder = 'Select Action';
                } else if (fieldName.includes('location_id')) {
                    placeholder = 'Select Location';
                } else if (fieldName.includes('project_id')) {
                    placeholder = 'Select Project (Optional)';
                } else if (fieldName.includes('activator_id')) {
                    placeholder = 'Select Activator';
                } else if (fieldName.includes('severity')) {
                    placeholder = 'Select Severity';
                }

                $select.select2({
                    dropdownParent: $('#observationModal'),
                    theme: 'bootstrap-5',
                    placeholder: placeholder,
                    allowClear: !$select.prop('required'), // Allow clear only if not required
                    width: '100%'
                });
            });
        }

        // Preview selected images
        function previewImages(input, detailId) {
            const previewContainer = document.getElementById(`image-preview-${detailId}`);
            previewContainer.innerHTML = '';

            if (input.files) {
                Array.from(input.files).forEach((file, index) => {
                    if (file.type.startsWith('image/')) {
                        const reader = new FileReader();

                        reader.onload = function(e) {
                            const imageWrapper = document.createElement('div');
                            imageWrapper.className = 'position-relative d-inline-block me-2 mb-2';
                            imageWrapper.style.maxWidth = '150px';

                            const img = document.createElement('img');
                            img.src = e.target.result;
                            img.className = 'img-thumbnail';
                            img.style.maxWidth = '150px';
                            img.style.maxHeight = '150px';
                            img.style.objectFit = 'cover';

                            const removeBtn = document.createElement('button');
                            removeBtn.type = 'button';
                            removeBtn.className = 'btn btn-danger btn-sm position-absolute top-0 end-0';
                            removeBtn.style.transform = 'translate(50%, -50%)';
                            removeBtn.innerHTML = '<i class="ri-close-line"></i>';
                            removeBtn.onclick = function() {
                                removeImagePreview(input, index, detailId);
                            };

                            const fileName = document.createElement('small');
                            fileName.className = 'd-block text-muted mt-1 text-truncate';
                            fileName.textContent = file.name;
                            fileName.style.maxWidth = '150px';

                            imageWrapper.appendChild(img);
                            imageWrapper.appendChild(removeBtn);
                            imageWrapper.appendChild(fileName);
                            previewContainer.appendChild(imageWrapper);
                        };

                        reader.readAsDataURL(file);
                    }
                });
            }
        }

        // Remove image from preview and file input
        function removeImagePreview(input, indexToRemove, detailId) {
            const dt = new DataTransfer();

            Array.from(input.files).forEach((file, index) => {
                if (index !== indexToRemove) {
                    dt.items.add(file);
                }
            });

            input.files = dt.files;
            previewImages(input, detailId);
        }

        // Handle observation type change (show/hide activator field)
        function handleObservationTypeChangeIndex(selectElement, detailId) {
            const activatorRow = document.getElementById(`activator-row-${detailId}`);
            const activatorSelect = activatorRow.querySelector('select[name*="[activator_id]"]');
            const $activatorSelect = $(activatorSelect);

            if (selectElement.value === 'at_risk_behavior') {
                activatorRow.style.display = 'block';
                activatorSelect.setAttribute('required', 'required');

                // Reinitialize Select2 for activator if not already initialized
                if (!$activatorSelect.hasClass('select2-hidden-accessible')) {
                    $activatorSelect.select2({
                        dropdownParent: $('#observationModal'),
                        theme: 'bootstrap-5',
                        placeholder: 'Select Activator',
                        allowClear: false,
                        width: '100%'
                    });
                }
            } else {
                activatorRow.style.display = 'none';
                activatorSelect.removeAttribute('required');

                // Clear the Select2 value
                if ($activatorSelect.hasClass('select2-hidden-accessible')) {
                    $activatorSelect.val(null).trigger('change');
                } else {
                    activatorSelect.value = '';
                }
            }
        }

        function removeObservationDetail(detailId) {
            // Destroy Select2 instances before removing the element
            $(`#${detailId} select`).each(function() {
                if ($(this).hasClass('select2-hidden-accessible')) {
                    $(this).select2('destroy');
                }
            });

            // Remove the detail element
            $(`#${detailId}`).remove();

            // Update button visibility
            updateAddDetailButtons();
        }

        function resetObservationForm() {
            // Destroy all Select2 instances in observation details before clearing
            $('#observationDetails select').each(function() {
                if ($(this).hasClass('select2-hidden-accessible')) {
                    $(this).select2('destroy');
                }
            });

            $('#observationForm')[0].reset();
            $('#observationId').val('');
            $('#observationDetails').empty();
            detailCounter = 0;
            clearFormErrors();
            showFormLoading(false);

            // Reset Select2 for user dropdown
            $('#userId').val(null).trigger('change');
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

    <style>
        /* Image Preview Styles */
        .image-preview {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }

        .image-preview .position-relative {
            transition: all 0.3s ease;
        }

        .image-preview .position-relative:hover {
            transform: scale(1.05);
        }

        .image-preview img {
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
        }

        .image-preview img:hover {
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.2);
        }

        .image-preview .btn-danger {
            width: 24px;
            height: 24px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
        }

        /* Select2 Custom Styles */
        /* .select2-container--bootstrap-5 .select2-selection--single {
                    height: calc(2.25rem + 2px) !important;
                } */

        .select2-container--bootstrap-5 .select2-selection--single .select2-selection__rendered {
            /* line-height: calc(2.25rem) !important; */
            padding-left: 12px !important;
        }

        /* Observation Detail Item Styles */
        .observation-detail-item {
            background: #f8f9fa;
            border: 1px solid #dee2e6 !important;
            transition: all 0.3s ease;
        }

        .observation-detail-item:hover {
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            border-color: #0d6efd !important;
        }

        /* Required field asterisk styling */
        .form-label .text-danger {
            font-weight: bold;
        }

        /* Custom scrollbar for modal */
        .modal-body {
            max-height: 80vh;
            overflow-y: auto;
        }

        .modal-body::-webkit-scrollbar {
            width: 6px;
        }

        .modal-body::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 10px;
        }

        .modal-body::-webkit-scrollbar-thumb {
            background: #888;
            border-radius: 10px;
        }

        .modal-body::-webkit-scrollbar-thumb:hover {
            background: #555;
        }
    </style>
@endpush
