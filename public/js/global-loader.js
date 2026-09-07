(function() {
    // Flag to track page load status
    window.pageLoadedAlready = false;

    // 1. Inject CSS Styles
    const css = `
        .global-loader-overlay {
            position: fixed;
            inset: 0;
            z-index: 999999;
            background: rgba(15, 23, 42, 0.75);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            opacity: 0;
            pointer-events: none;
            transition: opacity 0.35s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .global-loader-overlay.active {
            opacity: 1;
            pointer-events: auto;
        }
        .loader-container {
            position: relative;
            width: 95px;
            height: 95px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .loader-ring {
            position: absolute;
            width: 100%;
            height: 100%;
            border-radius: 50%;
            border: 3.5px solid transparent;
            border-top-color: var(--primary, #2563eb);
            border-bottom-color: #10b981;
            animation: loader-spin 1.2s cubic-bezier(0.5, 0, 0.5, 1) infinite;
        }
        .loader-ring::before {
            content: '';
            position: absolute;
            inset: 6px;
            border-radius: 50%;
            border: 3px solid transparent;
            border-left-color: #f59e0b;
            border-right-color: #ec4899;
            animation: loader-spin-reverse 1.8s linear infinite;
        }
        .loader-logo {
            width: 40px;
            height: 40px;
            object-fit: contain;
            animation: loader-pulse 1.5s ease-in-out infinite;
            z-index: 10;
            border-radius: 8px;
        }
        .loader-text {
            margin-top: 20px;
            color: #f8fafc;
            font-family: 'Inter', sans-serif;
            font-size: 0.9rem;
            font-weight: 600;
            letter-spacing: 0.5px;
            text-shadow: 0 2px 4px rgba(0,0,0,0.3);
            animation: loader-text-pulse 1.5s ease-in-out infinite;
        }
        @keyframes loader-spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        @keyframes loader-spin-reverse {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(-360deg); }
        }
        @keyframes loader-pulse {
            0%, 100% { transform: scale(0.9); opacity: 0.7; }
            50% { transform: scale(1.1); opacity: 1; }
        }
        @keyframes loader-text-pulse {
            0%, 100% { opacity: 0.5; }
            50% { opacity: 1; }
        }
    `;

    const styleEl = document.createElement('style');
    styleEl.innerHTML = css;
    document.head.appendChild(styleEl);

    // 2. Create Overlay element and insert it into body
    function initLoader() {
        if (document.getElementById('global-loader-overlay')) return;

        const overlay = document.createElement('div');
        overlay.id = 'global-loader-overlay';
        overlay.className = 'global-loader-overlay';
        
        // Start as active immediately for a premium preloader fade-in effect on load
        if (!window.pageLoadedAlready) {
            overlay.classList.add('active');
        }

        const logoUrl = window.appLogoSource || '/gambar/logo.png';

        overlay.innerHTML = `
            <div class="loader-container">
                <div class="loader-ring"></div>
                <img class="loader-logo" src="${logoUrl}" alt="App Logo" onerror="this.src='/gambar/logo.png'">
            </div>
            <div class="loader-text" id="global-loader-text">Memuat halaman...</div>
        `;
        document.body.appendChild(overlay);
    }

    // Initialize immediately
    initLoader();

    // 3. Define Global Window functions
    window.showLoader = function(message = 'Memproses data...') {
        initLoader(); // ensure it exists
        const textEl = document.getElementById('global-loader-text');
        if (textEl) textEl.textContent = message;
        
        const overlay = document.getElementById('global-loader-overlay');
        if (overlay) overlay.classList.add('active');
    };

    window.hideLoader = function() {
        const overlay = document.getElementById('global-loader-overlay');
        if (overlay) overlay.classList.remove('active');
    };

    // 4. Automatic page transition loader on local navigation links click
    document.addEventListener('click', function(e) {
        // Find closest <a> tag up the tree
        const link = e.target.closest('a');
        if (!link) return;

        const href = link.getAttribute('href');
        
        // Skip conditions:
        // - No href attribute
        // - Hash link or empty href
        // - Javascript/mailto/tel protocol
        // - External links
        // - Target blank links
        // - Download attribute
        // - Collapse toggles or modal triggers
        if (!href || 
            href.startsWith('#') || 
            href.startsWith('javascript:') || 
            href.startsWith('mailto:') || 
            href.startsWith('tel:') ||
            link.hasAttribute('download') ||
            link.getAttribute('target') === '_blank' ||
            link.getAttribute('data-bs-toggle') === 'collapse' ||
            link.getAttribute('data-bs-toggle') === 'modal' ||
            link.classList.contains('no-loader')
        ) {
            return;
        }

        // Validate local domain URL
        try {
            const url = new URL(link.href);
            if (url.origin !== window.location.origin) {
                return;
            }
        } catch (err) {
            // If URL parsing fails, ignore
            return;
        }

        // Show the loader overlay
        window.showLoader('Memuat halaman...');
    });

    // 5. Fade out preloader smoothly after page is fully loaded
    function dismissPreloader() {
        window.pageLoadedAlready = true;
        setTimeout(function() {
            window.hideLoader();
        }, 400);
    }

    if (document.readyState === 'complete') {
        dismissPreloader();
    } else {
        window.addEventListener('load', dismissPreloader);
        document.addEventListener('DOMContentLoaded', function() {
            setTimeout(dismissPreloader, 600);
        });
    }

    // Safety fallback timeout: Preloader will never get stuck longer than 1.5 seconds
    setTimeout(function() {
        if (!window.pageLoadedAlready) {
            window.hideLoader();
        }
    }, 1500);
})();
