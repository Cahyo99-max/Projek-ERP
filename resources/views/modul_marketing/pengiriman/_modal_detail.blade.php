<!-- Modal Detail Surat Jalan / Delivery Order (Cinematic View) -->
<div class="modal modal-blur fade" id="modalDetailDO" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-xl">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            
            <!-- Modal Header -->
            <div class="modal-header border-bottom px-4 py-3 d-flex justify-content-between align-items-center" style="background: rgba(37, 99, 235, 0.04);">
                <div class="d-flex align-items-center gap-3">
                    <div class="p-2 rounded-3 text-white d-inline-flex align-items-center justify-content-center shadow-sm" style="background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);">
                        <i class="bi bi-truck fs-5"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold mb-0">Rincian Surat Jalan &amp; Pengiriman</h5>
                        <div class="text-muted small">Informasi pengeluaran fisik barang dan pelacakan ekspedisi/kurir.</div>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-2 flex-wrap">
                    @can('lihat-jurnal-delivery-order')
                    <button type="button" class="btn btn-outline-success btn-sm rounded-pill px-3 py-1.5 fw-bold d-none align-items-center gap-1.5 shadow-sm" id="btn_modal_lihat_jurnal">
                        <i class="bi bi-journal-bookmark-fill"></i> <span class="d-none d-sm-inline">Lihat Jurnal Akuntansi</span><span class="d-inline d-sm-none">Jurnal</span>
                    </button>
                    @endcan
                   
                    <button type="button" class="btn-close text-reset" data-bs-dismiss="modal"></button>
                </div>
            </div>
            
            <div class="modal-body p-4" id="printable_do_area">
                


                <!-- Nomor DO & Header Meta Bar -->
                <div class="p-3.5 px-4 rounded-4 mb-4 do-detail-header-bar border d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3">
                    <div class="d-flex align-items-center gap-2.5 flex-wrap">
                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle font-monospace px-3 py-1.5 rounded-pill fw-bold" id="detail_do_nomor" style="font-size: 0.88rem;">
                            Nomor DO: -
                        </span>
                        <span class="badge bg-body border text-body-secondary font-monospace px-3 py-1.5 rounded-pill small" id="detail_do_tanggal">
                            <i class="bi bi-calendar3 me-1.5 text-primary"></i> Tgl Kirim: -
                        </span>
                    </div>
                    <div class="d-flex align-items-center gap-2.5 flex-wrap">
                        <span class="badge bg-body border text-body px-3 py-1.5 rounded-pill small font-monospace d-flex align-items-center gap-1.5">
                            <i class="bi bi-shop text-muted"></i> Gudang: <strong id="detail_do_gudang" class="text-body-emphasis ms-0.5">-</strong>
                        </span>
                        <div id="detail_do_status_badge"></div>
                    </div>
                </div>

                <!-- Info Grid: Tujuan & Ekspedisi -->
                <div class="row g-3.5 mb-4">
                    <!-- Kartu Informasi Penerima -->
                    <div class="col-lg-6">
                        <div class="card border-0 rounded-4 h-100 shadow-sm do-detail-card overflow-hidden">
                            <div class="card-header bg-transparent py-3 px-4 border-bottom d-flex align-items-center justify-content-between">
                                <div class="fw-bold text-body-emphasis small text-uppercase d-flex align-items-center gap-2">
                                    <span class="do-mini-badge"><i class="bi bi-person-fill"></i></span>
                                    <span>Tujuan &amp; Informasi Penerima</span>
                                </div>
                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill small font-monospace px-2.5 py-0.5" style="font-size: 0.68rem;">DESTINASI</span>
                            </div>
                            <div class="card-body p-3.5 p-md-4 d-flex flex-column gap-3">
                                
                                <!-- No Pesanan (SO) & No. Tender Row -->
                                <div class="row g-2.5">
                                    <div class="col">
                                        <div class="do-detail-field-box h-100">
                                            <div class="do-detail-label">
                                                <i class="bi bi-receipt text-primary"></i> No. Pesanan (SO)
                                            </div>
                                            <div class="do-detail-value fw-bold text-primary font-monospace" id="detail_do_nomor_so">-</div>
                                        </div>
                                    </div>
                                    <div class="col-sm-6" id="detail_do_tender_row" style="display: none;">
                                        <div class="do-detail-field-box h-100">
                                            <div class="do-detail-label">
                                                <i class="bi bi-file-earmark-text text-info"></i> No. Tender
                                            </div>
                                            <div class="do-detail-value font-monospace" id="detail_do_tender_no">-</div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Nama Instansi / Penerima -->
                                <div class="do-detail-field-box">
                                    <div class="do-detail-label">
                                        <i class="bi bi-building text-primary"></i> Nama Instansi / Penerima
                                    </div>
                                    <div class="do-detail-value fw-bold fs-6 lh-base text-body-emphasis" id="detail_do_penerima">-</div>
                                </div>

                                <!-- Nomor Telepon / WhatsApp -->
                                <div class="do-detail-field-box">
                                    <div class="do-detail-label">
                                        <i class="bi bi-telephone text-success"></i> No. Telepon / WhatsApp
                                    </div>
                                    <div class="do-detail-value font-monospace fw-semibold text-body" id="detail_do_telepon">-</div>
                                </div>

                                <!-- Alamat Lengkap Pengiriman -->
                                <div class="do-detail-field-box flex-grow-1">
                                    <div class="do-detail-label">
                                        <i class="bi bi-geo-alt-fill text-danger"></i> Alamat Lengkap Pengiriman
                                    </div>
                                    <div class="do-detail-value small lh-base text-body" id="detail_do_alamat">-</div>
                                </div>

                            </div>
                        </div>
                    </div>

                    <!-- Kartu Informasi Logistik & Kurir -->
                    <div class="col-lg-6">
                        <div class="card border-0 rounded-4 h-100 shadow-sm do-detail-card overflow-hidden">
                            <div class="card-header bg-transparent py-3 px-4 border-bottom d-flex align-items-center justify-content-between">
                                <div class="fw-bold text-body-emphasis small text-uppercase d-flex align-items-center gap-2">
                                    <span class="do-mini-badge"><i class="bi bi-truck"></i></span>
                                    <span>Detail Logistik &amp; Pengiriman</span>
                                </div>
                                <span class="badge bg-info-subtle text-info border border-info-subtle rounded-pill small font-monospace px-2.5 py-0.5" style="font-size: 0.68rem;">EKSPEDISI</span>
                            </div>
                            <div class="card-body p-3.5 p-md-4 d-flex flex-column gap-3">
                                
                                <!-- Metode & Ekspedisi Row -->
                                <div class="row g-2.5">
                                    <div class="col-sm-6">
                                        <div class="do-detail-field-box h-100">
                                            <div class="do-detail-label">
                                                <i class="bi bi-diagram-2 text-primary"></i> Metode Kirim
                                            </div>
                                            <div class="do-detail-value fw-bold text-body-emphasis" id="detail_do_metode">-</div>
                                        </div>
                                    </div>
                                    <div class="col-sm-6">
                                        <div class="do-detail-field-box h-100">
                                            <div class="do-detail-label">
                                                <i class="bi bi-person-badge text-primary"></i> Ekspedisi / Supir
                                            </div>
                                            <div class="do-detail-value fw-bold text-body-emphasis" id="detail_do_ekspedisi">-</div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Resi / Plat Mobil -->
                                <div class="do-detail-field-box">
                                    <div class="do-detail-label">
                                        <i class="bi bi-upc-scan text-primary"></i> No. Resi / Plat Mobil
                                    </div>
                                    <div class="do-detail-value font-monospace fw-bold text-primary" id="detail_do_resi">-</div>
                                </div>

                                <!-- Catatan / Instruksi Pengiriman -->
                                <div class="do-detail-field-box flex-grow-1">
                                    <div class="do-detail-label">
                                        <i class="bi bi-chat-left-text text-warning"></i> Catatan &amp; Instruksi Khusus
                                    </div>
                                    <div class="do-detail-value small fst-italic text-body-secondary lh-base" id="detail_do_catatan">-</div>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>

                <!-- Kartu Status Alur Persetujuan & Penerimaan (Workflow Tracking) -->
                <div class="card border-0 rounded-4 mb-4 shadow-sm do-timeline-box overflow-hidden">
                    <div class="card-body p-3 p-md-3.5">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <div class="do-timeline-step-card d-flex flex-column gap-1.5">
                                    <div class="d-flex align-items-center justify-content-between">
                                        <div class="d-flex align-items-center gap-2">
                                            <span class="badge bg-primary text-white rounded-circle p-1 d-inline-flex align-items-center justify-content-center" style="width: 22px; height: 22px;"><i class="bi bi-pencil-fill" style="font-size:0.65rem;"></i></span>
                                            <span class="fw-bold small text-body-emphasis">1. Diajukan</span>
                                        </div>
                                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill font-monospace" style="font-size: 0.65rem;">PEMBUAT</span>
                                    </div>
                                    <div class="fw-bold text-primary mt-1" id="detail_timeline_creator">-</div>
                                    <div class="text-body-secondary small font-monospace" id="detail_timeline_created_at">-</div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="do-timeline-step-card d-flex flex-column gap-1.5">
                                    <div class="d-flex align-items-center justify-content-between">
                                        <div class="d-flex align-items-center gap-2">
                                            <span class="badge bg-info text-white rounded-circle p-1 d-inline-flex align-items-center justify-content-center" style="width: 22px; height: 22px;"><i class="bi bi-shield-check" style="font-size:0.65rem;"></i></span>
                                            <span class="fw-bold small text-body-emphasis">2. Persetujuan</span>
                                        </div>
                                        <span class="badge bg-info-subtle text-info border border-info-subtle rounded-pill font-monospace" style="font-size: 0.65rem;">ATASAN</span>
                                    </div>
                                    <div id="detail_timeline_approver_box" class="mt-1">
                                        <span class="text-muted small fst-italic">Menunggu Persetujuan</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="do-timeline-step-card d-flex flex-column gap-1.5">
                                    <div class="d-flex align-items-center justify-content-between">
                                        <div class="d-flex align-items-center gap-2">
                                            <span class="badge bg-success text-white rounded-circle p-1 d-inline-flex align-items-center justify-content-center" style="width: 22px; height: 22px;"><i class="bi bi-box-seam-fill" style="font-size:0.65rem;"></i></span>
                                            <span class="fw-bold small text-body-emphasis">3. Konfirmasi Sampai</span>
                                        </div>
                                        <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill font-monospace" style="font-size: 0.65rem;">PENERIMA</span>
                                    </div>
                                    <div id="detail_timeline_receiver_box" class="mt-1">
                                        <span class="text-muted small fst-italic">Belum Selesai</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tabel Item Barang yang Dikirim -->
                <div class="card border-0 rounded-4 overflow-hidden shadow-sm do-detail-card mb-3">
                    <div class="card-header bg-transparent py-3 px-4 border-bottom d-flex align-items-center justify-content-between">
                        <div class="fw-bold text-body-emphasis small text-uppercase d-flex align-items-center gap-2">
                            <span class="do-mini-badge"><i class="bi bi-boxes"></i></span>
                            <span>Rincian Produk &amp; Serial Number Terkirim</span>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0" id="detail_do_table" style="min-width: 720px;">
                            <thead>
                                <tr class="do-table-header">
                                    <th style="width: 50px;" class="text-center py-3">No</th>
                                    <th class="py-3" style="min-width: 240px;">Nama Produk / Barang &amp; Serial Number</th>
                                    <th class="text-center py-3" style="width: 140px;">Qty Order</th>
                                    <th class="text-center py-3" style="width: 150px;">Qty Dikirim</th>
                                    <th class="py-3" style="min-width: 180px;">Keterangan Item</th>
                                </tr>
                            </thead>
                            <tbody id="detail_do_items_tbody">
                                <!-- Injected Dynamically by JS -->
                            </tbody>
                        </table>
                    </div>
                </div>



            </div>

            <!-- Modal Footer -->
            <div class="modal-footer border-top px-4 py-3 d-flex justify-content-between align-items-center bg-body-tertiary">
                <div class="d-flex align-items-center gap-2 flex-wrap" id="detail_modal_actions">
                    <!-- Action buttons injected via JS -->
                </div>
                <button type="button" class="btn btn-secondary btn-sm rounded-pill px-4 py-1.5 fw-semibold" data-bs-dismiss="modal">Tutup</button>
            </div>

        </div>
    </div>
