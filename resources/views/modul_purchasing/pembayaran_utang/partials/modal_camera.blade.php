<!-- Modal Live Camera Capture (Webcam & HP Camera) -->
<div class="modal fade" id="modalLiveCameraCapture" tabindex="-1" aria-labelledby="modalLiveCameraCaptureLabel" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden bg-dark text-white">
            
            <!-- Header -->
            <div class="modal-header border-bottom border-secondary px-4 py-3 bg-black d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center gap-2.5">
                    <div class="rounded-circle bg-primary bg-opacity-25 p-2 d-flex align-items-center justify-content-center" style="width: 2.35rem; height: 2.35rem;">
                        <i class="bi bi-camera-fill text-primary fs-5"></i>
                    </div>
                    <div>
                        <h6 class="modal-title mb-0 fw-bold text-white" id="modalLiveCameraCaptureLabel">Pengambilan Foto Bukti Transaksi</h6>
                        <small class="text-secondary" style="font-size: 0.75rem;">Posisikan dokumen bukti transfer atau nota di dalam area bingkai</small>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <button type="button" class="btn btn-sm btn-outline-light rounded-circle d-flex align-items-center justify-content-center p-0 shadow-sm" id="btnToggleMirrorCamera" title="Balik Cermin / Normal (Non-Mirror)" style="width: 38px; height: 38px;">
                        <i class="bi bi-symmetry-vertical fs-5"></i>
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-light rounded-circle d-flex align-items-center justify-content-center p-0 shadow-sm" id="btnSwitchCamera" title="Alihkan Lensa Kamera (Depan / Belakang)" style="width: 38px; height: 38px; display: none;">
                        <i class="bi bi-arrow-repeat fs-5"></i>
                    </button>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close" id="btnCloseCameraModal"></button>
                </div>
            </div>

            <!-- Body / Camera Viewfinder -->
            <div class="modal-body p-0 position-relative bg-black d-flex flex-column align-items-center justify-content-center" style="min-height: 380px; max-height: 75vh; overflow: hidden;">
                
                <!-- Loading / Permission State -->
                <div id="cameraLoadingState" class="text-center py-5">
                    <div class="spinner-border text-primary mb-3" role="status" style="width: 3rem; height: 3rem;"></div>
                    <div class="fw-semibold fs-6">Menghubungkan ke Perangkat Kamera...</div>
                    <small class="text-secondary d-block mt-1">Pastikan izin akses kamera telah diizinkan pada peramban (browser) Anda.</small>
                </div>

                <!-- Error / Fallback State -->
                <div id="cameraErrorState" class="text-center p-4" style="display: none;">
                    <i class="bi bi-camera-video-off fs-1 text-danger d-block mb-3"></i>
                    <div class="fw-bold text-white fs-6 mb-1" id="cameraErrorMessage">Tidak dapat mengakses kamera perangkat.</div>
                    <small class="text-secondary d-block mb-3" id="cameraErrorHint">Kamera mungkin sedang digunakan aplikasi lain (Zoom/OBS/DroidCam) atau tidak terpasang.</small>
                    <div class="d-flex align-items-center justify-content-center gap-2 flex-wrap">
                        <button type="button" class="btn btn-outline-light btn-sm rounded-pill px-3" id="btnRetryCamera">
                            <i class="bi bi-arrow-repeat me-1"></i> Hubungkan Ulang
                        </button>
                        <button type="button" class="btn btn-warning btn-sm rounded-pill px-3 text-dark fw-semibold" id="btnNativePhoneCamera">
                            <i class="bi bi-camera-fill me-1"></i> Buka Kamera Asli HP
                        </button>
                        <button type="button" class="btn btn-primary btn-sm rounded-pill px-3" id="btnCameraFallbackFile">
                            <i class="bi bi-folder2-open me-1"></i> Pilih dari Berkas / Galeri
                        </button>
                    </div>
                </div>

                <!-- Live Video Stream -->
                <div id="cameraVideoWrapper" class="position-relative w-100 h-100 d-none" style="background: #000;">
                    <video id="liveCameraVideo" autoplay playsinline muted class="w-100 h-100" style="object-fit: contain; max-height: 60vh; transform: scaleX(1); transition: transform 0.2s ease;"></video>
                    
                    <!-- Guide Frame Overlay -->
                    <div class="position-absolute top-50 start-50 translate-middle border border-2 border-primary rounded-3 pointer-events-none" style="width: 85%; height: 75%; box-shadow: 0 0 0 9999px rgba(0,0,0,0.45); pointer-events: none;">
                        <div class="position-absolute top-0 start-0 p-2 text-primary small fw-bold"><i class="bi bi-crop me-1"></i>Area Bingkai Dokumen Bukti</div>
                    </div>
                </div>

                <!-- Captured Canvas Preview -->
                <div id="cameraCapturedPreviewWrapper" class="position-relative w-100 h-100 d-none text-center p-2" style="background: #000;">
                    <canvas id="cameraCapturedCanvas" class="rounded border border-secondary shadow-lg" style="max-width: 100%; max-height: 60vh; object-fit: contain;"></canvas>
                </div>

            </div>

            <!-- Footer / Capture Action Controls -->
            <div class="modal-footer border-top border-secondary px-4 py-3 bg-black d-flex flex-column-reverse flex-sm-row align-items-stretch align-items-sm-center justify-content-sm-between gap-2">
                <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill px-3 py-2 text-light" data-bs-dismiss="modal">
                    Batal
                </button>
                
                <!-- Live Capture Buttons -->
                <div id="cameraLiveControls" class="d-flex align-items-center justify-content-center">
                    <button type="button" class="btn btn-primary btn-lg rounded-pill px-4 py-2.5 fw-bold shadow w-100 w-sm-auto d-inline-flex align-items-center justify-content-center gap-2" id="btnSnapPhoto">
                        <i class="bi bi-camera-fill fs-5"></i>
                        <span>Ambil Gambar</span>
                    </button>
                </div>

                <!-- Review Snapped Photo Buttons -->
                <div id="cameraReviewControls" class="d-none align-items-center justify-content-center gap-2 flex-wrap">
                    <button type="button" class="btn btn-outline-warning btn-sm rounded-pill px-3 py-2 text-white fw-semibold" id="btnRetakePhoto">
                        <i class="bi bi-arrow-repeat me-1"></i> Ambil Ulang
                    </button>
                    <button type="button" class="btn btn-success btn-sm rounded-pill px-4 py-2 text-white fw-bold shadow-sm" id="btnUseSnappedPhoto">
                        <i class="bi bi-check2-circle me-1"></i> Terapkan Dokumen Ini
                    </button>
                </div>
            </div>

        </div>
    </div>
</div>
