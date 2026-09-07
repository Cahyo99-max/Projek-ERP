{{-- SweetAlert2 Custom Notification Component --}}
@if (session('success'))
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            Swal.fire({
                icon: 'success',
                title: 'Berhasil',
                text: "{{ session('success') }}",
                showConfirmButton: false,
                timer: 2000,
                timerProgressBar: true,
                customClass: {
                    popup: 'swal2-custom-popup'
                }
            });
        });
    </script>
@endif

@if (session('error'))
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            Swal.fire({
                icon: 'error',
                title: 'Gagal',
                text: "{{ session('error') }}",
                showConfirmButton: true,
                customClass: {
                    popup: 'swal2-custom-popup'
                }
            });
        });
    </script>
@endif

<script>
    // Global helper object for showing modern SweetAlert2 notifications
    window.showAlert = {
        success: function(message, title = 'Berhasil') {
            return Swal.fire({
                icon: 'success',
                title: title,
                text: message,
                showConfirmButton: false,
                timer: 2000,
                timerProgressBar: true,
                customClass: {
                    popup: 'swal2-custom-popup'
                }
            });
        },
        error: function(message, title = 'Gagal') {
            return Swal.fire({
                icon: 'error',
                title: title,
                text: message,
                showConfirmButton: true,
                customClass: {
                    popup: 'swal2-custom-popup'
                }
            });
        },
        confirm: function(message, confirmCallback, title = 'Apakah Anda yakin?') {
            Swal.fire({
                title: title,
                text: message,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#2563EB',
                cancelButtonColor: '#EF4444',
                confirmButtonText: 'Ya, Lanjutkan!',
                cancelButtonText: 'Batal',
                customClass: {
                    popup: 'swal2-custom-popup'
                }
            }).then((result) => {
                if (result.isConfirmed && typeof confirmCallback === 'function') {
                    confirmCallback();
                }
            });
        }
    };
</script>

<style>
    /* Styling agar match dengan tema premium (dark/light) */
    .swal2-custom-popup {
        border-radius: var(--border-radius-lg) !important;
        background-color: var(--card-bg) !important;
        color: var(--text-main) !important;
        border: 1px solid var(--border-color) !important;
        box-shadow: var(--shadow-lg) !important;
    }
    .swal2-title, .swal2-html-container {
        color: var(--text-main) !important;
    }
</style>
