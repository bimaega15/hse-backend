@extends('admin.layouts')

@section('title', 'Corrective Actions Management')

@section('content')
    <div class="page-content">
        <!-- Page Title -->
        <div class="page-title-head d-flex align-items-center gap-2">
            <div class="flex-grow-1">
                <h4 class="fs-18 fw-bold mb-0">Corrective Actions Management</h4>
            </div>
            <div class="text-end">
                <ol class="breadcrumb m-0 py-0 fs-13">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard.index') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="javascript: void(0);">Master Data</a></li>
                    <li class="breadcrumb-item"><a href="javascript: void(0);">Risk Management</a></li>
                    <li class="breadcrumb-item active">Corrective Actions</li>
                </ol>
            </div>
        </div>

        <div class="page-container">
            <!-- Filter Row -->
            <div class="row mb-3">
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-body p-3">
                            <label for="contributingFilter" class="form-label fw-bold">Filter by Contributing
                                Factor:</label>
                            <select class="form-select" id="contributingFilter">
                                <option value="">All Contributing Factors</option>
                                @foreach ($contributings as $contributing)
                                    <option value="{{ $contributing->id }}">{{ $contributing->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-body p-3 d-flex align-items-center justify-content-between">
                            <div>
                                <h6 class="mb-1">Quick Stats</h6>
                                <small class="text-muted">Total actions and contributing factors overview</small>
                            </div>
                            <div class="d-flex gap-3">
                                <div class="text-center">
                                    <div class="fs-20 fw-bold text-primary" id="totalActions">-</div>
                                    <small class="text-muted">Total Actions</small>
                                </div>
                                <div class="text-center">
                                    <div class="fs-20 fw-bold text-success" id="activeActions">-</div>
                                    <small class="text-muted">Active Actions</small>
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
                            <h4 class="header-title mb-0">Actions List</h4>
                            <button type="button" class="btn btn-primary" onclick="createAction()">
                                <i class="ri-add-line me-1"></i>Add New Action
                            </button>
                        </div>

                        <div class="card-body">
                            <div class="table-responsive">
                                <table id="actionsTable" class="table table-striped table-bordered dt-responsive nowrap"
                                    style="width:100%">
                                    <thead class="table-dark">
                                        <tr>
                                            <th width="5%">#</th>
                                            <th width="20%">Contributing Factor</th>
                                            <th width="20%">Action Name</th>
                                            <th width="25%">Description</th>
                                            <th width="10%">Status</th>
                                            <th width="10%">Created At</th>
                                            <th width="10%">Actions</th>
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

    <!-- Create/Edit Action Modal -->
    <div class="modal fade" id="actionModal" tabindex="-1" aria-labelledby="actionModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form id="actionForm">
                    <div class="modal-header">
                        <h5 class="modal-title" id="actionModalLabel">Add New Action</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" id="actionId" name="id">

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="actionContributing" class="form-label">Contributing Factor <span
                                            class="text-danger">*</span></label>
                                    <select class="form-select" id="actionContributing" name="contributing_id" required>
                                        <option value="">Select Contributing Factor</option>
                                        @foreach ($contributings as $contributing)
                                            <option value="{{ $contributing->id }}">{{ $contributing->name }}</option>
                                        @endforeach
                                    </select>
                                    <div class="invalid-feedback" id="contributing_idError"></div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="actionStatus" class="form-label">Status <span
                                            class="text-danger">*</span></label>
                                    <select class="form-select" id="actionStatus" name="is_active" required>
                                        <option value="">Select Status</option>
                                        <option value="1">Active</option>
                                        <option value="0">Inactive</option>
                                    </select>
                                    <div class="invalid-feedback" id="statusError"></div>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="actionName" class="form-label">Action Name <span
                                    class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="actionName" name="name" required
                                maxlength="255">
                            <div class="invalid-feedback" id="nameError"></div>
                        </div>

                        <div class="mb-3">
                            <label for="actionDescription" class="form-label">Description</label>
                            <textarea class="form-control" id="actionDescription" name="description" rows="4" maxlength="1000"
                                placeholder="Enter action description (optional)"></textarea>
                            <div class="form-text">Maximum 1000 characters</div>
                            <div class="invalid-feedback" id="descriptionError"></div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">
                            <span class="spinner-border spinner-border-sm d-none" id="submitSpinner"
                                role="status"></span>
                            <span id="submitText">Save Action</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- View Action Modal -->
    <div class="modal fade" id="viewActionModal" tabindex="-1" aria-labelledby="viewActionModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="viewActionModalLabel">Action Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Contributing Factor:</label>
                                <p class="form-control-plaintext" id="viewActionContributing">-</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Status:</label>
                                <p class="form-control-plaintext" id="viewActionStatus">-</p>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Action Name:</label>
                        <p class="form-control-plaintext" id="viewActionName">-</p>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Full Name:</label>
                        <p class="form-control-plaintext text-muted" id="viewActionFullName">-</p>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Description:</label>
                        <p class="form-control-plaintext" id="viewActionDescription">-</p>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Created At:</label>
                                <p class="form-control-plaintext" id="viewActionCreated">-</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Updated At:</label>
                                <p class="form-control-plaintext" id="viewActionUpdated">-</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-warning" onclick="editActionFromView()">
                        <i class="ri-edit-line me-1"></i>Edit Action
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

            // Initialize filter
            initFilter();

            // Load stats
            loadStats();
        });

        let actionsTable;
        let isEditMode = false;

        function initDataTable() {
            actionsTable = $('#actionsTable').DataTable({
                processing: true,
                serverSide: true,
                responsive: true,
                ajax: {
                    url: "{{ route('admin.actions.data') }}",
                    type: 'GET',
                    data: function(d) {
                        d.contributing_id = $('#contributingFilter').val();
                    }
                },
                columns: [{
                        data: 'DT_RowIndex',
                        name: 'DT_RowIndex',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'contributing_name',
                        name: 'contributing.name'
                    },
                    {
                        data: 'name',
                        name: 'name'
                    },
                    {
                        data: 'description_short',
                        name: 'description'
                    },
                    {
                        data: 'status',
                        name: 'is_active'
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
                    [5, 'desc']
                ] // Order by created_at desc
            });
        }

        function initForm() {
            $('#actionForm').on('submit', function(e) {
                e.preventDefault();
                submitAction();
            });

            // Reset form when modal is closed
            $('#actionModal').on('hidden.bs.modal', function() {
                resetForm();
            });
        }

        function initFilter() {
            $('#contributingFilter').on('change', function() {
                actionsTable.ajax.reload();
                loadStats();
            });
        }

        function loadStats() {
            // This would typically load from an API endpoint
            // For now, we'll use placeholder values
            $('#totalActions').text('...');
            $('#activeActions').text('...');
        }

        function createAction() {
            isEditMode = false;
            $('#actionModalLabel').text('Add New Action');
            $('#submitText').text('Save Action');
            resetForm();
            $('#actionModal').modal('show');
        }

        function editAction(id) {
            isEditMode = true;
            $('#actionModalLabel').text('Edit Action');
            $('#submitText').text('Update Action');

            // Show loading state
            showFormLoading(true);
            $('#actionModal').modal('show');

            // Fetch action data
            $.ajax({
                url: `{{ url('/') }}/admin/actions/${id}`,
                type: 'GET',
                success: function(response) {
                    if (response.success) {
                        const action = response.data;
                        $('#actionId').val(action.id);
                        $('#actionContributing').val(action.contributing_id);
                        $('#actionName').val(action.name);
                        $('#actionDescription').val(action.description);
                        $('#actionStatus').val(action.is_active ? '1' : '0');
                    } else {
                        showAlert('error', 'Error', response.message);
                    }
                },
                error: function(xhr) {
                    const response = JSON.parse(xhr.responseText);
                    showAlert('error', 'Error', response.message || 'Failed to load action data');
                },
                complete: function() {
                    showFormLoading(false);
                }
            });
        }

        function viewAction(id) {
            // Show loading state
            $('#viewActionName').text('Loading...');
            $('#viewActionModal').modal('show');

            // Fetch action data
            $.ajax({
                url: `{{ url('/') }}/admin/actions/${id}`,
                type: 'GET',
                success: function(response) {
                    if (response.success) {
                        const action = response.data;
                        $('#viewActionContributing').text(action.contributing?.name || '-');
                        $('#viewActionName').text(action.name);
                        $('#viewActionFullName').text(action.full_name || '-');
                        $('#viewActionDescription').text(action.description || '-');
                        $('#viewActionStatus').html(
                            `<span class="badge bg-${action.is_active ? 'success' : 'danger'}">
                        ${action.is_active ? 'Active' : 'Inactive'}
                    </span>`
                        );
                        $('#viewActionCreated').text(formatDateTime(action.created_at));
                        $('#viewActionUpdated').text(formatDateTime(action.updated_at));

                        // Store ID for potential edit action
                        $('#viewActionModal').data('action-id', action.id);
                    } else {
                        showAlert('error', 'Error', response.message);
                        $('#viewActionModal').modal('hide');
                    }
                },
                error: function(xhr) {
                    const response = JSON.parse(xhr.responseText);
                    showAlert('error', 'Error', response.message || 'Failed to load action data');
                    $('#viewActionModal').modal('hide');
                }
            });
        }

        function editActionFromView() {
            const actionId = $('#viewActionModal').data('action-id');
            $('#viewActionModal').modal('hide');

            // Small delay to ensure modal is closed before opening edit modal
            setTimeout(() => {
                editAction(actionId);
            }, 300);
        }

        function deleteAction(id) {
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
                url: `{{ url('/') }}/admin/actions/${id}?_method=delete`,
                type: 'POST',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    if (response.success) {
                        showAlert('success', 'Deleted!', response.message);
                        actionsTable.ajax.reload();
                        loadStats();
                    } else {
                        showAlert('error', 'Error', response.message);
                    }
                },
                error: function(xhr) {
                    const response = JSON.parse(xhr.responseText);
                    showAlert('error', 'Error', response.message || 'Failed to delete action');
                }
            });
        }

        function submitAction() {
            // Clear previous errors
            clearFormErrors();

            const formData = new FormData($('#actionForm')[0]);
            let url = isEditMode ? `{{ url('/') }}/admin/actions/${$('#actionId').val()}` : '{{ url('/') }}/admin/actions';
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
                        $('#actionModal').modal('hide');
                        actionsTable.ajax.reload();
                        loadStats();
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
                        showAlert('error', 'Error', response.message || 'Failed to save action');
                    }
                },
                complete: function() {
                    showFormLoading(false);
                }
            });
        }

        function resetForm() {
            $('#actionForm')[0].reset();
            $('#actionId').val('');
            clearFormErrors();
            showFormLoading(false);
        }

        function showFormLoading(show) {
            if (show) {
                $('#submitSpinner').removeClass('d-none');
                $('#actionForm button[type="submit"]').prop('disabled', true);
            } else {
                $('#submitSpinner').addClass('d-none');
                $('#actionForm button[type="submit"]').prop('disabled', false);
            }
        }

        function clearFormErrors() {
            $('.is-invalid').removeClass('is-invalid');
            $('.invalid-feedback').text('');
        }

        function displayFormErrors(errors) {
            $.each(errors, function(field, messages) {
                let input;
                if (field === 'contributing_id') {
                    input = $(`#actionContributing`);
                } else {
                    input = $(`#action${field.charAt(0).toUpperCase() + field.slice(1)}`);
                }
                const errorDiv = $(`#${field}Error`);

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
