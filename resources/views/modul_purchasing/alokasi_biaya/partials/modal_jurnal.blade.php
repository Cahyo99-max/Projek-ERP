<!-- Modal Jurnal Akuntansi Alokasi Biaya (Landed Cost) -->
<div class="modal fade" id="modalJurnalAlokasi" tabindex="-1" aria-labelledby="modalJurnalAlokasiLabel" aria-hidden="true" style="z-index: 1065;">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable" style="max-width: 950px;">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden bg-body">
            <!-- Modal Header -->
            <div class="modal-header border-0 py-3 px-4 d-flex justify-content-between align-items-center" 
                 style="background: linear-gradient(135deg, #0f172a 0%, #1e293b 60%, #334155 100%); color: #ffffff;">
                <div class="d-flex align-items-center gap-3">
                    <div class="p-2.5 rounded-3 d-flex align-items-center justify-content-center shadow-sm" 
                         style="background: rgba(255, 255, 255, 0.12); backdrop-filter: blur(8px); border: 1px solid rgba(255, 255, 255, 0.2); width: 44px; height: 44px;">
                        <i class="bi bi-journal-check fs-4 text-info"></i>
                    </div>
                    <div>
                        <div class="d-flex align-items-center gap-2 mb-0.5">
                            <h5 class="modal-title fw-bold text-white mb-0" id="modalJurnalAlokasiLabel">Jurnal Akuntansi Alokasi Biaya</h5>
                            <span class="badge bg-success-subtle text-success border border-success-subtle text-uppercase fw-bold font-monospace px-2 py-0.5" style="font-size: 0.72rem;">POSTED</span>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <span class="small" style="color: rgba(255, 255, 255, 0.75);">No. Voucher Jurnal:</span>
                            <span class="fw-bold font-monospace px-2 py-0.5 rounded-2 bg-dark text-info border border-info-subtle" id="alokasi_jurnal_nomor">-</span>
                        </div>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <a href="#" target="_blank" class="btn btn-outline-light btn-sm rounded-pill px-3 py-1.5 d-inline-flex align-items-center gap-1.5 shadow-sm fw-bold no-loader" id="btn_print_alokasi_jurnal">
                        <i class="bi bi-printer"></i> Cetak Voucher
                    </a>
                    <button type="button" class="btn-close btn-close-white ms-2" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
            </div>

            <!-- Modal Body -->
            <div class="modal-body p-4 bg-body">

                <!-- Loading State -->
                <div class="text-center py-5" id="alokasi_jurnal_loading">
                    <div class="spinner-border text-primary" role="status" style="width: 2.5rem; height: 2.5rem;">
                        <span class="visually-hidden">Memuat jurnal...</span>
                    </div>
                    <p class="text-body-secondary small mt-2 mb-0 fw-semibold">Memuat rincian jurnal akuntansi kapitalisasi HPP...</p>
                </div>

                <div id="alokasi_jurnal_content" class="d-none">

                    <!-- TENDER / PROYEK HIGHLIGHT CARD (JIKA ADA TENDER) -->
                    <div id="alokasi_jurnal_tender_card" class="card border rounded-3 p-3 mb-3 d-none shadow-sm" 
                         style="background: linear-gradient(135deg, rgba(245, 158, 11, 0.08) 0%, rgba(217, 119, 6, 0.12) 100%); border-color: rgba(245, 158, 11, 0.35) !important;">
                        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                            <div class="d-flex align-items-center gap-2.5">
                                <div class="p-2 rounded-3 bg-warning text-dark d-flex align-items-center justify-content-center shadow-sm" style="width: 40px; height: 40px;">
                                    <i class="bi bi-briefcase-fill fs-5"></i>
                                </div>
                                <div>
                                    <div class="d-flex align-items-center gap-2 mb-0.5">
                                        <span class="badge bg-warning text-dark font-monospace text-uppercase fw-bold px-2 py-0.5 rounded-pill" style="font-size: 0.72rem;">
                                            <i class="bi bi-link-45deg me-0.5"></i> Terkait Tender / Proyek
                                        </span>
                                    </div>
                                    <div class="fw-bold text-body-emphasis fs-6" id="alokasi_jurnal_tender_title">-</div>
                                </div>
                            </div>
                            <div class="text-end">
                                <small class="text-body-secondary d-block fw-semibold" style="font-size: 0.75rem;">Customer / Pemberi Kerja</small>
                                <span class="fw-bold text-dark font-monospace" id="alokasi_jurnal_tender_customer">-</span>
                            </div>
                        </div>
                    </div>

                    <!-- Info Header Jurnal Grid -->
                    <div class="card border border-body-tertiary bg-body-tertiary rounded-3 p-3 mb-3 shadow-sm">
                        <div class="row g-3">
                            <div class="col-6 col-md-3">
                                <span class="text-body-secondary small fw-semibold d-block mb-1">
                                    <i class="bi bi-calendar3 text-success me-1"></i> Tanggal Jurnal
                                </span>
                                <div class="font-monospace fw-bold text-body-emphasis" id="alokasi_jurnal_tanggal">-</div>
                            </div>
                            <div class="col-6 col-md-3">
                                <span class="text-body-secondary small fw-semibold d-block mb-1">
                                    <i class="bi bi-building text-primary me-1"></i> Entitas Pembukuan
                                </span>
                                <div id="alokasi_jurnal_entitas_badge">-</div>
                            </div>
                            <div class="col-6 col-md-3">
                                <span class="text-body-secondary small fw-semibold d-block mb-1">
                                    <i class="bi bi-receipt text-info me-1"></i> Ref. Alokasi Biaya
                                </span>
                                <div class="font-monospace fw-bold text-body-emphasis" id="alokasi_jurnal_ref_alokasi">-</div>
                            </div>
                            <div class="col-6 col-md-3">
                                <span class="text-body-secondary small fw-semibold d-block mb-1">
                                    <i class="bi bi-truck text-warning me-1"></i> Ekspedisi / PO
                                </span>
                                <div class="fw-semibold text-body-emphasis text-truncate" id="alokasi_jurnal_vendor">-</div>
                            </div>
                            <div class="col-12 border-top pt-2">
                                <span class="text-body-secondary small fw-semibold d-block mb-1">
                                    <i class="bi bi-card-text text-secondary me-1"></i> Keterangan Transaksi
                                </span>
                                <div class="text-body-emphasis text-break small" id="alokasi_jurnal_keterangan">-</div>
                            </div>
                        </div>
                    </div>

                    <!-- Tabel Baris Jurnal -->
                    <div class="table-responsive rounded-3 border border-body-tertiary mb-3 shadow-sm">
                        <table class="table table-hover align-middle mb-0 w-100">
                            <thead class="bg-body-secondary text-body-secondary small text-uppercase fw-bold">
                                <tr>
                                    <th class="text-center" style="width: 44px;">No</th>
                                    <th>Kode &amp; Nama Akun COA</th>
                                    <th class="text-end" style="width: 175px;">Debit (Rp)</th>
                                    <th class="text-end" style="width: 175px;">Kredit (Rp)</th>
                                    <th style="width: 250px;">Catatan / Keterangan</th>
                                </tr>
                            </thead>
                            <tbody id="alokasi_jurnal_rows_container" class="font-monospace small">
                                <!-- Populated via JS -->
                            </tbody>
                            <tfoot class="bg-body-secondary border-top">
                                <tr>
                                    <td colspan="2" class="text-end fw-bold text-uppercase small text-body-secondary">TOTAL:</td>
                                    <td class="text-end font-monospace fw-bold text-success fs-6" id="alokasi_jurnal_total_debit">Rp 0</td>
                                    <td class="text-end font-monospace fw-bold text-primary fs-6" id="alokasi_jurnal_total_kredit">Rp 0</td>
                                    <td class="text-center">
                                        <div id="alokasi_jurnal_balance_container">
                                            <span class="badge bg-success-subtle text-success border border-success-subtle font-monospace px-3 py-1">
                                                <i class="bi bi-check2-circle me-1"></i>Balance
                                            </span>
                                        </div>
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    <!-- Information footer alert -->
                    <div class="p-2.5 rounded-3 bg-body-tertiary border text-body-secondary small d-flex align-items-center gap-2">
                        <i class="bi bi-shield-check text-success fs-5"></i>
                        <div>
                            Jurnal ini dibukukan otomatis oleh sistem saat dokumen Alokasi Biaya berstatus <b>Posted</b> untuk mengkapitalisasi biaya freight/ekspedisi ke nilai persediaan barang (HPP).
                        </div>
                    </div>

                </div>
            </div>

            <!-- Modal Footer -->
            <div class="modal-footer bg-body-tertiary border-top py-2.5 px-4 d-flex justify-content-between">
                <span class="text-body-secondary small"><i class="bi bi-check-all text-success me-1"></i> Terintegrasi Buku Besar Akuntansi</span>
                <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>
