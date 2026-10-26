<!-- Modal Rincian Informasi Rekening Kas & Bank -->
<div class="modal modal-blur fade" id="modalDetailAccount" tabindex="-1" aria-labelledby="modalDetailAccountTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden kb-modal-container">
            
            <!-- Modal Header -->
            <div class="modal-header px-4 py-3.5 d-flex justify-content-between align-items-center kb-modal-header border-bottom">
                <div class="d-flex align-items-center gap-3">
                    <div class="kb-icon-badge shadow-sm text-white rounded-3 d-flex align-items-center justify-content-center flex-shrink-0" id="detail_acc_icon_box" style="width: 46px; height: 46px; background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);">
                        <i class="bi bi-bank2 fs-4" id="detail_acc_icon"></i>
                    </div>
                    <div>
                        <div class="d-flex align-items-center gap-2 flex-wrap">
                            <h5 class="modal-title fw-bold mb-0 text-body-emphasis" id="modalDetailAccountTitle">Detail Rekening Kas &amp; Bank</h5>
                            <span class="badge rounded-pill font-monospace px-2.5 py-0.5" id="detail_acc_tipe_badge" style="font-size: 0.72rem;">-</span>
                            <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill font-monospace px-2.5 py-0.5" id="detail_acc_status_badge" style="font-size: 0.72rem;">Aktif</span>
                        </div>
                        <div class="text-muted small mt-0.5">Spesifikasi akun, parameter perbankan, dan integrasi Chart of Accounts (COA).</div>
                    </div>
                </div>
                <button type="button" class="btn-close text-reset shadow-none" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            <!-- Modal Body -->
            <div class="modal-body p-4 kb-modal-body d-flex flex-column gap-3.5">
                
                <!-- Kartu Ringkasan Saldo & Metrik Utama -->
                <div class="p-4 rounded-4 border bg-body-tertiary shadow-sm">
                    <!-- Title & Entitas Row -->
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 pb-3 mb-3 border-bottom">
                        <div class="d-flex align-items-center gap-2.5 flex-wrap">
                            <span class="badge bg-body text-body-emphasis border font-monospace px-3 py-1.5 fs-6 shadow-sm" id="detail_acc_kode">-</span>
                            <h4 class="fw-bold text-body-emphasis mb-0" id="detail_acc_nama">-</h4>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill font-monospace px-3 py-1.5" id="detail_acc_entitas">-</span>
                        </div>
                    </div>

                    <!-- 4 Stat Cards Row dengan Padding Lapang & Elegan -->
                    <div class="row g-3">
                        <div class="col-12 col-sm-6 col-lg-3">
                            <div class="p-3 rounded-3 border bg-body h-100 text-start" style="border-color: rgba(34, 197, 94, 0.28) !important; background: linear-gradient(145deg, rgba(34, 197, 94, 0.06) 0%, rgba(34, 197, 94, 0.01) 100%);">
                                <div class="text-muted small text-uppercase font-monospace d-flex align-items-center gap-1.5 mb-1.5" style="font-size: 0.68rem; letter-spacing: 0.6px;">
                                    <span class="p-1 rounded-circle bg-success d-inline-block"></span> SALDO SAAT INI
                                </div>
                                <div class="fs-5 fw-bold font-monospace text-success text-nowrap" id="detail_acc_saldo_berjalan">Rp 0</div>
                            </div>
                        </div>
                        <div class="col-12 col-sm-6 col-lg-3">
                            <div class="p-3 rounded-3 border bg-body h-100 text-start">
                                <div class="text-muted small text-uppercase font-monospace d-flex align-items-center gap-1.5 mb-1.5" style="font-size: 0.68rem; letter-spacing: 0.6px;">
                                    <span class="p-1 rounded-circle bg-secondary d-inline-block"></span> SALDO AWAL
                                </div>
                                <div class="fs-6 fw-bold font-monospace text-body-emphasis text-nowrap" id="detail_acc_saldo_awal">Rp 0</div>
                            </div>
                        </div>
                        <div class="col-12 col-sm-6 col-lg-3">
                            <div class="p-3 rounded-3 border bg-body h-100 text-start">
                                <div class="text-muted small text-uppercase font-monospace d-flex align-items-center gap-1.5 mb-1.5" style="font-size: 0.68rem; letter-spacing: 0.6px;">
                                    <span class="p-1 rounded-circle bg-success d-inline-block"></span> KAS MASUK
                                </div>
                                <div class="fs-6 fw-bold font-monospace text-success text-nowrap" id="detail_acc_total_masuk">Rp 0</div>
                            </div>
                        </div>
                        <div class="col-12 col-sm-6 col-lg-3">
                            <div class="p-3 rounded-3 border bg-body h-100 text-start">
                                <div class="text-muted small text-uppercase font-monospace d-flex align-items-center gap-1.5 mb-1.5" style="font-size: 0.68rem; letter-spacing: 0.6px;">
                                    <span class="p-1 rounded-circle bg-danger d-inline-block"></span> KAS KELUAR
                                </div>
                                <div class="fs-6 fw-bold font-monospace text-danger text-nowrap" id="detail_acc_total_keluar">Rp 0</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Detail Spesifikasi Akun & COA -->
                <div class="row g-3">
                    <div class="col-12 col-md-6">
                        <div class="p-4 rounded-4 border bg-body-tertiary h-100 shadow-sm">
                            <div class="d-flex align-items-center gap-2 mb-3 pb-2 border-bottom">
                                <div class="p-1.5 rounded-3 bg-primary-subtle text-primary d-flex align-items-center justify-content-center" style="width: 28px; height: 28px;">
                                    <i class="bi bi-bank fs-6"></i>
                                </div>
                                <span class="fw-bold text-uppercase small text-body-emphasis font-monospace" style="letter-spacing: 0.5px;">Data Perbankan &amp; Akun</span>
                            </div>

                            <div class="d-flex flex-column gap-2.5">
                                <div class="d-flex justify-content-between align-items-center py-1.5 border-bottom border-body-secondary border-opacity-25">
                                    <span class="text-muted small">Tipe Akun</span>
                                    <span class="fw-semibold text-body" id="detail_acc_tipe_val">-</span>
                                </div>
                                <div class="d-flex justify-content-between align-items-center py-1.5 border-bottom border-body-secondary border-opacity-25">
                                    <span class="text-muted small">Nama Bank</span>
                                    <span class="fw-semibold text-body" id="detail_acc_nama_bank">-</span>
                                </div>
                                <div class="d-flex justify-content-between align-items-center py-1.5 border-bottom border-body-secondary border-opacity-25">
                                    <span class="text-muted small">Nomor Rekening</span>
                                    <span class="font-monospace fw-bold text-body fs-6" id="detail_acc_no_rek">-</span>
                                </div>
                                <div class="d-flex justify-content-between align-items-center py-1.5">
                                    <span class="text-muted small">Atas Nama</span>
                                    <span class="fw-semibold text-body" id="detail_acc_atas_nama">-</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-12 col-md-6">
                        <div class="p-4 rounded-4 border bg-body-tertiary h-100 shadow-sm">
                            <div class="d-flex align-items-center gap-2 mb-3 pb-2 border-bottom">
                                <div class="p-1.5 rounded-3 bg-success-subtle text-success d-flex align-items-center justify-content-center" style="width: 28px; height: 28px;">
                                    <i class="bi bi-journal-bookmark-fill fs-6"></i>
                                </div>
                                <span class="fw-bold text-uppercase small text-body-emphasis font-monospace" style="letter-spacing: 0.5px;">Integrasi Akuntansi (COA)</span>
                            </div>

                            <div class="mb-3">
                                <div class="text-muted small mb-1.5">Buku Besar / Akun COA Terhubung:</div>
                                <div class="p-2.5 rounded-3 border bg-body" style="border-color: rgba(22, 163, 74, 0.25) !important;">
                                    <span class="font-monospace fw-bold text-success d-flex align-items-center gap-2" id="detail_acc_coa_badge" style="font-size: 0.85rem;">
                                        -
                                    </span>
                                </div>
                            </div>

                            <div class="pt-2 border-top border-body-secondary border-opacity-25">
                                <div class="text-muted small mb-1.5">Catatan / Deskripsi:</div>
                                <div class="small text-body bg-body p-2.5 rounded-3 border fst-italic" style="min-height: 48px;" id="detail_acc_deskripsi">
                                    -
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
            
            <!-- Modal Footer -->
            <div class="modal-footer px-4 py-3 d-flex justify-content-end align-items-center kb-modal-footer border-top">
                <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill px-4 py-2 fw-semibold" data-bs-dismiss="modal">Tutup</button>
            </div>

        </div>
    </div>
</div>
