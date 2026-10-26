<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
      <title>{{ $appName }}</title>
    <link rel="shortcut icon" href="{{ $appLogo }}" type="image/x-icon">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/login.css') }}">

    <!-- PWA Settings -->
    <link rel="manifest" href="{{ url('/manifest.json') }}">
    <meta name="theme-color" content="#111827">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="{{ $appName }}">
    <link rel="apple-touch-icon" href="{{ $appLogo }}">

    <style>
        /* Sembunyikan banner PWA jika aplikasi sudah terpasang (PWA Standalone) */
        @media all and (display-mode: standalone), (display-mode: window-controls-overlay), (display-mode: fullscreen) {
            .pwa-download-banner {
                display: none !important;
            }
        }
    </style>
</head>

<body>

    <div class="login-wrapper">
        <div class="bg-overlay"></div>

        <div class="container-fluid content-container">
            <div class="row h-100">

                <div class="col-lg-7 d-none d-lg-flex flex-column justify-content-between left-content">

                    <div class="brand-logo-left anim-fade-right delay-1">
                        <img src="{{ $appLogo }}" alt="Icon" style="width: 48px; height: 48px; object-fit: contain;">
                        <span>{{ $appName }}</span>
                    </div>

                    <div class="hero-section">
                        <h1 class="hero-title anim-fade-right delay-2">
                            Kelola <span class="text-gradient-gold">Bisnis<br>Lebih Mudah.</span>
                        </h1>
                        <p class="hero-subtitle anim-fade-right delay-3">
                            Kelola stok barang, Produksi, laporan keuangan, hingga Penjualan.
                        </p>
                    </div>

                    <div class="footer-text anim-fade-right delay-4">
                        © CP 2026 {{ $appName }}
                    </div>
                </div>

                <div class="col-12 col-lg-5 right-content">
                    <div class="login-card anim-scale-in delay-2">

                        <div class="card-brand anim-fade-up delay-3">
                            <img src="{{ $appLogo }}" alt="Icon" style="width: 48px; height: 48px; object-fit: contain;">
                            <span>{{ $appName }}</span>
                        </div>

                        <div class="anim-fade-up delay-3">
                            <h2 class="card-title">Selamat Datang Kembali!</h2>
                            <p class="card-subtitle">Kelola stok barang, Produksi, laporan keuangan, hingga Penjualan.</p>
                        </div>

                        <!-- PWA Download / Install App Banner -->
                        <div class="pwa-download-banner anim-fade-up delay-3 mb-3">
                            <div class="d-flex align-items-center justify-content-between p-2 px-3 rounded-4" 
                                 style="background: rgba(255, 255, 255, 0.65); border: 1px solid rgba(255, 255, 255, 0.85); box-shadow: 0 4px 15px rgba(0,0,0,0.04); backdrop-filter: blur(6px);">
                                <div class="d-flex align-items-center gap-2">
                                    <div class="p-1 rounded-3 bg-white shadow-sm d-flex align-items-center justify-content-center" style="width: 38px; height: 38px;">
                                        <img src="{{ $appLogo }}" alt="{{ $appName }}" style="width: 28px; height: 28px; object-fit: contain;">
                                    </div>
                                    <div>
                                        <div style="font-weight: 700; font-size: 0.83rem; color: #1e293b; line-height: 1.2;">Aplikasi {{ $appName }}</div>
                                        <div style="font-size: 0.72rem; color: #64748b;">Pasang di HP / Laptop (PWA)</div>
                                    </div>
                                </div>
                                <button type="button" id="btn-install-pwa" class="btn btn-sm d-flex align-items-center gap-1 rounded-pill px-3 py-1 text-white" 
                                        style="font-size: 0.78rem; font-weight: 600; background: linear-gradient(135deg, #1e293b, #0f172a); border: none; box-shadow: 0 4px 10px rgba(15,23,42,0.25); transition: all 0.2s;">
                                    <i class="bi bi-download"></i> <span>Download</span>
                                </button>
                            </div>
                        </div>

                        @if(request()->get('expired'))
                            <div class="alert alert-warning border-0 text-center mb-3 py-2 rounded-3 anim-fade-up delay-3" style="background-color: rgba(245, 158, 11, 0.1); color: #d97706; font-size: 0.85rem; font-weight: 500;">
                                <i class="bi bi-exclamation-triangle-fill me-1"></i> Sesi Anda telah berakhir (tidak ada aktivitas atau login di perangkat lain). Silakan masuk kembali.
                            </div>
                        @endif

                        <form action="{{ url('/login') }}" method="POST">
                            @csrf {{-- Wajib untuk keamanan token Laravel --}}

                            <div class="input-group-custom anim-fade-up delay-4">
                                <i class="bi bi-envelope icon-left"></i>
                                <input type="text" name="login"
                                    class="custom-input @error('login') is-invalid @enderror"
                                    placeholder="Username atau Email" value="{{ old('login') }}" autocomplete="off"
                                    required>
                            </div>
                            {{-- Menampilkan error di bawah input jika login gagal/salah --}}
                            @error('login')
                                <div class="text-danger small mt-1 ps-2 anim-fade-up delay-4">
                                    {{ $message }}
                                </div>
                            @enderror

                            <div class="input-group-custom anim-fade-up delay-4 mt-3">
                                <i class="bi bi-lock icon-left"></i>
                                <input type="password" name="password" id="passwordField"
                                    class="custom-input @error('password') is-invalid @enderror" placeholder="Password"
                                    autocomplete="off" required>
                                <i id="togglePasswordIcon" class="bi bi-eye-slash icon-right"
                                    style="cursor: pointer;"></i>
                            </div>
                            @error('password')
                                <div class="text-danger small mt-1 ps-2 anim-fade-up delay-4">
                                    {{ $message }}
                                </div>
                            @enderror

                            <div class="d-flex align-items-center mb-4 mt-3 anim-fade-up delay-4">
                                <input class="form-check-input custom-checkbox me-2" type="checkbox" name="remember"
                                    id="remember">
                                <label class="form-check-label text-dark fs-6" for="remember">
                                    Ingat perangkat ini
                                </label>
                            </div>

                            <button type="submit"
                                class="btn-login d-flex align-items-center justify-content-center gap-2 anim-fade-up delay-5">
                                Masuk ke Sistem <i class="bi bi-arrow-right"></i>
                            </button>

        

                        </form>

                    </div>
                </div>

            </div>
        </div>
    </div>


    <!-- PWA Install Guide Modal -->
    <div class="modal fade" id="pwaInstallModal" tabindex="-1" aria-labelledby="pwaInstallModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content rounded-4 border-0 shadow-lg" style="background: #ffffff;">
                <div class="modal-header border-0 pb-0">
                    <div class="d-flex align-items-center gap-2">
                        <img src="{{ $appLogo }}" alt="Icon" style="width: 32px; height: 32px; object-fit: contain;">
                        <h5 class="modal-title fs-6 fw-bold text-dark" id="pwaInstallModalLabel">Instal Aplikasi {{ $appName }}</h5>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body pt-3 text-start">
                    <p class="text-muted small mb-3">
                        Aplikasi ini didukung teknologi <strong>PWA (Progressive Web App)</strong>. Anda dapat memasang aplikasi langsung ke layar utama perangkat tanpa perlu download dari Play Store / App Store.
                    </p>
                    <div class="list-group list-group-flush rounded-3 border">
                        <div class="list-group-item d-flex gap-3 align-items-center py-2">
                            <i class="bi bi-phone fs-4 text-primary"></i>
                            <div class="small">
                                <strong>Android (Chrome):</strong><br>
                                Buka menu browser (titik 3 <i class="bi bi-three-dots-vertical"></i>) di kanan atas, lalu pilih <em>"Instal Aplikasi"</em> atau <em>"Tambahkan ke Layar Utama"</em>.
                            </div>
                        </div>
                        <div class="list-group-item d-flex gap-3 align-items-center py-2">
                            <i class="bi bi-apple fs-4 text-dark"></i>
                            <div class="small">
                                <strong>iPhone / iPad (Safari):</strong><br>
                                Ketuk tombol Bagikan <i class="bi bi-box-arrow-up"></i> (Share) di menu bawah Safari, lalu pilih <em>"Tambah ke Layar Utama"</em> (+).
                            </div>
                        </div>
                        <div class="list-group-item d-flex gap-3 align-items-center py-2">
                            <i class="bi bi-laptop fs-4 text-success"></i>
                            <div class="small">
                                <strong>Laptop / Desktop (Chrome / Edge):</strong><br>
                                Klik ikon instal <i class="bi bi-display"></i> di ujung kanan bilah alamat (URL bar) browser Anda.
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-dark rounded-pill px-4 btn-sm w-100" data-bs-dismiss="modal">Saya Mengerti</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap 5 JS Bundle & SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- Reusable Global Loader Component -->
    <script>window.appLogoSource = "{{ $appLogo }}";</script>
    <script src="{{ asset('js/global-loader.js') }}"></script>

