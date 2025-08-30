<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <title>Log In | BAIK Management System</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta content="BAIK Management System - Health, Safety & Environment reporting platform by BAIK TECHNOLOGY" name="description" />
    <meta content="BAIK TECHNOLOGY" name="author" />

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
</head>

<body>

    @yield('content')


    <script src="https://code.jquery.com/jquery-3.7.1.js"></script>
    <!-- Vendor js -->
    <script src="{{ asset('admin/backend/dist') }}/assets/js/vendor.min.js"></script>

    <!-- App js -->
    <script src="{{ asset('admin/backend/dist') }}/assets/js/app.js"></script>

    @stack('jsSection')

</body>

</html>
