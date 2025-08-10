<div class="sidenav-menu">

    <!-- Brand Logo -->
    <a href="index.html" class="logo">
        <span class="logo-light">
            <span class="logo-lg"><img src="{{ asset('admin/backend/dist') }}/assets/images/logo.png"
                    alt="logo"></span>
            <span class="logo-sm"><img src="{{ asset('admin/backend/dist') }}/assets/images/logo-sm.png"
                    alt="small logo"></span>
        </span>

        <span class="logo-dark">
            <span class="logo-lg"><img src="{{ asset('admin/backend/dist') }}/assets/images/logo-dark.png"
                    alt="dark logo"></span>
            <span class="logo-sm"><img src="{{ asset('admin/backend/dist') }}/assets/images/logo-sm.png"
                    alt="small logo"></span>
        </span>
    </a>

    <!-- Full Sidebar Menu Close Button -->
    <button class="button-close-fullsidebar">
        <i class="ri-close-line align-middle"></i>
    </button>

    <div data-simplebar>

        <!--- Sidenav Menu -->
        <ul class="side-nav">
            <li class="side-nav-title">Navigation</li>

            <li class="side-nav-item">
                <a href="{{ route('admin.dashboard') }}"
                    class="side-nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                    <span class="menu-icon"><i data-lucide="airplay"></i></span>
                    <span class="menu-text"> Dashboard </span>
                </a>
            </li>

            <li class="side-nav-title">HSE Management</li>

            <li class="side-nav-item">
                <a data-bs-toggle="collapse" href="#sidebarReports" aria-expanded="false" aria-controls="sidebarReports"
                    class="side-nav-link">
                    <span class="menu-icon"><i data-lucide="file-text"></i></span>
                    <span class="menu-text"> Reports</span>
                    <span class="menu-arrow"></span>
                </a>
                <div class="collapse" id="sidebarReports">
                    <ul class="sub-menu">
                        <li class="side-nav-item">
                            <a href="#" class="side-nav-link">
                                <span class="menu-text">All Reports</span>
                            </a>
                        </li>
                        <li class="side-nav-item">
                            <a href="#" class="side-nav-link">
                                <span class="menu-text">Pending Reports</span>
                            </a>
                        </li>
                        <li class="side-nav-item">
                            <a href="#" class="side-nav-link">
                                <span class="menu-text">Report Analytics</span>
                            </a>
                        </li>
                    </ul>
                </div>
            </li>

            <li class="side-nav-item">
                <a data-bs-toggle="collapse" href="#sidebarObservations" aria-expanded="false"
                    aria-controls="sidebarObservations" class="side-nav-link">
                    <span class="menu-icon"><i data-lucide="eye"></i></span>
                    <span class="menu-text"> Observations</span>
                    <span class="menu-arrow"></span>
                </a>
                <div class="collapse" id="sidebarObservations">
                    <ul class="sub-menu">
                        <li class="side-nav-item">
                            <a href="#" class="side-nav-link">
                                <span class="menu-text">All Observations</span>
                            </a>
                        </li>
                        <li class="side-nav-item">
                            <a href="#" class="side-nav-link">
                                <span class="menu-text">Submitted Observations</span>
                            </a>
                        </li>
                        <li class="side-nav-item">
                            <a href="#" class="side-nav-link">
                                <span class="menu-text">Observation Statistics</span>
                            </a>
                        </li>
                    </ul>
                </div>
            </li>

            <li class="side-nav-title">Master Data</li>

            <li class="side-nav-item">
                <a href="{{ route('admin.categories.index') }}"
                    class="side-nav-link {{ request()->routeIs('admin.categories.*') ? 'active' : '' }}">
                    <span class="menu-icon"><i data-lucide="tag"></i></span>
                    <span class="menu-text"> Categories </span>
                </a>
            </li>

            <li class="side-nav-item">
                <a data-bs-toggle="collapse" href="#sidebarContributing" aria-expanded="false"
                    aria-controls="sidebarContributing" class="side-nav-link">
                    <span class="menu-icon"><i data-lucide="layers"></i></span>
                    <span class="menu-text"> Contributing Factors</span>
                    <span class="menu-arrow"></span>
                </a>
                <div class="collapse" id="sidebarContributing">
                    <ul class="sub-menu">
                        <li class="side-nav-item">
                            <a href="#" class="side-nav-link">
                                <span class="menu-text">All Contributing Factors</span>
                            </a>
                        </li>
                        <li class="side-nav-item">
                            <a href="#" class="side-nav-link">
                                <span class="menu-text">Actions Management</span>
                            </a>
                        </li>
                    </ul>
                </div>
            </li>

            <li class="side-nav-item">
                <a href="{{ route('admin.banners.index') }}"
                    class="side-nav-link {{ request()->routeIs('admin.banners.*') ? 'active' : '' }}">
                    <span class="menu-icon"><i data-lucide="image"></i></span>
                    <span class="menu-text"> Banners </span>
                </a>
            </li>

            <li class="side-nav-title">User Management</li>

            <li class="side-nav-item">
                <a href="{{ route('admin.users.index') }}"
                    class="side-nav-link {{ request()->routeIs('admin.users.*') && !request()->has('role') ? 'active' : '' }}">
                    <span class="menu-icon"><i data-lucide="users"></i></span>
                    <span class="menu-text"> All Users </span>
                </a>
            </li>

            <li class="side-nav-item">
                <a data-bs-toggle="collapse" href="#sidebarUsersByRole"
                    aria-expanded="{{ request()->routeIs('admin.users.*') && request()->has('role') ? 'true' : 'false' }}"
                    aria-controls="sidebarUsersByRole"
                    class="side-nav-link {{ request()->routeIs('admin.users.*') && request()->has('role') ? 'active' : '' }}">
                    <span class="menu-icon"><i data-lucide="user-check"></i></span>
                    <span class="menu-text"> Users by Role</span>
                    <span class="menu-arrow"></span>
                </a>
                <div class="collapse {{ request()->routeIs('admin.users.*') && request()->has('role') ? 'show' : '' }}"
                    id="sidebarUsersByRole">
                    <ul class="sub-menu">
                        <li class="side-nav-item">
                            <a href="{{ route('admin.users.index') }}?role=admin"
                                class="side-nav-link {{ request()->routeIs('admin.users.*') && request()->get('role') == 'admin' ? 'text-primary' : '' }}">
                                <span class="menu-text">Administrators</span>
                            </a>
                        </li>
                        <li class="side-nav-item">
                            <a href="{{ route('admin.users.index') }}?role=hse_staff"
                                class="side-nav-link {{ request()->routeIs('admin.users.*') && request()->get('role') == 'hse_staff' ? 'text-primary' : '' }}">
                                <span class="menu-text">HSE Staff</span>
                            </a>
                        </li>
                        <li class="side-nav-item">
                            <a href="{{ route('admin.users.index') }}?role=employee"
                                class="side-nav-link {{ request()->routeIs('admin.users.*') && request()->get('role') == 'employee' ? 'text-primary' : '' }}">
                                <span class="menu-text">Employees</span>
                            </a>
                        </li>
                    </ul>
                </div>
            </li>

            <li class="side-nav-item">
                <a href="{{ route('admin.profile.index') }}"
                    class="side-nav-link {{ request()->routeIs('admin.profile.*') ? 'active' : '' }}">
                    <span class="menu-icon"><i data-lucide="user"></i></span>
                    <span class="menu-text"> Profile </span>
                </a>
            </li>

            <li class="side-nav-title">System</li>

            <li class="side-nav-item">
                <a href="#" class="side-nav-link">
                    <span class="menu-icon"><i data-lucide="bell"></i></span>
                    <span class="menu-text"> Notifications </span>
                </a>
            </li>

            <li class="side-nav-item">
                <a href="#" class="side-nav-link">
                    <span class="menu-icon"><i data-lucide="settings"></i></span>
                    <span class="menu-text"> Settings </span>
                </a>
            </li>

            <li class="side-nav-item">
                <a data-bs-toggle="collapse" href="#sidebarPagesAuth" aria-expanded="false"
                    aria-controls="sidebarPagesAuth" class="side-nav-link">
                    <span class="menu-icon"><i data-lucide="log-out"></i></span>
                    <span class="menu-text"> Auth Pages </span>
                    <span class="menu-arrow"></span>
                </a>
                <div class="collapse" id="sidebarPagesAuth">
                    <ul class="sub-menu">
                        <li class="side-nav-item">
                            <a href="#" class="side-nav-link">
                                <span class="menu-text">Login</span>
                            </a>
                        </li>
                        <li class="side-nav-item">
                            <a href="#" class="side-nav-link">
                                <span class="menu-text">Register</span>
                            </a>
                        </li>
                        <li class="side-nav-item">
                            <a href="#" class="side-nav-link">
                                <span class="menu-text">Logout</span>
                            </a>
                        </li>
                        <li class="side-nav-item">
                            <a href="#" class="side-nav-link">
                                <span class="menu-text">Recover Password</span>
                            </a>
                        </li>
                    </ul>
                </div>
            </li>

        </ul>

        <div class="clearfix"></div>
    </div>