</body>
<script>
    // Show Loader on Form Submit
    const loginForm = document.querySelector('form');
    if (loginForm) {
        loginForm.addEventListener('submit', function() {
            if (typeof window.showLoader === 'function') {
                window.showLoader('Memverifikasi kredensial Anda...');
            }
        });
    }

    // Ambil elemen input dan ikon matanya
    const passwordField = document.getElementById('passwordField');
    const toggleIcon = document.getElementById('togglePasswordIcon');

    // Tambahkan efek klik pada ikon mata jika elemen tersedia
    if (passwordField && toggleIcon) {
        toggleIcon.addEventListener('click', function() {
            if (passwordField.type === 'password') {
                passwordField.type = 'text';
                toggleIcon.classList.remove('bi-eye-slash');
                toggleIcon.classList.add('bi-eye');
            } else {
                passwordField.type = 'password';
                toggleIcon.classList.remove('bi-eye');
                toggleIcon.classList.add('bi-eye-slash');
            }
        });
    }

    // ==================== LOGIN LOCKOUT LOCKING & COUNTDOWN ====================
    @if(session('lockout_time'))
        localStorage.setItem('login_lockout_until', Date.now() + {{ (int) session('lockout_time') }} * 1000);
    @endif

    function checkLockout() {
        const lockoutUntil = localStorage.getItem('login_lockout_until');
        if (!lockoutUntil) return;

        const remainingMs = parseInt(lockoutUntil) - Date.now();
        if (remainingMs <= 0) {
            localStorage.removeItem('login_lockout_until');
            return;
        }

        // Disable inputs and buttons
        const loginInput = document.querySelector('input[name="login"]');
        const passwordInput = document.querySelector('input[name="password"]');
        const submitBtn = document.querySelector('.btn-login');
        
        if (loginInput && passwordInput && submitBtn) {
            loginInput.disabled = true;
            passwordInput.disabled = true;
            submitBtn.disabled = true;

            // Create or get countdown message container
            let countdownEl = document.getElementById('lockout-countdown');
            if (!countdownEl) {
                countdownEl = document.createElement('div');
                countdownEl.id = 'lockout-countdown';
                countdownEl.className = 'alert alert-danger border-0 text-center mb-3 py-2 rounded-3 mt-3';
                countdownEl.style.fontSize = '0.85rem';
                countdownEl.style.fontWeight = '600';
                countdownEl.innerHTML = '<i class="bi bi-clock-fill me-1 animate-spin"></i> Coba lagi dalam <span id="countdown-timer"></span>';
                
                // Insert it before the login button
                submitBtn.parentNode.insertBefore(countdownEl, submitBtn);
            }

            function updateTimerDisplay() {
                const currentRemaining = parseInt(localStorage.getItem('login_lockout_until')) - Date.now();
                if (currentRemaining <= 0) {
                    return false;
                }
                const totalSeconds = Math.ceil(currentRemaining / 1000);
                const minutes = Math.floor(totalSeconds / 60);
                const seconds = totalSeconds % 60;
                
                const formattedMinutes = String(minutes).padStart(2, '0');
                const formattedSeconds = String(seconds).padStart(2, '0');
                
                const timerEl = document.getElementById('countdown-timer');
                if (timerEl) {
                    timerEl.innerText = `${formattedMinutes}:${formattedSeconds}`;
                }
                return true;
            }

            // Jalankan segera agar tidak ada jeda kosong 1 detik
            updateTimerDisplay();

            const interval = setInterval(function() {
                if (!updateTimerDisplay()) {
                    clearInterval(interval);
                    localStorage.removeItem('login_lockout_until');
                    
                    loginInput.disabled = false;
                    passwordInput.disabled = false;
                    submitBtn.disabled = false;
                    
                    if (countdownEl) {
                        countdownEl.remove();
                    }
                    
                    window.location.reload();
                }
            }, 1000);
        }
    }

    // Call checkLockout on load
    checkLockout();

    // ==================== PWA INSTALLATION & SERVICE WORKER ====================
    let deferredPrompt = null;
    const btnInstallPwa = document.getElementById('btn-install-pwa');

    // 1. Register Service Worker
    if ('serviceWorker' in navigator) {
        window.addEventListener('load', function() {
            navigator.serviceWorker.register('/sw.js').then(function(reg) {
                console.log('PWA ServiceWorker registered with scope: ', reg.scope);
            }).catch(function(err) {
                console.log('PWA ServiceWorker registration failed: ', err);
            });
        });
    }

    // Helper untuk sembunyikan banner jika aplikasi sudah terpasang
    const pwaBanner = document.querySelector('.pwa-download-banner');
    function hidePwaBanner() {
        if (pwaBanner) {
            pwaBanner.style.setProperty('display', 'none', 'important');
        }
    }

    // 2. Cek apakah sedang berjalan dalam mode terpasang (PWA Standalone) atau sudah diinstal
    const isStandalone = window.matchMedia('(display-mode: standalone)').matches || 
                         window.navigator.standalone === true ||
                         document.referrer.includes('android-app://') ||
                         localStorage.getItem('pwa_installed') === '1';

    if (isStandalone) {
        hidePwaBanner();
    }

    // Cek dengan modern browser API (getInstalledRelatedApps)
    if ('getInstalledRelatedApps' in navigator) {
        navigator.getInstalledRelatedApps().then(function(apps) {
            if (apps && apps.length > 0) {
                localStorage.setItem('pwa_installed', '1');
                hidePwaBanner();
            }
        }).catch(function() {});
    }

    // 3. Capture beforeinstallprompt
    window.addEventListener('beforeinstallprompt', function(e) {
        // Prevent mini-infobar on mobile
        e.preventDefault();
        deferredPrompt = e;
    });

    // 4. Click Download / Install Button with SweetAlert2
    if (btnInstallPwa) {
        btnInstallPwa.addEventListener('click', async function() {
            if (isStandalone) {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'info',
                        title: 'Aplikasi Sudah Terpasang',
                        text: 'Aplikasi {{ $appName }} sudah terpasang dan sedang berjalan di perangkat Anda.',
                        confirmButtonColor: '#0f172a',
                        confirmButtonText: 'Tutup'
                    });
                }
                return;
            }

            if (deferredPrompt) {
                // Show stylish SweetAlert2 confirmation before showing native install prompt
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        title: 'Pasang Aplikasi {{ $appName }}',
                        text: 'Aplikasi akan dipasang ke perangkat Anda untuk akses cepat, ringan, dan tanpa browser.',
                        imageUrl: '{{ $appLogo }}',
                        imageWidth: 64,
                        imageHeight: 64,
                        imageAlt: 'Logo {{ $appName }}',
                        showCancelButton: true,
                        confirmButtonColor: '#0f172a',
                        cancelButtonColor: '#64748b',
                        confirmButtonText: '<i class="bi bi-download me-1"></i> Ya, Pasang Sekarang!',
                        cancelButtonText: 'Batal',
                        reverseButtons: true,
                        customClass: {
                            popup: 'rounded-4 shadow-lg border-0'
                        }
                    }).then(async (result) => {
                        if (result.isConfirmed) {
                            // Trigger native browser install prompt
                            deferredPrompt.prompt();
                            const choiceResult = await deferredPrompt.userChoice;
                            if (choiceResult && choiceResult.outcome === 'accepted') {
                                localStorage.setItem('pwa_installed', '1');
                                hidePwaBanner();

                                Swal.fire({
                                    icon: 'success',
                                    title: 'Berhasil Dipasang!',
                                    text: 'Aplikasi {{ $appName }} kini siap digunakan langsung dari layar perangkat Anda.',
                                    confirmButtonColor: '#0f172a',
                                    confirmButtonText: 'Mantap!',
                                    timer: 3500,
                                    timerProgressBar: true
                                });
                            } else {
                                Swal.fire({
                                    icon: 'info',
                                    title: 'Pemasangan Dibatalkan',
                                    text: 'Anda dapat menekan tombol Download kapan saja jika ingin memasang aplikasi.',
                                    timer: 2000,
                                    showConfirmButton: false,
                                    toast: true,
                                    position: 'top-end'
                                });
                            }
                            deferredPrompt = null;
                        }
                    });
                } else {
                    deferredPrompt.prompt();
                }
            } else {
                // If native prompt is not available (iOS Safari, desktop browser without prompt, etc.)
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        title: 'Panduan Instalasi {{ $appName }}',
                        imageUrl: '{{ $appLogo }}',
                        imageWidth: 54,
                        imageHeight: 54,
                        imageAlt: 'Logo {{ $appName }}',
                        html: `
                            <div class="text-start small mt-2">
                                <p class="text-muted mb-3">Aplikasi ini didukung teknologi <b>PWA</b>. Anda dapat memasang langsung ke layar utama:</p>
                                <div class="p-2 mb-2 rounded-3 border bg-light">
                                    <div class="fw-bold text-primary mb-1"><i class="bi bi-phone me-1"></i> Android (Chrome):</div>
                                    <div>Ketuk menu titik tiga (<i class="bi bi-three-dots-vertical"></i>) di kanan atas browser, lalu pilih <b>"Instal Aplikasi"</b> atau <b>"Tambahkan ke Layar Utama"</b>.</div>
                                </div>
                                <div class="p-2 mb-2 rounded-3 border bg-light">
                                    <div class="fw-bold text-dark mb-1"><i class="bi bi-apple me-1"></i> iPhone / iPad (Safari):</div>
                                    <div>Ketuk tombol Bagikan (<i class="bi bi-box-arrow-up"></i> Share) di bawah, lalu pilih <b>"Tambah ke Layar Utama"</b> (+).</div>
                                </div>
                                <div class="p-2 rounded-3 border bg-light">
                                    <div class="fw-bold text-success mb-1"><i class="bi bi-laptop me-1"></i> Laptop / PC:</div>
                                    <div>Klik ikon instal (<i class="bi bi-display"></i>) di ujung kanan bilah URL browser Anda.</div>
                                </div>
                            </div>
                        `,
                        confirmButtonText: 'Saya Mengerti',
                        confirmButtonColor: '#0f172a',
                        customClass: {
                            popup: 'rounded-4 shadow-lg border-0'
                        }
                    });
                } else {
                    const modalEl = document.getElementById('pwaInstallModal');
                    if (modalEl && typeof bootstrap !== 'undefined') {
                        const modal = new bootstrap.Modal(modalEl);
                        modal.show();
                    }
                }
            }
        });
    }

    // 5. Detect when installed successfully
    window.addEventListener('appinstalled', function() {
        localStorage.setItem('pwa_installed', '1');
        hidePwaBanner();

        if (typeof Swal !== 'undefined') {
            Swal.fire({
                icon: 'success',
                title: 'Aplikasi Berhasil Dipasang!',
                text: 'Aplikasi {{ $appName }} telah berhasil dipasang ke perangkat Anda.',
                confirmButtonColor: '#0f172a',
                confirmButtonText: 'Mulai Buka',
                timer: 4000,
                timerProgressBar: true
            });
        }
    });
</script>

</html>
