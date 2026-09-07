<!-- Modal View / Detail Pembayaran Utang -->
<div class="modal fade" id="modalViewPayment" tabindex="-1" aria-labelledby="modalViewPaymentLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-payment-wide modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            
            <!-- Modal Header -->
            <div class="modal-header pay-modal-header text-white d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center gap-2">
                    <div class="rounded-circle bg-info bg-opacity-25 p-2 d-flex align-items-center justify-content-center" style="width: 2.25rem; height: 2.25rem;">
                        <i class="bi bi-file-earmark-text text-info fs-5"></i>
                    </div>
                    <div>
                        <h5 class="modal-title mb-0 fw-bold" id="modalViewPaymentLabel">Detail Pembayaran Utang</h5>
                        <div class="text-subtitle" id="viewModalSubtitle">Voucher Bukti Pengeluaran Kas &amp; Alokasi Faktur AP</div>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <span id="viewStatusBadge"></span>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
            </div>

            <!-- Modal Body -->
            <div class="modal-body pay-modal-body p-4">
                
                <!-- Notice If Cancelled -->
                <div class="alert alert-danger border-danger align-items-center gap-2 mb-4 d-none" id="viewCancelledAlert">
                    <i class="bi bi-exclamation-triangle-fill fs-5"></i>
                    <div>
                        <strong>Pembayaran Ini Telah Dibatalkan / Void</strong>
                        <div class="small" id="viewCancelledReason"></div>
                    </div>
                </div>

                <!-- 1. Header Information Card -->
                <div class="card border-0 shadow-sm rounded-3 p-3 mb-4">
                    <div class="row g-3">
                        <div class="col-12 col-md-3">
                            <div class="text-muted small text-uppercase fw-bold">Nomor Pembayaran</div>
                            <div class="fs-6 fw-bold text-primary" id="viewNomorPembayaran">-</div>
                            <div class="small text-muted" id="viewNomorReferensi"></div>
                        </div>

                        <div class="col-12 col-md-3">
                            <div class="text-muted small text-uppercase fw-bold">Tanggal Pembayaran</div>
                            <div class="fs-6 fw-semibold" id="viewTanggalBayar">-</div>
                            <div class="small text-muted" id="viewMetodeBayar">-</div>
                        </div>

                        <div class="col-12 col-md-3">
                            <div class="text-muted small text-uppercase fw-bold">Supplier</div>
                            <div class="fs-6 fw-bold text-primary" id="viewSupplierNama">-</div>
                            <div class="small text-muted" id="viewSupplierKode">-</div>
                        </div>

                        <div class="col-12 col-md-3">
                            <div class="text-muted small text-uppercase fw-bold">Sumber Kas / Bank</div>
                            <div class="fs-6 fw-bold" id="viewKasBankNama">-</div>
                            <div class="small text-muted" id="viewKasBankCoa">-</div>
                        </div>
                    </div>
                </div>

                <!-- 2. Allocated Invoices Table -->
                <div class="card border-0 shadow-sm rounded-3 p-3 mb-4">
                    <h6 class="fw-bold mb-3 text-uppercase" style="font-size: 0.8rem; letter-spacing: 0.05em;">
                        <i class="bi bi-receipt-cutoff me-1 text-primary"></i> Daftar Faktur Pembelian yang Dibayar
                    </h6>

                    <div class="table-responsive">
                        <table class="table table-bordered table-hover align-middle mb-0" style="font-size: 0.85rem;">
                            <thead>
                                <tr>
                                    <th class="text-center" style="width: 40px;">#</th>
                                    <th>No. Faktur</th>
                                    <th>Tgl Faktur</th>
                                    <th>Jatuh Tempo</th>
                                    <th class="text-end">Total Faktur</th>
                                    <th class="text-end">Sisa Sblm Bayar</th>
                                    <th class="text-end text-primary">Jumlah Bayar</th>
                                    <th class="text-end text-danger">Potongan</th>
                                    <th class="text-end">Sisa Akhir</th>
                                    <th>Catatan</th>
                                </tr>
                            </thead>
                            <tbody id="viewInvoicesTbody">
                                <!-- Populated via AJAX -->
                            </tbody>
                            <tfoot class="fw-bold">
                                <tr>
                                    <td colspan="6" class="text-end">TOTAL ALOKASI:</td>
                                    <td class="text-end text-primary" id="viewTotalBayar">Rp 0</td>
                                    <td class="text-end text-danger" id="viewTotalPotongan">Rp 0</td>
                                    <td class="text-end" id="viewTotalSisaAkhir">Rp 0</td>
                                    <td></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>

                <!-- 3. Accounting & Audit Trail Section -->
                <div class="row g-3">
                    
                    <!-- Keterangan & Jurnal Info -->
                    <div class="col-12 col-lg-7">
                        <div class="card border-0 shadow-sm rounded-3 p-3 h-100">
                            <h6 class="fw-bold mb-2 text-uppercase" style="font-size: 0.8rem; letter-spacing: 0.05em;">
                                <i class="bi bi-journal-text me-1 text-primary"></i> Catatan &amp; Jurnal Akuntansi
                            </h6>

                            <div class="mb-3">
                                <label class="text-muted small">Keterangan:</label>
                                <div class="p-2 rounded border small" id="viewKeterangan" style="background: rgba(37,99,235,0.04);">-</div>
                            </div>

                            <!-- Bukti Transfer / Lampiran Foto -->
                            <div class="mb-3 d-none" id="viewLampiranWrapper">
                                <label class="text-muted small fw-bold text-uppercase mb-1">
                                    <i class="bi bi-image text-primary me-1"></i>Bukti Transfer / Lampiran Foto:
                                </label>
                                <div class="p-2 rounded border bg-body-tertiary d-flex align-items-center justify-content-between gap-3">
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="d-inline-block position-relative group" style="cursor: pointer;" id="viewLampiranClickContainer" title="Klik untuk memperbesar foto">
                                            <img id="viewLampiranImg" src="" alt="Bukti Transfer" class="rounded border shadow-sm bg-white" style="width: 75px; height: 75px; object-fit: cover; cursor: pointer;">
                                            <div class="position-absolute bottom-0 end-0 bg-dark bg-opacity-75 text-white rounded-circle p-1 d-flex align-items-center justify-content-center m-1 shadow-sm" style="width: 22px; height: 22px; font-size: 0.65rem;">
                                                <i class="bi bi-arrows-fullscreen"></i>
                                            </div>
                                        </div>
                                        <div>
                                            <div class="fw-bold small text-dark">Bukti Pembayaran / Transfer</div>
                                            <small class="text-muted d-block" style="font-size: 0.75rem;">Klik gambar untuk memperbesar foto</small>
                                            <button type="button" id="viewLampiranModalBtn" class="btn btn-xs btn-outline-primary rounded-pill px-2.5 py-0.5 mt-1" style="font-size: 0.72rem;">
                                                <i class="bi bi-eye me-1"></i> Lihat Foto Penuh
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="d-none" id="viewJurnalWrapper">
                                <div class="d-flex align-items-center justify-content-between mb-2">
                                    <span class="text-muted small fw-bold"><i class="bi bi-journal-check text-primary me-1"></i>Jurnal Posting Asli:</span>
                                    <span class="badge bg-primary text-white" id="viewNomorJurnal">-</span>
                                </div>
                                <div class="table-responsive">
                                    <table class="table table-sm table-bordered mb-0" style="font-size: 0.78rem;">
                                        <thead>
                                            <tr>
                                                <th>Akun COA</th>
                                                <th class="text-end">Debit</th>
                                                <th class="text-end">Kredit</th>
                                            </tr>
                                        </thead>
                                        <tbody id="viewJurnalTbody">
                                            <!-- Journal lines populated via AJAX -->
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <!-- Jurnal Pembalik (Reversal) jika transaksi Dibatalkan -->
                            <div class="d-none mt-3 pt-3 border-top" id="viewReversalJurnalWrapper">
                                <div class="d-flex align-items-center justify-content-between mb-2">
                                    <span class="text-danger small fw-bold"><i class="bi bi-arrow-counterclockwise text-danger me-1"></i>Jurnal Pembalik (Reversal):</span>
                                    <span class="badge bg-danger text-white" id="viewNomorReversalJurnal">-</span>
                                </div>
                                <div class="table-responsive">
                                    <table class="table table-sm table-bordered mb-0" style="font-size: 0.78rem;">
                                        <thead>
                                            <tr class="table-danger border-danger-subtle">
                                                <th>Akun COA (Pembalikan)</th>
                                                <th class="text-end">Debit</th>
                                                <th class="text-end">Kredit</th>
                                            </tr>
                                        </thead>
                                        <tbody id="viewReversalJurnalTbody">
                                            <!-- Reversal Journal lines populated via AJAX -->
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <div id="viewJurnalDraftNotice" class="alert alert-info py-2 px-3 small border-info align-items-center gap-2 mb-0 d-none">
                                <i class="bi bi-info-circle-fill fs-5 text-info"></i>
                                <span>Status pembayaran ini masih <strong>Draft</strong>. Jurnal akuntansi dan mutasi kas akan diterbitkan otomatis saat pembayaran <strong>Diposting</strong>.</span>
                            </div>
                        </div>
                    </div>

                    <!-- Audit Trail Card (Modern Enterprise Timeline) -->
                    <div class="col-12 col-lg-5">
                        <div class="card border-0 shadow-sm rounded-3 p-3 h-100 d-flex flex-column justify-content-between">
                            <div>
                                <div class="d-flex align-items-center justify-content-between mb-3 pb-2 border-bottom">
                                    <h6 class="fw-bold mb-0 text-uppercase text-body-emphasis" style="font-size: 0.8rem; letter-spacing: 0.05em;">
                                        <i class="bi bi-clock-history me-1.5 text-primary"></i> Jejak Audit (Audit Trail)
                                    </h6>
                                    <span class="badge bg-body-tertiary text-muted font-monospace border" style="font-size: 0.68rem;">Log Terverifikasi</span>
                                </div>

                                <div class="pay-audit-timeline">
                                    <div class="pay-audit-line"></div>

                                    <!-- 1. Dibuat Oleh (Draft) -->
                                    <div class="pay-audit-item mb-3" id="viewCreatedItem">
                                        <div class="pay-audit-dot pay-dot-primary shadow-sm" title="Pembuatan Draf">
                                            <i class="bi bi-person-fill"></i>
                                        </div>
                                        <div class="pay-audit-card p-2.5 rounded-3 border">
                                            <div class="d-flex justify-content-between align-items-center mb-1">
                                                <span class="pay-audit-role text-primary text-uppercase fw-bold"><i class="bi bi-file-earmark-plus me-1"></i>Dibuat</span>
                                                <span class="pay-audit-time font-monospace text-muted" id="viewCreatedAt">-</span>
                                            </div>
                                            <div class="d-flex align-items-center gap-2">
                                                <div class="pay-audit-avatar bg-primary-subtle text-primary fw-bold" id="viewCreatedAvatar">CP</div>
                                                <div class="overflow-hidden">
                                                    <div class="pay-audit-name fw-bold text-truncate" id="viewCreatedBy">-</div>
                                                    <div class="pay-audit-sub text-muted text-truncate">Pembuat Transaksi</div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- 2. Diajukan Oleh (Submit for Approval) -->
                                    <div class="pay-audit-item mb-3 d-none" id="viewSubmittedItem">
                                        <div class="pay-audit-dot pay-dot-primary shadow-sm" title="Diajukan untuk Approval">
                                            <i class="bi bi-send-fill"></i>
                                        </div>
                                        <div class="pay-audit-card p-2.5 rounded-3 border">
                                            <div class="d-flex justify-content-between align-items-center mb-1">
                                                <span class="pay-audit-role text-primary text-uppercase fw-bold"><i class="bi bi-clock-history me-1"></i>Diajukan untuk Approval</span>
                                                <span class="pay-audit-time font-monospace text-muted" id="viewSubmittedAt">-</span>
                                            </div>
                                            <div class="d-flex align-items-center gap-2">
                                                <div class="pay-audit-avatar bg-primary-subtle text-primary fw-bold" id="viewSubmittedAvatar">CP</div>
                                                <div class="overflow-hidden">
                                                    <div class="pay-audit-name fw-bold text-truncate" id="viewSubmittedBy">-</div>
                                                    <div class="pay-audit-sub text-muted text-truncate">Pengaju Transaksi</div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- 3. Ditolak Oleh (Rejected) -->
                                    <div class="pay-audit-item mb-3 d-none" id="viewRejectedItem">
                                        <div class="pay-audit-dot pay-dot-danger shadow-sm" title="Pengajuan Ditolak">
                                            <i class="bi bi-x-circle-fill"></i>
                                        </div>
                                        <div class="pay-audit-card pay-audit-card-danger p-2.5 rounded-3 border">
                                            <div class="d-flex justify-content-between align-items-center mb-1">
                                                <span class="pay-audit-role text-danger text-uppercase fw-bold"><i class="bi bi-x-circle me-1"></i>Pengajuan Ditolak</span>
                                                <span class="pay-audit-time font-monospace text-danger opacity-75" id="viewRejectedAt">-</span>
                                            </div>
                                            <div class="d-flex align-items-center gap-2 mb-1.5">
                                                <div class="pay-audit-avatar bg-danger text-white fw-bold" id="viewRejectedAvatar">CP</div>
                                                <div class="overflow-hidden">
                                                    <div class="pay-audit-name fw-bold text-danger text-truncate" id="viewRejectedBy">-</div>
                                                    <div class="pay-audit-sub text-danger opacity-75 text-truncate">Penolak Pengajuan</div>
                                                </div>
                                            </div>
                                            <div class="p-1.5 rounded border border-danger-subtle bg-danger bg-opacity-10 text-danger" style="font-size: 0.73rem;">
                                                <strong>Alasan:</strong> <span id="viewRejectedReasonAudit" class="fst-italic">-</span>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- 4. Disetujui / Diposting Oleh (Approver) -->
                                    <div class="pay-audit-item mb-3 d-none" id="viewPostedItem">
                                        <div class="pay-audit-dot pay-dot-success shadow-sm" title="Disetujui &amp; Diposting">
                                            <i class="bi bi-shield-check"></i>
                                        </div>
                                        <div class="pay-audit-card p-2.5 rounded-3 border">
                                            <div class="d-flex justify-content-between align-items-center mb-1">
                                                <span class="pay-audit-role text-success text-uppercase fw-bold"><i class="bi bi-check-circle-fill me-1"></i>Disetujui &amp; Diposting</span>
                                                <span class="pay-audit-time font-monospace text-muted" id="viewPostedAt">-</span>
                                            </div>
                                            <div class="d-flex align-items-center gap-2">
                                                <div class="pay-audit-avatar bg-success-subtle text-success fw-bold" id="viewPostedAvatar">CP</div>
                                                <div class="overflow-hidden">
                                                    <div class="pay-audit-name fw-bold text-truncate" id="viewPostedBy">-</div>
                                                    <div class="pay-audit-sub text-muted text-truncate" id="viewPostedSubtitle">Otorisator Pembayaran Kas</div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- 5. Terakhir Diperbarui (jika pernah diedit) -->
                                    <div class="pay-audit-item mb-3 d-none" id="viewUpdatedItem">
                                        <div class="pay-audit-dot pay-dot-warning shadow-sm" title="Pembaruan Terakhir">
                                            <i class="bi bi-pencil-fill"></i>
                                        </div>
                                        <div class="pay-audit-card p-2.5 rounded-3 border">
                                            <div class="d-flex justify-content-between align-items-center mb-1">
                                                <span class="pay-audit-role text-warning text-uppercase fw-bold"><i class="bi bi-pencil-square me-1"></i>Terakhir Diubah</span>
                                                <span class="pay-audit-time font-monospace text-muted" id="viewUpdatedAt">-</span>
                                            </div>
                                            <div class="d-flex align-items-center gap-2">
                                                <div class="pay-audit-avatar bg-warning-subtle text-warning fw-bold" id="viewUpdatedAvatar">CP</div>
                                                <div class="overflow-hidden">
                                                    <div class="pay-audit-name fw-bold text-truncate" id="viewUpdatedBy">-</div>
                                                    <div class="pay-audit-sub text-muted text-truncate">Editor Terakhir</div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- 6. Dibatalkan Oleh (jika status Cancelled) -->
                                    <div class="pay-audit-item mb-1 d-none" id="viewCancelledItem">
                                        <div class="pay-audit-dot pay-dot-danger shadow-sm" title="Dibatalkan / Void">
                                            <i class="bi bi-x-circle-fill"></i>
                                        </div>
                                        <div class="pay-audit-card pay-audit-card-danger p-2.5 rounded-3 border">
                                            <div class="d-flex justify-content-between align-items-center mb-1">
                                                <span class="pay-audit-role text-danger text-uppercase fw-bold"><i class="bi bi-x-octagon-fill me-1"></i>Dibatalkan / Void</span>
                                                <span class="pay-audit-time font-monospace text-danger opacity-75" id="viewCancelledAt">-</span>
                                            </div>
                                            <div class="d-flex align-items-center gap-2 mb-1.5">
                                                <div class="pay-audit-avatar bg-danger text-white fw-bold" id="viewCancelledAvatar">CP</div>
                                                <div class="overflow-hidden">
                                                    <div class="pay-audit-name fw-bold text-danger text-truncate" id="viewCancelledBy">-</div>
                                                    <div class="pay-audit-sub text-danger opacity-75 text-truncate">Pembatal Transaksi</div>
                                                </div>
                                            </div>
                                            <div class="p-1.5 rounded border border-danger-subtle bg-danger bg-opacity-10 text-danger" style="font-size: 0.73rem;">
                                                <strong>Alasan:</strong> <span id="viewCancelledReasonAudit" class="fst-italic">-</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="calc-grand-total mt-3">
                                <div class="label">Total Pengeluaran Kas / Bank</div>
                                <div class="value" id="viewSummaryGrandTotal">Rp 0</div>
                            </div>
                        </div>
                    </div>

                </div>

            </div>

            <!-- Modal Footer -->
            <div class="modal-footer border-top px-4 py-3 d-flex align-items-center justify-content-between">
                <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill px-3" data-bs-dismiss="modal">
                    <i class="bi bi-x-lg me-1"></i> Tutup
                </button>
                <div class="d-flex align-items-center gap-2">
                    @can('cetak-pembayaran-utang')
                    <a href="#" target="_blank" class="btn btn-outline-primary btn-sm rounded-pill px-3 shadow-sm" id="btnViewPrintVoucher">
                        <i class="bi bi-printer me-1"></i> Cetak Bukti Bayar
                    </a>
                    @endcan
                </div>
            </div>

        </div>
    </div>
</div>
