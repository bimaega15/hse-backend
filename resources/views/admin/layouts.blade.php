<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <title>@yield('title')</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta content="BAIK Management System - Health, Safety & Environment reporting platform by BAIK TECHNOLOGY" name="description" />
    <meta content="BAIK TECHNOLOGY" name="author" />
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- App favicon -->
    <link rel="shortcut icon" href="{{ asset('assets/logo/logo-app.jpg') }}">

    <!-- Vendor css -->
    <link href="{{ asset('admin/backend/dist') }}/assets/css/vendor.min.css" rel="stylesheet" type="text/css" />

    <!-- App css -->
    <link href="{{ asset('admin/backend/dist') }}/assets/css/app.min.css" rel="stylesheet" type="text/css"
        id="app-style" />

    <!-- Icons css -->
    <link href="{{ asset('admin/backend/dist') }}/assets/css/icons.min.css" rel="stylesheet" type="text/css" />

    <!-- Theme Config Js -->
    <script src="{{ asset('admin/backend/dist') }}/assets/js/config.js"></script>

    <!-- DataTables CSS -->
    <link href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap5.min.css" rel="stylesheet">

    @stack('cssSection')
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

    <!-- DataTables JS -->
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/responsive.bootstrap5.min.js"></script>

    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- Projects Analytics Dashboard App js -->
    @stack('jsSection')

</body>

</html>
