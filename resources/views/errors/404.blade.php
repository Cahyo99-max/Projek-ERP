<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 - Halaman Tidak Ditemukan</title>
    <link rel="shortcut icon" href="{{ $appLogo ?? asset('gambar/logo.png') }}" type="image/x-icon">
    <link rel="icon" href="{{ $appLogo ?? asset('gambar/logo.png') }}" type="image/x-icon">
    <!-- Google Font: Outfit -->
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;800&display=swap" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    
    <style>
        :root {
            --bg-dark: #070913;
            --primary: #2563eb;
            --accent: #38bdf8;
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
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            position: relative;
        }

        /* ===== BACKGROUND COSMIC EFFECT ===== */
        .nebula {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: 
                radial-gradient(circle at 20% 30%, rgba(37, 99, 235, 0.15) 0%, transparent 50%),
                radial-gradient(circle at 80% 70%, rgba(14, 165, 233, 0.15) 0%, transparent 50%);
            filter: blur(80px);
            z-index: 1;
            animation: nebulaPulse 12s ease-in-out infinite alternate;
        }

        @keyframes nebulaPulse {
            0% { transform: scale(1); opacity: 0.8; }
            100% { transform: scale(1.1); opacity: 1; }
        }

        /* Stars background */
        .stars {
            position: absolute;
            width: 100%;
            height: 100%;
            background-image: 
                radial-gradient(circle, #fff 1px, transparent 1px),
                radial-gradient(circle, rgba(255,255,255,0.7) 1px, transparent 1px),
                radial-gradient(circle, rgba(255,255,255,0.4) 1.5px, transparent 1.5px);
            background-size: 550px 550px, 350px 350px, 250px 250px;
            background-position: 0 0, 40px 60px, 130px 270px;
            opacity: 0.3;
            z-index: 1;
            animation: starsMove 180s linear infinite;
        }

        @keyframes starsMove {
            from { background-position: 0 0, 40px 60px, 130px 270px; }
            to { background-position: 550px 550px, 590px 610px, 680px 820px; }
        }

        /* Twinkling Stars */
        .star-sparkle {
            position: absolute;
            width: 4px;
            height: 4px;
            background: #fff;
            border-radius: 50%;
            box-shadow: 0 0 10px #fff, 0 0 20px var(--accent);
            z-index: 2;
            animation: sparkle 4s ease-in-out infinite;
        }
        .s1 { top: 20%; left: 15%; animation-delay: 0s; }
        .s2 { top: 75%; left: 10%; animation-delay: 1.5s; }
        .s3 { top: 15%; left: 80%; animation-delay: 0.8s; }
        .s4 { top: 80%; left: 85%; animation-delay: 2.3s; }
        .s5 { top: 45%; left: 90%; animation-delay: 3s; }

        @keyframes sparkle {
            0%, 100% { opacity: 0.2; transform: scale(0.8); }
            50% { opacity: 1; transform: scale(1.4); }
        }

        /* ===== CONTAINER LAYOUT ===== */
        .container {
            width: 100%;
            max-width: 1100px;
            margin: 0 auto;
            padding: 2rem;
            display: flex;
            flex-direction: column-reverse;
            align-items: center;
            justify-content: space-between;
            position: relative;
            z-index: 10;
            gap: 2rem;
        }

        @media (min-width: 768px) {
            .container {
                flex-direction: row;
                gap: 4rem;
            }
        }

        /* ===== CONTENT COLUMN (Glassmorphism) ===== */
        .content-box {
            text-align: center;
            max-width: 500px;
            padding: 2.5rem;
            background: rgba(255, 255, 255, 0.02);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 0.05);
            border-radius: 24px;
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.3);
            animation: slideUp 0.8s cubic-bezier(0.16, 1, 0.3, 1);
        }

        @media (min-width: 768px) {
            .content-box {
                text-align: left;
                padding: 3rem;
            }
        }

        @keyframes slideUp {
            from { opacity: 0; transform: translateY(40px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .error-code {
            font-size: 6rem;
            font-weight: 800;
            line-height: 1;
            margin-bottom: 1rem;
            background: linear-gradient(135deg, #ffffff 30%, var(--accent) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            letter-spacing: -2px;
            position: relative;
            display: inline-block;
        }

        .error-code::after {
            content: '';
            position: absolute;
            bottom: -5px;
            left: 0;
            width: 40px;
            height: 4px;
            background: var(--accent);
            border-radius: 2px;
        }
        @media (max-width: 767px) {
            .error-code::after {
                left: 50%;
                transform: translateX(-50%);
            }
        }

        .error-title {
            font-size: 1.8rem;
            font-weight: 600;
            color: var(--text-light);
            margin-bottom: 1rem;
        }

        .error-desc {
            font-size: 1rem;
            color: var(--text-muted);
            line-height: 1.6;
            margin-bottom: 2rem;
        }

        /* Button Glassmorphism and Hover */
        .btn-back {
            display: inline-flex;
            align-items: center;
            gap: 0.75rem;
            background: linear-gradient(135deg, var(--primary), #1d4ed8);
            color: #fff;
            text-decoration: none;
            padding: 0.9rem 2.2rem;
            border-radius: 50px;
            font-weight: 600;
            font-size: 0.95rem;
            border: 1px solid rgba(255, 255, 255, 0.1);
            box-shadow: 0 10px 20px rgba(37, 99, 235, 0.25);
            transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
            position: relative;
            overflow: hidden;
        }

        .btn-back::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: 0.5s;
        }

        .btn-back:hover::before {
            left: 100%;
        }

        .btn-back:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 30px rgba(37, 99, 235, 0.4);
            border-color: rgba(255, 255, 255, 0.2);
        }

        .btn-back:active {
            transform: translateY(-1px);
        }

        /* ===== ART COLUMN (Space Illustration) ===== */
        .art-box {
            position: relative;
            width: 320px;
            height: 320px;
            display: flex;
            align-items: center;
            justify-content: center;
            animation: fadeIn 1.2s ease-in-out;
        }

        @media (min-width: 768px) {
            .art-box {
                width: 480px;
                height: 480px;
            }
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        /* Floating Astronaut */
        .astronaut-svg {
            width: 160px;
            height: auto;
            position: absolute;
            top: 10%;
            right: 15%;
            z-index: 4;
            animation: floatAstronaut 6s ease-in-out infinite alternate;
        }

        @keyframes floatAstronaut {
            0% { transform: translateY(0) rotate(0deg); }
            50% { transform: translateY(-20px) rotate(3deg); }
            100% { transform: translateY(10px) rotate(-3deg); }
        }

        /* Tether wire (Tali pengaman) */
        .tether {
            position: absolute;
            z-index: 3;
            stroke-dasharray: 6;
            animation: tetherMove 6s ease-in-out infinite alternate;
            transform-origin: 50% 50%;
        }

        @keyframes tetherMove {
            0% { transform: scaleY(1) rotate(0deg); }
            100% { transform: scaleY(1.05) rotate(2deg); }
        }

        /* Launching Rocket */
        .rocket-container {
            position: absolute;
            bottom: 15%;
            left: 15%;
            width: 180px;
            height: auto;
            z-index: 5;
            transform: rotate(-15deg);
            animation: rocketHover 4s ease-in-out infinite alternate;
        }

        @keyframes rocketHover {
            0% { transform: translate(0, 0) rotate(-15deg); }
            100% { transform: translate(-8px, -15px) rotate(-12deg); }
        }

        /* Fire flame pulse */
        .rocket-flame {
            transform-origin: top center;
            animation: flamePulse 0.15s linear infinite alternate;
        }

        @keyframes flamePulse {
            0% { transform: scaleY(0.9) scaleX(0.95); opacity: 0.9; }
            100% { transform: scaleY(1.25) scaleX(1.05); opacity: 1; filter: drop-shadow(0 0 8px rgba(249, 115, 22, 0.8)); }
        }

        /* Moon / Planet background */
        .planet {
            width: 100px;
            height: 100px;
            background: linear-gradient(135deg, #1e3a8a, #0369a1);
            border-radius: 50%;
            position: absolute;
            top: 15%;
            left: 20%;
            z-index: 2;
            box-shadow: inset -15px -15px 30px rgba(0, 0, 0, 0.4), 0 0 20px rgba(30, 58, 138, 0.5);
            overflow: hidden;
        }

        .planet-crater {
            background-color: rgba(0,0,0,0.1);
            border-radius: 50%;
            position: absolute;
        }
        .c1 { width: 30px; height: 30px; top: 20%; left: 20%; }
        .c2 { width: 15px; height: 15px; top: 60%; left: 55%; }
        .c3 { width: 20px; height: 20px; top: 40%; left: 65%; }

        /* Saturn-like planet */
        .planet-saturn {
            width: 70px;
            height: 70px;
            background: linear-gradient(135deg, #3730a3, #4338ca);
            border-radius: 50%;
            position: absolute;
            bottom: 20%;
            right: 15%;
            z-index: 2;
            box-shadow: inset -10px -10px 20px rgba(0, 0, 0, 0.5), 0 0 15px rgba(55, 48, 163, 0.4);
            transform: rotate(-25deg);
        }
        /* Planet Ring */
        .ring {
            position: absolute;
            top: 50%;
            left: 50%;
            width: 130px;
            height: 24px;
            border: 8px solid rgba(139, 92, 246, 0.5);
            border-radius: 50%;
            transform: translate(-50%, -50%) rotate(25deg);
            z-index: 1;
            box-shadow: 0 0 10px rgba(139, 92, 246, 0.3);
        }
    </style>
</head>
<body>

    <div class="nebula"></div>
    <div class="stars"></div>
    <div class="star-sparkle s1"></div>
    <div class="star-sparkle s2"></div>
    <div class="star-sparkle s3"></div>
    <div class="star-sparkle s4"></div>
    <div class="star-sparkle s5"></div>

    <div class="container">
        <!-- Content Box (Left) -->
        <div class="content-box">
            <h1 class="error-code">404</h1>
            <h2 class="error-title">Kamu Tersesat di Luar Angkasa?</h2>
            <p class="error-desc">
                Halaman yang kamu cari tidak dapat ditemukan. Mungkin halaman tersebut telah dipindahkan, dihapus, atau koordinat navigasi yang dimasukkan salah.
            </p>
            <a href="/dashbord" class="btn-back">
                <i class="bi bi-rocket-takeoff-fill"></i> Kembali ke Dashboard
            </a>
        </div>

        <!-- Illustration Column (Right) -->
        <div class="art-box">
            <!-- Blue Planet (Top Left) -->
            <div class="planet">
                <div class="planet-crater c1"></div>
                <div class="planet-crater c2"></div>
                <div class="planet-crater c3"></div>
            </div>

            <!-- Saturn-like Planet (Bottom Right) -->
            <div class="planet-saturn">
                <div class="ring"></div>
            </div>

            <!-- SVG Tether security wire -->
            <svg class="tether" width="400" height="400" viewBox="0 0 400 400" fill="none" style="position: absolute; top:0; left:0; width:100%; height:100%;">
                <path d="M 120,280 Q 200,320 280,100" stroke="rgba(255, 255, 255, 0.35)" stroke-width="2" fill="none"/>
            </svg>

            <!-- SVG Astronaut (Floating) -->
            <svg class="astronaut-svg" viewBox="0 0 240 240" fill="none" xmlns="http://www.w3.org/2000/svg">
                <!-- Suit Body -->
                <rect x="75" y="80" width="90" height="100" rx="35" fill="#FFFFFF" stroke="#0F172A" stroke-width="6"/>
                <!-- Limbs -->
                <rect x="50" y="90" width="26" height="60" rx="13" fill="#E2E8F0" stroke="#0F172A" stroke-width="6" transform="rotate(25 50 90)"/>
                <rect x="160" y="90" width="26" height="60" rx="13" fill="#E2E8F0" stroke="#0F172A" stroke-width="6" transform="rotate(-35 160 90)"/>
                <rect x="85" y="160" width="28" height="65" rx="14" fill="#E2E8F0" stroke="#0F172A" stroke-width="6"/>
                <rect x="125" y="160" width="28" height="65" rx="14" fill="#E2E8F0" stroke="#0F172A" stroke-width="6"/>
                <!-- Helmet -->
                <circle cx="120" cy="70" r="45" fill="#FFFFFF" stroke="#0F172A" stroke-width="6"/>
                <!-- Visor -->
                <rect x="90" y="45" width="60" height="40" rx="15" fill="#1E293B" stroke="#0EA5E9" stroke-width="4"/>
                <path d="M 98,52 Q 120,44 142,52" stroke="#FFFFFF" stroke-width="3" stroke-linecap="round"/>
                <!-- Details -->
                <rect x="95" y="105" width="50" height="35" rx="8" fill="#F1F5F9" stroke="#0F172A" stroke-width="4"/>
                <circle cx="107" cy="122" r="5" fill="#EF4444"/>
                <circle cx="122" cy="122" r="5" fill="#10B981"/>
                <circle cx="137" cy="122" r="5" fill="#3B82F6"/>
            </svg>

            <!-- SVG Rocket (Hovering) -->
            <div class="rocket-container">
                <svg viewBox="0 0 120 180" fill="none" xmlns="http://www.w3.org/2000/svg" style="width: 100%; height: auto;">
                    <!-- Thruster fire flame -->
                    <path class="rocket-flame" d="M 50,115 Q 60,165 70,115 Q 60,135 50,115" fill="#F97316"/>
                    <path class="rocket-flame" d="M 55,115 Q 60,150 65,115" fill="#FACC15"/>
                    <!-- Fins / Wings -->
                    <path d="M 30,110 L 10,120 C 15,90 35,70 35,70 Z" fill="#1E293B" stroke="#0F172A" stroke-width="4"/>
                    <path d="M 90,110 L 110,120 C 105,90 85,70 85,70 Z" fill="#1E293B" stroke="#0F172A" stroke-width="4"/>
                    <!-- Main Body -->
                    <path d="M 60,10 C 60,10 90,50 90,100 L 30,100 C 30,50 60,10 60,10 Z" fill="#FFFFFF" stroke="#0F172A" stroke-width="5"/>
                    <!-- Tip cone red -->
                    <path d="M 60,10 C 60,10 75,30 75,45 L 45,45 C 45,30 60,10 60,10 Z" fill="#3B82F6" stroke="#0F172A" stroke-width="2"/>
                    <!-- Window / Port hole -->
                    <circle cx="60" cy="70" r="16" fill="#F1F5F9" stroke="#0F172A" stroke-width="4"/>
                    <circle cx="60" cy="70" r="11" fill="#38BDF8"/>
                    <!-- Booster Base -->
                    <rect x="42" y="100" width="36" height="15" rx="3" fill="#475569" stroke="#0F172A" stroke-width="4"/>
                </svg>
            </div>
        </div>
    </div>

</body>
</html>
