@extends('admin.layouts')

@section('title', 'Banners Management')

@section('content')
    <div class="page-content">
        <!-- Page Title -->
        <div class="page-title-head d-flex align-items-center gap-2">
            <div class="flex-grow-1">
                <h4 class="fs-18 fw-bold mb-0">Banners Management</h4>
            </div>
            <div class="text-end">
                <ol class="breadcrumb m-0 py-0 fs-13">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="javascript: void(0);">Master Data</a></li>
                    <li class="breadcrumb-item active">Banners</li>
                </ol>
            </div>
        </div>

        <div class="page-container">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div
                            class="card-header border-bottom border-dashed d-flex align-items-center justify-content-between">
                            <h4 class="header-title mb-0">Banners List</h4>
                            <button type="button" class="btn btn-primary" onclick="createBanner()">
                                <i class="ri-add-line me-1"></i>Add New Banner
                            </button>
                        </div>

                        <div class="card-body">
                            <div class="table-responsive">
                                <table id="bannersTable" class="table table-striped table-bordered dt-responsive nowrap"
                                    style="width:100%">
                                    <thead class="table-dark">
                                        <tr>
                                            <th width="5%">#</th>
                                            <th width="15%">Preview</th>
                                            <th width="10%">Image</th>
                                            <th width="20%">Title</th>
                                            <th width="25%">Description</th>
                                            <th width="8%">Order</th>
                                            <th width="8%">Status</th>
                                            <th width="9%">Actions</th>
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

    <!-- Create/Edit Banner Modal -->
    <div class="modal fade" id="bannerModal" tabindex="-1" aria-labelledby="bannerModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <form id="bannerForm" enctype="multipart/form-data">
                    <div class="modal-header">
                        <h5 class="modal-title" id="bannerModalLabel">Add New Banner</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" id="bannerId" name="id">

                        <div class="row">
                            <div class="col-md-8">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="bannerTitle" class="form-label">Banner Title <span
                                                    class="text-danger">*</span></label>
                                            <input type="text" class="form-control" id="bannerTitle" name="title"
                                                required maxlength="255" placeholder="Enter banner title">
                                            <div class="invalid-feedback" id="titleError"></div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="bannerIcon" class="form-label">Icon Class</label>
                                            <input type="text" class="form-control" id="bannerIcon" name="icon"
                                                maxlength="100" placeholder="e.g., ri-home-line, fas fa-home">
                                            <div class="form-text">FontAwesome or Remix Icon class</div>
                                            <div class="invalid-feedback" id="iconError"></div>
                                        </div>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="bannerDescription" class="form-label">Description <span
                                            class="text-danger">*</span></label>
                                    <textarea class="form-control" id="bannerDescription" name="description" rows="4" maxlength="1000"
                                        placeholder="Enter banner description" required></textarea>
                                    <div class="form-text">Maximum 1000 characters</div>
                                    <div class="invalid-feedback" id="descriptionError"></div>
                                </div>

                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label for="bannerBackgroundColor" class="form-label">Background Color</label>
                                            <input type="color" class="form-control form-control-color"
                                                id="bannerBackgroundColor" name="background_color" value="#ff9500">
                                            <div class="invalid-feedback" id="backgroundcolorError"></div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label for="bannerTextColor" class="form-label">Text Color</label>
                                            <input type="color" class="form-control form-control-color"
                                                id="bannerTextColor" name="text_color" value="#ffffff">
                                            <div class="invalid-feedback" id="textcolorError"></div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label for="bannerSortOrder" class="form-label">Sort Order</label>
                                            <input type="number" class="form-control" id="bannerSortOrder"
                                                name="sort_order" min="0" placeholder="0">
                                            <div class="form-text">Lower numbers appear first</div>
                                            <div class="invalid-feedback" id="sortorderError"></div>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="bannerImage" class="form-label">Banner Image</label>
                                            <input type="file" class="form-control" id="bannerImage" name="image"
                                                accept="image/jpeg,image/png,image/jpg,image/gif">
                                            <div class="form-text">Max 2MB, formats: jpeg, png, jpg, gif</div>
                                            <div class="invalid-feedback" id="imageError"></div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="bannerStatus" class="form-label">Status <span
                                                    class="text-danger">*</span></label>
                                            <select class="form-select" id="bannerStatus" name="is_active" required>
                                                <option value="">Select Status</option>
                                                <option value="1">Active</option>
                                                <option value="0">Inactive</option>
                                            </select>
                                            <div class="invalid-feedback" id="isactiveError"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="banner-preview-container">
                                    <label class="form-label fw-bold">Live Preview:</label>
                                    <div id="bannerPreview" class="border rounded p-3 text-center"
                                        style="background-color: #ff9500; color: #ffffff; min-height: 120px; display: flex; flex-direction: column; justify-content: center;">
                                        <div id="previewIcon" class="mb-2">
                                            <i class="ri-home-line" style="font-size: 2rem;"></i>
                                        </div>
                                        <h5 id="previewTitle" class="mb-2">Banner Title</h5>
                                        <p id="previewDescription" class="mb-0 small">Banner description will appear here
                                        </p>
                                    </div>

                                    <div id="imagePreviewContainer" class="mt-3" style="display: none;">
                                        <label class="form-label fw-bold">Image Preview:</label>
                                        <img id="imagePreview" src="" alt="Banner Image"
                                            class="img-fluid rounded border"
                                            style="max-height: 150px; width: 100%; object-fit: cover;">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">
                            <span class="spinner-border spinner-border-sm d-none" id="submitSpinner"
                                role="status"></span>
                            <span id="submitText">Save Banner</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- View Banner Modal -->
    <div class="modal fade" id="viewBannerModal" tabindex="-1" aria-labelledby="viewBannerModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="viewBannerModalLabel">Banner Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-8">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label fw-bold">Banner Title:</label>
                                        <p class="form-control-plaintext" id="viewBannerTitle">-</p>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label fw-bold">Status:</label>
                                        <p class="form-control-plaintext" id="viewBannerStatus">-</p>
                                    </div>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold">Description:</label>
                                <p class="form-control-plaintext" id="viewBannerDescription">-</p>
                            </div>

                            <div class="row">
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label class="form-label fw-bold">Icon:</label>
                                        <p class="form-control-plaintext" id="viewBannerIcon">-</p>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label class="form-label fw-bold">Sort Order:</label>
                                        <p class="form-control-plaintext" id="viewBannerOrder">-</p>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label class="form-label fw-bold">Colors:</label>
                                        <div id="viewBannerColors">-</div>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label fw-bold">Created At:</label>
                                        <p class="form-control-plaintext" id="viewBannerCreated">-</p>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label fw-bold">Updated At:</label>
                                        <p class="form-control-plaintext" id="viewBannerUpdated">-</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="banner-preview-view">
                                <label class="form-label fw-bold">Banner Preview:</label>
                                <div id="viewBannerPreview" class="border rounded p-3 text-center"
                                    style="min-height: 120px; display: flex; flex-direction: column; justify-content: center;">
                                    <div id="viewPreviewIcon" class="mb-2"></div>
                                    <h5 id="viewPreviewTitle" class="mb-2">-</h5>
                                    <p id="viewPreviewDescription" class="mb-0 small">-</p>
                                </div>

                                <div id="viewImageContainer" class="mt-3" style="display: none;">
                                    <label class="form-label fw-bold">Banner Image:</label>
                                    <img id="viewBannerImage" src="" alt="Banner Image"
                                        class="img-fluid rounded border"
                                        style="max-height: 200px; width: 100%; object-fit: cover;">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-warning" onclick="editBannerFromView()">
                        <i class="ri-edit-line me-1"></i>Edit Banner
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

            // Initialize preview updates
            initPreviewUpdates();
        });

        let bannersTable;
        let isEditMode = false;

        function initDataTable() {
            bannersTable = $('#bannersTable').DataTable({
                processing: true,
                serverSide: true,
                responsive: true,
                ajax: {
                    url: "{{ route('admin.banners.data') }}",
                    type: 'GET'
                },
                columns: [{
                        data: 'DT_RowIndex',
                        name: 'DT_RowIndex',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'preview',
                        name: 'preview',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'image_preview',
                        name: 'image_preview',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'title',
                        name: 'title'
                    },
                    {
                        data: 'description_short',
                        name: 'description'
                    },
                    {
                        data: 'sort_order',
                        name: 'sort_order'
                    },
                    {
                        data: 'status',
                        name: 'is_active'
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
                    [5, 'asc'] // Order by sort_order asc
                ]
            });
        }

        function initForm() {
            $('#bannerForm').on('submit', function(e) {
                e.preventDefault();
                submitBanner();
            });

            // Reset form when modal is closed
            $('#bannerModal').on('hidden.bs.modal', function() {
                resetForm();
            });
        }

        function initPreviewUpdates() {
            // Update preview when inputs change
            $('#bannerTitle').on('input', function() {
                const title = $(this).val() || 'Banner Title';
                $('#previewTitle').text(title);
            });

            $('#bannerDescription').on('input', function() {
                const description = $(this).val() || 'Banner description will appear here';
                $('#previewDescription').text(description);
            });

            $('#bannerIcon').on('input', function() {
                const iconClass = $(this).val();
                if (iconClass) {
                    // Handle different icon formats
                    let finalIconClass = iconClass;
                    if (!iconClass.includes('fa-') && !iconClass.includes('ri-')) {
                        finalIconClass = 'ri-' + iconClass;
                    }
                    $('#previewIcon').html('<i class="' + finalIconClass + '" style="font-size: 2rem;"></i>');
                } else {
                    $('#previewIcon').html('<i class="ri-home-line" style="font-size: 2rem;"></i>');
                }
            });

            $('#bannerBackgroundColor').on('change', function() {
                const bgColor = $(this).val();
                $('#bannerPreview').css('background-color', bgColor);
            });

            $('#bannerTextColor').on('change', function() {
                const textColor = $(this).val();
                $('#bannerPreview').css('color', textColor);
            });

            // Image preview
            $('#bannerImage').on('change', function() {
                const file = this.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        $('#imagePreview').attr('src', e.target.result);
                        $('#imagePreviewContainer').show();
                    };
                    reader.readAsDataURL(file);
                } else {
                    $('#imagePreviewContainer').hide();
                }
            });
        }

        function createBanner() {
            isEditMode = false;
            $('#bannerModalLabel').text('Add New Banner');
            $('#submitText').text('Save Banner');
            resetForm();
            $('#bannerModal').modal('show');
        }

        function editBanner(id) {
            isEditMode = true;
            $('#bannerModalLabel').text('Edit Banner');
            $('#submitText').text('Update Banner');

            // Show loading state
            showFormLoading(true);
            $('#bannerModal').modal('show');

            // Fetch banner data
            $.ajax({
                url: `/admin/banners/${id}`,
                type: 'GET',
                success: function(response) {
                    if (response.success) {
                        const banner = response.data;
                        $('#bannerId').val(banner.id);
                        $('#bannerTitle').val(banner.title).trigger('input');
                        $('#bannerDescription').val(banner.description).trigger('input');
                        $('#bannerIcon').val(banner.icon).trigger('input');
                        $('#bannerBackgroundColor').val(banner.background_color).trigger('change');
                        $('#bannerTextColor').val(banner.text_color).trigger('change');
                        $('#bannerSortOrder').val(banner.sort_order);
                        $('#bannerStatus').val(banner.is_active ? '1' : '0');

                        // Show existing image if available
                        if (banner.image_url) {
                            $('#imagePreview').attr('src', banner.image_url);
                            $('#imagePreviewContainer').show();
                        }
                    } else {
                        showAlert('error', 'Error', response.message);
                    }
                },
                error: function(xhr) {
                    const response = JSON.parse(xhr.responseText);
                    showAlert('error', 'Error', response.message || 'Failed to load banner data');
                },
                complete: function() {
                    showFormLoading(false);
                }
            });
        }

        function viewBanner(id) {
            // Show loading state
            $('#viewBannerTitle').text('Loading...');
            $('#viewBannerModal').modal('show');

            // Fetch banner data
            $.ajax({
                url: `/admin/banners/${id}`,
                type: 'GET',
                success: function(response) {
                    if (response.success) {
                        const banner = response.data;
                        $('#viewBannerTitle').text(banner.title);
                        $('#viewBannerDescription').text(banner.description || '-');
                        $('#viewBannerStatus').html(
                            `<span class="badge bg-${banner.is_active ? 'success' : 'danger'}">
                        ${banner.status_label}
                    </span>`
                        );
                        $('#viewBannerIcon').html(banner.icon ?
                            `<i class="${banner.icon_class}"></i> ${banner.icon}` : '-');
                        $('#viewBannerOrder').text(banner.sort_order);

                        // Colors preview
                        $('#viewBannerColors').html(
                            `<div class="d-flex gap-2">
                                <div class="color-box" style="width: 20px; height: 20px; background-color: ${banner.background_color}; border: 1px solid #ccc; border-radius: 3px;" title="Background: ${banner.background_color}"></div>
                                <div class="color-box" style="width: 20px; height: 20px; background-color: ${banner.text_color}; border: 1px solid #ccc; border-radius: 3px;" title="Text: ${banner.text_color}"></div>
                            </div>`
                        );

                        $('#viewBannerCreated').text(formatDateTime(banner.created_at));
                        $('#viewBannerUpdated').text(formatDateTime(banner.updated_at));

                        // Preview
                        $('#viewBannerPreview').css({
                            'background-color': banner.background_color,
                            'color': banner.text_color
                        });
                        $('#viewPreviewIcon').html(banner.icon ?
                            `<i class="${banner.icon_class}" style="font-size: 2rem;"></i>` : '');
                        $('#viewPreviewTitle').text(banner.title);
                        $('#viewPreviewDescription').text(banner.description);

                        // Image
                        if (banner.image_url) {
                            $('#viewBannerImage').attr('src', banner.image_url);
                            $('#viewImageContainer').show();
                        } else {
                            $('#viewImageContainer').hide();
                        }

                        // Store ID for potential edit action
                        $('#viewBannerModal').data('banner-id', banner.id);
                    } else {
                        showAlert('error', 'Error', response.message);
                        $('#viewBannerModal').modal('hide');
                    }
                },
                error: function(xhr) {
                    const response = JSON.parse(xhr.responseText);
                    showAlert('error', 'Error', response.message || 'Failed to load banner data');
                    $('#viewBannerModal').modal('hide');
                }
            });
        }

        function editBannerFromView() {
            const bannerId = $('#viewBannerModal').data('banner-id');
            $('#viewBannerModal').modal('hide');

            // Small delay to ensure modal is closed before opening edit modal
            setTimeout(() => {
                editBanner(bannerId);
            }, 300);
        }

        function deleteBanner(id) {
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
                url: `/admin/banners/${id}?_method=delete`,
                type: 'POST',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    if (response.success) {
                        showAlert('success', 'Deleted!', response.message);
                        bannersTable.ajax.reload();
                    } else {
                        showAlert('error', 'Error', response.message);
                    }
                },
                error: function(xhr) {
                    const response = JSON.parse(xhr.responseText);
                    showAlert('error', 'Error', response.message || 'Failed to delete banner');
                }
            });
        }

        function toggleBannerStatus(id) {
            Swal.fire({
                title: 'Change Banner Status?',
                text: "This will change the banner's active status.",
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, change it!',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    performToggleStatus(id);
                }
            });
        }

        function performToggleStatus(id) {
            $.ajax({
                url: `/admin/banners/${id}/toggle-status?_method=patch`,
                type: 'POST',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    if (response.success) {
                        showAlert('success', 'Success!', response.message);
                        bannersTable.ajax.reload();
                    } else {
                        showAlert('error', 'Error', response.message);
                    }
                },
                error: function(xhr) {
                    const response = JSON.parse(xhr.responseText);
                    showAlert('error', 'Error', response.message || 'Failed to toggle banner status');
                }
            });
        }

        function submitBanner() {
            // Clear previous errors
            clearFormErrors();

            const formData = new FormData($('#bannerForm')[0]);
            let url = isEditMode ? `/admin/banners/${$('#bannerId').val()}` : '/admin/banners';
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
                        $('#bannerModal').modal('hide');
                        bannersTable.ajax.reload();
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
                        showAlert('error', 'Error', response.message || 'Failed to save banner');
                    }
                },
                complete: function() {
                    showFormLoading(false);
                }
            });
        }

        function resetForm() {
            $('#bannerForm')[0].reset();
            $('#bannerId').val('');
            clearFormErrors();
            showFormLoading(false);

            // Reset preview
            $('#bannerBackgroundColor').val('#ff9500').trigger('change');
            $('#bannerTextColor').val('#ffffff').trigger('change');
            $('#bannerTitle').val('').trigger('input');
            $('#bannerDescription').val('').trigger('input');
            $('#bannerIcon').val('').trigger('input');
            $('#imagePreviewContainer').hide();
        }

        function showFormLoading(show) {
            if (show) {
                $('#submitSpinner').removeClass('d-none');
                $('#bannerForm button[type="submit"]').prop('disabled', true);
            } else {
                $('#submitSpinner').addClass('d-none');
                $('#bannerForm button[type="submit"]').prop('disabled', false);
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

                // Handle different field names mapping to input IDs and error divs
                switch (field) {
                    case 'background_color':
                        input = $('#bannerBackgroundColor');
                        errorDiv = $('#backgroundcolorError');
                        break;
                    case 'text_color':
                        input = $('#bannerTextColor');
                        errorDiv = $('#textcolorError');
                        break;
                    case 'sort_order':
                        input = $('#bannerSortOrder');
                        errorDiv = $('#sortorderError');
                        break;
                    case 'is_active':
                        input = $('#bannerStatus');
                        errorDiv = $('#isactiveError');
                        break;
                    case 'title':
                        input = $('#bannerTitle');
                        errorDiv = $('#titleError');
                        break;
                    case 'description':
                        input = $('#bannerDescription');
                        errorDiv = $('#descriptionError');
                        break;
                    case 'icon':
                        input = $('#bannerIcon');
                        errorDiv = $('#iconError');
                        break;
                    case 'image':
                        input = $('#bannerImage');
                        errorDiv = $('#imageError');
                        break;
                    default:
                        input = $(`#banner${field.charAt(0).toUpperCase() + field.slice(1)}`);
                        errorDiv = $(`#${field}Error`);
                }

                if (input.length && errorDiv.length) {
                    input.addClass('is-invalid');
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
    </script>
@endpush
    