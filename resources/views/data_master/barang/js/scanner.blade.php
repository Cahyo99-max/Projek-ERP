// ===== CAMERA SCANNER LOGIC =====
let html5QrcodeScanner = null;
let currentScanTarget = null;
let availableCameraIds = [];
let currentCameraIndex = 0;

function switchCamera() {
    if (!availableCameraIds || availableCameraIds.length <= 1) {
        Swal.fire({ icon: 'info', title: 'Info Kamera', text: 'Hanya 1 perangkat kamera yang terdeteksi pada perangkat ini.', timer: 1500, showConfirmButton: false });
        return;
    }
    currentCameraIndex = (currentCameraIndex + 1) % availableCameraIds.length;
    startCameraScanner(availableCameraIds[currentCameraIndex]);
}

$(document).on('click', '.btn-scan-barcode', function() {
    currentScanTarget = $($(this).data('target'));
    $('#modalScanner').modal('show');
    
    // Start scanner
    setTimeout(() => {
        startCameraScanner();
    }, 450); // Wait for modal slide transition
});

function startCameraScanner(specificCameraId = null) {
    if (html5QrcodeScanner) {
        try {
            html5QrcodeScanner.clear().catch(err => console.log(err));
        } catch (e) {}
    }

    $('#scanner_reader').html('<div class="d-flex align-items-center justify-content-center text-white flex-column py-5" style="min-height: 220px;"><div class="spinner-border text-success mb-2" role="status"></div><span class="small text-success">Menginisialisasi Kamera...</span></div>');
    $('.scanner-overlay').hide();

    let formats = undefined;
    if (typeof Html5QrcodeSupportedFormats !== 'undefined') {
        formats = [
            Html5QrcodeSupportedFormats.QR_CODE,
            Html5QrcodeSupportedFormats.CODE_128,
            Html5QrcodeSupportedFormats.CODE_39,
            Html5QrcodeSupportedFormats.EAN_13,
            Html5QrcodeSupportedFormats.EAN_8,
            Html5QrcodeSupportedFormats.UPC_A,
            Html5QrcodeSupportedFormats.UPC_E,
            Html5QrcodeSupportedFormats.ITF,
            Html5QrcodeSupportedFormats.DATA_MATRIX
        ];
    }

    html5QrcodeScanner = new Html5Qrcode("scanner_reader", {
        formatsToSupport: formats,
        verbose: false
    });
    
    let isProcessingScan = false;
    const qrCodeSuccessCallback = (decodedText, decodedResult) => {
        if (isProcessingScan) return;
        isProcessingScan = true;

        // Play sound beep
        try {
            let audioCtx = new (window.AudioContext || window.webkitAudioContext)();
            let osc = audioCtx.createOscillator();
            let gain = audioCtx.createGain();
            osc.connect(gain);
            gain.connect(audioCtx.destination);
            osc.type = "sine";
            osc.frequency.value = 800;
            gain.gain.setValueAtTime(0.1, audioCtx.currentTime);
            osc.start();
            osc.stop(audioCtx.currentTime + 0.15);
        } catch(e) {}

        // Flash green box on success
        $('.scanner-target-box').css('box-shadow', '0 0 35px 12px #22c55e');

        if (currentScanTarget) {
            currentScanTarget.val(decodedText);
            currentScanTarget.trigger('change');
        }
        
        setTimeout(() => {
            $('#modalScanner').modal('hide');
            
            Swal.fire({
                icon: 'success',
                title: 'Scan Berhasil',
                text: 'Kode: ' + decodedText,
                timer: 1500,
                showConfirmButton: false
            });
        }, 120);
    };

    const proceedStart = (cameraConfig) => {
        const scanConfig = { 
            fps: 15,
            qrbox: function(viewfinderWidth, viewfinderHeight) {
                const w = Math.min(Math.floor(viewfinderWidth * 0.82), 300);
                const h = Math.min(Math.floor(viewfinderHeight * 0.52), 160);
                return { width: w, height: h };
            }
        };

        html5QrcodeScanner.start(
            cameraConfig, 
            scanConfig, 
            qrCodeSuccessCallback,
            (errorMessage) => {}
        ).then(() => {
            $('.scanner-overlay').fadeIn(150);
        }).catch(err => {
            console.error("Camera access failed:", err);
            let cleanError = err ? err.toString() : 'Unknown Error';
            
            $('#scanner_reader').html(`
                <div class="d-flex align-items-center justify-content-center text-white flex-column p-4 text-center" style="height: 250px; background: #27272a;">
                    <i class="bi bi-exclamation-triangle-fill text-warning mb-2" style="font-size: 2rem;"></i>
                    <span class="fw-semibold">Gagal Membuka Kamera</span>
                    <small class="text-muted mt-2">${cleanError}</small>
                    <small class="text-danger mt-1" style="font-size: 0.75rem;">Pastikan izin kamera aktif & menggunakan koneksi aman HTTPS / Localhost.</small>
                </div>
            `);
        });
    };

    if (specificCameraId) {
        proceedStart(specificCameraId);
    } else {
        Html5Qrcode.getCameras().then(devices => {
            if (devices && devices.length) {
                availableCameraIds = devices.map(d => d.id);
                // Prefer rear / back camera (environment)
                let backCamera = devices.find(d => {
                    let label = (d.label || '').toLowerCase();
                    return label.includes('back') || label.includes('rear') || label.includes('belakang') || label.includes('environment');
                });
                if (backCamera) {
                    currentCameraIndex = availableCameraIds.indexOf(backCamera.id);
                    proceedStart(backCamera.id);
                } else {
                    currentCameraIndex = 0;
                    proceedStart({ facingMode: "environment" });
                }
            } else {
                proceedStart({ facingMode: "environment" });
            }
        }).catch(() => {
            proceedStart({ facingMode: "environment" });
        });
    }
}

