<header class="app-topbar">
    <div class="page-container topbar-menu">
        <div class="d-flex align-items-center gap-2">

            <!-- Brand Logo -->
            <a href="{{ route('admin.dashboard.index') }}" class="logo">
                <span class="logo-light">
                    <span class="logo-lg"><img src="{{ asset('assets/logo/logo-app.jpg') }}" alt="logo"></span>
                    <span class="logo-sm"><img src="{{ asset('assets/logo/logo-app.jpg') }}" alt="small logo"></span>
                </span>

                <span class="logo-dark">
                    <span class="logo-lg"><img src="{{ asset('assets/logo/logo-app.jpg') }}" alt="dark logo"></span>
                    <span class="logo-sm"><img src="{{ asset('assets/logo/logo-app.jpg') }}" alt="small logo"></span>
                </span>
            </a>

            <!-- Sidebar Menu Toggle Button -->
            <button class="sidenav-toggle-button px-2">
                <i class="ri-menu-2-line fs-24"></i>
            </button>

            <!-- Horizontal Menu Toggle Button -->
            <button class="topnav-toggle-button px-2" data-bs-toggle="collapse" data-bs-target="#topnav-menu-content">
                <i class="ri-menu-2-line fs-24"></i>
            </button>

            <!-- Search for small devices -->
            <div class="topbar-item d-flex d-xl-none">
                <button class="topbar-link" data-bs-toggle="modal" data-bs-target="#searchModal" type="button">
                    <i class="ri-search-line fs-22"></i>
                </button>
            </div>

            <!-- Button Trigger Search Modal -->
            <div class="topbar-search d-none d-xl-flex gap-2 me-2 align-items-center" data-bs-toggle="modal"
                data-bs-target="#searchModal" type="button">
                <i class="ri-search-line fs-18"></i>
                <span class="me-2">Search something..</span>
            </div>
        </div>

        <div class="d-flex align-items-center gap-2">

            <!-- Notification Dropdown -->
            <div class="topbar-item">
                <div class="dropdown">
                    <button class="topbar-link dropdown-toggle drop-arrow-none" data-bs-toggle="dropdown"
                        data-bs-offset="0,25" type="button" data-bs-auto-close="outside" aria-haspopup="false"
                        aria-expanded="false">
                        <i data-lucide="bell" class="animate-ring fs-22"></i>
                        <span class="noti-icon-badge"></span>
                    </button>

                    <div class="dropdown-menu p-0 dropdown-menu-end dropdown-menu-lg" style="min-height: 300px;">
                        <div class="p-2 border-bottom position-relative border-dashed">
                            <div class="row align-items-center">
                                <div class="col">
                                    <h6 class="m-0 fs-16 fw-semibold"> Notifications</h6>
                                </div>
                                <div class="col-auto">
                                    <div class="dropdown">
                                        <a href="#" class="dropdown-toggle drop-arrow-none link-dark"
                                            data-bs-toggle="dropdown" data-bs-offset="0,15" aria-expanded="false">
                                            <i class="ri-settings-2-line fs-22 align-middle"></i>
                                        </a>
                                        <div class="dropdown-menu dropdown-menu-end">
                                            <a href="javascript:void(0);" class="dropdown-item">Mark as Read</a>
                                            <a href="javascript:void(0);" class="dropdown-item">Delete All</a>
                                            <a href="javascript:void(0);" class="dropdown-item">Settings</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="position-relative rounded-0" style="max-height: 300px;" data-simplebar>
                            <!-- No notifications placeholder -->
                            <div class="text-center py-4">
                                <i class="ri-notification-line fs-48 text-muted"></i>
                                <p class="text-muted mt-2">No new notifications</p>
                            </div>
                        </div>

                        <!-- All notifications link -->
                        <a href="javascript:void(0);"
                            class="dropdown-item position-absolute bottom-0 notification-item text-center text-reset text-decoration-underline fw-bold notify-item border-top border-light py-2">
                            View All Notifications
                        </a>
                    </div>
                </div>
            </div>

            <!-- Light/Dark Mode Button -->
            <div class="topbar-item d-none d-sm-flex">
                <button class="topbar-link" id="light-dark-mode" type="button">
                    <i data-lucide="moon" class="light-mode-icon fs-22"></i>
                    <i data-lucide="sun" class="dark-mode-icon fs-22"></i>
                </button>
            </div>

            <!-- Button Trigger Customizer Offcanvas -->
            <div class="topbar-item d-none d-sm-flex">
                <button class="topbar-link" data-bs-toggle="offcanvas" data-bs-target="#theme-settings-offcanvas"
                    type="button">
                    <i data-lucide="settings" class="fs-22"></i>
                </button>
            </div>

            <!-- User Dropdown -->
            <div class="topbar-item nav-user">
                <div class="dropdown">
                    <a class="topbar-link dropdown-toggle drop-arrow-none px-2" data-bs-toggle="dropdown"
                        data-bs-offset="0,25" type="button" aria-haspopup="false" aria-expanded="false">
                        @if (auth()->user()->profile_image)
                            <img src="{{ url('storage/' . auth()->user()->profile_image) }}" width="32"
                                class="rounded-circle me-lg-2 d-flex" alt="user-image" height="32">
                        @else
                            <img src="{{ asset('admin/backend/dist') }}/assets/images/users/avatar-1.jpg"
                                width="32" class="rounded-circle me-lg-2 d-flex" alt="user-image"
                                height="32">
                        @endif
                        <span class="d-lg-flex flex-column gap-1 d-none">
                            <span class="fw-semibold">{{ auth()->user()->name }}</span>
                            <span class="fs-12">{{ auth()->user()->role_display }}</span>
                        </span>
                        <i class="ri-arrow-down-s-line d-none d-lg-block align-middle ms-2"></i>
                    </a>
                    <div class="dropdown-menu dropdown-menu-end">
                        <!-- item-->
                        <div class="dropdown-header noti-title">
                            <h6 class="text-overflow m-0">Welcome {{ auth()->user()->name }}!</h6>
                        </div>

                        <!-- item-->
                        <a href="{{ route('admin.profile.index') }}" class="dropdown-item">
                            <i class="ri-account-circle-line me-1 fs-16 align-middle"></i>
                            <span class="align-middle">My Profile</span>
                        </a>

                        <div class="dropdown-divider"></div>

                        <!-- item-->
                        <a href="javascript:void(0);" class="dropdown-item" onclick="logout()">
                            <i class="ri-logout-box-line me-1 fs-16 align-middle"></i>
                            <span class="align-middle">Sign Out</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</header>

<!-- Logout Form (Hidden) -->
<form id="logout-form" action="{{ route('admin.logout') }}" method="POST" style="display: none;">
    @csrf
</form>

<script>
    function logout() {
        if (confirm('Are you sure you want to logout?')) {
            document.getElementById('logout-form').submit();
        }
    }
</script>
