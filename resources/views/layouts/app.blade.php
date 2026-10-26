<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Bhav Booking - Just Udhari')</title>

    <!-- Google Font: Quicksand -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Quicksand:wght@500;600;700&display=swap" rel="stylesheet">

    <!-- Bootstrap 5 CSS CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Font Awesome 6 CDN -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">

    <!-- Custom CSS via asset() -->
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
</head>
<body>

    <!-- Dummy Top Header -->
    @include('partials.header')

    <div class="app-container">
        <!-- Dummy Left Icon Sidebar -->
        @include('partials.sidebar')

        <!-- Floating Staff Tab (Right Edge) -->
        <aside class="floating-staff-tab" title="Staff Status">
            <div class="staff-tab-inner">
                <i class="fa-solid fa-users staff-icon"></i>
                <span class="staff-text">STAFF</span>
                <span class="staff-badge">0</span>
            </div>
        </aside>

        <!-- Main Content Area -->
        <main class="main-content-wrapper">
            @yield('content')
        </main>
    </div>

    <!-- Reusable Toast Container -->
    <div class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 9999;" id="toastContainer"></div>

    <!-- Bootstrap 5 Bundle JS CDN -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Page Specific Scripts -->
    @yield('scripts')
</body>
</html>
