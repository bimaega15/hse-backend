@extends('admin.layouts')

@section('title', 'Contributing Factors Management')

@section('content')
    <div class="page-content">
        <!-- Page Title -->
        <div class="page-title-head d-flex align-items-center gap-2">
            <div class="flex-grow-1">
                <h4 class="fs-18 fw-bold mb-0">Contributing Factors Management</h4>
            </div>
            <div class="text-end">
                <ol class="breadcrumb m-0 py-0 fs-13">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard.index') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="javascript: void(0);">Master Data</a></li>
                    <li class="breadcrumb-item"><a href="javascript: void(0);">Risk Management</a></li>
                    <li class="breadcrumb-item active">Contributing Factors</li>
                </ol>
            </div>
        </div>

        <div class="page-container">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div
                            class="card-header border-bottom border-dashed d-flex align-items-center justify-content-between">
                            <h4 class="header-title mb-0">Contributing Factors List</h4>
                            <button type="button" class="btn btn-primary" onclick="createContributing()">
                                <i class="ri-add-line me-1"></i>Add New Contributing Factor
                            </button>
                        </div>

                        <div class="card-body">
                            <div class="table-responsive">
                                <table id="contributingTable"
                                    class="table table-striped table-bordered dt-responsive nowrap" style="width:100%">
                                    <thead class="table-dark">
                                        <tr>
                                            <th width="5%">#</th>
                                            <th width="15%">Category</th>
                                            <th width="20%">Name</th>
                                            <th width="25%">Description</th>
                                            <th width="10%">Actions Count</th>
                                            <th width="10%">Status</th>
                                            <th width="10%">Created At</th>
                                            <th width="15%">Actions</th>
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

    <!-- Create/Edit Contributing Factor Modal -->
    <div class="modal fade" id="contributingModal" tabindex="-1" aria-labelledby="contributingModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form id="contributingForm">
                    <div class="modal-header">
                        <h5 class="modal-title" id="contributingModalLabel">Add New Contributing Factor</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" id="contributingId" name="id">

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="contributingCategory" class="form-label">Category <span
                                            class="text-danger">*</span></label>
                                    <select class="form-select" id="contributingCategory" name="category_id" required>
                                        <option value="">Select Category</option>
                                        @foreach ($categories as $category)
                                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                                        @endforeach
                                    </select>
                                    <div class="invalid-feedback" id="categoryIdError"></div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="contributingStatus" class="form-label">Status <span
                                            class="text-danger">*</span></label>
                                    <select class="form-select" id="contributingStatus" name="is_active" required>
                                        <option value="">Select Status</option>
                                        <option value="1">Active</option>
                                        <option value="0">Inactive</option>
                                    </select>
                                    <div class="invalid-feedback" id="isActiveError"></div>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="contributingName" class="form-label">Contributing Factor Name <span
                                    class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="contributingName" name="name" required
                                maxlength="255">
                            <div class="invalid-feedback" id="nameError"></div>
                        </div>

                        <div class="mb-3">
                            <label for="contributingDescription" class="form-label">Description</label>
                            <textarea class="form-control" id="contributingDescription" name="description" rows="4" maxlength="1000"
                                placeholder="Enter contributing factor description (optional)"></textarea>
                            <div class="form-text">Maximum 1000 characters</div>
                            <div class="invalid-feedback" id="descriptionError"></div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">
                            <span class="spinner-border spinner-border-sm d-none" id="submitSpinner"
                                role="status"></span>
                            <span id="submitText">Save Contributing Factor</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- View Contributing Factor Modal -->
    <div class="modal fade" id="viewContributingModal" tabindex="-1" aria-labelledby="viewContributingModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="viewContributingModalLabel">Contributing Factor Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Category:</label>
                                <p class="form-control-plaintext" id="viewContributingCategory">-</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Status:</label>
                                <p class="form-control-plaintext" id="viewContributingStatus">-</p>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Contributing Factor Name:</label>
                        <p class="form-control-plaintext" id="viewContributingName">-</p>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Description:</label>
                        <p class="form-control-plaintext" id="viewContributingDescription">-</p>
                    </div>

                    <div class="row">
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Total Actions:</label>
                                <p class="form-control-plaintext" id="viewContributingActionsCount">-</p>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Active Actions:</label>
                                <p class="form-control-plaintext" id="viewContributingActiveActionsCount">-</p>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Inactive Actions:</label>
                                <p class="form-control-plaintext" id="viewContributingInactiveActionsCount">-</p>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Created At:</label>
                                <p class="form-control-plaintext" id="viewContributingCreated">-</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Updated At:</label>
                                <p class="form-control-plaintext" id="viewContributingUpdated">-</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-warning" onclick="editContributingFromView()">
                        <i class="ri-edit-line me-1"></i>Edit Contributing Factor
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
        });

        let contributingTable;
        let isEditMode = false;

        function initDataTable() {
            contributingTable = $('#contributingTable').DataTable({
                processing: true,
                serverSide: true,
                responsive: true,
                ajax: {
                    url: "{{ route('admin.contributing.data') }}",
                    type: 'GET'
                },
                columns: [{
                        data: 'DT_RowIndex',
                        name: 'DT_RowIndex',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'category_name',
                        name: 'category_name'
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
                        data: 'actions_count',
                        name: 'actions_count',
                        render: function(data, type, row) {
                            return `<span class="badge bg-info">${data || 0}</span>`;
                        }
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
                    [6, 'desc']
                ] // Order by created_at desc
            });
        }

        function initForm() {
            $('#contributingForm').on('submit', function(e) {
                e.preventDefault();
                submitContributing();
            });

            // Reset form when modal is closed
            $('#contributingModal').on('hidden.bs.modal', function() {
                resetForm();
            });
        }

        function createContributing() {
            isEditMode = false;
            $('#contributingModalLabel').text('Add New Contributing Factor');
            $('#submitText').text('Save Contributing Factor');
            resetForm();
            $('#contributingModal').modal('show');
        }

        function editContributing(id) {
            isEditMode = true;
            $('#contributingModalLabel').text('Edit Contributing Factor');
            $('#submitText').text('Update Contributing Factor');

            // Show loading state
            showFormLoading(true);
            $('#contributingModal').modal('show');

            // Fetch contributing factor data
            $.ajax({
                url: `{{ url('/') }}/admin/contributing/${id}`,
                type: 'GET',
                success: function(response) {
                    if (response.success) {
                        const contributing = response.data;
                        $('#contributingId').val(contributing.id);
                        $('#contributingCategory').val(contributing.category_id);
                        $('#contributingName').val(contributing.name);
                        $('#contributingDescription').val(contributing.description);
                        $('#contributingStatus').val(contributing.is_active ? '1' : '0');
                    } else {
                        showAlert('error', 'Error', response.message);
                    }
                },
                error: function(xhr) {
                    const response = JSON.parse(xhr.responseText);
                    showAlert('error', 'Error', response.message || 'Failed to load contributing factor data');
                },
                complete: function() {
                    showFormLoading(false);
                }
            });
        }

        function viewContributing(id) {
            // Show loading state
            $('#viewContributingName').text('Loading...');
            $('#viewContributingModal').modal('show');

            // Fetch contributing factor data
            $.ajax({
                url: `{{ url('/') }}/admin/contributing/${id}`,
                type: 'GET',
                success: function(response) {
                    if (response.success) {
                        const contributing = response.data;
                        $('#viewContributingCategory').text(contributing.category ? contributing.category.name :
                            '-');
                        $('#viewContributingName').text(contributing.name);
                        $('#viewContributingDescription').text(contributing.description || '-');
                        $('#viewContributingStatus').html(
                            `<span class="badge bg-${contributing.is_active ? 'success' : 'danger'}">
                        ${contributing.is_active ? 'Active' : 'Inactive'}
                    </span>`
                        );
                        $('#viewContributingActionsCount').text(contributing.actions_count || 0);
                        $('#viewContributingActiveActionsCount').text(contributing.active_actions_count || 0);
                        $('#viewContributingInactiveActionsCount').text((contributing.actions_count || 0) - (
                            contributing.active_actions_count || 0));
                        $('#viewContributingCreated').text(formatDateTime(contributing.created_at));
                        $('#viewContributingUpdated').text(formatDateTime(contributing.updated_at));

                        // Store ID for potential edit action
                        $('#viewContributingModal').data('contributing-id', contributing.id);
                    } else {
                        showAlert('error', 'Error', response.message);
                        $('#viewContributingModal').modal('hide');
                    }
                },
                error: function(xhr) {
                    const response = JSON.parse(xhr.responseText);
                    showAlert('error', 'Error', response.message || 'Failed to load contributing factor data');
                    $('#viewContributingModal').modal('hide');
                }
            });
        }

        function editContributingFromView() {
            const contributingId = $('#viewContributingModal').data('contributing-id');
            $('#viewContributingModal').modal('hide');

            // Small delay to ensure modal is closed before opening edit modal
            setTimeout(() => {
                editContributing(contributingId);
            }, 300);
        }

        function deleteContributing(id) {
            Swal.fire({
                title: 'Are you sure?',
                text: "This action cannot be undone! All related actions will also be deleted.",
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
                url: `{{ url('/') }}/admin/contributing/${id}?_method=delete`,
                type: 'POST',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    if (response.success) {
                        showAlert('success', 'Deleted!', response.message);
                        contributingTable.ajax.reload();
                    } else {
                        showAlert('error', 'Error', response.message);
                    }
                },
                error: function(xhr) {
                    const response = JSON.parse(xhr.responseText);
                    showAlert('error', 'Error', response.message || 'Failed to delete contributing factor');
                }
            });
        }

        function submitContributing() {
            // Clear previous errors
            clearFormErrors();

            const formData = new FormData($('#contributingForm')[0]);
            let url = isEditMode ? `{{ url('/') }}/admin/contributing/${$('#contributingId').val()}` : '{{ url('/') }}/admin/contributing';
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
                        $('#contributingModal').modal('hide');
                        contributingTable.ajax.reload();
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
                        showAlert('error', 'Error', response.message || 'Failed to save contributing factor');
                    }
                },
                complete: function() {
                    showFormLoading(false);
                }
            });
        }

        function resetForm() {
            $('#contributingForm')[0].reset();
            $('#contributingId').val('');
            clearFormErrors();
            showFormLoading(false);
        }

        function showFormLoading(show) {
            if (show) {
                $('#submitSpinner').removeClass('d-none');
                $('#contributingForm button[type="submit"]').prop('disabled', true);
            } else {
                $('#submitSpinner').addClass('d-none');
                $('#contributingForm button[type="submit"]').prop('disabled', false);
            }
        }

        function clearFormErrors() {
            $('.is-invalid').removeClass('is-invalid');
            $('.invalid-feedback').text('');
        }

        function displayFormErrors(errors) {
            $.each(errors, function(field, messages) {
                const input = $(`#contributing${field.charAt(0).toUpperCase() + field.slice(1)}`);
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