</div>

{{-- Custom JavaScript for Sidebar Active State --}}
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Handle sidebar active states for user management
        const currentPath = window.location.pathname;
        const currentParams = new URLSearchParams(window.location.search);

        // Check if we're on users page
        if (currentPath.includes('/admin/users')) {
            // Get role parameter
            const roleParam = currentParams.get('role');

            if (roleParam) {
                // We're on a filtered users page
                // Ensure the collapse is shown
                const usersByRoleCollapse = document.getElementById('sidebarUsersByRole');
                const usersByRoleToggle = document.querySelector('[href="#sidebarUsersByRole"]');

                if (usersByRoleCollapse) {
                    usersByRoleCollapse.classList.add('show');
                }

                if (usersByRoleToggle) {
                    usersByRoleToggle.classList.add('active');
                    usersByRoleToggle.setAttribute('aria-expanded', 'true');
                }

                // Set active on specific role link
                const roleLinks = {
                    'admin': 'a[href*="role=admin"]',
                    'hse_staff': 'a[href*="role=hse_staff"]',
                    'employee': 'a[href*="role=employee"]'
                };

                if (roleLinks[roleParam]) {
                    const activeLink = document.querySelector(roleLinks[roleParam]);
                    if (activeLink) {
                        activeLink.classList.add('active');
                    }
                }
            } else {
                // We're on the main users page (All Users)
                const allUsersLink = document.querySelector('a[href="' + "{{ route('admin.users.index') }}" +
                    '"]');
                if (allUsersLink && !allUsersLink.getAttribute('href').includes('role=')) {
                    allUsersLink.classList.add('active');
                }
            }
        }
    });

    // Handle collapse state when clicking on role filter links
    document.addEventListener('click', function(e) {
        const target = e.target.closest('a');
        if (target && target.getAttribute('href') && target.getAttribute('href').includes(
                'admin/users?role=')) {
            // Ensure the collapse stays open when navigating between role filters
            const usersByRoleCollapse = document.getElementById('sidebarUsersByRole');
            if (usersByRoleCollapse) {
                // Mark that we want this to stay open
                sessionStorage.setItem('keepUsersByRoleOpen', 'true');
            }
        }
    });
</script>