</div>

<!-- Modal Voucher Jurnal Akuntansi Pengiriman / Delivery Order -->
<div class="modal fade" id="modalJurnalDO" tabindex="-1" aria-labelledby="modalJurnalDOLabel" aria-hidden="true" style="z-index: 1065;">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable" style="max-width: 940px;">
        <div class="modal-content do-jurnal-modal-content border-0 shadow-2xl rounded-4 overflow-hidden">
            <!-- Modal Header -->
            <div class="modal-header do-jurnal-modal-header py-3 px-4 d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center gap-3">
                    <div class="do-jurnal-icon-badge">
                        <i class="bi bi-journal-check fs-4"></i>
                    </div>
                    <div>
                        <div class="d-flex align-items-center gap-2 mb-0.5">
                            <h5 class="modal-title fw-bold text-body-emphasis mb-0" id="modalJurnalDOLabel">Jurnal Akuntansi Pengiriman Barang</h5>
                            <span class="badge do-jurnal-auto-badge text-uppercase fw-bold font-monospace">Auto-Posted</span>
                        </div>
                        <div class="d-flex align-items-center gap-2 flex-wrap">
                            <span class="text-muted small">No. Voucher Jurnal:</span>
                            <span class="do-jurnal-badge-code fw-bold font-monospace px-2 py-0.5 rounded-2" id="do_jurnal_nomor">-</span>
                            <span id="do_jurnal_tender_badge" class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle font-monospace px-2 py-0.5 rounded-pill fw-bold" style="display: none; font-size: 0.72rem;"></span>
                        </div>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <a href="#" target="_blank" class="btn do-jurnal-btn-print btn-sm rounded-pill px-3 py-1.5 d-inline-flex align-items-center gap-1.5" id="btn_print_do_jurnal">
                        <i class="bi bi-printer"></i> Cetak Voucher
                    </a>
                    <button type="button" class="btn-close ms-2" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
            </div>

            <!-- Modal Body -->
            <div class="modal-body p-4">
                <!-- Info Header Jurnal Grid -->
                <div class="do-jurnal-meta-grid p-3 mb-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="do-jurnal-meta-item h-100">
                                <div class="d-flex align-items-center gap-1.5 mb-1.5">
                                    <i class="bi bi-calendar3 text-emerald fs-6"></i>
                                    <span class="do-jurnal-meta-label">Tanggal Jurnal</span>
                                </div>
                                <div class="do-jurnal-meta-value font-monospace" id="do_jurnal_tanggal">-</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="do-jurnal-meta-item h-100">
                                <div class="d-flex align-items-center gap-1.5 mb-1.5">
                                    <i class="bi bi-building text-primary fs-6"></i>
                                    <span class="do-jurnal-meta-label">Entitas Pembukuan</span>
                                </div>
                                <div id="do_jurnal_entitas_badge">-</div>
                            </div>
                        </div>
                        <div class="col-12" id="do_jurnal_tender_row" style="display: none;">
                            <div class="do-jurnal-meta-item">
                                <div class="d-flex align-items-center gap-1.5 mb-1.5">
                                    <i class="bi bi-briefcase text-warning-emphasis fs-6"></i>
                                    <span class="do-jurnal-meta-label">Proyek / Tender Terkait</span>
                                </div>
                                <div class="do-jurnal-meta-value font-monospace fw-bold" id="do_jurnal_tender_text" style="color: #d97706;">-</div>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="do-jurnal-meta-item">
                                <div class="d-flex align-items-center gap-1.5 mb-1.5">
                                    <i class="bi bi-card-text text-secondary fs-6"></i>
                                    <span class="do-jurnal-meta-label">Keterangan Transaksi</span>
                                </div>
                                <div class="do-jurnal-meta-value text-break" id="do_jurnal_keterangan">-</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tabel Baris Jurnal -->
                <div class="do-jurnal-table-wrapper rounded-3 overflow-hidden mb-3">
                    <table class="table do-jurnal-table align-middle mb-0 w-100">
                        <thead>
                            <tr>
                                <th class="text-center" style="width: 44px;">No</th>
                                <th>Kode &amp; Nama Akun COA</th>
                                <th class="text-end" style="width: 175px;">Debit (Rp)</th>
                                <th class="text-end" style="width: 175px;">Kredit (Rp)</th>
                                <th style="width: 250px;">Catatan / Keterangan</th>
                            </tr>
                        </thead>
                        <tbody id="do_jurnal_rows_container">
                            <!-- Populated via JS -->
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="2" class="text-end fw-bold text-uppercase do-jurnal-total-label">TOTAL:</td>
                                <td class="text-end font-monospace fw-bold do-jurnal-debit-val fs-6" id="do_jurnal_total_debit">Rp 0</td>
                                <td class="text-end font-monospace fw-bold do-jurnal-kredit-val fs-6" id="do_jurnal_total_kredit">Rp 0</td>
                                <td class="text-center">
                                    <div id="do_jurnal_balance_container">
                                        <span class="badge do-jurnal-balance-badge font-monospace px-3 py-1">
                                            <i class="bi bi-check2-circle me-1"></i>Balance
                                        </span>
                                    </div>
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            <!-- Modal Footer -->
            <div class="modal-footer do-jurnal-modal-footer py-2.5 px-4 d-flex justify-content-between align-items-center">
                <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill px-3.5 py-1.5 d-inline-flex align-items-center gap-1.5" id="btn_back_to_do">
                    <i class="bi bi-arrow-left"></i> Kembali ke Rincian DO
                </button>
                <button type="button" class="btn btn-secondary btn-sm rounded-pill px-4 py-1.5" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>