function stopCameraScanner() {
    // 1. Explicitly stop all video MediaStreamTracks to turn off camera hardware light immediately
    try {
        $('#scanner_reader video').each(function() {
            if (this.srcObject) {
                let stream = this.srcObject;
                if (stream && stream.getTracks) {
                    stream.getTracks().forEach(track => {
                        try { track.stop(); } catch(e) {}
                    });
                }
                this.srcObject = null;
            }
        });
    } catch(e) {}

    // 2. Stop Html5Qrcode instance
    if (html5QrcodeScanner) {
        try {
            if (html5QrcodeScanner.isScanning) {
                html5QrcodeScanner.stop().then(() => {
                    try { html5QrcodeScanner.clear(); } catch(e) {}
                    html5QrcodeScanner = null;
                }).catch(err => {
                    try { html5QrcodeScanner.clear(); } catch(e) {}
                    html5QrcodeScanner = null;
                });
            } else {
                try { html5QrcodeScanner.clear(); } catch(e) {}
                html5QrcodeScanner = null;
            }
        } catch (e) {
            html5QrcodeScanner = null;
        }
    }

    // 3. Clear container DOM and hide overlay
    $('#scanner_reader').empty();
    $('.scanner-overlay').hide();
}

let dataMasterCameraTimer = null;

$(document).on('click', '.btn-scan-barcode', function() {
    currentScanTarget = $($(this).data('target'));
    if (dataMasterCameraTimer) clearTimeout(dataMasterCameraTimer);
    $('#modalScanner').modal('show');
    dataMasterCameraTimer = setTimeout(() => {
        startCameraScanner();
    }, 400);
});

// Stop scanner when modal is closed
$('#modalScanner').on('hide.bs.modal hidden.bs.modal', function () {
    if (dataMasterCameraTimer) {
        clearTimeout(dataMasterCameraTimer);
        dataMasterCameraTimer = null;
    }
    stopCameraScanner();
});
