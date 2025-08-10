<div class="sidenav-menu">

    <!-- Brand Logo -->
    <a href="{{ route('admin.dashboard') }}" class="logo">
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
            <li class="side-nav-title">Main</li>

            <!-- Dashboard -->
            <li class="side-nav-item">
                <a href="{{ route('admin.dashboard') }}"
                    class="side-nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                    <span class="menu-icon"><i data-lucide="home"></i></span>
                    <span class="menu-text"> Dashboard </span>
                </a>
            </li>

            <!-- HSE Management -->
            <li class="side-nav-title">HSE Management</li>

            <!-- Reports -->
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
                                <span class="menu-text">In Progress</span>
                            </a>
                        </li>
                        <li class="side-nav-item">
                            <a href="#" class="side-nav-link">
                                <span class="menu-text">Completed</span>
                            </a>
                        </li>
                        <li class="side-nav-item">
                            <a href="#" class="side-nav-link">
                                <span class="menu-text">Report Details</span>
                            </a>
                        </li>
                    </ul>
                </div>
            </li>

            <!-- Observations -->
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
                                <span class="menu-text">Submitted</span>
                            </a>
                        </li>
                        <li class="side-nav-item">
                            <a href="#" class="side-nav-link">
                                <span class="menu-text">Under Review</span>
                            </a>
                        </li>
                        <li class="side-nav-item">
                            <a href="#" class="side-nav-link">
                                <span class="menu-text">Approved</span>
                            </a>
                        </li>
                    </ul>
                </div>
            </li>

            <!-- Master Data -->
            <li class="side-nav-item">
                <a data-bs-toggle="collapse" href="#sidebarMasterData" aria-expanded="false"
                    aria-controls="sidebarMasterData" class="side-nav-link">
                    <span class="menu-icon"><i data-lucide="database"></i></span>
                    <span class="menu-text"> Master Data</span>
                    <span class="menu-arrow"></span>
                </a>
                <div class="collapse" id="sidebarMasterData">
                    <ul class="sub-menu">
                        <li class="side-nav-item">
                            <a href="#" class="side-nav-link">
                                <span class="menu-text">Categories</span>
                            </a>
                        </li>
                        <li class="side-nav-item">
                            <a href="#" class="side-nav-link">
                                <span class="menu-text">Contributing Factors</span>
                            </a>
                        </li>
                        <li class="side-nav-item">
                            <a href="#" class="side-nav-link">
                                <span class="menu-text">Actions</span>
                            </a>
                        </li>
                    </ul>
                </div>
            </li>

            <!-- User Management -->
            <li class="side-nav-title">User Management</li>

            <!-- Users -->
            <li class="side-nav-item">
                <a data-bs-toggle="collapse" href="#sidebarUsers" aria-expanded="false" aria-controls="sidebarUsers"
                    class="side-nav-link">
                    <span class="menu-icon"><i data-lucide="users"></i></span>
                    <span class="menu-text"> Users</span>
                    <span class="menu-arrow"></span>
                </a>
                <div class="collapse" id="sidebarUsers">
                    <ul class="sub-menu">
                        <li class="side-nav-item">
                            <a href="#" class="side-nav-link">
                                <span class="menu-text">All Users</span>
                            </a>
                        </li>
                        <li class="side-nav-item">
                            <a href="#" class="side-nav-link">
                                <span class="menu-text">Employees</span>
                            </a>
                        </li>
                        <li class="side-nav-item">
                            <a href="#" class="side-nav-link">
                                <span class="menu-text">HSE Staff</span>
                            </a>
                        </li>
                        <li class="side-nav-item">
                            <a href="#" class="side-nav-link">
                                <span class="menu-text">Administrators</span>
                            </a>
                        </li>
                    </ul>
                </div>
            </li>

            <!-- Content Management -->
            <li class="side-nav-title">Content</li>

            <!-- Banners -->
            <li class="side-nav-item">
                <a href="#" class="side-nav-link">
                    <span class="menu-icon"><i data-lucide="image"></i></span>
                    <span class="menu-text"> Banners </span>
                </a>
            </li>

            <!-- Notifications -->
            <li class="side-nav-item">
                <a href="#" class="side-nav-link">
                    <span class="menu-icon"><i data-lucide="bell"></i></span>
                    <span class="menu-text"> Notifications </span>
                </a>
            </li>

            <!-- Settings -->
            <li class="side-nav-title">Settings</li>

            <!-- System Settings -->
            <li class="side-nav-item">
                <a data-bs-toggle="collapse" href="#sidebarSettings" aria-expanded="false"
                    aria-controls="sidebarSettings" class="side-nav-link">
                    <span class="menu-icon"><i data-lucide="settings"></i></span>
                    <span class="menu-text"> Settings</span>
                    <span class="menu-arrow"></span>
                </a>
                <div class="collapse" id="sidebarSettings">
                    <ul class="sub-menu">
                        <li class="side-nav-item">
                            <a href="#" class="side-nav-link">
                                <span class="menu-text">General Settings</span>
                            </a>
                        </li>
                        <li class="side-nav-item">
                            <a href="#" class="side-nav-link">
                                <span class="menu-text">Email Settings</span>
                            </a>
                        </li>
                        <li class="side-nav-item">
                            <a href="#" class="side-nav-link">
                                <span class="menu-text">Backup & Restore</span>
                            </a>
                        </li>
                    </ul>
                </div>
            </li>

            <!-- Profile -->
            <li class="side-nav-item">
                <a href="{{ route('admin.profile.index') }}"
                    class="side-nav-link {{ request()->routeIs('admin.profile.*') ? 'active' : '' }}">
                    <span class="menu-icon"><i data-lucide="user"></i></span>
                    <span class="menu-text"> My Profile </span>
                </a>
            </li>

            <!-- Analytics & Reports -->
            <li class="side-nav-title">Analytics</li>

            <!-- Analytics -->
            <li class="side-nav-item">
                <a data-bs-toggle="collapse" href="#sidebarAnalytics" aria-expanded="false"
                    aria-controls="sidebarAnalytics" class="side-nav-link">
                    <span class="menu-icon"><i data-lucide="bar-chart-2"></i></span>
                    <span class="menu-text"> Analytics</span>
                    <span class="menu-arrow"></span>
                </a>
                <div class="collapse" id="sidebarAnalytics">
                    <ul class="sub-menu">
                        <li class="side-nav-item">
                            <a href="#" class="side-nav-link">
                                <span class="menu-text">HSE Statistics</span>
                            </a>
                        </li>
                        <li class="side-nav-item">
                            <a href="#" class="side-nav-link">
                                <span class="menu-text">Performance Reports</span>
                            </a>
                        </li>
                        <li class="side-nav-item">
                            <a href="#" class="side-nav-link">
                                <span class="menu-text">Trend Analysis</span>
                            </a>
                        </li>
                        <li class="side-nav-item">
                            <a href="#" class="side-nav-link">
                                <span class="menu-text">Export Data</span>
                            </a>
                        </li>
                    </ul>
                </div>
            </li>

        </ul>

        <div class="clearfix"></div>
    </div>
</div>
