<script>
$(document).ready(function () {

    let activeCameraStream = null;
    let activeFacingMode   = 'environment'; // Default rear camera
    let cameraTargetModal  = 'create';      // 'create' or 'edit'

    // ===== OPEN CAMERA MODAL (CREATE) =====
    $(document).on('click', '#btnTriggerCreateCamera', function (e) {
        e.preventDefault();
        cameraTargetModal = 'create';
        openLiveCameraModal();
    });

    // ===== OPEN CAMERA MODAL (EDIT) =====
    $(document).on('click', '#btnTriggerEditCamera', function (e) {
        e.preventDefault();
        cameraTargetModal = 'edit';
        openLiveCameraModal();
    });

    function openLiveCameraModal() {
        $('#modalLiveCameraCapture').modal('show');
    }

    // Modal Shown Event -> Start Camera
    $('#modalLiveCameraCapture').on('shown.bs.modal', function () {
        initCameraStream(activeFacingMode);
    });

    // Modal Hidden Event -> Stop Camera Stream
    $('#modalLiveCameraCapture').on('hidden.bs.modal', function () {
        stopCameraStream();
    });

    // ===== INITIALIZE CAMERA STREAM =====
    async function initCameraStream(facingMode) {
        stopCameraStream();

        $('#cameraLoadingState').show();
        $('#cameraErrorState').hide();
        $('#cameraVideoWrapper').addClass('d-none');
        $('#cameraCapturedPreviewWrapper').addClass('d-none');
        $('#cameraLiveControls').removeClass('d-none').addClass('d-flex');
        $('#cameraReviewControls').removeClass('d-flex').addClass('d-none');

        if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
            showCameraError(
                'Browser tidak mendukung akses kamera langsung (getUserMedia).',
                'Silakan gunakan tombol "Buka Kamera Asli HP" atau pilih foto dari galeri.'
            );
            return;
        }

        // 250ms grace period so OS hardware locks are completely released
        await new Promise(resolve => setTimeout(resolve, 250));

        let stream = null;

        // Attempt 1: Specific facing mode (ideal)
        try {
            stream = await navigator.mediaDevices.getUserMedia({
                audio: false,
                video: {
                    facingMode: { ideal: facingMode }
                }
            });
        } catch (err1) {
            console.warn('Attempt 1 failed, trying fallback generic video stream:', err1);
            // Attempt 2: Simple generic video constraint
            try {
                stream = await navigator.mediaDevices.getUserMedia({
                    audio: false,
                    video: true
                });
            } catch (err2) {
                console.error('Camera initialization error:', err2);
                let msg = 'Gagal mengakses kamera.';
                let hint = 'Pastikan izin kamera diizinkan dan kamera tidak sedang dipakai aplikasi lain.';

                if (err2.name === 'NotAllowedError' || err2.name === 'PermissionDeniedError') {
                    msg = 'Izin kamera ditolak oleh browser.';
                    hint = 'Klik ikon izin/gembok di address bar browser lalu pilih "Izinkan Akses Kamera".';
                } else if (err2.name === 'NotReadableError' || err2.name === 'TrackStartError') {
                    msg = 'Kamera sedang sibuk / digunakan (Device in use).';
                    hint = 'Kamera sedang dikunci oleh driver DroidCam, Zoom, OBS, atau aplikasi lain. Tutup aplikasi tersebut atau klik "Buka Kamera Asli HP".';
                } else if (err2.name === 'NotFoundError' || err2.name === 'DevicesNotFoundError') {
                    msg = 'Tidak ditemukan perangkat kamera.';
                    hint = 'Gunakan tombol "Pilih File Foto / Galeri" untuk mengunggah struk/bukti transfer.';
                } else {
                    msg += ' ' + (err2.message || '');
                }

                showCameraError(msg, hint);
                return;
            }
        }

        if (stream) {
            activeCameraStream = stream;
            const video = document.getElementById('liveCameraVideo');
            video.srcObject = stream;
            
            video.onloadedmetadata = () => {
                video.play().catch(e => console.log('Autoplay handled:', e));
                applyCameraMirrorState();
                $('#cameraLoadingState').hide();
                $('#cameraVideoWrapper').removeClass('d-none');
            };

            checkMultipleCameras();
        }
    }

    let availableVideoDevices = [];
    let currentDeviceIndex    = 0;
    let isCameraMirrored      = false; // Default: Non-mirror (Normal document view)

    // ===== CAMERA MIRROR STATE HANDLER =====
    function applyCameraMirrorState() {
        const video = document.getElementById('liveCameraVideo');
        if (video) {
            video.style.transform = isCameraMirrored ? 'scaleX(-1)' : 'scaleX(1)';
        }
        if (isCameraMirrored) {
            $('#btnToggleMirrorCamera').addClass('btn-primary text-white').removeClass('btn-outline-light');
        } else {
            $('#btnToggleMirrorCamera').removeClass('btn-primary text-white').addClass('btn-outline-light');
        }
    }

    // Toggle mirror manually if needed
    $('#btnToggleMirrorCamera').on('click', function () {
        isCameraMirrored = !isCameraMirrored;
        applyCameraMirrorState();
    });

    // ===== CHECK FOR MULTIPLE CAMERAS =====
    async function checkMultipleCameras() {
        try {
            const devices = await navigator.mediaDevices.enumerateDevices();
            availableVideoDevices = devices.filter(d => d.kind === 'videoinput');
            if (availableVideoDevices.length > 1) {
                $('#btnSwitchCamera').removeClass('d-none').show();
            } else {
                $('#btnSwitchCamera').hide();
            }
        } catch (e) {
            $('#btnSwitchCamera').hide();
        }
    }

    // ===== SWITCH CAMERA =====
    $('#btnSwitchCamera').on('click', function () {
        if (availableVideoDevices.length > 1) {
            currentDeviceIndex = (currentDeviceIndex + 1) % availableVideoDevices.length;
            const targetDevice = availableVideoDevices[currentDeviceIndex];
            if (targetDevice && targetDevice.deviceId) {
                initCameraStreamWithDeviceId(targetDevice.deviceId);
                return;
            }
        }
        activeFacingMode = (activeFacingMode === 'environment') ? 'user' : 'environment';
        initCameraStream(activeFacingMode);
    });

    // ===== INITIALIZE CAMERA STREAM BY DEVICE ID =====
    async function initCameraStreamWithDeviceId(deviceId) {
        stopCameraStream();

        $('#cameraLoadingState').show();
        $('#cameraErrorState').hide();
        $('#cameraVideoWrapper').addClass('d-none');
        $('#cameraCapturedPreviewWrapper').addClass('d-none');
        $('#cameraLiveControls').removeClass('d-none').addClass('d-flex');
        $('#cameraReviewControls').removeClass('d-flex').addClass('d-none');

        await new Promise(resolve => setTimeout(resolve, 250));

        try {
            const constraints = {
                audio: false,
                video: {
                    deviceId: { exact: deviceId }
                }
            };

            const stream = await navigator.mediaDevices.getUserMedia(constraints);
            activeCameraStream = stream;

            const video = document.getElementById('liveCameraVideo');
            video.srcObject = stream;
            
            video.onloadedmetadata = () => {
                video.play().catch(e => console.log('Autoplay handled:', e));
                applyCameraMirrorState();
                $('#cameraLoadingState').hide();
                $('#cameraVideoWrapper').removeClass('d-none');
            };
        } catch (err) {
            console.warn('Fallback to standard facingMode:', err);
            initCameraStream(activeFacingMode);
        }
    }

    // ===== STOP CAMERA STREAM =====
    function stopCameraStream() {
        if (activeCameraStream) {
            try {
                activeCameraStream.getTracks().forEach(track => {
                    track.stop();
                });
            } catch (e) {
                console.warn('Error stopping stream tracks:', e);
            }
            activeCameraStream = null;
        }
        const video = document.getElementById('liveCameraVideo');
        if (video) {
            video.srcObject = null;
            try { video.load(); } catch (e) {}
        }
    }

    // ===== SHOW CAMERA ERROR STATE =====
    function showCameraError(msg, hint) {
        $('#cameraLoadingState').hide();
        $('#cameraVideoWrapper').addClass('d-none');
        $('#cameraCapturedPreviewWrapper').addClass('d-none');
        $('#cameraErrorMessage').text(msg);
        if (hint) {
            $('#cameraErrorHint').text(hint);
        }
        $('#cameraErrorState').show();
        $('#cameraLiveControls').removeClass('d-flex').addClass('d-none');
    }

    // ===== RETRY CONNECTING CAMERA =====
    $('#btnRetryCamera').on('click', function () {
        initCameraStream(activeFacingMode);
    });

    // ===== NATIVE PHONE CAMERA TRIGGER =====
    $('#btnNativePhoneCamera').on('click', function () {
        $('#modalLiveCameraCapture').modal('hide');
        if (cameraTargetModal === 'create') {
            $('#createLampiranCameraInput').click();
        } else {
            $('#editLampiranCameraInput').click();
        }
    });

    // ===== FALLBACK TO FILE INPUT =====
    $('#btnCameraFallbackFile').on('click', function () {
        $('#modalLiveCameraCapture').modal('hide');
        if (cameraTargetModal === 'create') {
            $('#createLampiran').click();
        } else {
            $('#editLampiran').click();
        }
    });

    // ===== SNAP PHOTO (AMBIL GAMBAR DARI VIDEO STREAM) =====
    $('#btnSnapPhoto').on('click', function () {
        const video  = document.getElementById('liveCameraVideo');
        const canvas = document.getElementById('cameraCapturedCanvas');

        if (!video || !video.videoWidth) {
            Swal.fire({ icon: 'warning', title: 'Kamera Belum Siap', text: 'Tunggu beberapa saat hingga kamera aktif.' });
            return;
        }

        const width  = video.videoWidth;
        const height = video.videoHeight;

        canvas.width  = width;
        canvas.height = height;

        const ctx = canvas.getContext('2d');
        ctx.clearRect(0, 0, width, height);

        // Render with mirror transform if isCameraMirrored, else render 100% normal/non-mirror
        if (isCameraMirrored) {
            ctx.save();
            ctx.translate(width, 0);
            ctx.scale(-1, 1);
            ctx.drawImage(video, 0, 0, width, height);
            ctx.restore();
        } else {
            ctx.drawImage(video, 0, 0, width, height);
        }

        // Switch to Review Mode
        $('#cameraVideoWrapper').addClass('d-none');
        $('#cameraCapturedPreviewWrapper').removeClass('d-none');
        $('#cameraLiveControls').removeClass('d-flex').addClass('d-none');
        $('#cameraReviewControls').removeClass('d-none').addClass('d-flex');
    });

    // ===== RETAKE PHOTO (FOTO ULANG) =====
    $('#btnRetakePhoto').on('click', function () {
        $('#cameraCapturedPreviewWrapper').addClass('d-none');
        $('#cameraVideoWrapper').removeClass('d-none');
        $('#cameraReviewControls').removeClass('d-flex').addClass('d-none');
        $('#cameraLiveControls').removeClass('d-none').addClass('d-flex');
    });

    // ===== USE SNAPPED PHOTO (GUNAKAN FOTO) =====
    $('#btnUseSnappedPhoto').on('click', function () {
        const canvas = document.getElementById('cameraCapturedCanvas');

        // Convert canvas to Blob (WebP 85% quality or JPEG fallback)
        let mimeType = 'image/webp';
        canvas.toBlob(function (blob) {
            if (!blob) {
                mimeType = 'image/jpeg';
                canvas.toBlob(processSnappedBlob, 'image/jpeg', 0.85);
            } else {
                processSnappedBlob(blob);
            }
        }, mimeType, 0.85);

        function processSnappedBlob(blob) {
            const ext = mimeType === 'image/webp' ? 'webp' : 'jpg';
            const now = new Date();
            const dateStr = now.getFullYear() + 
                String(now.getMonth() + 1).padStart(2, '0') + 
                String(now.getDate()).padStart(2, '0') + '_' + 
                String(now.getHours()).padStart(2, '0') + 
                String(now.getMinutes()).padStart(2, '0') + 
                String(now.getSeconds()).padStart(2, '0');
            const fileName = `Bukti_Bayar_${dateStr}.${ext}`;
            const file = new File([blob], fileName, { type: mimeType, lastModified: Date.now() });

            let sizeKb = (blob.size / 1024).toFixed(1);
            let sizeStr = (sizeKb > 1024) ? (sizeKb / 1024).toFixed(2) + ' MB' : sizeKb + ' KB';
            const dataUrl = canvas.toDataURL(mimeType, 0.85);

            if (cameraTargetModal === 'create') {
                if (window.DataTransfer) {
                    try {
                        const dt = new DataTransfer();
                        dt.items.add(file);
                        $('#createLampiran')[0].files = dt.files;
                    } catch (e) {
                        console.log(e);
                    }
                }
                $('#createLampiranFileName').text(fileName);
                $('#createLampiranFileSize').text(sizeStr);
                $('#createLampiranPreviewImg').attr('src', dataUrl);
                $('#createLampiranPreviewWrapper').removeClass('d-none');
                $('#createUploadActions').addClass('d-none');
            } else {
                if (window.DataTransfer) {
                    try {
                        const dt = new DataTransfer();
                        dt.items.add(file);
                        $('#editLampiran')[0].files = dt.files;
                    } catch (e) {
                        console.log(e);
                    }
                }
                $('#editLampiranFileName').text(fileName);
                $('#editLampiranFileSize').text(sizeStr);
                $('#editLampiranPreviewImg').attr('src', dataUrl);
                $('#editLampiranPreviewWrapper').removeClass('d-none');
                $('#editRemoveLampiran').val('0');
                $('#editUploadActions').addClass('d-none');
            }

            $('#modalLiveCameraCapture').modal('hide');
        }
    });

});
</script>
