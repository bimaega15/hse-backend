@extends('admin.layouts')

@section('title', 'Activity Data')

@section('content')
    <div class="page-content">
        <!-- Page Title -->
        <div class="page-title-head d-flex align-items-center gap-2">
            <div class="flex-grow-1">
                <h4 class="fs-18 fw-bold mb-0">Activity Data</h4>
            </div>
            <div class="text-end">
                <ol class="breadcrumb m-0 py-0 fs-13">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard.index') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="javascript: void(0);">HSE Activity</a></li>
                    <li class="breadcrumb-item active">Activity Data</li>
                </ol>
            </div>
        </div>

        <div class="page-container">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header border-bottom border-dashed d-flex align-items-center justify-content-between">
                            <h4 class="header-title mb-0">Activity List</h4>
                            <button type="button" class="btn btn-primary" onclick="createActivity()">
                                <i class="ri-add-line me-1"></i>Add New Activity
                            </button>
                        </div>

                        <div class="card-body">
                            <div class="table-responsive">
                                <table id="activitiesTable" class="table table-striped table-bordered dt-responsive nowrap"
                                    style="width:100%">
                                    <thead class="table-dark">
                                        <tr>
                                            <th width="5%">#</th>
                                            <th width="25%">Name</th>
                                            <th width="40%">Description</th>
                                            <th width="10%">Status</th>
                                            <th width="12%">Created At</th>
                                            <th width="8%">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Create/Edit Activity Modal -->
    <div class="modal fade" id="activityModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form id="activityForm">
                    <div class="modal-header">
                        <h5 class="modal-title" id="activityModalLabel">Add New Activity</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" id="activityId" name="id">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="activityName" class="form-label">Activity Name <span
                                            class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="activityName" name="name" required
                                        maxlength="255" placeholder="mis. Toolbox Meeting">
                                    <div class="invalid-feedback" id="nameError"></div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="activityStatus" class="form-label">Status <span
                                            class="text-danger">*</span></label>
                                    <select class="form-select" id="activityStatus" name="is_active" required>
                                        <option value="">Select Status</option>
                                        <option value="1">Active</option>
                                        <option value="0">Inactive</option>
                                    </select>
                                    <div class="invalid-feedback" id="statusError"></div>
                                </div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="activityDescription" class="form-label">Description</label>
                            <textarea class="form-control" id="activityDescription" name="description" rows="4" maxlength="1000"
                                placeholder="Enter activity description (optional)"></textarea>
                            <div class="form-text">Maximum 1000 characters</div>
                            <div class="invalid-feedback" id="descriptionError"></div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">
                            <span class="spinner-border spinner-border-sm d-none" id="submitSpinner" role="status"></span>
                            <span id="submitText">Save Activity</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- View Activity Modal -->
    <div class="modal fade" id="viewActivityModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Activity Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Activity Name:</label>
                                <p class="form-control-plaintext" id="viewActivityName">-</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Status:</label>
                                <p class="form-control-plaintext" id="viewActivityStatus">-</p>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Description:</label>
                        <p class="form-control-plaintext" id="viewActivityDescription">-</p>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Created At:</label>
                                <p class="form-control-plaintext" id="viewActivityCreated">-</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Updated At:</label>
                                <p class="form-control-plaintext" id="viewActivityUpdated">-</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-warning" onclick="editActivityFromView()">
                        <i class="ri-edit-line me-1"></i>Edit Activity
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('jsSection')
    <script>
        let activitiesTable;
        let isEditMode = false;

        $(document).ready(function() {
            activitiesTable = $('#activitiesTable').DataTable({
                processing: true,
                serverSide: true,
                responsive: true,
                ajax: {
                    url: "{{ route('admin.activities.data') }}",
                    type: 'GET'
                },
                columns: [{
                        data: 'DT_RowIndex',
                        name: 'DT_RowIndex',
                        orderable: false,
                        searchable: false
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
                pageLength: 10,
                order: [
                    [4, 'desc']
                ]
            });

            $('#activityForm').on('submit', function(e) {
                e.preventDefault();
                submitActivity();
            });

            $('#activityModal').on('hidden.bs.modal', resetForm);
        });

        function createActivity() {
            isEditMode = false;
            $('#activityModalLabel').text('Add New Activity');
            $('#submitText').text('Save Activity');
            resetForm();
            $('#activityModal').modal('show');
        }

        function editActivity(id) {
            isEditMode = true;
            $('#activityModalLabel').text('Edit Activity');
            $('#submitText').text('Update Activity');
            showFormLoading(true);
            $('#activityModal').modal('show');

            $.get(`{{ url('/') }}/admin/activities/${id}`, function(res) {
                if (res.success) {
                    $('#activityId').val(res.data.id);
                    $('#activityName').val(res.data.name);
                    $('#activityDescription').val(res.data.description);
                    $('#activityStatus').val(res.data.is_active ? '1' : '0');
                } else {
                    showAlert('error', 'Error', res.message);
                }
            }).fail(function() {
                showAlert('error', 'Error', 'Failed to load activity data');
            }).always(function() {
                showFormLoading(false);
            });
        }

        function viewActivity(id) {
            $('#viewActivityName').text('Loading...');
            $('#viewActivityModal').modal('show');

            $.get(`{{ url('/') }}/admin/activities/${id}`, function(res) {
                if (res.success) {
                    const a = res.data;
                    $('#viewActivityName').text(a.name);
                    $('#viewActivityDescription').text(a.description || '-');
                    $('#viewActivityStatus').html(
                        `<span class="badge bg-${a.is_active ? 'success' : 'danger'}">${a.is_active ? 'Active' : 'Inactive'}</span>`
                    );
                    $('#viewActivityCreated').text(formatDateTime(a.created_at));
                    $('#viewActivityUpdated').text(formatDateTime(a.updated_at));
                    $('#viewActivityModal').data('activity-id', a.id);
                } else {
                    showAlert('error', 'Error', res.message);
                    $('#viewActivityModal').modal('hide');
                }
            }).fail(function() {
                showAlert('error', 'Error', 'Failed to load activity data');
                $('#viewActivityModal').modal('hide');
            });
        }

        function editActivityFromView() {
            const id = $('#viewActivityModal').data('activity-id');
            $('#viewActivityModal').modal('hide');
            setTimeout(() => editActivity(id), 300);
        }

        function deleteActivity(id) {
            Swal.fire({
                title: 'Are you sure?',
                text: "This action cannot be undone!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: `{{ url('/') }}/admin/activities/${id}?_method=delete`,
                        type: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        success: function(res) {
                            if (res.success) {
                                showAlert('success', 'Deleted!', res.message);
                                activitiesTable.ajax.reload();
                            } else {
                                showAlert('error', 'Error', res.message);
                            }
                        },
                        error: function(xhr) {
                            const res = xhr.responseJSON || {};
                            showAlert('error', 'Error', res.message || 'Failed to delete activity');
                        }
                    });
                }
            });
        }

        function submitActivity() {
            clearFormErrors();
            const formData = new FormData($('#activityForm')[0]);
            let url = isEditMode ? `{{ url('/') }}/admin/activities/${$('#activityId').val()}` :
                '{{ url('/') }}/admin/activities';
            if (isEditMode) {
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
                success: function(res) {
                    if (res.success) {
                        showAlert('success', 'Success!', res.message);
                        $('#activityModal').modal('hide');
                        activitiesTable.ajax.reload();
                        resetForm();
                    } else {
                        showAlert('error', 'Error', res.message);
                    }
                },
                error: function(xhr) {
                    if (xhr.status === 422) {
                        displayFormErrors(xhr.responseJSON.errors);
                    } else {
                        const res = xhr.responseJSON || {};
                        showAlert('error', 'Error', res.message || 'Failed to save activity');
                    }
                },
                complete: function() {
                    showFormLoading(false);
                }
            });
        }

        function resetForm() {
            $('#activityForm')[0].reset();
            $('#activityId').val('');
            clearFormErrors();
            showFormLoading(false);
        }

        function showFormLoading(show) {
            if (show) {
                $('#submitSpinner').removeClass('d-none');
                $('#activityForm button[type="submit"]').prop('disabled', true);
            } else {
                $('#submitSpinner').addClass('d-none');
                $('#activityForm button[type="submit"]').prop('disabled', false);
            }
        }

        function clearFormErrors() {
            $('.is-invalid').removeClass('is-invalid');
            $('.invalid-feedback').text('');
        }

        function displayFormErrors(errors) {
            $.each(errors, function(field, messages) {
                const input = $(`#activity${field.charAt(0).toUpperCase() + field.slice(1)}`);
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

        function formatDateTime(dt) {
            if (!dt) return '-';
            return new Date(dt).toLocaleDateString('en-GB', {
                day: '2-digit',
                month: 'short',
                year: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            });
        }
    </script>
@endpush
