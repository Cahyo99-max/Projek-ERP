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
                            <p class="card-subtitle">Kelola stok barang, Produksi, laporan keuangan, hingga Penjualan.
                           </p>
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

    // Tambahkan efek klik pada ikon mata
    toggleIcon.addEventListener('click', function() {
        // Jika tipenya password, ubah jadi text (kelihatan) dan ganti ikonnya
        if (passwordField.type === 'password') {
            passwordField.type = 'text';
            toggleIcon.classList.remove('bi-eye-slash');
            toggleIcon.classList.add('bi-eye');
        } else {
            // Jika tipenya text, kembalikan jadi password (tersembunyi)
            passwordField.type = 'password';
            toggleIcon.classList.remove('bi-eye');
            toggleIcon.classList.add('bi-eye-slash');
        }
    });

    // ==================== LOGIN LOCKOUT LOCKING & COUNTDOWN ====================
    @if(session('lockout_time'))
        localStorage.setItem('login_lockout_until', Date.now() + {{ session('lockout_time') }} * 1000);
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

            const interval = setInterval(function() {
                const currentRemaining = parseInt(localStorage.getItem('login_lockout_until')) - Date.now();
                if (currentRemaining <= 0) {
                    clearInterval(interval);
                    localStorage.removeItem('login_lockout_until');
                    
                    loginInput.disabled = false;
                    passwordInput.disabled = false;
                    submitBtn.disabled = false;
                    
                    if (countdownEl) {
                        countdownEl.remove();
                    }
                    
                    window.location.reload();
                } else {
                    const totalSeconds = Math.ceil(currentRemaining / 1000);
                    const minutes = Math.floor(totalSeconds / 60);
                    const seconds = totalSeconds % 60;
                    
                    // Format: mm:ss
                    const formattedMinutes = String(minutes).padStart(2, '0');
                    const formattedSeconds = String(seconds).padStart(2, '0');
                    
                    const timerEl = document.getElementById('countdown-timer');
                    if (timerEl) {
                        timerEl.innerText = `${formattedMinutes}:${formattedSeconds}`;
                    }
                }
            }, 1000);
        }
    }

    // Call checkLockout on load
    checkLockout();
</script>

</html>
