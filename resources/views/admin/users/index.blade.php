@extends('admin.layouts')

@section('title', 'User Management')

@section('content')
    <div class="page-content">
        <!-- Page Title -->
        <div class="page-title-head d-flex align-items-center gap-2">
            <div class="flex-grow-1">
                <h4 class="fs-18 fw-bold mb-0">User Management</h4>
            </div>
            <div class="text-end">
                <ol class="breadcrumb m-0 py-0 fs-13">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="javascript: void(0);">User Management</a></li>
                    <li class="breadcrumb-item active">Users</li>
                </ol>
            </div>
        </div>

        <div class="page-container">
            <!-- Statistics Cards -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="flex-shrink-0">
                                    <div class="avatar-sm d-flex align-items-center justify-content-center rounded-circle bg-primary-subtle">
                                        <span class="avatar-title text-primary">
                                            <i class="ri-group-line fs-18"></i>
                                        </span>
                                    </div>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <p class="text-muted mb-1">Total Users</p>
                                    <h5 class="mb-0" id="totalUsers">-</h5>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="flex-shrink-0">
                                    <div class="avatar-sm d-flex align-items-center justify-content-center rounded-circle bg-success-subtle">
                                        <span class="avatar-title text-success">
                                            <i class="ri-user-check-line fs-18"></i>
                                        </span>
                                    </div>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <p class="text-muted mb-1">Active Users</p>
                                    <h5 class="mb-0" id="activeUsers">-</h5>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="flex-shrink-0">
                                    <div class="avatar-sm d-flex align-items-center justify-content-center rounded-circle bg-warning-subtle">
                                        <span class="avatar-title text-warning">
                                            <i class="ri-shield-user-line fs-18"></i>
                                        </span>
                                    </div>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <p class="text-muted mb-1">HSE Staff</p>
                                    <h5 class="mb-0" id="hseStaff">-</h5>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <div class="flex-shrink-0">
                                    <div class="avatar-sm d-flex align-items-center justify-content-center rounded-circle bg-info-subtle">
                                        <span class="avatar-title text-info">
                                            <i class="ri-user-line fs-18"></i>
                                        </span>
                                    </div>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <p class="text-muted mb-1">Employees</p>
                                    <h5 class="mb-0" id="employees">-</h5>
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
                            <div class="d-flex align-items-center gap-3">
                                <h4 class="header-title mb-0">Users List</h4>
                                <div id="activeFilters" class="d-flex gap-2">
                                    <!-- Active filter badges will be inserted here -->
                                </div>
                            </div>
                            <div class="d-flex gap-2">
                                <!-- Filters -->
                                <div class="dropdown">
                                    <button class="btn btn-outline-secondary dropdown-toggle" type="button"
                                        data-bs-toggle="dropdown">
                                        <i class="ri-filter-line me-1"></i>Filter Role
                                    </button>
                                    <ul class="dropdown-menu">
                                        <li><a class="dropdown-item" href="#" onclick="filterByRole('all')">All
                                                Roles</a></li>
                                        <li><a class="dropdown-item" href="#"
                                                onclick="filterByRole('admin')">Admin</a></li>
                                        <li><a class="dropdown-item" href="#" onclick="filterByRole('hse_staff')">HSE
                                                Staff</a></li>
                                        <li><a class="dropdown-item" href="#"
                                                onclick="filterByRole('employee')">Employee</a></li>
                                    </ul>
                                </div>
                                <div class="dropdown">
                                    <button class="btn btn-outline-secondary dropdown-toggle" type="button"
                                        data-bs-toggle="dropdown">
                                        <i class="ri-filter-line me-1"></i>Filter Status
                                    </button>
                                    <ul class="dropdown-menu">
                                        <li><a class="dropdown-item" href="#" onclick="filterByStatus('all')">All
                                                Status</a></li>
                                        <li><a class="dropdown-item" href="#"
                                                onclick="filterByStatus('active')">Active</a></li>
                                        <li><a class="dropdown-item" href="#"
                                                onclick="filterByStatus('inactive')">Inactive</a></li>
                                    </ul>
                                </div>
                                <button type="button" class="btn btn-primary" onclick="createUser()">
                                    <i class="ri-add-line me-1"></i>Add New User
                                </button>
                            </div>
                        </div>

                        <div class="card-body">
                            <div class="table-responsive">
                                <table id="usersTable" class="table table-striped table-bordered dt-responsive nowrap"
                                    style="width:100%">
                                    <thead class="table-dark">
                                        <tr>
                                            <th width="5%">#</th>
                                            <th width="5%">Avatar</th>
                                            <th width="20%">User Info</th>
                                            <th width="10%">Role</th>
                                            <th width="15%">Department</th>
                                            <th width="12%">Contact</th>
                                            <th width="8%">Status</th>
                                            <th width="12%">Created</th>
                                            <th width="13%">Actions</th>
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

    <!-- Create/Edit User Modal -->
    <div class="modal fade" id="userModal" tabindex="-1" aria-labelledby="userModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <form id="userForm" enctype="multipart/form-data">
                    <div class="modal-header">
                        <h5 class="modal-title" id="userModalLabel">Add New User</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" id="userId" name="id">

                        <div class="row">
                            <div class="col-md-8">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="userName" class="form-label">Full Name <span
                                                    class="text-danger">*</span></label>
                                            <input type="text" class="form-control" id="userName" name="name"
                                                required maxlength="255" placeholder="Enter full name">
                                            <div class="invalid-feedback" id="nameError"></div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="userEmail" class="form-label">Email Address <span
                                                    class="text-danger">*</span></label>
                                            <input type="email" class="form-control" id="userEmail" name="email"
                                                required maxlength="255" placeholder="Enter email address">
                                            <div class="invalid-feedback" id="emailError"></div>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="userPassword" class="form-label">Password <span
                                                    class="text-danger" id="passwordRequired">*</span></label>
                                            <input type="password" class="form-control" id="userPassword"
                                                name="password" placeholder="Enter password">
                                            <div class="form-text">Minimum 6 characters. Leave empty to keep current
                                                password (when editing).</div>
                                            <div class="invalid-feedback" id="passwordError"></div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="userPasswordConfirmation" class="form-label">Confirm Password
                                                <span class="text-danger"
                                                    id="passwordConfirmationRequired">*</span></label>
                                            <input type="password" class="form-control" id="userPasswordConfirmation"
                                                name="password_confirmation" placeholder="Confirm password">
                                            <div class="invalid-feedback" id="passwordconfirmationError"></div>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="userRole" class="form-label">Role <span
                                                    class="text-danger">*</span></label>
                                            <select class="form-select" id="userRole" name="role" required>
                                                <option value="">Select Role</option>
                                                <option value="admin">Administrator</option>
                                                <option value="hse_staff">HSE Staff</option>
                                                <option value="employee">Employee</option>
                                            </select>
                                            <div class="invalid-feedback" id="roleError"></div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="userStatus" class="form-label">Status <span
                                                    class="text-danger">*</span></label>
                                            <select class="form-select" id="userStatus" name="is_active" required>
                                                <option value="">Select Status</option>
                                                <option value="1">Active</option>
                                                <option value="0">Inactive</option>
                                            </select>
                                            <div class="invalid-feedback" id="isactiveError"></div>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="userDepartment" class="form-label">Department</label>
                                            <input type="text" class="form-control" id="userDepartment"
                                                name="department" maxlength="100" placeholder="Enter department">
                                            <div class="invalid-feedback" id="departmentError"></div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="userPhone" class="form-label">Phone Number</label>
                                            <input type="text" class="form-control" id="userPhone" name="phone"
                                                maxlength="20" placeholder="Enter phone number">
                                            <div class="invalid-feedback" id="phoneError"></div>
                                        </div>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="userProfileImage" class="form-label">Profile Image</label>
                                    <input type="file" class="form-control" id="userProfileImage"
                                        name="profile_image" accept="image/jpeg,image/png,image/jpg,image/webp">
                                    <div class="form-text">Max 5MB, formats: jpeg, png, jpg, webp</div>
                                    <div class="invalid-feedback" id="profileimageError"></div>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="user-preview-container">
                                    <label class="form-label fw-bold">User Preview:</label>
                                    <div class="card">
                                        <div class="card-body text-center">
                                            <div class="mb-3">
                                                <div class="avatar-lg mx-auto">
                                                    <img id="previewAvatar"
                                                        src="{{ asset('admin/backend/dist/assets/images/users/avatar-1.jpg') }}"
                                                        alt="User Avatar" class="img-fluid rounded-circle">
                                                </div>
                                            </div>
                                            <h5 id="previewName" class="mb-1">User Name</h5>
                                            <p id="previewEmail" class="text-muted mb-2">user@example.com</p>
                                            <span id="previewRole" class="badge bg-secondary">Role</span>
                                            <div class="mt-3">
                                                <p class="mb-1"><strong>Department:</strong></p>
                                                <p id="previewDepartment" class="text-muted">-</p>
                                            </div>
                                            <div class="mt-2">
                                                <p class="mb-1"><strong>Phone:</strong></p>
                                                <p id="previewPhone" class="text-muted">-</p>
                                            </div>
                                        </div>
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
                            <span id="submitText">Save User</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- View User Modal -->
    <div class="modal fade" id="viewUserModal" tabindex="-1" aria-labelledby="viewUserModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="viewUserModalLabel">User Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-4 text-center">
                            <div class="mb-3">
                                <div class="avatar-lg mx-auto">
                                    <img id="viewUserAvatar" src="" alt="User Avatar"
                                        class="img-fluid rounded-circle">
                                </div>
                            </div>
                            <h5 id="viewUserName">-</h5>
                            <p id="viewUserEmail" class="text-muted">-</p>
                            <span id="viewUserRole" class="badge">-</span>
                            <div class="mt-3">
                                <span id="viewUserStatus" class="badge">-</span>
                            </div>
                        </div>
                        <div class="col-md-8">
                            <div class="table-responsive">
                                <table class="table table-borderless">
                                    <tbody>
                                        <tr>
                                            <td class="fw-bold">Department:</td>
                                            <td id="viewUserDepartment">-</td>
                                        </tr>
                                        <tr>
                                            <td class="fw-bold">Phone:</td>
                                            <td id="viewUserPhone">-</td>
                                        </tr>
                                        <tr>
                                            <td class="fw-bold">Created At:</td>
                                            <td id="viewUserCreated">-</td>
                                        </tr>
                                        <tr>
                                            <td class="fw-bold">Updated At:</td>
                                            <td id="viewUserUpdated">-</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <div class="mt-4">
                                <h6 class="fw-bold">Statistics:</h6>
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="card border">
                                            <div class="card-body text-center">
                                                <h4 id="viewUserReports" class="mb-1">-</h4>
                                                <p class="text-muted mb-0">Reports Created</p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="card border">
                                            <div class="card-body text-center">
                                                <h4 id="viewUserAssignedReports" class="mb-1">-</h4>
                                                <p class="text-muted mb-0">Assigned Reports</p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="card border">
                                            <div class="card-body text-center">
                                                <h4 id="viewUserNotifications" class="mb-1">-</h4>
                                                <p class="text-muted mb-0">Unread Notifications</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-warning" onclick="editUserFromView()">
                        <i class="ri-edit-line me-1"></i>Edit User
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('jsSection')
    <script>
        $(document).ready(function() {
            // Get URL parameters for initial filter state
            const urlParams = new URLSearchParams(window.location.search);
            const roleParam = urlParams.get('role');
            const statusParam = urlParams.get('status');

            // Set initial filter values
            if (roleParam && roleParam !== 'all') {
                currentFilters.role = roleParam;
            }
            if (statusParam && statusParam !== 'all') {
                currentFilters.status = statusParam;
            }

            // Initialize DataTable
            initDataTable();

            // Initialize form
            initForm();

            // Initialize preview updates
            initPreviewUpdates();

            // Load statistics
            loadStatistics();

            // Update filter buttons to show current state
            updateFilterButtonText();
        });

        let usersTable;
        let isEditMode = false;
        let currentFilters = {
            role: 'all',
            status: 'all'
        };

        function initDataTable() {
            usersTable = $('#usersTable').DataTable({
                processing: true,
                serverSide: true,
                responsive: true,
                ajax: {
                    url: "{{ route('admin.users.data') }}",
                    type: 'GET',
                    data: function(d) {
                        d.role_filter = currentFilters.role;
                        d.status_filter = currentFilters.status;

                        // Also send URL parameters in case they are different from current filters
                        const urlParams = new URLSearchParams(window.location.search);
                        if (urlParams.get('role')) {
                            d.role = urlParams.get('role');
                        }
                        if (urlParams.get('status')) {
                            d.status = urlParams.get('status');
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
                        data: 'avatar',
                        name: 'avatar',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'user_info',
                        name: 'name'
                    },
                    {
                        data: 'role_badge',
                        name: 'role'
                    },
                    {
                        data: 'department_info',
                        name: 'department'
                    },
                    {
                        data: 'contact_info',
                        name: 'phone'
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
                    [7, 'desc']
                ] // Order by created_at desc
            });
        }

        function initForm() {
            $('#userForm').on('submit', function(e) {
                e.preventDefault();
                submitUser();
            });

            // Reset form when modal is closed
            $('#userModal').on('hidden.bs.modal', function() {
                resetForm();
            });
        }

        function initPreviewUpdates() {
            // Update preview when inputs change
            $('#userName').on('input', function() {
                const name = $(this).val() || 'User Name';
                $('#previewName').text(name);
            });

            $('#userEmail').on('input', function() {
                const email = $(this).val() || 'user@example.com';
                $('#previewEmail').text(email);
            });

            $('#userRole').on('change', function() {
                const role = $(this).val();
                const roleLabels = {
                    'admin': {
                        text: 'Administrator',
                        class: 'bg-danger'
                    },
                    'hse_staff': {
                        text: 'HSE Staff',
                        class: 'bg-warning'
                    },
                    'employee': {
                        text: 'Employee',
                        class: 'bg-info'
                    }
                };

                const roleConfig = roleLabels[role] || {
                    text: 'Role',
                    class: 'bg-secondary'
                };
                $('#previewRole').removeClass('bg-danger bg-warning bg-info bg-secondary').addClass(roleConfig
                    .class).text(roleConfig.text);
            });

            $('#userDepartment').on('input', function() {
                const department = $(this).val() || '-';
                $('#previewDepartment').text(department);
            });

            $('#userPhone').on('input', function() {
                const phone = $(this).val() || '-';
                $('#previewPhone').text(phone);
            });

            // Image preview
            $('#userProfileImage').on('change', function() {
                const file = this.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        $('#previewAvatar').attr('src', e.target.result);
                    };
                    reader.readAsDataURL(file);
                }
            });
        }

        function loadStatistics() {
            $.ajax({
                url: "{{ route('admin.users.statistics') }}",
                type: 'GET',
                success: function(response) {
                    if (response.success) {
                        const stats = response.data;
                        $('#totalUsers').text(stats.total_users);
                        $('#activeUsers').text(stats.active_users);
                        $('#hseStaff').text(stats.role_breakdown.hse_staff);
                        $('#employees').text(stats.role_breakdown.employee);
                    }
                },
                error: function() {
                    // Handle error silently
                }
            });
        }

        function filterByRole(role) {
            currentFilters.role = role;
            usersTable.ajax.reload();
            updateFilterButtonText();
            updateURL();
        }

        function filterByStatus(status) {
            currentFilters.status = status;
            usersTable.ajax.reload();
            updateFilterButtonText();
            updateURL();
        }

        function updateFilterButtonText() {
            // Update role filter button text
            const roleText = currentFilters.role === 'all' ? 'All Roles' :
                currentFilters.role === 'admin' ? 'Admin' :
                currentFilters.role === 'hse_staff' ? 'HSE Staff' :
                currentFilters.role === 'employee' ? 'Employee' : 'All Roles';

            $('.dropdown-toggle').each(function() {
                if ($(this).text().includes('Filter Role') || $(this).html().includes('Filter Role')) {
                    $(this).html('<i class="ri-filter-line me-1"></i>' + roleText);
                }
            });

            // Update status filter button text
            const statusText = currentFilters.status === 'all' ? 'All Status' :
                currentFilters.status === 'active' ? 'Active' :
                currentFilters.status === 'inactive' ? 'Inactive' : 'All Status';

            $('.dropdown-toggle').each(function() {
                if ($(this).text().includes('Filter Status') || $(this).html().includes('Filter Status')) {
                    $(this).html('<i class="ri-filter-line me-1"></i>' + statusText);
                }
            });

            // Update active filter badges
            updateActiveFilterBadges();
        }

        function updateActiveFilterBadges() {
            const activeFiltersContainer = $('#activeFilters');
            activeFiltersContainer.empty();

            // Add role filter badge if not 'all'
            if (currentFilters.role && currentFilters.role !== 'all') {
                const roleLabels = {
                    'admin': 'Admin',
                    'hse_staff': 'HSE Staff',
                    'employee': 'Employee'
                };
                const badgeText = roleLabels[currentFilters.role] || currentFilters.role;
                activeFiltersContainer.append(
                    `<span class="badge bg-primary-subtle text-primary">
                        Role: ${badgeText}
                        <button type="button" class="btn-close btn-close-sm ms-1" onclick="filterByRole('all')" aria-label="Close"></button>
                    </span>`
                );
            }

            // Add status filter badge if not 'all'
            if (currentFilters.status && currentFilters.status !== 'all') {
                const statusText = currentFilters.status === 'active' ? 'Active' : 'Inactive';
                const badgeClass = currentFilters.status === 'active' ? 'bg-success-subtle text-success' :
                    'bg-danger-subtle text-danger';
                activeFiltersContainer.append(
                    `<span class="badge ${badgeClass}">
                        Status: ${statusText}
                        <button type="button" class="btn-close btn-close-sm ms-1" onclick="filterByStatus('all')" aria-label="Close"></button>
                    </span>`
                );
            }
        }

        function updateURL() {
            // Update URL with current filters
            const url = new URL(window.location);

            if (currentFilters.role && currentFilters.role !== 'all') {
                url.searchParams.set('role', currentFilters.role);
            } else {
                url.searchParams.delete('role');
            }

            if (currentFilters.status && currentFilters.status !== 'all') {
                url.searchParams.set('status', currentFilters.status);
            } else {
                url.searchParams.delete('status');
            }

            // Update URL without reloading page
            window.history.replaceState({}, '', url);
        }

        function createUser() {
            isEditMode = false;
            $('#userModalLabel').text('Add New User');
            $('#submitText').text('Save User');
            $('#passwordRequired').show();
            $('#passwordConfirmationRequired').show();
            $('#userPassword').prop('required', true);
            $('#userPasswordConfirmation').prop('required', true);
            resetForm();
            $('#userModal').modal('show');
        }

        function editUser(id) {
            isEditMode = true;
            $('#userModalLabel').text('Edit User');
            $('#submitText').text('Update User');
            $('#passwordRequired').hide();
            $('#passwordConfirmationRequired').hide();
            $('#userPassword').prop('required', false);
            $('#userPasswordConfirmation').prop('required', false);

            // Show loading state
            showFormLoading(true);
            $('#userModal').modal('show');

            // Fetch user data
            $.ajax({
                url: `/admin/users/${id}`,
                type: 'GET',
                success: function(response) {
                    if (response.success) {
                        const user = response.data.user;
                        $('#userId').val(user.id);
                        $('#userName').val(user.name).trigger('input');
                        $('#userEmail').val(user.email).trigger('input');
                        $('#userRole').val(user.role).trigger('change');
                        $('#userDepartment').val(user.department).trigger('input');
                        $('#userPhone').val(user.phone).trigger('input');
                        $('#userStatus').val(user.is_active ? '1' : '0');

                        // Show existing image if available
                        if (response.data.profile_image_url) {
                            $('#previewAvatar').attr('src', response.data.profile_image_url);
                        }
                    } else {
                        showAlert('error', 'Error', response.message);
                    }
                },
                error: function(xhr) {
                    const response = JSON.parse(xhr.responseText);
                    showAlert('error', 'Error', response.message || 'Failed to load user data');
                },
                complete: function() {
                    showFormLoading(false);
                }
            });
        }

        function viewUser(id) {
            // Show loading state
            $('#viewUserName').text('Loading...');
            $('#viewUserModal').modal('show');

            // Fetch user data
            $.ajax({
                url: `/admin/users/${id}`,
                type: 'GET',
                success: function(response) {
                    if (response.success) {
                        const user = response.data.user;
                        const statistics = response.data.statistics;

                        $('#viewUserName').text(user.name);
                        $('#viewUserEmail').text(user.email);
                        $('#viewUserDepartment').text(user.department || '-');
                        $('#viewUserPhone').text(user.phone || '-');
                        $('#viewUserCreated').text(formatDateTime(user.created_at));
                        $('#viewUserUpdated').text(formatDateTime(user.updated_at));

                        // Role badge
                        const roleConfig = {
                            'admin': {
                                text: 'Administrator',
                                class: 'bg-danger'
                            },
                            'hse_staff': {
                                text: 'HSE Staff',
                                class: 'bg-warning'
                            },
                            'employee': {
                                text: 'Employee',
                                class: 'bg-info'
                            }
                        };
                        const roleData = roleConfig[user.role] || {
                            text: user.role,
                            class: 'bg-secondary'
                        };
                        $('#viewUserRole').removeClass('bg-danger bg-warning bg-info bg-secondary').addClass(
                            roleData.class).text(roleData.text);

                        // Status badge
                        $('#viewUserStatus').removeClass('bg-success bg-danger')
                            .addClass(user.is_active ? 'bg-success' : 'bg-danger')
                            .text(user.is_active ? 'Active' : 'Inactive');

                        // Avatar
                        const avatarUrl = response.data.profile_image_url ||
                            '{{ asset('admin/backend/dist/assets/images/users/avatar-1.jpg') }}';
                        $('#viewUserAvatar').attr('src', avatarUrl);

                        // Statistics
                        $('#viewUserReports').text(statistics.reports_count);
                        $('#viewUserAssignedReports').text(statistics.assigned_reports_count);
                        $('#viewUserNotifications').text(statistics.unread_notifications_count);

                        // Store ID for potential edit action
                        $('#viewUserModal').data('user-id', user.id);
                    } else {
                        showAlert('error', 'Error', response.message);
                        $('#viewUserModal').modal('hide');
                    }
                },
                error: function(xhr) {
                    const response = JSON.parse(xhr.responseText);
                    showAlert('error', 'Error', response.message || 'Failed to load user data');
                    $('#viewUserModal').modal('hide');
                }
            });
        }

        function editUserFromView() {
            const userId = $('#viewUserModal').data('user-id');
            $('#viewUserModal').modal('hide');

            // Small delay to ensure modal is closed before opening edit modal
            setTimeout(() => {
                editUser(userId);
            }, 300);
        }

        function deleteUser(id) {
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
                url: `/admin/users/${id}?_method=delete`,
                type: 'POST',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    if (response.success) {
                        showAlert('success', 'Deleted!', response.message);
                        usersTable.ajax.reload();
                        loadStatistics();
                    } else {
                        showAlert('error', 'Error', response.message);
                    }
                },
                error: function(xhr) {
                    const response = JSON.parse(xhr.responseText);
                    showAlert('error', 'Error', response.message || 'Failed to delete user');
                }
            });
        }

        function toggleUserStatus(id) {
            Swal.fire({
                title: 'Change User Status?',
                text: "This will change the user's active status.",
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
                url: `/admin/users/${id}/toggle-status?_method=patch`,
                type: 'POST',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    if (response.success) {
                        showAlert('success', 'Success!', response.message);
                        usersTable.ajax.reload();
                        loadStatistics();
                    } else {
                        showAlert('error', 'Error', response.message);
                    }
                },
                error: function(xhr) {
                    const response = JSON.parse(xhr.responseText);
                    showAlert('error', 'Error', response.message || 'Failed to toggle user status');
                }
            });
        }

        function submitUser() {
            // Clear previous errors
            clearFormErrors();

            const formData = new FormData($('#userForm')[0]);
            let url = isEditMode ? `/admin/users/${$('#userId').val()}` : '/admin/users';
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
                        $('#userModal').modal('hide');
                        usersTable.ajax.reload();
                        loadStatistics();
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
                        showAlert('error', 'Error', response.message || 'Failed to save user');
                    }
                },
                complete: function() {
                    showFormLoading(false);
                }
            });
        }

        function resetForm() {
            $('#userForm')[0].reset();
            $('#userId').val('');
            clearFormErrors();
            showFormLoading(false);

            // Reset preview
            $('#previewAvatar').attr('src', '{{ asset('admin/backend/dist/assets/images/users/avatar-1.jpg') }}');
            $('#previewName').text('User Name');
            $('#previewEmail').text('user@example.com');
            $('#previewRole').removeClass('bg-danger bg-warning bg-info').addClass('bg-secondary').text('Role');
            $('#previewDepartment').text('-');
            $('#previewPhone').text('-');
        }

        function showFormLoading(show) {
            if (show) {
                $('#submitSpinner').removeClass('d-none');
                $('#userForm button[type="submit"]').prop('disabled', true);
            } else {
                $('#submitSpinner').addClass('d-none');
                $('#userForm button[type="submit"]').prop('disabled', false);
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
                    case 'password_confirmation':
                        input = $('#userPasswordConfirmation');
                        errorDiv = $('#passwordconfirmationError');
                        break;
                    case 'profile_image':
                        input = $('#userProfileImage');
                        errorDiv = $('#profileimageError');
                        break;
                    case 'is_active':
                        input = $('#userStatus');
                        errorDiv = $('#isactiveError');
                        break;
                    default:
                        input = $(`#user${field.charAt(0).toUpperCase() + field.slice(1)}`);
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
