<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>403 - Akses Cabang Dibatasi</title>
    <link rel="shortcut icon" href="{{ $appLogo ?? asset('gambar/logo.png') }}" type="image/x-icon">
    <link rel="icon" href="{{ $appLogo ?? asset('gambar/logo.png') }}" type="image/x-icon">
    <!-- Google Font: Outfit -->
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;800&display=swap" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    
    <style>
        :root {
            --bg-dark: #090615;
            --primary: #f43f5e; /* Rose / soft red for restriction */
            --accent: #fda4af;
            --text-light: #f8fafc;
            --text-muted: #94a3b8;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Outfit', sans-serif;
            background-color: var(--bg-dark);
            color: var(--text-light);
            min-height: 100vh;
            min-height: 100dvh;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow-x: hidden;
            overflow-y: auto;
            -webkit-overflow-scrolling: touch;
            position: relative;
            padding: 1.5rem 1rem;
        }

        /* ===== BACKGROUND COSMIC EFFECT ===== */
        .nebula {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: 
                radial-gradient(circle at 30% 20%, rgba(244, 63, 94, 0.12) 0%, transparent 50%),
                radial-gradient(circle at 70% 80%, rgba(139, 92, 246, 0.12) 0%, transparent 50%);
            filter: blur(80px);
            z-index: 1;
            pointer-events: none;
            animation: nebulaPulse 10s ease-in-out infinite alternate;
        }

        @keyframes nebulaPulse {
            0% { transform: scale(1); opacity: 0.8; }
            100% { transform: scale(1.15); opacity: 1; }
        }

        .stars {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-image: 
                radial-gradient(circle, #fff 1px, transparent 1px),
                radial-gradient(circle, rgba(255,255,255,0.7) 1px, transparent 1px),
                radial-gradient(circle, rgba(255,255,255,0.3) 1.5px, transparent 1.5px);
            background-size: 450px 450px, 300px 300px, 200px 200px;
            background-position: 0 0, 30px 40px, 90px 180px;
            opacity: 0.25;
            z-index: 1;
            pointer-events: none;
            animation: starsMove 150s linear infinite;
        }

        @keyframes starsMove {
            from { background-position: 0 0, 30px 40px, 90px 180px; }
            to { background-position: 450px 450px, 480px 490px, 490px 580px; }
        }

        /* ===== CONTAINER LAYOUT ===== */
        .container {
            width: 100%;
            max-width: 1000px;
            margin: auto;
            padding: 1rem 0;
            display: flex;
            flex-direction: column-reverse;
            align-items: center;
            justify-content: space-between;
            position: relative;
            z-index: 10;
            gap: 1.25rem;
        }

        @media (min-width: 768px) {
            .container {
                flex-direction: row;
                gap: 4rem;
                padding: 2rem;
            }
        }

        /* ===== CONTENT BOX ===== */
        .content-box {
            text-align: center;
            width: 100%;
            max-width: 480px;
            padding: 1.6rem 1.25rem;
            background: rgba(255, 255, 255, 0.02);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 0.05);
            border-radius: 20px;
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.4);
            animation: slideUp 0.8s cubic-bezier(0.16, 1, 0.3, 1);
        }

        @media (min-width: 768px) {
            .content-box {
                text-align: left;
                padding: 3rem;
                border-radius: 24px;
            }
        }

        @keyframes slideUp {
            from { opacity: 0; transform: translateY(40px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .error-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            background: rgba(244, 63, 94, 0.1);
            border: 1px solid rgba(244, 63, 94, 0.2);
            color: var(--accent);
            padding: 0.35rem 1rem;
            border-radius: 50px;
            font-size: 0.8rem;
            font-weight: 600;
            margin-bottom: 1.2rem;
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }

        @media (min-width: 768px) {
            .error-badge {
                padding: 0.4rem 1.2rem;
                font-size: 0.85rem;
                margin-bottom: 1.5rem;
            }
        }

        .error-title {
            font-size: 1.35rem;
            font-weight: 800;
            color: var(--text-light);
            margin-bottom: 0.75rem;
            line-height: 1.3;
        }

        @media (min-width: 768px) {
            .error-title {
                font-size: 1.75rem;
                margin-bottom: 1.2rem;
            }
        }

        .error-desc {
            font-size: 0.88rem;
            color: var(--text-muted);
            line-height: 1.55;
            margin-bottom: 1.5rem;
        }

        @media (min-width: 768px) {
            .error-desc {
                font-size: 0.98rem;
                line-height: 1.6;
                margin-bottom: 2.2rem;
            }
        }

        .btn-logout {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.75rem;
            background: linear-gradient(135deg, var(--primary), #e11d48);
            color: #fff;
            border: 1px solid rgba(255, 255, 255, 0.1);
            padding: 0.8rem 1.75rem;
            border-radius: 50px;
            font-weight: 600;
            font-size: 0.92rem;
            cursor: pointer;
            box-shadow: 0 10px 20px rgba(244, 63, 94, 0.25);
            transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
            width: 100%;
        }

        @media (min-width: 576px) {
            .btn-logout {
                width: auto;
                padding: 0.9rem 2.2rem;
                font-size: 0.95rem;
            }
        }

        .btn-logout:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 30px rgba(244, 63, 94, 0.4);
            border-color: rgba(255, 255, 255, 0.2);
        }

        /* ===== ART BOX ===== */
        .art-box {
            position: relative;
            width: 190px;
            height: 190px;
            display: flex;
            align-items: center;
            justify-content: center;
            animation: fadeIn 1.2s ease-in-out;
            flex-shrink: 0;
        }

        @media (min-width: 768px) {
            .art-box {
                width: 400px;
                height: 400px;
            }
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        /* Floating Lock / Shield */
        .space-shield {
            width: 75px;
            height: auto;
            position: absolute;
            z-index: 4;
            animation: floatLock 5s ease-in-out infinite alternate;
            filter: drop-shadow(0 10px 20px rgba(244, 63, 94, 0.3));
        }

        @media (min-width: 768px) {
            .space-shield {
                width: 140px;
            }
        }

        @keyframes floatLock {
            0% { transform: translateY(0) rotate(0deg); }
            50% { transform: translateY(-12px) rotate(4deg); }
            100% { transform: translateY(6px) rotate(-4deg); }
        }

        /* Red warning planet */
        .planet-red {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, #4c0519, #9f1239);
            border-radius: 50%;
            position: absolute;
            z-index: 2;
            box-shadow: inset -10px -10px 20px rgba(0, 0, 0, 0.6), 0 0 20px rgba(159, 18, 57, 0.4);
        }

        @media (min-width: 768px) {
            .planet-red {
                width: 120px;
                height: 120px;
                box-shadow: inset -15px -15px 30px rgba(0, 0, 0, 0.6), 0 0 25px rgba(159, 18, 57, 0.4);
            }
        }

        .c1 { width: 35px; height: 35px; top: 15%; left: 20%; background-color: rgba(0,0,0,0.15); border-radius: 50%; position: absolute; }
        .c2 { width: 20px; height: 20px; bottom: 20%; right: 25%; background-color: rgba(0,0,0,0.15); border-radius: 50%; position: absolute; }

        /* Orbiting warning alert ring */
        .orbit-ring {
            width: 160px;
            height: 55px;
            border: 3px dashed rgba(244, 63, 94, 0.3);
            border-radius: 50%;
            position: absolute;
            transform: rotate(-15deg);
            z-index: 3;
            animation: spinOrbit 40s linear infinite;
        }

        @media (min-width: 768px) {
            .orbit-ring {
                width: 240px;
                height: 80px;
                border-width: 4px;
            }
        }

        @keyframes spinOrbit {
            from { transform: rotate(-15deg) rotate(0deg); }
            to { transform: rotate(-15deg) rotate(360deg); }
        }
    </style>
</head>
<body>

    <div class="nebula"></div>
    <div class="stars"></div>

    <div class="container">
        <!-- Content Column -->
        <div class="content-box">
            <div class="error-badge">
                <i class="bi bi-shield-lock-fill"></i> Akses Dibatasi
            </div>
            <h1 class="error-title">Akun Anda Belum Memiliki Akses Cabang</h1>
            <p class="error-desc">
                Maaf, akun Anda saat ini aktif namun belum terasosiasi atau diizinkan untuk mengakses cabang mana pun. Silakan hubungi Administrator atau Super Admin untuk mendaftarkan akun Anda ke cabang perusahaan agar dapat melanjutkan.
            </p>
            
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="btn-logout">
                    <i class="bi bi-box-arrow-right"></i> Keluar (Sign Out)
                </button>
            </form>
        </div>

        <!-- Illustration Column -->
        <div class="art-box">
            <!-- Alert orbit ring -->
            <div class="orbit-ring"></div>

            <!-- Danger Red Planet -->
            <div class="planet-red">
                <div class="c1"></div>
                <div class="c2"></div>
            </div>

            <!-- Floating Padlock SVG -->
            <svg class="space-shield" viewBox="0 0 140 160" fill="none" xmlns="http://www.w3.org/2000/svg">
                <!-- Padlock arch -->
                <path d="M 35,60 L 35,45 C 35,22 50,5 70,5 C 90,5 105,22 105,45 L 105,60" stroke="#FFFFFF" stroke-width="8" stroke-linecap="round"/>
                <!-- Shackle body -->
                <rect x="15" y="60" width="110" height="90" rx="20" fill="#E11D48" stroke="#FFFFFF" stroke-width="8"/>
                <!-- Keyhole -->
                <circle cx="70" cy="100" r="12" fill="#FFFFFF"/>
                <path d="M 66,108 L 74,108 L 78,130 L 62,130 Z" fill="#FFFFFF"/>
                <!-- Detail warning exclamation inside shackle -->
                <circle cx="28" cy="73" r="4" fill="#FCA5A5"/>
            </svg>
        </div>
    </div>

</body>
</html>
