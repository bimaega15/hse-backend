@extends('admin.layouts')
@section('content')
    <div class="page-content">
        <div class="page-title-head d-flex align-items-center gap-2">
            <div class="flex-grow-1">
                <h4 class="fs-18 fw-bold mb-0">HSE Dashboard</h4>
                <p class="text-muted mb-0">Monitoring & Overview Health, Safety & Environment</p>
            </div>

            <div class="text-end">
                <ol class="breadcrumb m-0 py-0 fs-13">
                    <li class="breadcrumb-item"><a href="javascript: void(0);">HSE Admin</a></li>
                    <li class="breadcrumb-item active">Dashboard</li>
                </ol>
            </div>
        </div>

        <div class="page-container">
            <!-- Statistics Cards -->
            <div class="row row-cols-xxl-4 row-cols-md-2 row-cols-1">
                <div class="col">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex align-items-start gap-2 justify-content-between">
                                <div>
                                    <h5 class="text-muted fs-13 fw-bold text-uppercase">Total Reports</h5>
                                    <h3 class="mt-2 mb-1 fw-bold" id="total-reports">125</h3>
                                    <p class="mb-0 text-muted">
                                        <span class="text-success me-1"><i class="ri-arrow-up-line"></i>
                                            8.5%</span>
                                        <span class="text-nowrap">Since last month</span>
                                    </p>
                                </div>
                                <div class="avatar-lg flex-shrink-0">
                                    <span class="avatar-title bg-primary-subtle text-primary rounded fs-28">
                                        <iconify-icon icon="solar:document-bold-duotone"></iconify-icon>
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="apex-charts" id="chart-reports"></div>
                    </div>
                </div>

                <div class="col">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex align-items-start gap-2 justify-content-between">
                                <div>
                                    <h5 class="text-muted fs-13 fw-bold text-uppercase">Pending Reports</h5>
                                    <h3 class="mt-2 mb-1 fw-bold text-warning" id="pending-reports">12</h3>
                                    <p class="mb-0 text-muted">
                                        <span class="text-warning me-1"><i class="ri-time-line"></i>
                                            Need Action</span>
                                        <span class="text-nowrap">Requires review</span>
                                    </p>
                                </div>
                                <div class="avatar-lg flex-shrink-0">
                                    <span class="avatar-title bg-warning-subtle text-warning rounded fs-28">
                                        <iconify-icon icon="solar:clock-circle-bold-duotone"></iconify-icon>
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="apex-charts" id="chart-pending"></div>
                    </div>
                </div>

                <div class="col">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex align-items-start gap-2 justify-content-between">
                                <div>
                                    <h5 class="text-muted fs-13 fw-bold text-uppercase">Critical Incidents</h5>
                                    <h3 class="mt-2 mb-1 fw-bold text-danger" id="critical-incidents">3</h3>
                                    <p class="mb-0 text-muted">
                                        <span class="text-danger me-1"><i class="ri-alert-line"></i>
                                            High Priority</span>
                                        <span class="text-nowrap">This month</span>
                                    </p>
                                </div>
                                <div class="avatar-lg flex-shrink-0">
                                    <span class="avatar-title bg-danger-subtle text-danger rounded fs-28">
                                        <iconify-icon icon="solar:danger-triangle-bold-duotone"></iconify-icon>
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="apex-charts" id="chart-critical"></div>
                    </div>
                </div>

                <div class="col">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex align-items-start gap-2 justify-content-between">
                                <div>
                                    <h5 class="text-muted fs-13 fw-bold text-uppercase">Completion Rate</h5>
                                    <h3 class="mt-2 mb-1 fw-bold text-success" id="completion-rate">89.5%</h3>
                                    <p class="mb-0 text-muted">
                                        <span class="text-success me-1"><i class="ri-arrow-up-line"></i>
                                            12.3%</span>
                                        <span class="text-nowrap">Since last month</span>
                                    </p>
                                </div>
                                <div class="avatar-lg flex-shrink-0">
                                    <span class="avatar-title bg-success-subtle text-success rounded fs-28">
                                        <iconify-icon icon="solar:check-circle-bold-duotone"></iconify-icon>
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="apex-charts" id="chart-completion"></div>
                    </div>
                </div>
            </div>

            <!-- Charts Section -->
            <div class="row">
                <div class="col-xl-8">
                    <div class="card">
                        <div class="d-flex card-header justify-content-between align-items-center">
                            <div>
                                <h4 class="header-title">HSE Reports Overview</h4>
                                <p class="text-muted mb-0">Monthly trend of safety reports and incidents</p>
                            </div>
                            <div class="dropdown">
                                <a href="#" class="dropdown-toggle drop-arrow-none card-drop"
                                    data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="ri-more-2-fill fs-18"></i>
                                </a>
                                <div class="dropdown-menu dropdown-menu-end">
                                    <a href="javascript:void(0);" class="dropdown-item">Download Report</a>
                                    <a href="javascript:void(0);" class="dropdown-item">Export Data</a>
                                    <a href="javascript:void(0);" class="dropdown-item">View Details</a>
                                </div>
                            </div>
                        </div>

                        <div class="card-body px-0 pt-0">
                            <div class="bg-light bg-opacity-50">
                                <div class="row text-center">
                                    <div class="col-md-3 col-6">
                                        <p class="text-muted mt-3 mb-1">Waiting Reports</p>
                                        <h4 class="mb-3">
                                            <span class="text-warning me-1"><i class="ri-time-line"></i></span>
                                            <span id="waiting-count">12</span>
                                        </h4>
                                    </div>
                                    <div class="col-md-3 col-6">
                                        <p class="text-muted mt-3 mb-1">In Progress</p>
                                        <h4 class="mb-3">
                                            <span class="text-info me-1"><i class="ri-play-circle-line"></i></span>
                                            <span id="progress-count">8</span>
                                        </h4>
                                    </div>
                                    <div class="col-md-3 col-6">
                                        <p class="text-muted mt-3 mb-1">Completed</p>
                                        <h4 class="mb-3">
                                            <span class="text-success me-1"><i class="ri-check-circle-line"></i></span>
                                            <span id="completed-count">105</span>
                                        </h4>
                                    </div>
                                    <div class="col-md-3 col-6">
                                        <p class="text-muted mt-3 mb-1">Avg. Resolution</p>
                                        <h4 class="mb-3">
                                            <span class="text-primary me-1"><i class="ri-timer-line"></i></span>
                                            <span id="avg-resolution">2.5 days</span>
                                        </h4>
                                    </div>
                                </div>
                            </div>

                            <div dir="ltr" class="px-1 mt-2">
                                <div id="reports-trend-chart" class="apex-charts" data-colors="#ff6b6b,#4ecdc4,#45b7d1">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-4">
                    <div class="card">
                        <div class="d-flex card-header justify-content-between align-items-center">
                            <div>
                                <h4 class="header-title">Severity Distribution</h4>
                                <p class="text-muted mb-0">Current month breakdown</p>
                            </div>
                        </div>

                        <div class="card-body px-0 pt-0">
                            <div class="border-top border-bottom border-light border-dashed">
                                <div class="row text-center align-items-center">
                                    <div class="col-6">
                                        <p class="text-muted mt-3 mb-1">High/Critical</p>
                                        <h4 class="mb-3">
                                            <span class="text-danger me-1"><i class="ri-error-warning-line"></i></span>
                                            <span id="high-critical">15</span>
                                        </h4>
                                    </div>
                                    <div class="col-6 border-start border-light border-dashed">
                                        <p class="text-muted mt-3 mb-1">Low/Medium</p>
                                        <h4 class="mb-3">
                                            <span class="text-success me-1"><i class="ri-shield-check-line"></i></span>
                                            <span id="low-medium">110</span>
                                        </h4>
                                    </div>
                                </div>
                            </div>

                            <div dir="ltr" class="px-2">
                                <div id="severity-chart" class="apex-charts"
                                    data-colors="#e74c3c,#f39c12,#f1c40f,#27ae60"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tables Section -->
            <div class="row">
                <div class="col-xxl-6">
                    <div class="card">
                        <div class="card-header d-flex flex-wrap align-items-center gap-2">
                            <h4 class="header-title me-auto">Recent HSE Reports</h4>
                            <div class="d-flex gap-2 justify-content-end text-end">
                                <a href="javascript:void(0);" class="btn btn-sm btn-light">View All</a>
                                <a href="javascript:void(0);" class="btn btn-sm btn-primary">New Report</a>
                            </div>
                        </div>

                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-custom table-centered table-sm table-nowrap table-hover mb-0">
                                    <thead class="bg-light">
                                        <tr>
                                            <th>Reporter</th>
                                            <th>Category</th>
                                            <th>Severity</th>
                                            <th>Status</th>
                                            <th>Date</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody id="recent-reports">
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="avatar-sm flex-shrink-0 me-2">
                                                        <span class="avatar-title bg-primary-subtle rounded-circle">
                                                            <i class="ri-user-line"></i>
                                                        </span>
                                                    </div>
                                                    <div>
                                                        <h6 class="fs-14 mt-1 mb-0">John Doe</h6>
                                                        <small class="text-muted">Safety Officer</small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="badge bg-info-subtle text-info">Near Miss</span>
                                            </td>
                                            <td>
                                                <span class="badge bg-warning-subtle text-warning">Medium</span>
                                            </td>
                                            <td>
                                                <span class="badge bg-success-subtle text-success">
                                                    <i class="ri-check-line me-1"></i>Completed
                                                </span>
                                            </td>
                                            <td>
                                                <small class="text-muted">2 hours ago</small>
                                            </td>
                                            <td>
                                                <div class="dropdown">
                                                    <a href="#"
                                                        class="dropdown-toggle text-muted drop-arrow-none p-0"
                                                        data-bs-toggle="dropdown" aria-expanded="false">
                                                        <i class="ri-more-2-fill"></i>
                                                    </a>
                                                    <div class="dropdown-menu dropdown-menu-end">
                                                        <a href="javascript:void(0);" class="dropdown-item">View
                                                            Details</a>
                                                        <a href="javascript:void(0);" class="dropdown-item">Edit</a>
                                                        <a href="javascript:void(0);" class="dropdown-item">Export</a>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="avatar-sm flex-shrink-0 me-2">
                                                        <span class="avatar-title bg-warning-subtle rounded-circle">
                                                            <i class="ri-user-line"></i>
                                                        </span>
                                                    </div>
                                                    <div>
                                                        <h6 class="fs-14 mt-1 mb-0">Jane Smith</h6>
                                                        <small class="text-muted">Production</small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="badge bg-danger-subtle text-danger">Accident</span>
                                            </td>
                                            <td>
                                                <span class="badge bg-danger-subtle text-danger">Critical</span>
                                            </td>
                                            <td>
                                                <span class="badge bg-info-subtle text-info">
                                                    <i class="ri-play-line me-1"></i>In Progress
                                                </span>
                                            </td>
                                            <td>
                                                <small class="text-muted">4 hours ago</small>
                                            </td>
                                            <td>
                                                <div class="dropdown">
                                                    <a href="#"
                                                        class="dropdown-toggle text-muted drop-arrow-none p-0"
                                                        data-bs-toggle="dropdown" aria-expanded="false">
                                                        <i class="ri-more-2-fill"></i>
                                                    </a>
                                                    <div class="dropdown-menu dropdown-menu-end">
                                                        <a href="javascript:void(0);" class="dropdown-item">View
                                                            Details</a>
                                                        <a href="javascript:void(0);" class="dropdown-item">Assign</a>
                                                        <a href="javascript:void(0);" class="dropdown-item">Priority</a>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="avatar-sm flex-shrink-0 me-2">
                                                        <span class="avatar-title bg-success-subtle rounded-circle">
                                                            <i class="ri-user-line"></i>
                                                        </span>
                                                    </div>
                                                    <div>
                                                        <h6 class="fs-14 mt-1 mb-0">Mike Wilson</h6>
                                                        <small class="text-muted">Maintenance</small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="badge bg-warning-subtle text-warning">Hazard</span>
                                            </td>
                                            <td>
                                                <span class="badge bg-warning-subtle text-warning">Medium</span>
                                            </td>
                                            <td>
                                                <span class="badge bg-warning-subtle text-warning">
                                                    <i class="ri-time-line me-1"></i>Waiting
                                                </span>
                                            </td>
                                            <td>
                                                <small class="text-muted">1 day ago</small>
                                            </td>
                                            <td>
                                                <div class="dropdown">
                                                    <a href="#"
                                                        class="dropdown-toggle text-muted drop-arrow-none p-0"
                                                        data-bs-toggle="dropdown" aria-expanded="false">
                                                        <i class="ri-more-2-fill"></i>
                                                    </a>
                                                    <div class="dropdown-menu dropdown-menu-end">
                                                        <a href="javascript:void(0);" class="dropdown-item">Review</a>
                                                        <a href="javascript:void(0);" class="dropdown-item">Assign</a>
                                                        <a href="javascript:void(0);" class="dropdown-item">Edit</a>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xxl-6">
                    <div class="card">
                        <div class="card-header d-flex flex-wrap align-items-center gap-2 border-bottom border-dashed">
                            <h4 class="header-title me-auto">HSE Staff Activity</h4>
                            <div class="d-flex gap-2 justify-content-end text-end">
                                <a href="javascript:void(0);" class="btn btn-sm btn-primary">Manage Staff</a>
                            </div>
                        </div>

                        <div class="card-body" data-simplebar style="height: 400px;">
                            <div class="timeline-alt py-0">
                                <div class="timeline-item">
                                    <span class="bg-success-subtle text-success timeline-icon">
                                        <i class="ri-check-line"></i>
                                    </span>
                                    <div class="timeline-item-info">
                                        <a href="javascript:void(0);" class="link-reset fw-semibold mb-1 d-block">
                                            Report Completed
                                        </a>
                                        <span class="mb-1">Sarah Johnson completed investigation for accident report
                                            #HSE-2024-156</span>
                                        <p class="mb-0 pb-3">
                                            <small class="text-muted">15 minutes ago</small>
                                        </p>
                                    </div>
                                </div>

                                <div class="timeline-item">
                                    <span class="bg-info-subtle text-info timeline-icon">
                                        <i class="ri-user-add-line"></i>
                                    </span>
                                    <div class="timeline-item-info">
                                        <a href="javascript:void(0);" class="link-reset fw-semibold mb-1 d-block">
                                            Report Assigned
                                        </a>
                                        <span class="mb-1">Critical incident report assigned to
                                            <span class="fw-medium">David Chen (HSE Manager)</span>
                                        </span>
                                        <p class="mb-0 pb-3">
                                            <small class="text-muted">1 hour ago</small>
                                        </p>
                                    </div>
                                </div>

                                <div class="timeline-item">
                                    <span class="bg-warning-subtle text-warning timeline-icon">
                                        <i class="ri-alert-line"></i>
                                    </span>
                                    <div class="timeline-item-info">
                                        <a href="javascript:void(0);" class="link-reset fw-semibold mb-1 d-block">
                                            New Critical Incident
                                        </a>
                                        <span class="mb-1">Chemical spill reported in Sector B - immediate response
                                            required</span>
                                        <p class="mb-0 pb-3">
                                            <small class="text-muted">2 hours ago</small>
                                        </p>
                                    </div>
                                </div>

                                <div class="timeline-item">
                                    <span class="bg-primary-subtle text-primary timeline-icon">
                                        <i class="ri-file-text-line"></i>
                                    </span>
                                    <div class="timeline-item-info">
                                        <a href="javascript:void(0);" class="link-reset fw-semibold mb-1 d-block">
                                            Safety Audit Completed
                                        </a>
                                        <span class="mb-1">Monthly safety audit for Production Area completed with
                                            <span class="fw-medium text-success">95% compliance</span>
                                        </span>
                                        <p class="mb-0 pb-3">
                                            <small class="text-muted">4 hours ago</small>
                                        </p>
                                    </div>
                                </div>

                                <div class="timeline-item">
                                    <span class="bg-info-subtle text-info timeline-icon">
                                        <i class="ri-graduation-cap-line"></i>
                                    </span>
                                    <div class="timeline-item-info">
                                        <a href="javascript:void(0);" class="link-reset fw-semibold mb-1 d-block">
                                            Training Session
                                        </a>
                                        <span class="mb-1">Safety training session conducted for 45 employees</span>
                                        <p class="mb-0 pb-3">
                                            <small class="text-muted">Yesterday</small>
                                        </p>
                                    </div>
                                </div>

                                <div class="timeline-item">
                                    <span class="bg-success-subtle text-success timeline-icon">
                                        <i class="ri-shield-check-line"></i>
                                    </span>
                                    <div class="timeline-item-info">
                                        <a href="javascript:void(0);" class="link-reset fw-semibold mb-1 d-block">
                                            Safety Milestone
                                        </a>
                                        <span class="mb-1">Achieved 100 days without workplace incidents</span>
                                        <p class="mb-0 pb-2">
                                            <small class="text-muted">2 days ago</small>
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions & Alerts -->
            <div class="row">
                <div class="col-xl-4">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="header-title">Quick Actions</h4>
                        </div>
                        <div class="card-body">
                            <div class="d-grid gap-2">
                                <a href="javascript:void(0);" class="btn btn-outline-primary">
                                    <i class="ri-add-line me-1"></i> Create New Report
                                </a>
                                <a href="javascript:void(0);" class="btn btn-outline-info">
                                    <i class="ri-eye-line me-1"></i> Review Pending Reports
                                </a>
                                <a href="javascript:void(0);" class="btn btn-outline-warning">
                                    <i class="ri-user-settings-line me-1"></i> Manage HSE Staff
                                </a>
                                <a href="javascript:void(0);" class="btn btn-outline-success">
                                    <i class="ri-file-chart-line me-1"></i> Generate Reports
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-8">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="header-title">System Alerts & Notifications</h4>
                        </div>
                        <div class="card-body">
                            <div class="alert alert-danger border-0" role="alert">
                                <div class="d-flex align-items-center">
                                    <div class="avatar-sm me-3">
                                        <span class="avatar-title bg-danger rounded-circle">
                                            <i class="ri-error-warning-line fs-18"></i>
                                        </span>
                                    </div>
                                    <div class="flex-grow-1">
                                        <h5 class="alert-heading">Critical Incident Alert</h5>
                                        <p class="mb-0">3 critical incidents require immediate attention. Review and
                                            assign to HSE staff.</p>
                                    </div>
                                    <button type="button" class="btn btn-sm btn-danger">Review Now</button>
                                </div>
                            </div>

                            <div class="alert alert-warning border-0" role="alert">
                                <div class="d-flex align-items-center">
                                    <div class="avatar-sm me-3">
                                        <span class="avatar-title bg-warning rounded-circle">
                                            <i class="ri-time-line fs-18"></i>
                                        </span>
                                    </div>
                                    <div class="flex-grow-1">
                                        <h5 class="alert-heading">Overdue Reports</h5>
                                        <p class="mb-0">5 reports are overdue for resolution. Follow up with assigned HSE
                                            staff.</p>
                                    </div>
                                    <button type="button" class="btn btn-sm btn-warning">View Details</button>
                                </div>
                            </div>

                            <div class="alert alert-info border-0" role="alert">
                                <div class="d-flex align-items-center">
                                    <div class="avatar-sm me-3">
                                        <span class="avatar-title bg-info rounded-circle">
                                            <i class="ri-calendar-line fs-18"></i>
                                        </span>
                                    </div>
                                    <div class="flex-grow-1">
                                        <h5 class="alert-heading">Scheduled Maintenance</h5>
                                        <p class="mb-0">Safety equipment inspection due next week. Schedule maintenance
                                            team.</p>
                                    </div>
                                    <button type="button" class="btn btn-sm btn-info">Schedule</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Footer Start -->
        <footer class="footer">
            <div class="page-container">
                <div class="row">
                    <div class="col-md-6 text-center text-md-start">
                        <script>
                            document.write(new Date().getFullYear())
                        </script> © HSE Management System -
                        <span class="fw-bold text-decoration-underline text-uppercase text-reset fs-12">Safety First</span>
                    </div>
                    <div class="col-md-6">
                        <div class="text-md-end footer-links d-none d-md-block">
                            <a href="javascript: void(0);">Documentation</a>
                            <a href="javascript: void(0);">Support</a>
                            <a href="javascript: void(0);">Contact HSE Team</a>
                        </div>
                    </div>
                </div>
            </div>
        </footer>
    </div>

    @push('jsSection')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                // Reports Trend Chart
                var reportsTrendOptions = {
                    series: [{
                        name: 'Completed',
                        data: [31, 40, 28, 51, 42, 85, 77, 92, 68, 85, 105, 98]
                    }, {
                        name: 'In Progress',
                        data: [11, 22, 18, 31, 32, 25, 17, 22, 18, 25, 8, 12]
                    }, {
                        name: 'Waiting',
                        data: [15, 11, 32, 18, 9, 24, 11, 18, 14, 21, 12, 15]
                    }],
                    chart: {
                        height: 350,
                        type: 'area',
                        toolbar: {
                            show: false
                        }
                    },
                    dataLabels: {
                        enabled: false
                    },
                    stroke: {
                        curve: 'smooth',
                        width: 2
                    },
                    xaxis: {
                        categories: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov',
                            'Dec'
                        ]
                    },
                    fill: {
                        type: 'gradient',
                        gradient: {
                            shadeIntensity: 1,
                            inverseColors: false,
                            opacityFrom: 0.45,
                            opacityTo: 0.05,
                            stops: [20, 100, 100, 100]
                        }
                    },
                    colors: ['#28a745', '#17a2b8', '#ffc107'],
                    legend: {
                        position: 'top',
                        horizontalAlign: 'right'
                    }
                };

                var reportsTrendChart = new ApexCharts(document.querySelector("#reports-trend-chart"),
                    reportsTrendOptions);
                reportsTrendChart.render();

                // Severity Distribution Chart
                var severityOptions = {
                    series: [25, 45, 35, 15],
                    chart: {
                        type: 'donut',
                        height: 300
                    },
                    labels: ['Critical', 'High', 'Medium', 'Low'],
                    colors: ['#dc3545', '#fd7e14', '#ffc107', '#28a745'],
                    legend: {
                        position: 'bottom'
                    },
                    plotOptions: {
                        pie: {
                            donut: {
                                size: '70%'
                            }
                        }
                    },
                    dataLabels: {
                        enabled: true,
                        formatter: function(val) {
                            return Math.round(val) + "%";
                        }
                    }
                };

                var severityChart = new ApexCharts(document.querySelector("#severity-chart"), severityOptions);
                severityChart.render();

                // Small Charts for Cards
                var cardChartOptions = {
                    chart: {
                        type: 'line',
                        height: 60,
                        sparkline: {
                            enabled: true
                        }
                    },
                    stroke: {
                        curve: 'smooth',
                        width: 2
                    },
                    markers: {
                        size: 0
                    }
                };

                // Reports Card Chart
                var reportsCardChart = new ApexCharts(document.querySelector("#chart-reports"), {
                    ...cardChartOptions,
                    series: [{
                        data: [12, 14, 18, 17, 13, 22, 25, 29, 26, 31, 27, 28]
                    }],
                    colors: ['#0d6efd']
                });
                reportsCardChart.render();

                // Pending Card Chart
                var pendingCardChart = new ApexCharts(document.querySelector("#chart-pending"), {
                    ...cardChartOptions,
                    series: [{
                        data: [8, 12, 10, 15, 12, 8, 6, 9, 12, 10, 8, 12]
                    }],
                    colors: ['#ffc107']
                });
                pendingCardChart.render();

                // Critical Card Chart
                var criticalCardChart = new ApexCharts(document.querySelector("#chart-critical"), {
                    ...cardChartOptions,
                    series: [{
                        data: [2, 1, 3, 4, 2, 1, 2, 3, 2, 1, 3, 2]
                    }],
                    colors: ['#dc3545']
                });
                criticalCardChart.render();

                // Completion Card Chart
                var completionCardChart = new ApexCharts(document.querySelector("#chart-completion"), {
                    ...cardChartOptions,
                    series: [{
                        data: [85, 87, 83, 89, 92, 88, 85, 90, 88, 92, 89, 91]
                    }],
                    colors: ['#28a745']
                });
                completionCardChart.render();

                // Load dashboard data via AJAX
                loadDashboardData();
            });

            function loadDashboardData() {
                // Load dashboard data from Laravel backend
                fetch('{{ route('admin.dashboard.data') }}')
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            updateDashboardStats(data.data.statistics);
                            updateCharts(data.data.charts);
                            updateSystemAlerts(data.data.alerts);
                            updateRecentActivity(data.data.recent_activity);
                        } else {
                            console.error('Failed to load dashboard data:', data.message);
                            showErrorNotification('Failed to load dashboard data');
                        }
                    })
                    .catch(error => {
                        console.error('Error loading dashboard data:', error);
                        showErrorNotification('Error loading dashboard data');
                    });

                // Load recent reports separately
                loadRecentReports();
            }

            function updateDashboardStats(stats) {
                document.getElementById('total-reports').textContent = stats.total_reports;
                document.getElementById('pending-reports').textContent = stats.pending_reports;
                document.getElementById('critical-incidents').textContent = stats.critical_incidents;
                document.getElementById('completion-rate').textContent = stats.completion_rate + '%';
                document.getElementById('waiting-count').textContent = stats.pending_reports;
                document.getElementById('progress-count').textContent = stats.in_progress_reports;
                document.getElementById('completed-count').textContent = stats.completed_reports;
                document.getElementById('avg-resolution').textContent = stats.avg_resolution_time;

                // Calculate high/critical vs low/medium
                var highCritical = stats.critical_incidents || 0;
                var lowMedium = stats.total_reports - highCritical;
                document.getElementById('high-critical').textContent = highCritical;
                document.getElementById('low-medium').textContent = lowMedium;
            }

            function loadRecentReports() {
                // Load recent reports from Laravel backend
                fetch('{{ route('admin.dashboard.recent-reports') }}')
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            updateReportsTable(data.data);
                        } else {
                            console.error('Failed to load recent reports:', data.message);
                        }
                    })
                    .catch(error => {
                        console.error('Error loading recent reports:', error);
                    });
            }

            function updateCharts(chartsData) {
                // Update severity chart with real data
                if (chartsData.severity_distribution) {
                    var severityData = [
                        chartsData.severity_distribution.critical || 0,
                        chartsData.severity_distribution.high || 0,
                        chartsData.severity_distribution.medium || 0,
                        chartsData.severity_distribution.low || 0
                    ];
                    severityChart.updateSeries(severityData);
                }

                // Update monthly trend chart
                if (chartsData.monthly_trend) {
                    reportsTrendChart.updateSeries([{
                        name: 'Completed',
                        data: chartsData.monthly_trend.completed
                    }, {
                        name: 'In Progress',
                        data: chartsData.monthly_trend.in_progress
                    }, {
                        name: 'Waiting',
                        data: chartsData.monthly_trend.waiting
                    }]);
                }
            }

            function updateSystemAlerts(alerts) {
                // Update system alerts section
                var alertsContainer = document.querySelector('.card:has(.alert)');
                if (alertsContainer && alerts.length > 0) {
                    var alertsHtml = '';
                    alerts.forEach(function(alert) {
                        alertsHtml += `
                        <div class="alert alert-${alert.type} border-0" role="alert">
                            <div class="d-flex align-items-center">
                                <div class="avatar-sm me-3">
                                    <span class="avatar-title bg-${alert.type} rounded-circle">
                                        <i class="${alert.icon} fs-18"></i>
                                    </span>
                                </div>
                                <div class="flex-grow-1">
                                    <h5 class="alert-heading">${alert.title}</h5>
                                    <p class="mb-0">${alert.message}</p>
                                </div>
                                <button type="button" class="btn btn-sm btn-${alert.type}">${alert.action}</button>
                            </div>
                        </div>
                    `;
                    });
                    alertsContainer.querySelector('.card-body').innerHTML = alertsHtml;
                }
            }

            function updateRecentActivity(activities) {
                // Update recent activity timeline
                var timelineContainer = document.querySelector('.timeline-alt');
                if (timelineContainer && activities.length > 0) {
                    var activitiesHtml = '';
                    activities.forEach(function(activity) {
                        var iconClass = activity.type === 'success' ? 'bg-success-subtle text-success' :
                            activity.type === 'warning' ? 'bg-warning-subtle text-warning' :
                            activity.type === 'info' ? 'bg-info-subtle text-info' :
                            'bg-primary-subtle text-primary';

                        activitiesHtml += `
                        <div class="timeline-item">
                            <span class="${iconClass} timeline-icon">
                                <i class="${activity.icon}"></i>
                            </span>
                            <div class="timeline-item-info">
                                <a href="javascript:void(0);" class="link-reset fw-semibold mb-1 d-block">
                                    ${activity.title}
                                </a>
                                <span class="mb-1">${activity.description}</span>
                                <p class="mb-0 pb-3">
                                    <small class="text-muted">${activity.time}</small>
                                </p>
                            </div>
                        </div>
                    `;
                    });
                    timelineContainer.innerHTML = activitiesHtml;
                }
            }

            function showErrorNotification(message) {
                // Simple error notification
                var notification = document.createElement('div');
                notification.className = 'alert alert-danger position-fixed top-0 end-0 m-3';
                notification.style.zIndex = '9999';
                notification.innerHTML = `
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                ${message}
            `;
                document.body.appendChild(notification);

                // Auto remove after 5 seconds
                setTimeout(function() {
                    if (notification.parentNode) {
                        notification.parentNode.removeChild(notification);
                    }
                }, 5000);
            }

            function updateReportsTable(reports) {
                var tbody = document.getElementById('recent-reports');
                tbody.innerHTML = '';

                reports.forEach(function(report) {
                    var statusClass = getStatusClass(report.status);
                    var severityClass = getSeverityClass(report.severity);
                    var categoryClass = getCategoryClass(report.category);

                    var row = `
                    <tr>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="avatar-sm flex-shrink-0 me-2">
                                    <span class="avatar-title ${report.avatarClass} rounded-circle">
                                        <i class="ri-user-line"></i>
                                    </span>
                                </div>
                                <div>
                                    <h6 class="fs-14 mt-1 mb-0">${report.reporter}</h6>
                                    <small class="text-muted">${report.department}</small>
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="badge ${categoryClass}">${report.category}</span>
                        </td>
                        <td>
                            <span class="badge ${severityClass}">${report.severity}</span>
                        </td>
                        <td>
                            <span class="badge ${statusClass}">
                                ${getStatusIcon(report.status)}${report.status}
                            </span>
                        </td>
                        <td>
                            <small class="text-muted">${report.date}</small>
                        </td>
                        <td>
                            <div class="dropdown">
                                <a href="#" class="dropdown-toggle text-muted drop-arrow-none p-0"
                                    data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="ri-more-2-fill"></i>
                                </a>
                                <div class="dropdown-menu dropdown-menu-end">
                                    <a href="javascript:void(0);" class="dropdown-item">View Details</a>
                                    <a href="javascript:void(0);" class="dropdown-item">Edit</a>
                                    <a href="javascript:void(0);" class="dropdown-item">Export</a>
                                </div>
                            </div>
                        </td>
                    </tr>
                `;
                    tbody.innerHTML += row;
                });
            }

            function getStatusClass(status) {
                switch (status) {
                    case 'Completed':
                        return 'bg-success-subtle text-success';
                    case 'In Progress':
                        return 'bg-info-subtle text-info';
                    case 'Waiting':
                        return 'bg-warning-subtle text-warning';
                    default:
                        return 'bg-secondary-subtle text-secondary';
                }
            }

            function getSeverityClass(severity) {
                switch (severity) {
                    case 'Critical':
                        return 'bg-danger-subtle text-danger';
                    case 'High':
                        return 'bg-danger-subtle text-danger';
                    case 'Medium':
                        return 'bg-warning-subtle text-warning';
                    case 'Low':
                        return 'bg-success-subtle text-success';
                    default:
                        return 'bg-secondary-subtle text-secondary';
                }
            }

            function getCategoryClass(category) {
                switch (category) {
                    case 'Accident':
                        return 'bg-danger-subtle text-danger';
                    case 'Near Miss':
                        return 'bg-info-subtle text-info';
                    case 'Hazard':
                        return 'bg-warning-subtle text-warning';
                    default:
                        return 'bg-secondary-subtle text-secondary';
                }
            }

            function getStatusIcon(status) {
                switch (status) {
                    case 'Completed':
                        return '<i class="ri-check-line me-1"></i>';
                    case 'In Progress':
                        return '<i class="ri-play-line me-1"></i>';
                    case 'Waiting':
                        return '<i class="ri-time-line me-1"></i>';
                    default:
                        return '';
                }
            }

            // Real-time updates simulation
            setInterval(function() {
                // Simulate real-time updates to pending reports count
                var currentPending = parseInt(document.getElementById('pending-reports').textContent);
                var newPending = Math.max(0, currentPending + Math.floor(Math.random() * 3) - 1);
                document.getElementById('pending-reports').textContent = newPending;
            }, 30000); // Update every 30 seconds
        </script>
    @endpush
@endsection
