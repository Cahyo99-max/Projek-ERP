{{-- Modal Detail --}}
<div class="modal fade" id="modalDetail" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 20px; overflow: hidden;">
            <div class="modal-header border-0 bg-primary bg-opacity-10 py-4">
                <h5 class="modal-title fw-bold text-primary">
                    <i class="bi bi-person-badge me-2"></i> Detail Karyawan
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4 p-md-5">
                <div class="row align-items-center mb-4">
                    <div class="col-12 col-md-4 text-center mb-4 mb-md-0">
                        <div class="position-relative d-inline-block">
                            <img id="detail_foto" src="" alt="Foto Karyawan" class="rounded-circle img-thumbnail shadow-sm" style="width: 150px; height: 150px; object-fit: cover; border-width: 3px; border-color: var(--tbl-accent);">
                            <div class="position-absolute bottom-0 end-0 mb-1 me-1" id="detail_status">
                                <!-- Status Badge inserted via JS -->
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-md-8">
                        <h3 id="detail_nama_lengkap" class="fw-bold text-dark mb-1 h4"></h3>
                        <p class="text-muted mb-3"><i class="bi bi-building"></i> <span id="detail_departemen"></span> • <span id="detail_jabatan"></span></p>
                        
                        <div class="d-flex flex-wrap gap-2">
                            <span class="badge bg-primary bg-opacity-10 text-primary px-3 py-2 rounded-pill border border-primary border-opacity-25">
                                <i class="bi bi-person-fill me-1"></i> <span id="detail_username"></span>
                            </span>
                            <span class="badge bg-info bg-opacity-10 text-info px-3 py-2 rounded-pill border border-info border-opacity-25">
                                <i class="bi bi-shield-check me-1"></i> <span id="detail_role"></span>
                            </span>
                            <span class="badge bg-warning bg-opacity-10 text-warning px-3 py-2 rounded-pill border border-warning border-opacity-25" id="detail_inisial_wrapper">
                                <i class="bi bi-hash me-1"></i> <span id="detail_inisial"></span>
                            </span>
                        </div>
                    </div>
                </div>
                
                <hr class="text-muted opacity-25 mb-4">

                <div class="row g-4">
                    <div class="col-12 col-sm-6">
                        <div class="d-flex align-items-start">
                            <div class="flex-shrink-0 bg-primary bg-opacity-10 text-primary rounded p-2 me-3">
                                <i class="bi bi-upc-scan fs-5"></i>
                            </div>
                            <div>
                                <p class="text-muted mb-1 small fw-semibold">Kode Karyawan</p>
                                <h6 id="detail_kode_karyawan" class="mb-0 fw-bold"></h6>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-sm-6">
                        <div class="d-flex align-items-start">
                            <div class="flex-shrink-0 bg-primary bg-opacity-10 text-primary rounded p-2 me-3">
                                <i class="bi bi-envelope-at fs-5"></i>
                            </div>
                            <div>
                                <p class="text-muted mb-1 small fw-semibold">Email</p>
                                <h6 id="detail_email" class="mb-0 fw-bold"></h6>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-sm-6">
                        <div class="d-flex align-items-start">
                            <div class="flex-shrink-0 bg-primary bg-opacity-10 text-primary rounded p-2 me-3">
                                <i class="bi bi-telephone fs-5"></i>
                            </div>
                            <div>
                                <p class="text-muted mb-1 small fw-semibold">No. HP / WhatsApp</p>
                                <h6 id="detail_no_hp" class="mb-0 fw-bold"></h6>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-sm-6">
                        <div class="d-flex align-items-start">
                            <div class="flex-shrink-0 bg-primary bg-opacity-10 text-primary rounded p-2 me-3">
                                <i class="bi bi-gender-ambiguous fs-5"></i>
                            </div>
                            <div>
                                <p class="text-muted mb-1 small fw-semibold">Jenis Kelamin</p>
                                <h6 id="detail_jenis_kelamin" class="mb-0 fw-bold"></h6>
                            </div>
                        </div>
                    </div>
                    <div class="col-12" id="detail_companies_wrapper">
                        <div class="d-flex align-items-start">
                            <div class="flex-shrink-0 bg-primary bg-opacity-10 text-primary rounded p-2 me-3">
                                <i class="bi bi-building fs-5"></i>
                            </div>
                            <div class="w-100">
                                <p class="text-muted mb-1 small fw-semibold">Akses Perusahaan (PT) Induk</p>
                                <div id="detail_companies" class="d-flex flex-wrap gap-2 pt-1">
                                    <!-- Badges inserted via JS -->
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-12" id="detail_branches_wrapper">
                        <div class="d-flex align-items-start">
                            <div class="flex-shrink-0 bg-primary bg-opacity-10 text-primary rounded p-2 me-3">
                                <i class="bi bi-buildings fs-5"></i>
                            </div>
                            <div class="w-100">
                                <p class="text-muted mb-1 small fw-semibold">Akses Cabang</p>
                                <div id="detail_branches" class="d-flex flex-wrap gap-2 pt-1">
                                    <!-- Badges will be inserted via JS -->
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-12">
                        <hr class="text-muted opacity-25 mb-4">
                        <div class="d-flex align-items-start">
                            <div class="flex-shrink-0 bg-primary bg-opacity-10 text-primary rounded p-2 me-3">
                                <i class="bi bi-pen fs-5"></i>
                            </div>
                            <div class="w-100">
                                <p class="text-muted mb-2 small fw-semibold">Tanda Tangan Digital</p>
                                <div class="border rounded-3 p-2 bg-light d-flex align-items-center justify-content-center" style="height: 120px; width: 100%; max-width: 300px;">
                                    <img id="detail_signature" src="" alt="Tanda Tangan" style="max-height: 100%; max-width: 100%; object-fit: contain; display: none;">
                                    <span id="detail_signature_empty" class="text-muted small">Belum ada tanda tangan</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-0 bg-light py-3">
                <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>
