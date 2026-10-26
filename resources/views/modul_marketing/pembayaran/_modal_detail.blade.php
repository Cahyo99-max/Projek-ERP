<!-- Modal Detail Penerimaan Pembayaran Penjualan -->
<div class="modal fade" id="modalDetailPayment" tabindex="-1" aria-labelledby="modalDetailPaymentLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable" style="max-width: 1400px; width: 95%;">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <!-- Modal Header -->
            <div class="modal-header bg-body-tertiary border-bottom py-3 px-4 d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center gap-3">
                    <span class="p-2 rounded-3 text-white d-inline-flex shadow-sm" style="background: linear-gradient(135deg, #2563eb 0%, #3b82f6 100%);">
                        <i class="bi bi-receipt fs-5"></i>
                    </span>
                    <div>
                        <h5 class="modal-title fw-bold text-body-emphasis mb-0" id="detail_nomor_pembayaran">Rincian Pembayaran</h5>
                        <small class="text-body-secondary" id="detail_tanggal_bayar">-</small>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <span id="detail_status_badge" class="badge rounded-pill bg-success px-3 py-1.5">Posted</span>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
            </div>

            <!-- Modal Body -->
            <div class="modal-body p-4 bg-body">
                <!-- Loading State -->
                <div class="text-center py-5" id="detail_payment_loading">
                    <div class="spinner-border text-primary" role="status" style="width: 2.5rem; height: 2.5rem;">
                        <span class="visually-hidden">Memuat...</span>
                    </div>
                    <p class="text-body-secondary small mt-2 mb-0">Memuat rincian pembayaran...</p>
                </div>

                <!-- Content Container -->
                <div id="detail_payment_content" class="d-none">
                    <!-- Top Action Toolbar -->
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div class="d-flex align-items-center gap-2">
                            <span class="text-body-secondary small"><i class="bi bi-clock-history me-1"></i> Dibuat oleh: <strong id="detail_creator">-</strong></span>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <button type="button" class="btn btn-sm btn-outline-info rounded-pill px-3.5 fw-semibold d-none" id="btn_detail_submit_payment">
                                <i class="bi bi-send me-1"></i> Ajukan Pembayaran
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-warning rounded-pill px-3.5 fw-semibold d-none" id="btn_detail_edit_payment">
                                <i class="bi bi-pencil me-1"></i> Edit Pembayaran
                            </button>
                            <button type="button" class="btn btn-sm btn-success rounded-pill px-3.5 fw-bold shadow-sm d-none" id="btn_detail_post_payment">
                                <i class="bi bi-check2-circle me-1"></i> Konfirmasi & Posting (ACC)
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-info rounded-pill px-3" id="btn_detail_view_jurnal">
                                <i class="bi bi-journal-text me-1"></i> Lihat Jurnal
                            </button>
                            <a href="#" target="_blank" class="btn btn-sm btn-outline-secondary rounded-pill px-3" id="btn_detail_print_kwitansi">
                                <i class="bi bi-printer me-1"></i> Cetak Kwitansi
                            </a>
                            <button type="button" class="btn btn-sm btn-outline-danger rounded-pill px-3 d-none" id="btn_detail_cancel_payment">
                                <i class="bi bi-x-circle me-1"></i> Batalkan Pembayaran (Void)
                            </button>
                        </div>
                    </div>

                    <!-- Information Cards Grid -->
                    <div class="row g-3 mb-4">
                        <!-- Info Customer -->
                        <div class="col-md-4">
                            <div class="card border border-body-tertiary shadow-sm rounded-4 h-100 bg-body-tertiary">
                                <div class="card-body p-3.5">
                                    <div class="d-flex align-items-center gap-2 mb-2 text-primary">
                                        <i class="bi bi-person-fill fs-5"></i>
                                        <span class="fw-bold small text-uppercase font-monospace">Customer / Pelanggan</span>
                                    </div>
                                    <h6 class="fw-bold text-body-emphasis mb-1" id="detail_customer_nama">-</h6>
                                    <small class="text-body-secondary d-block font-monospace" id="detail_customer_kode">-</small>
                                </div>
                            </div>
                        </div>

                        <!-- Info Kas/Bank & Metode -->
                        <div class="col-md-4">
                            <div class="card border border-body-tertiary shadow-sm rounded-4 h-100 bg-body-tertiary">
                                <div class="card-body p-3.5">
                                    <div class="d-flex align-items-center gap-2 mb-2 text-success">
                                        <i class="bi bi-bank fs-5"></i>
                                        <span class="fw-bold small text-uppercase font-monospace">Kas / Bank Tujuan</span>
                                    </div>
                                    <h6 class="fw-bold text-body-emphasis mb-1" id="detail_kas_bank">-</h6>
                                    <small class="text-body-secondary d-block font-monospace" id="detail_rekening">-</small>
                                    <span class="badge bg-secondary-subtle text-secondary mt-1" id="detail_metode_bayar">-</span>
                                </div>
                            </div>
                        </div>

                        <!-- Info Referensi & Lampiran -->
                        <div class="col-md-4">
                            <div class="card border border-body-tertiary shadow-sm rounded-4 h-100 bg-body-tertiary">
                                <div class="card-body p-3.5">
                                    <div class="d-flex align-items-center gap-2 mb-2 text-info">
                                        <i class="bi bi-paperclip fs-5"></i>
                                        <span class="fw-bold small text-uppercase font-monospace">Referensi & Lampiran</span>
                                    </div>
                                    <div class="mb-1">
                                        <small class="text-body-secondary d-block" style="font-size: 0.75rem;">No. Referensi:</small>
                                        <span class="fw-semibold text-body-emphasis font-monospace" id="detail_nomor_referensi">-</span>
                                    </div>
                                    <div id="detail_lampiran_container" class="mt-2">
                                        <!-- Link lampiran -->
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Keterangan Memo -->
                        <div class="col-12" id="detail_keterangan_wrapper">
                            <div class="p-3 rounded-3 bg-body-secondary border border-body-tertiary">
                                <small class="text-body-secondary fw-semibold text-uppercase font-monospace d-block mb-1" style="font-size: 0.725rem;">Catatan / Keterangan</small>
                                <span class="text-body-emphasis small" id="detail_keterangan">-</span>
                            </div>
                        </div>
                    </div>

                    <!-- Audit Trail Section: Pembuat, Pengedit & Approver -->
                    <div class="card border border-body-tertiary shadow-sm rounded-4 mb-4 bg-body-tertiary">
                        <div class="card-header bg-transparent border-bottom py-2.5 px-3">
                            <h6 class="fw-bold mb-0 text-body-emphasis d-flex align-items-center gap-2" style="font-size: 0.85rem;">
                                <i class="bi bi-shield-check text-primary"></i> Jejak Audit & Otorisasi Dokumen (Audit Trail)
                            </h6>
                        </div>
                        <div class="card-body p-3">
                            <div class="row g-3" id="detail_audit_row">
                                <!-- 1. Dibuat Oleh -->
                                <div class="col-md-6 border-end" id="col_audit_creator">
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="p-2 rounded-circle bg-primary-subtle text-primary d-flex align-items-center justify-content-center flex-shrink-0" style="width: 38px; height: 38px;">
                                            <i class="bi bi-person-plus-fill fs-6"></i>
                                        </div>
                                        <div class="overflow-hidden">
                                            <small class="text-body-secondary d-block font-monospace text-uppercase" style="font-size: 0.7rem; letter-spacing: 0.5px;">Dibuat Oleh</small>
                                            <strong class="text-body-emphasis d-block text-truncate" id="detail_audit_creator">-</strong>
                                            <small class="text-muted font-monospace d-block" style="font-size: 0.72rem;" id="detail_audit_created_at">-</small>
                                        </div>
                                    </div>
                                </div>

                                <!-- 2. Terakhir Diedit Oleh (Hanya tampil jika ada riwayat edit) -->
                                <div class="col-md-4 border-end d-none" id="col_audit_updater">
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="p-2 rounded-circle bg-warning-subtle text-warning d-flex align-items-center justify-content-center flex-shrink-0" style="width: 38px; height: 38px;">
                                            <i class="bi bi-pencil-square fs-6"></i>
                                        </div>
                                        <div class="overflow-hidden">
                                            <small class="text-body-secondary d-block font-monospace text-uppercase" style="font-size: 0.7rem; letter-spacing: 0.5px;">Terakhir Diedit</small>
                                            <strong class="text-body-emphasis d-block text-truncate" id="detail_audit_updater">-</strong>
                                            <small class="text-muted font-monospace d-block" style="font-size: 0.72rem;" id="detail_audit_updated_at">-</small>
                                        </div>
                                    </div>
                                </div>

                                <!-- 3. Disetujui / Approval Oleh -->
                                <div class="col-md-6" id="col_audit_approver">
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="p-2 rounded-circle bg-success-subtle text-success d-flex align-items-center justify-content-center flex-shrink-0" id="detail_audit_approver_icon_wrapper" style="width: 38px; height: 38px;">
                                            <i class="bi bi-check-circle-fill fs-6" id="detail_audit_approver_icon"></i>
                                        </div>
                                        <div class="overflow-hidden">
                                            <small class="text-body-secondary d-block font-monospace text-uppercase" style="font-size: 0.7rem; letter-spacing: 0.5px;" id="detail_audit_approver_title">Disetujui / Diposting (ACC)</small>
                                            <strong class="text-body-emphasis d-block text-truncate" id="detail_audit_approver">-</strong>
                                            <small class="text-muted font-monospace d-block" style="font-size: 0.72rem;" id="detail_audit_approved_at">-</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Invoices Settled Table -->
                    <div class="card border border-body-tertiary shadow-sm rounded-4 overflow-hidden mb-4 bg-body-tertiary">
                        <div class="card-header bg-transparent border-bottom py-2.5 px-3">
                            <h6 class="fw-bold mb-0 text-body-emphasis d-flex align-items-center gap-2" style="font-size: 0.9rem;">
                                <i class="bi bi-receipt text-success"></i> Faktur yang Dilunasi
                            </h6>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0" id="table_detail_invoices">
                                <thead class="bg-body-secondary text-body-secondary font-monospace text-uppercase" style="font-size: 0.75rem;">
                                    <tr>
                                        <th class="ps-3 py-2.5" style="width: 5%;">No</th>
                                        <th style="width: 25%;">Nomor Faktur</th>
                                        <th style="width: 17%;" class="text-end">Total Faktur</th>
                                        <th style="width: 17%;" class="text-end">Sisa Piutang (Sebelum)</th>
                                        <th style="width: 18%;" class="text-end text-success">Nominal Dibayar</th>
                                        <th style="width: 18%;" class="text-end pe-3">Sisa Akhir</th>
                                    </tr>
                                </thead>
                                <tbody id="detail_invoices_tbody">
                                    <!-- Dynamic rows -->
                                </tbody>
                                <tfoot class="bg-body-secondary font-monospace fw-bold border-top border-2">
                                    <tr>
                                        <td colspan="4" class="ps-3 py-2 text-uppercase text-body-emphasis">Total Nominal Diterima</td>
                                        <td class="text-end py-2 text-success fs-6" id="detail_total_bayar">Rp 0,00</td>
                                        <td class="text-end pe-3 py-2" id="detail_total_potongan_text">-</td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Modal Footer -->
            <div class="modal-footer bg-body-tertiary border-top py-2.5 px-4 d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center gap-2">
                    <button type="button" class="btn btn-outline-danger rounded-pill px-4 fw-semibold shadow-sm d-none" id="btn_footer_cancel_payment">
                        <i class="bi bi-x-circle me-1"></i> Batalkan Pembayaran (Void)
                    </button>
                    <button type="button" class="btn btn-primary rounded-pill px-4 fw-bold shadow-sm d-none" id="btn_footer_submit_payment">
                        <i class="bi bi-send me-1"></i> Ajukan Pembayaran
                    </button>
                    <button type="button" class="btn btn-success rounded-pill px-4 fw-bold shadow-sm d-none" id="btn_footer_post_payment">
                        <i class="bi bi-check2-circle me-1"></i> Konfirmasi & Posting Pembayaran
                    </button>
                </div>
                <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>
