<!-- Modal Rincian Bukti Transaksi Kas & Bank (Cash Voucher & Audit Trail) -->
<div class="modal modal-blur fade" id="modalDetailMutasi" tabindex="-1" aria-labelledby="modalDetailMutasiTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden kb-modal-container">
            
            <!-- Modal Header (Cinematic Header) -->
            <div class="modal-header px-4 py-3.5 d-flex justify-content-between align-items-center kb-modal-header border-bottom">
                <div class="d-flex align-items-center gap-3">
                    <div class="kb-icon-badge shadow-sm text-white rounded-3 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 44px; height: 44px; background: linear-gradient(135deg, #16a34a 0%, #15803d 100%);">
                        <i class="bi bi-file-earmark-check-fill fs-5"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold mb-0 text-body-emphasis" id="modalDetailMutasiTitle">Bukti Transaksi Kas &amp; Bank</h5>
                        <div class="text-muted small">Voucher pembukuan resmi dan jejak audit mutasi kas.</div>
                    </div>
                </div>
                <button type="button" class="btn-close text-reset shadow-none" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            <!-- Modal Body -->
            <div class="modal-body p-4 kb-modal-body" id="printable_voucher_area">
                
                <!-- Kop Dokumen Transaksi & Entitas -->
                <div class="p-3 rounded-4 border bg-body-tertiary mb-3 d-flex justify-content-between align-items-center flex-wrap gap-3">
                    <div>
                        <div class="d-flex align-items-center gap-2 mb-1.5 flex-wrap">
                            <span class="badge px-3 py-1 rounded-pill font-monospace fw-bold" id="detail_tipe_badge" style="font-size: 0.78rem;">KAS MASUK</span>
                            <span class="badge px-2.5 py-1 rounded-pill font-monospace fw-semibold" id="detail_status_badge" style="font-size: 0.75rem;">Disetujui</span>
                            <span class="fw-bold font-monospace fs-5 text-body-emphasis" id="detail_nomor_transaksi">-</span>
                        </div>
                        <div class="text-muted small font-monospace d-flex align-items-center gap-1.5" id="detail_tanggal_transaksi">
                            <i class="bi bi-calendar-event text-secondary"></i> Tanggal: -
                        </div>
                    </div>
                    <div class="text-sm-end">
                        <div class="small text-muted mb-0.5">Rekening / Buku Kas:</div>
                        <div class="fw-bold text-success fs-6" id="detail_nama_akun">-</div>
                        <div class="text-muted small font-monospace" id="detail_nomor_rekening">-</div>
                    </div>
                </div>

                <!-- Nominal Card Highlight (Glowing Box) -->
                <div class="p-3.5 rounded-4 border mb-3 text-center shadow-sm position-relative overflow-hidden" id="detail_nominal_container" style="background: linear-gradient(135deg, rgba(22, 163, 74, 0.08) 0%, rgba(22, 163, 74, 0.02) 100%); border-color: rgba(22, 163, 74, 0.25) !important;">
                    <div class="text-muted small text-uppercase fw-bold font-monospace mb-1" style="letter-spacing: 0.8px;">Nominal Jumlah Transaksi</div>
                    <h2 class="fw-bold font-monospace mb-0" id="detail_jumlah_display" style="color: #16a34a; font-size: 2rem;">Rp 0,00</h2>
                </div>

                <!-- Grid Data Detail Transaksi -->
                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <div class="p-3 rounded-4 border bg-body-tertiary h-100 shadow-sm">
                            <div class="small text-muted mb-1 font-monospace" style="font-size: 0.72rem;">KATEGORI MUTASI</div>
                            <div class="fw-bold text-body-emphasis mb-3 fs-6" id="detail_kategori">-</div>

                            <div class="small text-muted mb-1 font-monospace" style="font-size: 0.72rem;">PIHAK TERKAIT (KONTAK / PENYETOR)</div>
                            <div class="fw-semibold text-body-emphasis" id="detail_kontak">-</div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="p-3 rounded-4 border bg-body-tertiary h-100 shadow-sm">
                            <div class="small text-muted mb-1 font-monospace" style="font-size: 0.72rem;">NOMOR REFERENSI DOKUMEN</div>
                            <div class="fw-bold font-monospace text-body-emphasis mb-3 fs-6" id="detail_referensi">-</div>

                            <div class="small text-muted mb-1 font-monospace" style="font-size: 0.72rem;">POSTING JURNAL UMUM (COA)</div>
                            <div class="fw-bold font-monospace" id="detail_nomor_jurnal">
                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle font-monospace px-2.5 py-1">
                                    <i class="bi bi-journal-check me-1"></i> Terposting Otomatis
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="col-12">
                        <div class="p-3 rounded-4 border bg-body-tertiary shadow-sm">
                            <div class="small text-muted mb-1 font-monospace" style="font-size: 0.72rem;">KETERANGAN / DESKRIPSI</div>
                            <div class="text-body fst-italic" id="detail_keterangan">-</div>
                        </div>
                    </div>

                    <!-- Info Tender / Proyek (Jika Terhubung) -->
                    <div class="col-12" id="detail_tender_container" style="display: none;">
                        <div class="p-3 rounded-4 border bg-body-tertiary shadow-sm">
                            <div class="small text-muted mb-1 font-monospace" style="font-size: 0.72rem;">TERHUBUNG KE TENDER / PROYEK</div>
                            <div class="d-flex align-items-center gap-2 flex-wrap">
                                <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle font-monospace px-2.5 py-1" style="font-size: 0.78rem;">
                                    <i class="bi bi-briefcase-fill me-1"></i> <span id="detail_tender_kontrak">-</span>
                                </span>
                                <span class="fw-semibold text-body-emphasis" id="detail_tender_nama">-</span>
                                <span class="badge bg-secondary-subtle text-secondary rounded-pill font-monospace" id="detail_tender_entitas" style="font-size: 0.7rem;">-</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Saldo Audit Trail (Sebelum vs Sesudah) -->
                <div class="p-3 rounded-4 border bg-body-tertiary mb-3 shadow-sm">
                    <div class="row text-center g-2 small">
                        <div class="col-6 border-end">
                            <div class="text-muted font-monospace mb-0.5" style="font-size: 0.72rem;">SALDO SEBELUM MUTASI</div>
                            <div class="fw-bold font-monospace text-body fs-6" id="detail_saldo_sebelum">Rp 0,00</div>
                        </div>
                        <div class="col-6">
                            <div class="text-muted font-monospace mb-0.5" style="font-size: 0.72rem;">SALDO SESUDAH MUTASI</div>
                            <div class="fw-bold font-monospace text-success fs-6" id="detail_saldo_sesudah">Rp 0,00</div>
                        </div>
                    </div>
                </div>

                <!-- 📸 BUKTI TRANSAKSI / NOTA / KUITANSI (ANTI-MANIPULASI) -->
                <div class="p-3.5 rounded-4 border bg-body-tertiary mb-3 shadow-sm d-none" id="detail_lampiran_container">
                    <div class="d-flex justify-content-between align-items-center mb-2.5">
                        <div class="d-flex align-items-center gap-2">
                            <i class="bi bi-camera-fill text-success fs-5"></i>
                            <span class="fw-bold text-body-emphasis small font-monospace text-uppercase" style="letter-spacing: 0.5px;">Foto Bukti / Kuitansi / Slip</span>
                        </div>
                        <div class="d-flex align-items-center gap-1.5">
                            <button type="button" id="detail_lampiran_preview_btn" class="btn btn-sm btn-outline-success rounded-pill px-3 py-1 font-monospace" style="font-size: 0.72rem;">
                                <i class="bi bi-arrows-fullscreen me-1"></i> Perbesar Bukti
                            </button>
                            <a href="#" id="detail_lampiran_download_btn" download class="btn btn-sm btn-outline-secondary rounded-pill px-2.5 py-1 font-monospace" style="font-size: 0.72rem;" title="Unduh Berkas">
                                <i class="bi bi-download"></i>
                            </a>
                        </div>
                    </div>
                    <div class="p-2 rounded-3 border bg-body text-center position-relative overflow-hidden" style="max-height: 280px;">
                        <img id="detail_lampiran_preview_img" src="#" alt="Bukti Transaksi" class="img-fluid rounded-2 shadow-sm" style="max-height: 260px; object-fit: contain; cursor: pointer;" title="Klik untuk memperbesar bukti">
                        <div id="detail_lampiran_preview_pdf" class="p-4 d-none" style="cursor: pointer;">
                            <i class="bi bi-file-earmark-pdf text-danger" style="font-size: 3rem;"></i>
                            <div class="fw-bold text-body mt-2">Dokumen Bukti Berformat PDF</div>
                            <div class="text-muted small">Klik tombol 'Perbesar Bukti' untuk membaca dokumen di modal.</div>
                        </div>
                    </div>
                </div>

                <!-- 🏛️ JEJAK AUDIT & HISTORI PENGGUNA (CLEAN AUDIT TIMELINE) -->
                <div class="p-3.5 rounded-4 border shadow-sm bg-body-tertiary">
                    <div class="d-flex justify-content-between align-items-center mb-2.5">
                        <div class="d-flex align-items-center gap-2">
                            <i class="bi bi-shield-check text-success fs-5"></i>
                            <span class="fw-bold text-body-emphasis small font-monospace text-uppercase" style="letter-spacing: 0.5px;">Jejak Audit &amp; Riwayat Operator</span>
                        </div>
                        <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle font-monospace px-2 py-0.5" style="font-size: 0.68rem;">
                            <i class="bi bi-fingerprint me-0.5"></i> Audit Log
                        </span>
                    </div>

                    <div class="row g-2.5">
                        <!-- 1. Dibuat Oleh -->
                        <div class="col-12 col-sm-6">
                            <div class="p-3 rounded-3 border bg-body h-100 shadow-sm">
                                <div class="d-flex align-items-center gap-2.5 mb-1.5">
                                    <div class="rounded-circle p-2 bg-success-subtle text-success d-flex align-items-center justify-content-center flex-shrink-0" style="width: 34px; height: 34px;">
                                        <i class="bi bi-pencil-square fs-6"></i>
                                    </div>
                                    <div class="text-truncate">
                                        <div class="text-muted font-monospace" style="font-size: 0.68rem; letter-spacing: 0.5px;">DIBUAT OLEH (OPERATOR / USER)</div>
                                        <div class="fw-bold text-body text-truncate fs-6" id="detail_audit_creator">-</div>
                                    </div>
                                </div>
                                <div class="text-muted small font-monospace ps-1 d-flex align-items-center gap-1.5" id="detail_audit_created_at" style="font-size: 0.72rem;">
                                    <i class="bi bi-clock-history text-secondary"></i> Waktu: -
                                </div>
                            </div>
                        </div>

                        <!-- 2. Status Transaksi / Dibatalkan Oleh -->
                        <div class="col-12 col-sm-6">
                            <div class="p-3 rounded-3 border bg-body h-100 shadow-sm" id="detail_audit_cancel_box">
                                <div class="d-flex align-items-center gap-2.5 mb-1.5">
                                    <div class="rounded-circle p-2 bg-success-subtle text-success d-flex align-items-center justify-content-center flex-shrink-0" id="detail_audit_cancel_icon" style="width: 34px; height: 34px;">
                                        <i class="bi bi-patch-check-fill fs-6"></i>
                                    </div>
                                    <div class="text-truncate">
                                        <div class="text-muted font-monospace" id="detail_audit_cancel_title" style="font-size: 0.68rem; letter-spacing: 0.5px;">STATUS TRANSAKSI</div>
                                        <div class="fw-bold text-body text-truncate fs-6" id="detail_audit_canceler">Disetujui &amp; Aktif</div>
                                    </div>
                                </div>
                                <div class="text-muted small font-monospace ps-1 d-flex align-items-center gap-1.5" id="detail_audit_cancelled_at" style="font-size: 0.72rem;">
                                    <i class="bi bi-shield-fill-check text-success"></i> Status: Pembukuan Sah
                                </div>
                                <div class="small fst-italic text-danger border-top border-danger-subtle pt-1.5 mt-1.5 d-none" id="detail_audit_alasan_row" style="font-size: 0.74rem;">
                                    <i class="bi bi-chat-left-text me-1"></i><span class="fw-semibold">Alasan:</span> "<span id="detail_audit_alasan_text">-</span>"
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
            
            <!-- Modal Footer -->
            <div class="modal-footer px-4 py-3 d-flex justify-content-between align-items-center kb-modal-footer border-top">
                <span class="text-muted small font-monospace" style="font-size: 0.74rem;">
                    <i class="bi bi-lock me-1"></i> Data diverifikasi otomatis oleh modul akuntansi.
                </span>
                <div class="d-flex align-items-center gap-2">
                    <button type="button" class="btn btn-outline-danger btn-sm rounded-pill px-3 py-1.5 fw-semibold d-none" id="btn_detail_cancel_mutasi" data-id="" data-nomor="" title="Batalkan transaksi kas ini">
                        <i class="bi bi-x-circle me-1"></i> Batalkan Transaksi
                    </button>
                    <button type="button" class="btn btn-secondary btn-sm rounded-pill px-4 py-1.5 fw-semibold" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>

        </div>
    </div>
</div>
