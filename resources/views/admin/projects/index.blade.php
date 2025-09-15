@extends('admin.layouts')

@section('title', 'Projects Management')

@section('content')
    <div class="page-content">
        <!-- Page Title -->
        <div class="page-title-head d-flex align-items-center gap-2">
            <div class="flex-grow-1">
                <h4 class="fs-18 fw-bold mb-0">Projects Management</h4>
            </div>
            <div class="text-end">
                <ol class="breadcrumb m-0 py-0 fs-13">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard.index') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="javascript: void(0);">Master Data</a></li>
                    <li class="breadcrumb-item active">Projects</li>
                </ol>
            </div>
        </div>

        <div class="page-container">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div
                            class="card-header border-bottom border-dashed d-flex align-items-center justify-content-between">
                            <h4 class="header-title mb-0">Projects List</h4>
                            <button type="button" class="btn btn-primary" onclick="createProject()">
                                <i class="ri-add-line me-1"></i>Add New Project
                            </button>
                        </div>

                        <div class="card-body">
                            <div class="table-responsive">
                                <table id="projectsTable" class="table table-striped table-bordered dt-responsive nowrap"
                                    style="width:100%">
                                    <thead class="table-dark">
                                        <tr>
                                            <th width="5%">#</th>
                                            <th width="10%">Code</th>
                                            <th width="25%">Project Name</th>
                                            <th width="12%">Start Date</th>
                                            <th width="12%">End Date</th>
                                            <th width="10%">Duration</th>
                                            <th width="8%">Status</th>
                                            <th width="13%">Created At</th>
                                            <th width="5%">Actions</th>
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

    <!-- Create/Edit Project Modal -->
    <div class="modal fade" id="projectModal" tabindex="-1" aria-labelledby="projectModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <form id="projectForm">
                    <div class="modal-header">
                        <h5 class="modal-title" id="projectModalLabel">Add New Project</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" id="projectId" name="id">

                        <div class="row">
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="projectCode" class="form-label">Project Code <span
                                            class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="projectCode" name="code" required
                                        maxlength="255" placeholder="e.g. PRJ001">
                                    <div class="invalid-feedback" id="codeError"></div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="projectStatus" class="form-label">Status <span
                                            class="text-danger">*</span></label>
                                    <select class="form-select" id="projectStatus" name="status" required>
                                        <option value="">Select Status</option>
                                        <option value="open">Open</option>
                                        <option value="closed">Closed</option>
                                    </select>
                                    <div class="invalid-feedback" id="statusError"></div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="projectDuration" class="form-label">Duration (Auto-calculated)</label>
                                    <input type="text" class="form-control" id="projectDuration" readonly
                                        placeholder="Will be calculated automatically">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <div class="mb-3">
                                    <label for="projectName" class="form-label">Project Name <span
                                            class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="projectName" name="project_name" required
                                        maxlength="255" placeholder="Enter project name">
                                    <div class="invalid-feedback" id="project_nameError"></div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="projectStartDate" class="form-label">Start Date <span
                                            class="text-danger">*</span></label>
                                    <input type="date" class="form-control" id="projectStartDate" name="start_date" required>
                                    <div class="invalid-feedback" id="start_dateError"></div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="projectEndDate" class="form-label">End Date <span
                                            class="text-danger">*</span></label>
                                    <input type="date" class="form-control" id="projectEndDate" name="end_date" required>
                                    <div class="invalid-feedback" id="end_dateError"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">
                            <span class="spinner-border spinner-border-sm d-none" id="submitSpinner" role="status"></span>
                            <span id="submitText">Save Project</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- View Project Modal -->
    <div class="modal fade" id="viewProjectModal" tabindex="-1" aria-labelledby="viewProjectModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="viewProjectModalLabel">Project Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Project Code:</label>
                                <p class="form-control-plaintext" id="viewProjectCode">-</p>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Status:</label>
                                <p class="form-control-plaintext" id="viewProjectStatus">-</p>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Duration:</label>
                                <p class="form-control-plaintext" id="viewProjectDuration">-</p>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Project Name:</label>
                                <p class="form-control-plaintext" id="viewProjectName">-</p>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Start Date:</label>
                                <p class="form-control-plaintext" id="viewProjectStartDate">-</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold">End Date:</label>
                                <p class="form-control-plaintext" id="viewProjectEndDate">-</p>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Created At:</label>
                                <p class="form-control-plaintext" id="viewProjectCreated">-</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Updated At:</label>
                                <p class="form-control-plaintext" id="viewProjectUpdated">-</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-warning" onclick="editProjectFromView()">
                        <i class="ri-edit-line me-1"></i>Edit Project
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('jsSection')
    <script>
        $(document).ready(function() {
            // Initialize DataTable
            initDataTable();

            // Initialize form
            initForm();

            // Date change listeners for duration calculation
            $('#projectStartDate, #projectEndDate').on('change', function() {
                calculateDuration();
            });
        });

        let projectsTable;
        let isEditMode = false;

        function initDataTable() {
            projectsTable = $('#projectsTable').DataTable({
                processing: true,
                serverSide: true,
                responsive: true,
                ajax: {
                    url: "{{ route('admin.projects.data') }}",
                    type: 'GET'
                },
                columns: [{
                        data: 'DT_RowIndex',
                        name: 'DT_RowIndex',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'code',
                        name: 'code'
                    },
                    {
                        data: 'project_name',
                        name: 'project_name'
                    },
                    {
                        data: 'start_date_formatted',
                        name: 'start_date'
                    },
                    {
                        data: 'end_date_formatted',
                        name: 'end_date'
                    },
                    {
                        data: 'duration_days',
                        name: 'durasi'
                    },
                    {
                        data: 'status_badge',
                        name: 'status'
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
                    [7, 'desc']
                ] // Order by created_at desc
            });
        }

        function initForm() {
            $('#projectForm').on('submit', function(e) {
                e.preventDefault();
                submitProject();
            });

            // Reset form when modal is closed
            $('#projectModal').on('hidden.bs.modal', function() {
                resetForm();
            });
        }

        function calculateDuration() {
            const startDate = $('#projectStartDate').val();
            const endDate = $('#projectEndDate').val();

            if (startDate && endDate) {
                const start = new Date(startDate);
                const end = new Date(endDate);

                if (end >= start) {
                    const diffTime = Math.abs(end - start);
                    const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24)) + 1;
                    $('#projectDuration').val(diffDays + ' days');
                } else {
                    $('#projectDuration').val('Invalid date range');
                }
            } else {
                $('#projectDuration').val('');
            }
        }

        function createProject() {
            isEditMode = false;
            $('#projectModalLabel').text('Add New Project');
            $('#submitText').text('Save Project');
            resetForm();
            $('#projectModal').modal('show');
        }

        function editProject(id) {
            isEditMode = true;
            $('#projectModalLabel').text('Edit Project');
            $('#submitText').text('Update Project');

            // Show loading state
            showFormLoading(true);
            $('#projectModal').modal('show');

            // Fetch project data
            $.ajax({
                url: `{{ url('/') }}/admin/projects/${id}`,
                type: 'GET',
                success: function(response) {
                    if (response.success) {
                        const project = response.data;
                        $('#projectId').val(project.id);
                        $('#projectCode').val(project.code);
                        $('#projectName').val(project.project_name);
                        $('#projectStartDate').val(project.start_date);
                        $('#projectEndDate').val(project.end_date);
                        $('#projectStatus').val(project.status);
                        calculateDuration();
                    } else {
                        showAlert('error', 'Error', response.message);
                    }
                },
                error: function(xhr) {
                    const response = JSON.parse(xhr.responseText);
                    showAlert('error', 'Error', response.message || 'Failed to load project data');
                },
                complete: function() {
                    showFormLoading(false);
                }
            });
        }

        function viewProject(id) {
            // Show loading state
            $('#viewProjectName').text('Loading...');
            $('#viewProjectModal').modal('show');

            // Fetch project data
            $.ajax({
                url: `{{ url('/') }}/admin/projects/${id}`,
                type: 'GET',
                success: function(response) {
                    if (response.success) {
                        const project = response.data;
                        $('#viewProjectCode').text(project.code);
                        $('#viewProjectName').text(project.project_name);
                        $('#viewProjectStartDate').text(formatDate(project.start_date));
                        $('#viewProjectEndDate').text(formatDate(project.end_date));
                        $('#viewProjectDuration').text(project.durasi + ' days');
                        $('#viewProjectStatus').html(
                            `<span class="badge bg-${project.status === 'open' ? 'success' : 'secondary'}">
                        ${project.status === 'open' ? 'Open' : 'Closed'}
                    </span>`
                        );
                        $('#viewProjectCreated').text(formatDateTime(project.created_at));
                        $('#viewProjectUpdated').text(formatDateTime(project.updated_at));

                        // Store ID for potential edit action
                        $('#viewProjectModal').data('project-id', project.id);
                    } else {
                        showAlert('error', 'Error', response.message);
                        $('#viewProjectModal').modal('hide');
                    }
                },
                error: function(xhr) {
                    const response = JSON.parse(xhr.responseText);
                    showAlert('error', 'Error', response.message || 'Failed to load project data');
                    $('#viewProjectModal').modal('hide');
                }
            });
        }

        function editProjectFromView() {
            const projectId = $('#viewProjectModal').data('project-id');
            $('#viewProjectModal').modal('hide');

            // Small delay to ensure modal is closed before opening edit modal
            setTimeout(() => {
                editProject(projectId);
            }, 300);
        }

        function deleteProject(id) {
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
                url: `{{ url('/') }}/admin/projects/${id}?_method=delete`,
                type: 'POST',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    if (response.success) {
                        showAlert('success', 'Deleted!', response.message);
                        projectsTable.ajax.reload();
                    } else {
                        showAlert('error', 'Error', response.message);
                    }
                },
                error: function(xhr) {
                    const response = JSON.parse(xhr.responseText);
                    showAlert('error', 'Error', response.message || 'Failed to delete project');
                }
            });
        }

        function submitProject() {
            // Clear previous errors
            clearFormErrors();

            const formData = new FormData($('#projectForm')[0]);
            let url = isEditMode ? `{{ url('/') }}/admin/projects/${$('#projectId').val()}` : '{{ url('/') }}/admin/projects';
            const method = isEditMode ? 'PUT' : 'POST';

            if (method === 'PUT') {
                formData.append('_method', 'PUT');
                url += '?_method=PUT';
            }

            // Show loading state
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
                        $('#projectModal').modal('hide');
                        projectsTable.ajax.reload();
                        resetForm();
                    } else {
                        showAlert('error', 'Error', response.message);
                    }
                },
                error: function(xhr) {
                    if (xhr.status === 422) {
                        // Validation errors
                        const response = JSON.parse(xhr.responseText);
                        displayFormErrors(response.errors);
                    } else {
                        const response = JSON.parse(xhr.responseText);
                        showAlert('error', 'Error', response.message || 'Failed to save project');
                    }
                },
                complete: function() {
                    showFormLoading(false);
                }
            });
        }

        function resetForm() {
            $('#projectForm')[0].reset();
            $('#projectId').val('');
            $('#projectDuration').val('');
            clearFormErrors();
            showFormLoading(false);
        }

        function showFormLoading(show) {
            if (show) {
                $('#submitSpinner').removeClass('d-none');
                $('#projectForm button[type="submit"]').prop('disabled', true);
            } else {
                $('#submitSpinner').addClass('d-none');
                $('#projectForm button[type="submit"]').prop('disabled', false);
            }
        }

        function clearFormErrors() {
            $('.is-invalid').removeClass('is-invalid');
            $('.invalid-feedback').text('');
        }

        function displayFormErrors(errors) {
            $.each(errors, function(field, messages) {
                let input;
                let errorDiv;

                if (field === 'project_name') {
                    input = $('#projectName');
                    errorDiv = $('#project_nameError');
                } else if (field === 'start_date') {
                    input = $('#projectStartDate');
                    errorDiv = $('#start_dateError');
                } else if (field === 'end_date') {
                    input = $('#projectEndDate');
                    errorDiv = $('#end_dateError');
                } else {
                    input = $(`#project${field.charAt(0).toUpperCase() + field.slice(1)}`);
                    errorDiv = $(`#${field}Error`);
                }

                input.addClass('is-invalid');
                errorDiv.text(messages[0]);
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

        function formatDate(dateString) {
            if (!dateString) return '-';
            const date = new Date(dateString);
            return date.toLocaleDateString('en-GB', {
                day: '2-digit',
                month: 'short',
                year: 'numeric'
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
    </script>
@endpush