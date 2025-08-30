@extends('admin.layouts')

@section('title', 'BAIK Profile')
@section('content')
    <div class="page-content">
        <div class="page-title-head d-flex align-items-center gap-2">
            <div class="flex-grow-1">
                <h4 class="fs-18 fw-bold mb-0">My Profile</h4>
            </div>
            <div class="text-end">
                <ol class="breadcrumb m-0 py-0 fs-13">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard.index') }}">Dashboard</a></li>
                    <li class="breadcrumb-item active">Profile</li>
                </ol>
            </div>
        </div>

        <div class="page-container">
            <div class="row">
                <!-- Profile Information Card -->
                <div class="col-xl-4">
                    <div class="card">
                        <div class="card-header border-bottom border-dashed">
                            <h5 class="card-title mb-0">Profile Information</h5>
                        </div>
                        <div class="card-body text-center">
                            <div class="position-relative d-inline-block mb-3">
                                <img id="profile-image-preview"
                                    src="{{ $user->profile_image ? url('storage/' . $user->profile_image) : asset('admin/backend/dist/assets/images/users/avatar-1.jpg') }}"
                                    alt="Profile Image"
                                    class="img-fluid rounded-circle avatar-xl border border-3 border-light shadow">
                                <button type="button"
                                    class="btn btn-sm btn-primary rounded-circle position-absolute bottom-0 end-0"
                                    style="width: 32px; height: 32px;" data-bs-toggle="modal"
                                    data-bs-target="#changeImageModal">
                                    <i class="ri-camera-line"></i>
                                </button>
                            </div>

                            <h5 class="mb-1" id="profile-name">{{ $user->name }}</h5>
                            <p class="text-muted mb-2" id="profile-role">{{ $user->role_display }}</p>
                            <p class="text-muted mb-3" id="profile-department">{{ $user->department ?? 'No Department' }}
                            </p>

                            <div class="row text-center">
                                <div class="col-6">
                                    <div class="border-end border-light">
                                        <h5 class="mb-1 text-primary">{{ $user->reports()->count() }}</h5>
                                        <p class="text-muted mb-0">Reports</p>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <h5 class="mb-1 text-success">{{ $user->assignedReports()->count() }}</h5>
                                    <p class="text-muted mb-0">Assigned</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Quick Info Card -->
                    <div class="card">
                        <div class="card-header border-bottom border-dashed">
                            <h5 class="card-title mb-0">Quick Information</h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-borderless mb-0">
                                    <tbody>
                                        <tr>
                                            <td class="ps-0 fw-semibold">Email:</td>
                                            <td class="text-end" id="quick-email">{{ $user->email }}</td>
                                        </tr>
                                        <tr>
                                            <td class="ps-0 fw-semibold">Phone:</td>
                                            <td class="text-end" id="quick-phone">{{ $user->phone ?? 'Not set' }}</td>
                                        </tr>
                                        <tr>
                                            <td class="ps-0 fw-semibold">Status:</td>
                                            <td class="text-end">
                                                <span class="badge bg-{{ $user->is_active ? 'success' : 'danger' }}">
                                                    {{ $user->is_active ? 'Active' : 'Inactive' }}
                                                </span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="ps-0 fw-semibold">Joined:</td>
                                            <td class="text-end">{{ $user->created_at->format('M d, Y') }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Profile Settings -->
                <div class="col-xl-8">
                    <div class="card">
                        <div class="card-header border-bottom border-dashed">
                            <ul class="nav nav-tabs card-header-tabs" role="tablist">
                                <li class="nav-item">
                                    <a class="nav-link active" data-bs-toggle="tab" href="#personal-info" role="tab">
                                        <i class="ri-user-3-line me-1"></i> Personal Information
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" data-bs-toggle="tab" href="#security" role="tab">
                                        <i class="ri-lock-line me-1"></i> Security
                                    </a>
                                </li>
                            </ul>
                        </div>
                        <div class="card-body">
                            <div class="tab-content">
                                <!-- Personal Information Tab -->
                                <div class="tab-pane show active" id="personal-info" role="tabpanel">
                                    <form id="profile-form">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label for="name" class="form-label">Full Name <span
                                                            class="text-danger">*</span></label>
                                                    <input type="text" class="form-control" id="name" name="name"
                                                        value="{{ $user->name }}" required>
                                                    <div class="invalid-feedback"></div>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label for="email" class="form-label">Email Address <span
                                                            class="text-danger">*</span></label>
                                                    <input type="email" class="form-control" id="email"
                                                        name="email" value="{{ $user->email }}" required>
                                                    <div class="invalid-feedback"></div>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label for="phone" class="form-label">Phone Number</label>
                                                    <input type="text" class="form-control" id="phone"
                                                        name="phone" value="{{ $user->phone }}">
                                                    <div class="invalid-feedback"></div>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label for="department" class="form-label">Department</label>
                                                    <input type="text" class="form-control" id="department"
                                                        name="department" value="{{ $user->department }}">
                                                    <div class="invalid-feedback"></div>
                                                </div>
                                            </div>
                                            <div class="col-12">
                                                <div class="mb-3">
                                                    <label for="role" class="form-label">Role</label>
                                                    <input type="text" class="form-control" id="role"
                                                        value="{{ $user->role_display }}" readonly>
                                                    <small class="text-muted">Role cannot be changed. Contact administrator
                                                        if needed.</small>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="text-end">
                                            <button type="submit" class="btn btn-primary" id="update-profile-btn">
                                                <i class="ri-save-line me-1"></i> Update Profile
                                            </button>
                                        </div>
                                    </form>
                                </div>

                                <!-- Security Tab -->
                                <div class="tab-pane" id="security" role="tabpanel">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <h5 class="mb-3">Change Password</h5>
                                            <form id="password-form">
                                                <div class="mb-3">
                                                    <label for="current_password" class="form-label">Current Password
                                                        <span class="text-danger">*</span></label>
                                                    <div class="input-group">
                                                        <input type="password" class="form-control" id="current_password"
                                                            name="current_password" required>
                                                        <button class="btn btn-outline-secondary" type="button"
                                                            id="toggle-current-password">
                                                            <i class="ri-eye-off-line"></i>
                                                        </button>
                                                    </div>
                                                    <div class="invalid-feedback"></div>
                                                </div>

                                                <div class="mb-3">
                                                    <label for="new_password" class="form-label">New Password <span
                                                            class="text-danger">*</span></label>
                                                    <div class="input-group">
                                                        <input type="password" class="form-control" id="new_password"
                                                            name="new_password" required minlength="6">
                                                        <button class="btn btn-outline-secondary" type="button"
                                                            id="toggle-new-password">
                                                            <i class="ri-eye-off-line"></i>
                                                        </button>
                                                    </div>
                                                    <div class="invalid-feedback"></div>
                                                    <small class="text-muted">Password must be at least 6 characters
                                                        long.</small>
                                                </div>

                                                <div class="mb-3">
                                                    <label for="new_password_confirmation" class="form-label">Confirm New
                                                        Password <span class="text-danger">*</span></label>
                                                    <div class="input-group">
                                                        <input type="password" class="form-control"
                                                            id="new_password_confirmation"
                                                            name="new_password_confirmation" required minlength="6">
                                                        <button class="btn btn-outline-secondary" type="button"
                                                            id="toggle-confirm-password">
                                                            <i class="ri-eye-off-line"></i>
                                                        </button>
                                                    </div>
                                                    <div class="invalid-feedback"></div>
                                                </div>

                                                <div class="text-end">
                                                    <button type="submit" class="btn btn-success"
                                                        id="change-password-btn">
                                                        <i class="ri-lock-line me-1"></i> Change Password
                                                    </button>
                                                </div>
                                            </form>
                                        </div>

                                        <div class="col-md-6">
                                            <h5 class="mb-3">Security Information</h5>
                                            <div class="alert alert-info">
                                                <i class="ri-information-line me-2"></i>
                                                <strong>Password Guidelines:</strong>
                                                <ul class="mb-0 mt-2">
                                                    <li>Use at least 6 characters</li>
                                                    <li>Include uppercase and lowercase letters</li>
                                                    <li>Include numbers and special characters</li>
                                                    <li>Don't use common passwords</li>
                                                </ul>
                                            </div>

                                            <div class="card border-light">
                                                <div class="card-body">
                                                    <h6 class="card-title">Recent Activity</h6>
                                                    <p class="text-muted mb-2">Last login:
                                                        <strong>{{ $user->updated_at->diffForHumans() }}</strong>
                                                    </p>
                                                    <p class="text-muted mb-0">Account created:
                                                        <strong>{{ $user->created_at->format('M d, Y') }}</strong>
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Change Profile Image Modal -->
    <div class="modal fade" id="changeImageModal" tabindex="-1" aria-labelledby="changeImageModalLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="changeImageModalLabel">Change Profile Image</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="image-upload-form" enctype="multipart/form-data">
                        <div class="text-center mb-3">
                            <img id="image-preview"
                                src="{{ $user->profile_image ? url('storage/' . $user->profile_image) : asset('admin/backend/dist/assets/images/users/avatar-1.jpg') }}"
                                alt="Preview"
                                class="img-fluid rounded-circle avatar-lg border border-3 border-light shadow mb-3">
                        </div>

                        <div class="mb-3">
                            <label for="profile_image" class="form-label">Choose Image</label>
                            <input type="file" class="form-control" id="profile_image" name="profile_image"
                                accept="image/jpeg,image/png,image/jpg,image/webp" required>
                            <div class="invalid-feedback"></div>
                            <small class="text-muted">Supported formats: JPEG, PNG, JPG, WEBP. Max size: 5MB.</small>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    @if ($user->profile_image)
                        <button type="button" class="btn btn-danger" id="delete-image-btn">
                            <i class="ri-delete-bin-line me-1"></i> Remove Current
                        </button>
                    @endif
                    <button type="button" class="btn btn-primary" id="upload-image-btn">
                        <i class="ri-upload-line me-1"></i> Upload Image
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('jsSection')
    <script>
        $(document).ready(function() {
            // CSRF token setup
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            // Profile image preview
            $('#profile_image').change(function() {
                const file = this.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        $('#image-preview').attr('src', e.target.result);
                    };
                    reader.readAsDataURL(file);
                }
            });

            // Password visibility toggles
            $('#toggle-current-password').click(function() {
                togglePasswordVisibility('#current_password', this);
            });

            $('#toggle-new-password').click(function() {
                togglePasswordVisibility('#new_password', this);
            });

            $('#toggle-confirm-password').click(function() {
                togglePasswordVisibility('#new_password_confirmation', this);
            });

            function togglePasswordVisibility(inputId, button) {
                const input = $(inputId);
                const icon = $(button).find('i');

                if (input.attr('type') === 'password') {
                    input.attr('type', 'text');
                    icon.removeClass('ri-eye-off-line').addClass('ri-eye-line');
                } else {
                    input.attr('type', 'password');
                    icon.removeClass('ri-eye-line').addClass('ri-eye-off-line');
                }
            }

            // Update Profile Form
            $('#profile-form').submit(function(e) {
                e.preventDefault();
                const $btn = $('#update-profile-btn');
                const originalText = $btn.html();

                $btn.html(
                    '<i class="ri-loader-4-line spinner-border spinner-border-sm me-1"></i> Updating...'
                ).prop('disabled', true);
                clearValidationErrors();

                $.ajax({
                    url: '{{ route('admin.profile.update') }}',
                    method: 'POST',
                    data: $(this).serialize(),
                    success: function(response) {
                        if (response.success) {
                            showAlert('success', response.message);
                            updateProfileDisplay(response.data);
                        } else {
                            showAlert('error', response.message);
                        }
                    },
                    error: function(xhr) {
                        handleValidationErrors(xhr);
                    },
                    complete: function() {
                        $btn.html(originalText).prop('disabled', false);
                    }
                });
            });

            // Change Password Form
            $('#password-form').submit(function(e) {
                e.preventDefault();
                const $btn = $('#change-password-btn');
                const originalText = $btn.html();

                // Check if passwords match
                if ($('#new_password').val() !== $('#new_password_confirmation').val()) {
                    showFieldError('#new_password_confirmation', 'Passwords do not match');
                    return;
                }

                $btn.html(
                    '<i class="ri-loader-4-line spinner-border spinner-border-sm me-1"></i> Changing...'
                ).prop('disabled', true);
                clearValidationErrors();

                $.ajax({
                    url: '{{ route('admin.profile.password') }}',
                    method: 'POST',
                    data: $(this).serialize(),
                    success: function(response) {
                        if (response.success) {
                            showAlert('success', response.message);
                            $('#password-form')[0].reset();
                        } else {
                            showAlert('error', response.message);
                        }
                    },
                    error: function(xhr) {
                        handleValidationErrors(xhr);
                    },
                    complete: function() {
                        $btn.html(originalText).prop('disabled', false);
                    }
                });
            });

            // Upload Profile Image
            $('#upload-image-btn').click(function() {
                const $btn = $(this);
                const originalText = $btn.html();
                const formData = new FormData($('#image-upload-form')[0]);

                $btn.html(
                    '<i class="ri-loader-4-line spinner-border spinner-border-sm me-1"></i> Uploading...'
                ).prop('disabled', true);
                clearValidationErrors();

                $.ajax({
                    url: '{{ route('admin.profile.image.upload') }}',
                    method: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        if (response.success) {
                            showAlert('success', response.message);
                            $('#profile-image-preview').attr('src', response.data
                                .profile_image_url);
                            $('#changeImageModal').modal('hide');

                            // Update delete button visibility
                            if (!$('#delete-image-btn').length) {
                                $('.modal-footer').prepend(`
                            <button type="button" class="btn btn-danger" id="delete-image-btn">
                                <i class="ri-delete-bin-line me-1"></i> Remove Current
                            </button>
                        `);
                                bindDeleteImageEvent();
                            }
                        } else {
                            showAlert('error', response.message);
                        }
                    },
                    error: function(xhr) {
                        handleValidationErrors(xhr);
                    },
                    complete: function() {
                        $btn.html(originalText).prop('disabled', false);
                    }
                });
            });

            // Delete Profile Image
            function bindDeleteImageEvent() {
                $('#delete-image-btn').click(function() {
                    if (confirm('Are you sure you want to remove your profile image?')) {
                        const $btn = $(this);
                        const originalText = $btn.html();

                        $btn.html(
                            '<i class="ri-loader-4-line spinner-border spinner-border-sm me-1"></i> Removing...'
                        ).prop('disabled', true);

                        $.ajax({
                            url: '{{ route('admin.profile.image.delete') }}',
                            method: 'DELETE',
                            success: function(response) {
                                if (response.success) {
                                    showAlert('success', response.message);
                                    const defaultImage =
                                        '{{ asset('admin/backend/dist/assets/images/users/avatar-1.jpg') }}';
                                    $('#profile-image-preview').attr('src', defaultImage);
                                    $('#image-preview').attr('src', defaultImage);
                                    $('#changeImageModal').modal('hide');
                                    $btn.remove();
                                } else {
                                    showAlert('error', response.message);
                                }
                            },
                            error: function(xhr) {
                                showAlert('error',
                                    'An error occurred while deleting the image.');
                            },
                            complete: function() {
                                $btn.html(originalText).prop('disabled', false);
                            }
                        });
                    }
                });
            }

            // Bind delete event if delete button exists
            if ($('#delete-image-btn').length) {
                bindDeleteImageEvent();
            }

            // Helper functions
            function updateProfileDisplay(userData) {
                $('#profile-name').text(userData.name);
                $('#profile-department').text(userData.department || 'No Department');
                $('#quick-email').text(userData.email);
                $('#quick-phone').text(userData.phone || 'Not set');
            }

            function showAlert(type, message) {
                const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
                const icon = type === 'success' ? 'ri-check-line' : 'ri-error-warning-line';

                const alert = `
            <div class="alert ${alertClass} alert-dismissible fade show" role="alert">
                <i class="${icon} me-2"></i>
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `;

                $('.page-content').prepend(alert);

                // Auto dismiss after 5 seconds
                setTimeout(() => {
                    $('.alert').fadeOut(() => {
                        $('.alert').remove();
                    });
                }, 5000);
            }

            function clearValidationErrors() {
                $('.is-invalid').removeClass('is-invalid');
                $('.invalid-feedback').text('');
            }

            function showFieldError(field, message) {
                $(field).addClass('is-invalid');
                $(field).siblings('.invalid-feedback').text(message);
            }

            function handleValidationErrors(xhr) {
                if (xhr.status === 422) {
                    const errors = xhr.responseJSON.errors;
                    $.each(errors, function(field, messages) {
                        showFieldError(`[name="${field}"]`, messages[0]);
                    });
                } else {
                    const message = xhr.responseJSON?.message || 'An error occurred. Please try again.';
                    showAlert('error', message);
                }
            }
        });
    </script>
@endpush
