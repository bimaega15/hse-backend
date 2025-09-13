<div class="sidenav-menu">

    <!-- Brand Logo -->
    <a href="index.html" class="logo">
        <span class="logo-light">
            <span class="logo-lg"><img src="{{ asset('assets/logo/logo-app.jpg') }}" alt="logo"></span>
            <span class="logo-sm"><img src="{{ asset('admin/backend/dist') }}/assets/images/logo-sm.png"
                    alt="small logo"></span>
        </span>

        <span class="logo-dark">
            <span class="logo-lg"><img src="{{ asset('assets/logo/logo-app.jpg') }}" alt="dark logo"></span>
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
                <a href="{{ route('admin.dashboard.index') }}"
                    class="side-nav-link {{ request()->routeIs('admin.dashboard.index') ? 'active' : '' }}">
                    <span class="menu-icon"><i data-lucide="airplay"></i></span>
                    <span class="menu-text"> Dashboard </span>
                </a>
            </li>

            <li class="side-nav-title">BAIK Management</li>

            <li class="side-nav-item">
                <a data-bs-toggle="collapse" href="#sidebarReports"
                    aria-expanded="{{ request()->routeIs('admin.reports.*') ? 'true' : 'false' }}"
                    aria-controls="sidebarReports"
                    class="side-nav-link {{ request()->routeIs('admin.reports.*') ? 'active' : '' }}">
                    <span class="menu-icon"><i data-lucide="file-text"></i></span>
                    <span class="menu-text"> Reports</span>
                    <span class="menu-arrow"></span>
                </a>
                <div class="collapse {{ request()->routeIs('admin.reports.*') ? 'show' : '' }}" id="sidebarReports">
                    <ul class="sub-menu">
                        <li class="side-nav-item">
                            <a href="{{ route('admin.reports.index') }}"
                                class="side-nav-link {{ request()->routeIs('admin.reports.index') && !request()->has('status') ? 'text-primary' : '' }}">
                                <span class="menu-text">All Reports</span>
                            </a>
                        </li>
                        <li class="side-nav-item">
                            <a href="{{ route('admin.reports.index') }}?status=waiting"
                                class="side-nav-link {{ request()->routeIs('admin.reports.index') && request()->get('status') == 'waiting' ? 'text-primary' : '' }}">
                                <span class="menu-text">Pending Reports</span>
                            </a>
                        </li>
                        <li class="side-nav-item">
                            <a href="{{ route('admin.reports.index') }}?view=analytics"
                                class="side-nav-link {{ request()->routeIs('admin.reports.index') && request()->get('view') == 'analytics' ? 'text-primary' : '' }}">
                                <span class="menu-text">Report Analytics</span>
                            </a>
                        </li>
                    </ul>
                </div>
            </li>

            <li class="side-nav-item">
                <a data-bs-toggle="collapse" href="#sidebarObservations"
                    aria-expanded="{{ request()->routeIs('admin.observations.*') ? 'true' : 'false' }}"
                    aria-controls="sidebarObservations"
                    class="side-nav-link {{ request()->routeIs('admin.observations.*') ? 'active' : '' }}">
                    <span class="menu-icon"><i data-lucide="eye"></i></span>
                    <span class="menu-text"> Observations</span>
                    <span class="menu-arrow"></span>
                </a>
                <div class="collapse {{ request()->routeIs('admin.observations.*') ? 'show' : '' }}"
                    id="sidebarObservations">
                    <ul class="sub-menu">
                        <li class="side-nav-item">
                            <a href="{{ route('admin.observations.index') }}"
                                class="side-nav-link {{ request()->routeIs('admin.observations.index') && !request()->has('status') && !request()->has('view') ? 'text-primary' : '' }}">
                                <span class="menu-text">All Observations</span>
                            </a>
                        </li>
                        <li class="side-nav-item">
                            <a href="{{ route('admin.observations.index') }}?status=submitted"
                                class="side-nav-link {{ request()->routeIs('admin.observations.index') && request()->get('status') == 'submitted' ? 'text-primary' : '' }}">
                                <span class="menu-text">Submitted Observations</span>
                            </a>
                        </li>
                        <li class="side-nav-item">
                            <a href="{{ route('admin.observations.index') }}?view=analytics"
                                class="side-nav-link {{ request()->routeIs('admin.observations.index') && request()->get('view') == 'analytics' ? 'text-primary' : '' }}">
                                <span class="menu-text">Observation Analytics</span>
                            </a>
                        </li>
                    </ul>
                </div>
            </li>

            <li class="side-nav-title">Master Data</li>

            <li class="side-nav-item">
                <a data-bs-toggle="collapse" href="#sidebarContributing"
                    aria-expanded="{{ request()->routeIs('admin.contributing.*') || request()->routeIs('admin.actions.*') || request()->routeIs('admin.categories.*') ? 'true' : 'false' }}"
                    aria-controls="sidebarContributing"
                    class="side-nav-link {{ request()->routeIs('admin.contributing.*') || request()->routeIs('admin.actions.*') || request()->routeIs('admin.categories.*') ? 'active' : '' }}">
                    <span class="menu-icon"><i data-lucide="layers"></i></span>
                    <span class="menu-text"> Risk Management</span>
                    <span class="menu-arrow"></span>
                </a>
                <div class="collapse {{ request()->routeIs('admin.contributing.*') || request()->routeIs('admin.actions.*') || request()->routeIs('admin.categories.*') ? 'show' : '' }}"
                    id="sidebarContributing">
                    <ul class="sub-menu">
                        <li class="side-nav-item">
                            <a href="{{ route('admin.categories.index') }}"
                                class="side-nav-link {{ request()->routeIs('admin.categories.*') ? 'text-primary' : '' }}">
                                <span class="menu-text">Risk Categories</span>
                            </a>
                        </li>
                        <li class="side-nav-item">
                            <a href="{{ route('admin.contributing.index') }}"
                                class="side-nav-link {{ request()->routeIs('admin.contributing.*') ? 'text-primary' : '' }}">
                                <span class="menu-text">Contributing Factors</span>
                            </a>
                        </li>
                        <li class="side-nav-item">
                            <a href="{{ route('admin.actions.index') }}"
                                class="side-nav-link {{ request()->routeIs('admin.actions.*') ? 'text-primary' : '' }}">
                                <span class="menu-text">Corrective Actions</span>
                            </a>
                        </li>
                    </ul>
                </div>
            </li>

            <li class="side-nav-item">
                <a href="{{ route('admin.locations.index') }}"
                    class="side-nav-link {{ request()->routeIs('admin.locations.*') ? 'active' : '' }}">
                    <span class="menu-icon"><i data-lucide="map-pin"></i></span>
                    <span class="menu-text"> Locations </span>
                </a>
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
                                <span class="menu-text">BAIK Staff</span>
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
                <a href="{{ route('admin.notifications.index') }}"
                    class="side-nav-link {{ request()->routeIs('admin.notifications.*') ? 'active' : '' }}">
                    <span class="menu-icon"><i data-lucide="bell"></i></span>
                    <span class="menu-text"> Notifications </span>
                </a>
            </li>

            <li class="side-nav-item">
                <a href="#" onclick="event.preventDefault(); document.getElementById('logout-form').submit();"
                    class="side-nav-link">
                    <span class="menu-icon"><i data-lucide="log-out"></i></span>
                    <span class="menu-text"> Logout </span>
                </a>
                <form id="logout-form" action="{{ route('admin.logout') }}" method="POST" style="display: none;">
                    @csrf
                </form>
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

        // Handle sidebar active states for risk factor management (categories, contributing factors, actions)
        if (currentPath.includes('/admin/contributing') || currentPath.includes('/admin/actions') || currentPath
            .includes('/admin/categories')) {
            const contributingCollapse = document.getElementById('sidebarContributing');
            const contributingToggle = document.querySelector('[href="#sidebarContributing"]');

            if (contributingCollapse) {
                contributingCollapse.classList.add('show');
            }

            if (contributingToggle) {
                contributingToggle.classList.add('active');
                contributingToggle.setAttribute('aria-expanded', 'true');
            }
        }

        if (currentPath.includes('/admin/reports')) {
            const reportsCollapse = document.getElementById('sidebarReports');
            const reportsToggle = document.querySelector('[href="#sidebarReports"]');

            if (reportsCollapse) {
                reportsCollapse.classList.add('show');
            }

            if (reportsToggle) {
                reportsToggle.classList.add('active');
                reportsToggle.setAttribute('aria-expanded', 'true');
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

        // Handle risk factor management section
        if (target && target.getAttribute('href') && (target.getAttribute('href').includes(
                    'admin/contributing') ||
                target.getAttribute('href').includes('admin/actions') ||
                target.getAttribute('href').includes('admin/categories'))) {
            // Ensure the collapse stays open when navigating between risk factor sections
            const contributingCollapse = document.getElementById('sidebarContributing');
            if (contributingCollapse) {
                sessionStorage.setItem('keepContributingOpen', 'true');
            }
        }

        if (target && target.getAttribute('href') && target.getAttribute('href').includes('admin/reports')) {
            const reportsCollapse = document.getElementById('sidebarReports');
            if (reportsCollapse) {
                sessionStorage.setItem('keepReportsOpen', 'true');
            }
        }
    });
</script>
