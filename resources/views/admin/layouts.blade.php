<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <title>Dashboard | Abstack - Responsive Bootstrap 5 Admin Dashboard</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta content="A fully featured admin theme which can be used to build CRM, CMS, etc." name="description" />
    <meta content="Coderthemes" name="author" />

    <!-- App favicon -->
    <link rel="shortcut icon" href="{{ asset('admin/backend/dist') }}/assets/images/favicon.ico">

    <!-- Vendor css -->
    <link href="{{ asset('admin/backend/dist') }}/assets/css/vendor.min.css" rel="stylesheet" type="text/css" />

    <!-- App css -->
    <link href="{{ asset('admin/backend/dist') }}/assets/css/app.min.css" rel="stylesheet" type="text/css"
        id="app-style" />

    <!-- Icons css -->
    <link href="{{ asset('admin/backend/dist') }}/assets/css/icons.min.css" rel="stylesheet" type="text/css" />

    <!-- Theme Config Js -->
    <script src="{{ asset('admin/backend/dist') }}/assets/js/config.js"></script>
</head>

<body>
    <!-- Begin page -->
    <div class="wrapper">

        <!-- Menu -->
        <!-- Sidenav Menu Start -->
        @include('admin.components.sidebar')
        <!-- Sidenav Menu End -->

        <!-- Topbar Start -->
        @include('admin.components.header')
        <!-- Topbar End -->

        <!-- Search Modal -->
        @include('admin.components.search-modal')




        <!-- ============================================================== -->
        <!-- Start Page Content here -->
        <!-- ============================================================== -->

        @yield('content')
        <!-- ============================================================== -->
        <!-- End Page content -->
        <!-- ============================================================== -->

    </div>
    <!-- END wrapper -->

    <!-- Theme Settings -->
    @include('admin.components.theme')

    <!-- Vendor js -->
    <script src="{{ asset('admin/backend/dist') }}/assets/js/vendor.min.js"></script>

    <!-- App js -->
    <script src="{{ asset('admin/backend/dist') }}/assets/js/app.js"></script>

    <!-- Apex Chart js -->
    <script src="{{ asset('admin/backend/dist') }}/assets/vendor/apexcharts/apexcharts.min.js"></script>

    <!-- Projects Analytics Dashboard App js -->
    <script src="{{ asset('admin/backend/dist') }}/assets/js/pages/dashboard.js"></script>

</body>

</html>
