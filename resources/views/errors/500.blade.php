<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>500 - Kesalahan Server Internal</title>
    <link rel="shortcut icon" href="{{ $appLogo ?? asset('gambar/logo.png') }}" type="image/x-icon">
    <link rel="icon" href="{{ $appLogo ?? asset('gambar/logo.png') }}" type="image/x-icon">
    <!-- Google Font: Outfit -->
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;800&display=swap" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    
    <style>
        :root {
            --bg-dark: #0f0a06;
            --primary: #f97316;
            --accent: #ef4444;
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
                radial-gradient(circle at 30% 20%, rgba(249, 115, 22, 0.12) 0%, transparent 50%),
                radial-gradient(circle at 70% 80%, rgba(239, 68, 68, 0.12) 0%, transparent 50%);
            filter: blur(80px);
            z-index: 1;
            animation: nebulaPulse 10s ease-in-out infinite alternate;
        }

        @keyframes nebulaPulse {
            0% { transform: scale(1); opacity: 0.8; }
            100% { transform: scale(1.08); opacity: 1; }
        }

        /* Stars background */
        .stars {
            position: absolute;
            width: 100%;
            height: 100%;
            background-image: 
                radial-gradient(circle, #fff 1px, transparent 1px),
                radial-gradient(circle, rgba(255,255,255,0.7) 1px, transparent 1px),
                radial-gradient(circle, rgba(249,115,22,0.3) 1.5px, transparent 1.5px);
            background-size: 500px 500px, 320px 320px, 220px 220px;
            background-position: 0 0, 30px 50px, 120px 200px;
            opacity: 0.35;
            z-index: 1;
            animation: starsMove 150s linear infinite;
        }

        @keyframes starsMove {
            from { background-position: 0 0, 30px 50px, 120px 200px; }
            to { background-position: 500px 500px, 530px 550px, 620px 700px; }
        }

        /* Twinkling Warning Sparks */
        .star-sparkle {
            position: absolute;
            width: 5px;
            height: 5px;
            background: #fef3c7;
            border-radius: 50%;
            box-shadow: 0 0 10px #fef3c7, 0 0 20px var(--accent);
            z-index: 2;
            animation: sparkle 4s ease-in-out infinite;
        }
        .s1 { top: 25%; left: 18%; animation-delay: 0s; }
        .s2 { top: 70%; left: 12%; animation-delay: 1.2s; }
        .s3 { top: 20%; left: 82%; animation-delay: 0.6s; }
        .s4 { top: 82%; left: 78%; animation-delay: 2.1s; }
        .s5 { top: 50%; left: 88%; animation-delay: 2.8s; }

        @keyframes sparkle {
            0%, 100% { opacity: 0.3; transform: scale(0.8); }
            50% { opacity: 1; transform: scale(1.5); }
        }

        /* ===== CONTAINER LAYOUT ===== */
        .container {
            width: 100%;
            max-width: 1050px;
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
            background: rgba(255, 255, 255, 0.015);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid rgba(249, 115, 22, 0.10);
            border-radius: 24px;
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.4);
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
            background: linear-gradient(135deg, #ffffff 40%, var(--accent) 100%);
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
            background: var(--primary);
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
            font-size: 0.98rem;
            color: var(--text-muted);
            line-height: 1.6;
            margin-bottom: 2rem;
        }

        /* Button Glassmorphism and Hover */
        .btn-back {
            display: inline-flex;
            align-items: center;
            gap: 0.75rem;
            background: linear-gradient(135deg, var(--primary), #d97706);
            color: #fff;
            text-decoration: none;
            padding: 0.9rem 2.2rem;
            border-radius: 50px;
            font-weight: 600;
            font-size: 0.95rem;
            border: 1px solid rgba(255, 255, 255, 0.1);
            box-shadow: 0 10px 20px rgba(249, 115, 22, 0.25);
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
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.25), transparent);
            transition: 0.5s;
        }

        .btn-back:hover::before {
            left: 100%;
        }

        .btn-back:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 30px rgba(249, 115, 22, 0.4);
            border-color: rgba(255, 255, 255, 0.2);
        }

        .btn-back:active {
            transform: translateY(-1px);
        }

        /* ===== ART COLUMN (Space Shield) ===== */
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
                width: 440px;
                height: 440px;
            }
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        /* Glowing Shield Gate */
        .shield-gate {
            position: absolute;
            width: 200px;
            height: 200px;
            border: 4px dashed rgba(249, 115, 22, 0.4);
            border-radius: 50%;
            z-index: 2;
            animation: rotateShield 20s linear infinite;
            box-shadow: 0 0 30px rgba(249, 115, 22, 0.2);
        }

        .shield-gate-inner {
            position: absolute;
            width: 150px;
            height: 150px;
            border: 2px solid rgba(239, 68, 68, 0.3);
            border-radius: 50%;
            z-index: 2;
            animation: rotateShieldReverse 15s linear infinite;
        }

        @keyframes rotateShield {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        @keyframes rotateShieldReverse {
            0% { transform: rotate(360deg); }
            100% { transform: rotate(0deg); }
        }

        /* Hologram Icon */
        .hologram-lock {
            z-index: 4;
            animation: floatLock 5s ease-in-out infinite alternate;
            filter: drop-shadow(0 0 15px rgba(249, 115, 22, 0.5));
        }

        @keyframes floatLock {
            0% { transform: translateY(0); }
            50% { transform: translateY(-15px); }
            100% { transform: translateY(5px); }
        }

        /* Background Warning Node */
        .warning-node {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, rgba(249, 115, 22, 0.15), rgba(217, 119, 6, 0.08));
            border-radius: 50%;
            position: absolute;
            top: 10%;
            right: 15%;
            z-index: 1;
            box-shadow: 0 0 25px rgba(249, 115, 22, 0.25);
            border: 1px solid rgba(249, 115, 22, 0.2);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            color: var(--primary);
            animation: pulseWarning 2s infinite alternate;
        }

        @keyframes pulseWarning {
            0% { transform: scale(1); opacity: 0.7; }
            100% { transform: scale(1.15); opacity: 1; }
        }

        .warning-node-2 {
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, rgba(239, 68, 68, 0.12), rgba(220, 38, 38, 0.05));
            border-radius: 50%;
            position: absolute;
            bottom: 12%;
            left: 10%;
            z-index: 1;
            box-shadow: 0 0 20px rgba(239, 68, 68, 0.15);
            border: 1px solid rgba(239, 68, 68, 0.15);
            animation: pulseWarning 2.5s infinite alternate-reverse;
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
            <h1 class="error-code">500</h1>
            <h2 class="error-title">Kesalahan Server Internal</h2>
            <p class="error-desc">
                Maaf, terjadi kesalahan tak terduga pada server kami saat memproses permintaan Anda. Tim IT kami sedang meninjau dan memperbaikinya. Silakan coba kembali beberapa saat lagi.
            </p>
            <a href="/dashbord" class="btn-back">
                <i class="bi bi-speedometer2"></i> Kembali ke Dashboard
            </a>
        </div>

        <!-- Illustration Column (Right) -->
        <div class="art-box">
            <div class="warning-node">
                <i class="bi bi-cpu"></i>
            </div>
            <div class="warning-node-2"></div>
            
            <div class="shield-gate"></div>
            <div class="shield-gate-inner"></div>

            <!-- Server Console Icon SVG -->
            <svg class="hologram-lock" width="160" height="160" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M19 15v3H5v-3h14m1-2H4c-.55 0-1 .45-1 1v6c0 .55.45 1 1 1h16c.55 0 1-.45 1-1v-6c0-.55-.45-1-1-1zM19 5v3H5V5h14m1-2H4c-.55 0-1 .45-1 1v6c0 .55.45 1 1 1h16c.55 0 1-.45 1-1V4c0-.55-.45-1-1-1z" fill="#f97316"/>
            </svg>
        </div>
    </div>

</body>
</html>
