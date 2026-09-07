<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $appName }}</title>
    <link rel="shortcut icon" href="{{ $appLogo }}" type="image/x-icon">
    <link rel="icon" href="{{ $appLogo }}" type="image/x-icon">

    <!-- Inline script to set dark/light theme immediately to prevent flashing of light screen on page load -->
    <script>
        (function() {
            const savedTheme = localStorage.getItem('theme') || 'light';
            document.documentElement.setAttribute('data-bs-theme', savedTheme);
        })();
    </script>

    <!-- Bootstrap 5.3 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <!-- Tabler Flags CSS -->
    <link href="{{ asset('tabler/dist/css/tabler-flags.min.css?1692870762') }}" rel="stylesheet" />
    <!-- DataTables Bootstrap 5 CSS -->
    <link href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    @stack('styles')
</head>

<body>

    <!-- Panggil komponen Sidebar -->
    <x-sidebar />

    <main class="main-content">
        <!-- Panggil komponen Navbar -->
        <x-navbar />

        <!-- Area ini akan diisi oleh konten dari halaman yang sedang dibuka -->
        <div class="content-body">
            {{ $slot }}
        </div>

        <!-- Panggil komponen Footer -->
        <x-footer />
    </main>

    <!-- Modal Container Outside Main Stacking Context -->
    @stack('modals')

    <!-- Floating Action Button & Scroll to Top -->
    <button class="fab-btn" id="scrollTopBtn" title="Scroll to top" style="display: none;">
        <i class="bi bi-arrow-up"></i>
    </button>

    <!-- ==========================================================================
         SCRIPTS
         ========================================================================== -->

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <!-- Bootstrap 5.3 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <!-- DataTables JS -->
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>

    <!-- Custom Vanilla JS -->
    <script src="{{ asset('js/main.js') }}"></script>
    
    <!-- SweetAlert2 & Reusable Alert Component -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <x-alert />

    <!-- Reusable Global Loader Component -->
    <script>window.appLogoSource = "{{ $appLogo }}";</script>
    <script src="{{ asset('js/global-loader.js') }}"></script>

    <!-- Untuk script khusus per halaman (misal chart data) -->
    @stack('scripts')

    @auth
    <script>
        // Check session status every 10 seconds to enforce single-session logout in real-time
        setInterval(function() {
            $.ajax({
                url: "{{ route('session.check') }}",
                method: 'GET',
                error: function(xhr) {
                    if (xhr.status === 401) {
                        window.location.href = "{{ route('login') }}?expired=1";
                    }
                }
            });
        }, 10000);

        // Auto-logout after 1 hour (60 minutes) of inactivity to prevent CSRF error 419
        let idleMinutes = 0;

        // Reset idle timer on any user activity
        $(document).on('mousemove keypress click scroll', function() {
            idleMinutes = 0;
        });

        // Increment idle counter every minute
        setInterval(function() {
            idleMinutes++;
            if (idleMinutes >= 60) { // 60 minutes = 1 hour
                window.location.href = "{{ route('logout') }}?expired=1";
            }
        }, 60000);
    </script>
    @endauth
</body>

</html>
