@extends('admin.layouts')

@section('title', 'Location Management')

@section('content')
    <div class="page-content">
        <!-- Page Title -->
        <div class="page-title-head d-flex align-items-center gap-2">
            <div class="flex-grow-1">
                <h4 class="fs-18 fw-bold mb-0">Location Management</h4>
            </div>
            <div class="text-end">
                <ol class="breadcrumb m-0 py-0 fs-13">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard.index') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="javascript: void(0);">Master Data</a></li>
                    <li class="breadcrumb-item active">Locations</li>
                </ol>
            </div>
        </div>

        <div class="page-container">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div
                            class="card-header border-bottom border-dashed d-flex align-items-center justify-content-between">
                            <h4 class="header-title mb-0">Locations List</h4>
                            <button type="button" class="btn btn-primary" onclick="createLocation()">
                                <i class="ri-add-line me-1"></i>Add New Location
                            </button>
                        </div>

                        <div class="card-body">
                            <div class="table-responsive">
                                <table id="locationsTable" class="table table-striped table-bordered dt-responsive nowrap"
                                    style="width:100%">
                                    <thead class="table-dark">
                                        <tr>
                                            <th width="5%">#</th>
                                            <th width="20%">Name</th>
                                            <th width="25%">Description</th>
                                            <th width="20%">Location Info</th>
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

    <!-- Create/Edit Location Modal -->
    <div class="modal fade" id="locationModal" tabindex="-1" aria-labelledby="locationModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <form id="locationForm">
                    <div class="modal-header">
                        <h5 class="modal-title" id="locationModalLabel">Add New Location</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" id="locationId" name="id">

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="locationName" class="form-label">Location Name <span
                                            class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="locationName" name="name" required
                                        maxlength="255">
                                    <div class="invalid-feedback" id="nameError"></div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="locationStatus" class="form-label">Status <span
                                            class="text-danger">*</span></label>
                                    <select class="form-select" id="locationStatus" name="is_active" required>
                                        <option value="">Select Status</option>
                                        <option value="1">Active</option>
                                        <option value="0">Inactive</option>
                                    </select>
                                    <div class="invalid-feedback" id="statusError"></div>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="locationDescription" class="form-label">Description</label>
                            <textarea class="form-control" id="locationDescription" name="description" rows="3" maxlength="1000"
                                placeholder="Enter location description (optional)"></textarea>
                            <div class="form-text">Maximum 1000 characters</div>
                            <div class="invalid-feedback" id="descriptionError"></div>
                        </div>

                        <div class="mb-3">
                            <label for="locationAddress" class="form-label">Address</label>
                            <textarea class="form-control" id="locationAddress" name="address" rows="2" maxlength="500"
                                placeholder="Enter complete address"></textarea>
                            <div class="form-text">Maximum 500 characters</div>
                            <div class="invalid-feedback" id="addressError"></div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="locationCity" class="form-label">City</label>
                                    <input type="text" class="form-control" id="locationCity" name="city" maxlength="100"
                                        placeholder="Enter city name">
                                    <div class="invalid-feedback" id="cityError"></div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="locationProvince" class="form-label">Province</label>
                                    <input type="text" class="form-control" id="locationProvince" name="province" maxlength="100"
                                        placeholder="Enter province name">
                                    <div class="invalid-feedback" id="provinceError"></div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="locationPostalCode" class="form-label">Postal Code</label>
                                    <input type="text" class="form-control" id="locationPostalCode" name="postal_code" maxlength="10"
                                        placeholder="Enter postal code">
                                    <div class="invalid-feedback" id="postalCodeError"></div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="locationLatitude" class="form-label">Latitude</label>
                                    <input type="number" class="form-control" id="locationLatitude" name="latitude"
                                        step="0.00000001" min="-90" max="90" placeholder="e.g. -6.2088">
                                    <div class="invalid-feedback" id="latitudeError"></div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="locationLongitude" class="form-label">Longitude</label>
                                    <input type="number" class="form-control" id="locationLongitude" name="longitude"
                                        step="0.00000001" min="-180" max="180" placeholder="e.g. 106.8456">
                                    <div class="invalid-feedback" id="longitudeError"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">
                            <span class="spinner-border spinner-border-sm d-none" id="submitSpinner" role="status"></span>
                            <span id="submitText">Save Location</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- View Location Modal -->
    <div class="modal fade" id="viewLocationModal" tabindex="-1" aria-labelledby="viewLocationModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="viewLocationModalLabel">Location Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Location Name:</label>
                                <p class="form-control-plaintext" id="viewLocationName">-</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Status:</label>
                                <p class="form-control-plaintext" id="viewLocationStatus">-</p>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Description:</label>
                        <p class="form-control-plaintext" id="viewLocationDescription">-</p>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Address:</label>
                        <p class="form-control-plaintext" id="viewLocationAddress">-</p>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold">City:</label>
                                <p class="form-control-plaintext" id="viewLocationCity">-</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Province:</label>
                                <p class="form-control-plaintext" id="viewLocationProvince">-</p>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Postal Code:</label>
                                <p class="form-control-plaintext" id="viewLocationPostalCode">-</p>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Latitude:</label>
                                <p class="form-control-plaintext" id="viewLocationLatitude">-</p>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Longitude:</label>
                                <p class="form-control-plaintext" id="viewLocationLongitude">-</p>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Created At:</label>
                                <p class="form-control-plaintext" id="viewLocationCreated">-</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Updated At:</label>
                                <p class="form-control-plaintext" id="viewLocationUpdated">-</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-warning" onclick="editLocationFromView()">
                        <i class="ri-edit-line me-1"></i>Edit Location
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

        let locationsTable;
        let isEditMode = false;

        function initDataTable() {
            locationsTable = $('#locationsTable').DataTable({
                processing: true,
                serverSide: true,
                responsive: true,
                ajax: {
                    url: "{{ route('admin.locations.data') }}",
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
                        data: 'location_info',
                        name: 'city'
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
            $('#locationForm').on('submit', function(e) {
                e.preventDefault();
                submitLocation();
            });

            // Reset form when modal is closed
            $('#locationModal').on('hidden.bs.modal', function() {
                resetForm();
            });
        }

        function createLocation() {
            isEditMode = false;
            $('#locationModalLabel').text('Add New Location');
            $('#submitText').text('Save Location');
            resetForm();
            $('#locationModal').modal('show');
        }

        function editLocation(id) {
            isEditMode = true;
            $('#locationModalLabel').text('Edit Location');
            $('#submitText').text('Update Location');

            // Show loading state
            showFormLoading(true);
            $('#locationModal').modal('show');

            // Fetch location data
            $.ajax({
                url: `{{ url('/') }}/admin/locations/${id}`,
                type: 'GET',
                success: function(response) {
                    if (response.success) {
                        const location = response.data;
                        $('#locationId').val(location.id);
                        $('#locationName').val(location.name);
                        $('#locationDescription').val(location.description);
                        $('#locationAddress').val(location.address);
                        $('#locationCity').val(location.city);
                        $('#locationProvince').val(location.province);
                        $('#locationPostalCode').val(location.postal_code);
                        $('#locationLatitude').val(location.latitude);
                        $('#locationLongitude').val(location.longitude);
                        $('#locationStatus').val(location.is_active ? '1' : '0');
                    } else {
                        showAlert('error', 'Error', response.message);
                    }
                },
                error: function(xhr) {
                    const response = JSON.parse(xhr.responseText);
                    showAlert('error', 'Error', response.message || 'Failed to load location data');
                },
                complete: function() {
                    showFormLoading(false);
                }
            });
        }

        function viewLocation(id) {
            // Show loading state
            $('#viewLocationName').text('Loading...');
            $('#viewLocationModal').modal('show');

            // Fetch location data
            $.ajax({
                url: `{{ url('/') }}/admin/locations/${id}`,
                type: 'GET',
                success: function(response) {
                    if (response.success) {
                        const location = response.data;
                        $('#viewLocationName').text(location.name);
                        $('#viewLocationDescription').text(location.description || '-');
                        $('#viewLocationAddress').text(location.address || '-');
                        $('#viewLocationCity').text(location.city || '-');
                        $('#viewLocationProvince').text(location.province || '-');
                        $('#viewLocationPostalCode').text(location.postal_code || '-');
                        $('#viewLocationLatitude').text(location.latitude || '-');
                        $('#viewLocationLongitude').text(location.longitude || '-');
                        $('#viewLocationStatus').html(
                            `<span class="badge bg-${location.is_active ? 'success' : 'danger'}">
                        ${location.is_active ? 'Active' : 'Inactive'}
                    </span>`
                        );
                        $('#viewLocationCreated').text(formatDateTime(location.created_at));
                        $('#viewLocationUpdated').text(formatDateTime(location.updated_at));

                        // Store ID for potential edit action
                        $('#viewLocationModal').data('location-id', location.id);
                    } else {
                        showAlert('error', 'Error', response.message);
                        $('#viewLocationModal').modal('hide');
                    }
                },
                error: function(xhr) {
                    const response = JSON.parse(xhr.responseText);
                    showAlert('error', 'Error', response.message || 'Failed to load location data');
                    $('#viewLocationModal').modal('hide');
                }
            });
        }

        function editLocationFromView() {
            const locationId = $('#viewLocationModal').data('location-id');
            $('#viewLocationModal').modal('hide');

            // Small delay to ensure modal is closed before opening edit modal
            setTimeout(() => {
                editLocation(locationId);
            }, 300);
        }

        function deleteLocation(id) {
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
                url: `{{ url('/') }}/admin/locations/${id}?_method=delete`,
                type: 'POST',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    if (response.success) {
                        showAlert('success', 'Deleted!', response.message);
                        locationsTable.ajax.reload();
                    } else {
                        showAlert('error', 'Error', response.message);
                    }
                },
                error: function(xhr) {
                    const response = JSON.parse(xhr.responseText);
                    showAlert('error', 'Error', response.message || 'Failed to delete location');
                }
            });
        }

        function submitLocation() {
            // Clear previous errors
            clearFormErrors();

            const formData = new FormData($('#locationForm')[0]);
            let url = isEditMode ? `{{ url('/') }}/admin/locations/${$('#locationId').val()}` : '{{ url('/') }}/admin/locations';
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
                        $('#locationModal').modal('hide');
                        locationsTable.ajax.reload();
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
                        showAlert('error', 'Error', response.message || 'Failed to save location');
                    }
                },
                complete: function() {
                    showFormLoading(false);
                }
            });
        }

        function resetForm() {
            $('#locationForm')[0].reset();
            $('#locationId').val('');
            clearFormErrors();
            showFormLoading(false);
        }

        function showFormLoading(show) {
            if (show) {
                $('#submitSpinner').removeClass('d-none');
                $('#locationForm button[type="submit"]').prop('disabled', true);
            } else {
                $('#submitSpinner').addClass('d-none');
                $('#locationForm button[type="submit"]').prop('disabled', false);
            }
        }

        function clearFormErrors() {
            $('.is-invalid').removeClass('is-invalid');
            $('.invalid-feedback').text('');
        }

        function displayFormErrors(errors) {
            $.each(errors, function(field, messages) {
                const input = $(`#location${field.charAt(0).toUpperCase() + field.slice(1)}`);
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