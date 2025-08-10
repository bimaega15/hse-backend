@extends('admin.layouts')

@section('title', 'Categories Management')

@section('content')
    <div class="page-content">
        <!-- Page Title -->
        <div class="page-title-head d-flex align-items-center gap-2">
            <div class="flex-grow-1">
                <h4 class="fs-18 fw-bold mb-0">Categories Management</h4>
            </div>
            <div class="text-end">
                <ol class="breadcrumb m-0 py-0 fs-13">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard.index') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="javascript: void(0);">Master Data</a></li>
                    <li class="breadcrumb-item active">Categories</li>
                </ol>
            </div>
        </div>

        <div class="page-container">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div
                            class="card-header border-bottom border-dashed d-flex align-items-center justify-content-between">
                            <h4 class="header-title mb-0">Categories List</h4>
                            <button type="button" class="btn btn-primary" onclick="createCategory()">
                                <i class="ri-add-line me-1"></i>Add New Category
                            </button>
                        </div>

                        <div class="card-body">
                            <div class="table-responsive">
                                <table id="categoriesTable" class="table table-striped table-bordered dt-responsive nowrap"
                                    style="width:100%">
                                    <thead class="table-dark">
                                        <tr>
                                            <th width="5%">#</th>
                                            <th width="20%">Name</th>
                                            <th width="35%">Description</th>
                                            <th width="10%">Status</th>
                                            <th width="15%">Created At</th>
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

    <!-- Create/Edit Category Modal -->
    <div class="modal fade" id="categoryModal" tabindex="-1" aria-labelledby="categoryModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form id="categoryForm">
                    <div class="modal-header">
                        <h5 class="modal-title" id="categoryModalLabel">Add New Category</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" id="categoryId" name="id">

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="categoryName" class="form-label">Category Name <span
                                            class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="categoryName" name="name" required
                                        maxlength="255">
                                    <div class="invalid-feedback" id="nameError"></div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="categoryStatus" class="form-label">Status <span
                                            class="text-danger">*</span></label>
                                    <select class="form-select" id="categoryStatus" name="is_active" required>
                                        <option value="">Select Status</option>
                                        <option value="1">Active</option>
                                        <option value="0">Inactive</option>
                                    </select>
                                    <div class="invalid-feedback" id="statusError"></div>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="categoryDescription" class="form-label">Description</label>
                            <textarea class="form-control" id="categoryDescription" name="description" rows="4" maxlength="1000"
                                placeholder="Enter category description (optional)"></textarea>
                            <div class="form-text">Maximum 1000 characters</div>
                            <div class="invalid-feedback" id="descriptionError"></div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">
                            <span class="spinner-border spinner-border-sm d-none" id="submitSpinner" role="status"></span>
                            <span id="submitText">Save Category</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- View Category Modal -->
    <div class="modal fade" id="viewCategoryModal" tabindex="-1" aria-labelledby="viewCategoryModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="viewCategoryModalLabel">Category Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Category Name:</label>
                                <p class="form-control-plaintext" id="viewCategoryName">-</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Status:</label>
                                <p class="form-control-plaintext" id="viewCategoryStatus">-</p>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Description:</label>
                        <p class="form-control-plaintext" id="viewCategoryDescription">-</p>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Created At:</label>
                                <p class="form-control-plaintext" id="viewCategoryCreated">-</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Updated At:</label>
                                <p class="form-control-plaintext" id="viewCategoryUpdated">-</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-warning" onclick="editCategoryFromView()">
                        <i class="ri-edit-line me-1"></i>Edit Category
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

        let categoriesTable;
        let isEditMode = false;

        function initDataTable() {
            categoriesTable = $('#categoriesTable').DataTable({
                processing: true,
                serverSide: true,
                responsive: true,
                ajax: {
                    url: "{{ route('admin.categories.data') }}",
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
                    [4, 'desc']
                ] // Order by created_at desc
            });
        }

        function initForm() {
            $('#categoryForm').on('submit', function(e) {
                e.preventDefault();
                submitCategory();
            });

            // Reset form when modal is closed
            $('#categoryModal').on('hidden.bs.modal', function() {
                resetForm();
            });
        }

        function createCategory() {
            isEditMode = false;
            $('#categoryModalLabel').text('Add New Category');
            $('#submitText').text('Save Category');
            resetForm();
            $('#categoryModal').modal('show');
        }

        function editCategory(id) {
            isEditMode = true;
            $('#categoryModalLabel').text('Edit Category');
            $('#submitText').text('Update Category');

            // Show loading state
            showFormLoading(true);
            $('#categoryModal').modal('show');

            // Fetch category data
            $.ajax({
                url: `/admin/categories/${id}`,
                type: 'GET',
                success: function(response) {
                    if (response.success) {
                        const category = response.data;
                        $('#categoryId').val(category.id);
                        $('#categoryName').val(category.name);
                        $('#categoryDescription').val(category.description);
                        $('#categoryStatus').val(category.is_active ? '1' : '0');
                    } else {
                        showAlert('error', 'Error', response.message);
                    }
                },
                error: function(xhr) {
                    const response = JSON.parse(xhr.responseText);
                    showAlert('error', 'Error', response.message || 'Failed to load category data');
                },
                complete: function() {
                    showFormLoading(false);
                }
            });
        }

        function viewCategory(id) {
            // Show loading state
            $('#viewCategoryName').text('Loading...');
            $('#viewCategoryModal').modal('show');

            // Fetch category data
            $.ajax({
                url: `/admin/categories/${id}`,
                type: 'GET',
                success: function(response) {
                    if (response.success) {
                        const category = response.data;
                        $('#viewCategoryName').text(category.name);
                        $('#viewCategoryDescription').text(category.description || '-');
                        $('#viewCategoryStatus').html(
                            `<span class="badge bg-${category.is_active ? 'success' : 'danger'}">
                        ${category.is_active ? 'Active' : 'Inactive'}
                    </span>`
                        );
                        $('#viewCategoryCreated').text(formatDateTime(category.created_at));
                        $('#viewCategoryUpdated').text(formatDateTime(category.updated_at));

                        // Store ID for potential edit action
                        $('#viewCategoryModal').data('category-id', category.id);
                    } else {
                        showAlert('error', 'Error', response.message);
                        $('#viewCategoryModal').modal('hide');
                    }
                },
                error: function(xhr) {
                    const response = JSON.parse(xhr.responseText);
                    showAlert('error', 'Error', response.message || 'Failed to load category data');
                    $('#viewCategoryModal').modal('hide');
                }
            });
        }

        function editCategoryFromView() {
            const categoryId = $('#viewCategoryModal').data('category-id');
            $('#viewCategoryModal').modal('hide');

            // Small delay to ensure modal is closed before opening edit modal
            setTimeout(() => {
                editCategory(categoryId);
            }, 300);
        }

        function deleteCategory(id) {
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
                url: `/admin/categories/${id}?_method=delete`,
                type: 'POST',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    if (response.success) {
                        showAlert('success', 'Deleted!', response.message);
                        categoriesTable.ajax.reload();
                    } else {
                        showAlert('error', 'Error', response.message);
                    }
                },
                error: function(xhr) {
                    const response = JSON.parse(xhr.responseText);
                    showAlert('error', 'Error', response.message || 'Failed to delete category');
                }
            });
        }

        function submitCategory() {
            // Clear previous errors
            clearFormErrors();

            const formData = new FormData($('#categoryForm')[0]);
            let url = isEditMode ? `/admin/categories/${$('#categoryId').val()}` : '/admin/categories';
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
                        $('#categoryModal').modal('hide');
                        categoriesTable.ajax.reload();
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
                        showAlert('error', 'Error', response.message || 'Failed to save category');
                    }
                },
                complete: function() {
                    showFormLoading(false);
                }
            });
        }

        function resetForm() {
            $('#categoryForm')[0].reset();
            $('#categoryId').val('');
            clearFormErrors();
            showFormLoading(false);
        }

        function showFormLoading(show) {
            if (show) {
                $('#submitSpinner').removeClass('d-none');
                $('#categoryForm button[type="submit"]').prop('disabled', true);
            } else {
                $('#submitSpinner').addClass('d-none');
                $('#categoryForm button[type="submit"]').prop('disabled', false);
            }
        }

        function clearFormErrors() {
            $('.is-invalid').removeClass('is-invalid');
            $('.invalid-feedback').text('');
        }

        function displayFormErrors(errors) {
            $.each(errors, function(field, messages) {
                const input = $(`#category${field.charAt(0).toUpperCase() + field.slice(1)}`);
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
