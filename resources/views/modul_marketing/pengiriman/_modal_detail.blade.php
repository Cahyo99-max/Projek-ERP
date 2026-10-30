<style>
/* Scoped Styling for Delivery Order Detail & Jurnal Modals */
#modalDetailDO .modal-dialog,
#modalJurnalDO .modal-dialog {
    max-width: 95vw;
    width: 95vw;
    transition: all 0.25s ease;
}

@media (min-width: 1200px) {
    #modalDetailDO .modal-dialog {
        max-width: 1100px;
    }
    #modalJurnalDO .modal-dialog {
        max-width: 960px;
    }
}

@media (max-width: 768px) {
    #modalDetailDO .modal-dialog,
    #modalJurnalDO .modal-dialog {
        width: calc(100% - 16px) !important;
        max-width: calc(100% - 16px) !important;
        margin: 8px auto !important;
    }
    #modalDetailDO .modal-body,
    #modalJurnalDO .modal-body {
        padding: 0.95rem !important;
    }
}

/* Modern Info Card */
.do-modern-info-card {
    background: var(--bs-card-bg, #ffffff);
    border: 1px solid rgba(226, 232, 240, 0.9);
    border-radius: 1rem;
    padding: 1.25rem 1.35rem;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.03);
    transition: all 0.2s ease;
}
@media (max-width: 576px) {
    .do-modern-info-card {
        padding: 1rem 1.05rem;
        border-radius: 0.85rem;
    }
}
[data-bs-theme="dark"] .do-modern-info-card {
    background: #111827;
    border-color: rgba(255, 255, 255, 0.08);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.25);
}

.do-card-title {
    font-size: 0.8rem;
    font-weight: 700;
    letter-spacing: 0.5px;
    text-transform: uppercase;
}

.do-icon-box {
    width: 28px;
    height: 28px;
    border-radius: 8px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 0.85rem;
    flex-shrink: 0;
}

.do-info-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.62rem 0;
    border-bottom: 1px dashed rgba(226, 232, 240, 0.85);
    gap: 12px;
}
[data-bs-theme="dark"] .do-info-row {
    border-bottom-color: rgba(255, 255, 255, 0.08);
}
.do-info-row:last-child {
    border-bottom: none;
    padding-bottom: 0;
}
.do-info-row:first-child {
    padding-top: 0.15rem;
}

.do-info-label {
    width: 125px;
    flex-shrink: 0;
    font-size: 0.82rem;
    color: var(--bs-secondary-color, #64748b);
    display: flex;
    align-items: center;
    gap: 6px;
    font-weight: 500;
}
.do-info-label i {
    font-size: 0.85rem;
    opacity: 0.85;
}

.do-info-val {
    font-size: 0.84rem;
    text-align: right;
    word-break: break-word;
    flex-grow: 1;
}

/* Close Button Modern */
.do-btn-close {
    width: 34px;
    height: 34px;
    border-radius: 50%;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border: 1px solid rgba(226, 232, 240, 0.8);
    background: #f8fafc;
    color: #64748b;
    cursor: pointer;
    transition: all 0.2s ease;
    padding: 0;
    font-size: 0.85rem;
}
.do-btn-close:hover {
    background: #e2e8f0;
    color: #0f172a;
    transform: rotate(90deg);
}
[data-bs-theme="dark"] .do-btn-close {
    background: #1e293b;
    border-color: rgba(255, 255, 255, 0.12);
    color: #94a3b8;
}
[data-bs-theme="dark"] .do-btn-close:hover {
    background: #334155;
    color: #f8fafc;
}

/* Jurnal Swipe Banner */
.do-jurnal-swipe-banner {
    background: rgba(16, 185, 129, 0.08);
    border: 1px dashed rgba(16, 185, 129, 0.3);
    color: #059669;
    font-size: 0.775rem;
}
[data-bs-theme="dark"] .do-jurnal-swipe-banner {
    background: rgba(16, 185, 129, 0.12);
    border-color: rgba(16, 185, 129, 0.35);
    color: #34d399;
}
.do-jurnal-swipe-pill {
    background: rgba(16, 185, 129, 0.18);
    color: #047857;
    font-size: 0.72rem;
}
[data-bs-theme="dark"] .do-jurnal-swipe-pill {
    background: rgba(16, 185, 129, 0.28);
    color: #6ee7b7;
}

/* Jurnal Table Styling */
.do-jurnal-table thead th {
    background: linear-gradient(135deg, #065f46 0%, #047857 50%, #059669 100%) !important;
    color: #ffffff !important;
    font-size: 0.76rem;
    font-weight: 700;
    letter-spacing: 0.5px;
    padding: 12px 14px;
    border: none !important;
}
.do-jurnal-table tbody td {
    padding: 11px 14px;
    border-bottom: 1px solid rgba(226, 232, 240, 0.7);
    font-size: 0.84rem;
}
[data-bs-theme="dark"] .do-jurnal-table tbody td {
    border-bottom-color: rgba(255, 255, 255, 0.06);
}
.do-jurnal-table tfoot td {
    padding: 12px 14px;
    border-top: 2px solid rgba(16, 185, 129, 0.3);
}

/* Jurnal Meta Styling */
.do-jurnal-meta-grid {
    background: var(--bs-card-bg, #ffffff);
    border: 1px solid rgba(226, 232, 240, 0.9);
    border-radius: 1rem;
    padding: 1.15rem 1.25rem;
}
[data-bs-theme="dark"] .do-jurnal-meta-grid {
    background: #111827;
    border-color: rgba(255, 255, 255, 0.08);
}
.do-jurnal-meta-item {
    background: rgba(248, 250, 252, 0.6);
    border: 1px solid rgba(226, 232, 240, 0.8);
    border-radius: 10px;
    padding: 10px 12px;
}
[data-bs-theme="dark"] .do-jurnal-meta-item {
    background: rgba(255, 255, 255, 0.03);
    border-color: rgba(255, 255, 255, 0.08);
}
</style>

<!-- Modal Detail Surat Jalan / Delivery Order (Cinematic View) -->
<div class="modal modal-blur fade" id="modalDetailDO" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-xl">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            
            <!-- Modal Header -->
            <div class="modal-header border-bottom px-3 px-sm-4 py-3 d-flex justify-content-between align-items-center bg-body-tertiary">
                <div class="d-flex align-items-center gap-2.5">
                    <div class="p-2 p-sm-2.5 rounded-3 text-white d-inline-flex align-items-center justify-content-center shadow-sm" style="background: linear-gradient(135deg, #1e3a8a 0%, #2563eb 100%); width: 38px; height: 38px;">
                        <i class="bi bi-truck fs-5"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold mb-0 text-body-emphasis fs-6 fs-sm-5">Rincian Surat Jalan &amp; Pengiriman</h5>
                        <div class="text-body-secondary small d-none d-sm-block" style="font-size: 0.78rem;">Informasi pengeluaran fisik barang dan pelacakan ekspedisi/kurir.</div>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-2">
                    @can('lihat-jurnal-delivery-order')
                    <button type="button" class="btn btn-sm rounded-pill px-3 py-1.5 fw-bold d-none align-items-center gap-1.5 shadow-xs text-white" id="btn_modal_lihat_jurnal" style="background: linear-gradient(135deg, #059669 0%, #10b981 100%); border: none;">
                        <i class="bi bi-journal-check"></i> <span class="d-none d-sm-inline">Jurnal Akuntansi</span><span class="d-inline d-sm-none">Jurnal</span>
                    </button>
                    @endcan
                    <button type="button" class="do-btn-close" data-bs-dismiss="modal" aria-label="Close">
                        <i class="bi bi-x-lg"></i>
                    </button>
                </div>
            </div>
            
            <div class="modal-body p-3 p-sm-4" id="printable_do_area">

                <!-- Nomor DO & Header Meta Bar -->
                <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3.5 pb-2.5 border-bottom border-secondary-subtle">
                    <div class="d-flex flex-wrap align-items-center gap-2">
                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle font-monospace px-3 py-1.5 rounded-pill fw-bold" id="detail_do_nomor" style="font-size: 0.82rem;">
                            Nomor DO: -
                        </span>
                        <span class="badge bg-body border text-body-secondary font-monospace px-2.5 py-1.5 rounded-pill small" id="detail_do_tanggal">
                            <i class="bi bi-calendar3 me-1 text-primary"></i> Tgl Kirim: -
                        </span>
                    </div>
                    <div class="d-flex align-items-center gap-2 flex-wrap">
                        <span class="badge bg-body border text-body px-2.5 py-1.5 rounded-pill small font-monospace d-flex align-items-center gap-1">
                            <i class="bi bi-shop text-muted"></i> Gudang: <strong id="detail_do_gudang" class="text-body-emphasis ms-0.5">-</strong>
                        </span>
                        <div id="detail_do_status_badge"></div>
                    </div>
                </div>

                <!-- Info Grid: Tujuan & Ekspedisi (Lega & Rapi) -->
                <div class="row g-3.5 g-md-4 mb-4">
                    <!-- Kartu Informasi Penerima -->
                    <div class="col-lg-6">
                        <div class="do-modern-info-card h-100">
                            <div class="do-card-header mb-3 pb-2.5 border-bottom border-secondary-subtle d-flex align-items-center justify-content-between">
                                <div class="d-flex align-items-center gap-2">
                                    <span class="do-icon-box bg-primary bg-opacity-10 text-primary">
                                        <i class="bi bi-person-fill"></i>
                                    </span>
                                    <span class="do-card-title text-primary">Tujuan &amp; Penerima</span>
                                </div>
                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill small font-monospace px-2.5 py-0.5" style="font-size: 0.68rem;">DESTINASI</span>
                            </div>
                            
                            <div class="d-flex flex-column">
                                <div class="do-info-row">
                                    <span class="do-info-label"><i class="bi bi-receipt text-primary"></i> No. SO</span>
                                    <span class="do-info-val fw-bold text-primary font-monospace" id="detail_do_nomor_so">-</span>
                                </div>
                                <div class="do-info-row" id="detail_do_tender_row" style="display: none;">
                                    <span class="do-info-label"><i class="bi bi-file-earmark-text text-warning"></i> No. Tender</span>
                                    <span class="do-info-val font-monospace fw-bold text-warning-emphasis" id="detail_do_tender_no">-</span>
                                </div>
                                <div class="do-info-row">
                                    <span class="do-info-label"><i class="bi bi-building text-primary"></i> Instansi/PIC</span>
                                    <span class="do-info-val fw-bold text-body-emphasis" id="detail_do_penerima">-</span>
                                </div>
                                <div class="do-info-row">
                                    <span class="do-info-label"><i class="bi bi-telephone text-success"></i> Telepon/WA</span>
                                    <span class="do-info-val font-monospace fw-semibold text-body" id="detail_do_telepon">-</span>
                                </div>
                                <div class="do-info-row align-items-start">
                                    <span class="do-info-label mt-0.5"><i class="bi bi-geo-alt-fill text-danger"></i> Alamat</span>
                                    <span class="do-info-val text-body" id="detail_do_alamat">-</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Kartu Informasi Logistik & Kurir -->
                    <div class="col-lg-6">
                        <div class="do-modern-info-card h-100">
                            <div class="do-card-header mb-3 pb-2.5 border-bottom border-secondary-subtle d-flex align-items-center justify-content-between">
                                <div class="d-flex align-items-center gap-2">
                                    <span class="do-icon-box bg-info bg-opacity-10 text-info">
                                        <i class="bi bi-truck"></i>
                                    </span>
                                    <span class="do-card-title text-info">Logistik &amp; Kurir</span>
                                </div>
                                <span class="badge bg-info-subtle text-info border border-info-subtle rounded-pill small font-monospace px-2.5 py-0.5" style="font-size: 0.68rem;">EKSPEDISI</span>
                            </div>
                            
                            <div class="d-flex flex-column">
                                <div class="do-info-row">
                                    <span class="do-info-label"><i class="bi bi-diagram-2 text-primary"></i> Metode Kirim</span>
                                    <span class="do-info-val fw-bold text-body-emphasis" id="detail_do_metode">-</span>
                                </div>
                                <div class="do-info-row">
                                    <span class="do-info-label"><i class="bi bi-person-badge text-primary"></i> Ekspedisi/Supir</span>
                                    <span class="do-info-val fw-bold text-body-emphasis" id="detail_do_ekspedisi">-</span>
                                </div>
                                <div class="do-info-row">
                                    <span class="do-info-label"><i class="bi bi-upc-scan text-primary"></i> Resi / Plat</span>
                                    <span class="do-info-val font-monospace fw-bold text-primary" id="detail_do_resi">-</span>
                                </div>
                                <div class="do-info-row align-items-start">
                                    <span class="do-info-label mt-0.5"><i class="bi bi-chat-left-text text-warning"></i> Catatan</span>
                                    <span class="do-info-val text-body-secondary fst-italic" id="detail_do_catatan">-</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Kartu Status Alur Persetujuan & Penerimaan (Workflow Tracking) -->
                <div class="do-modern-info-card mb-4">
                    <div class="do-card-header mb-3 pb-2 border-bottom border-secondary-subtle d-flex align-items-center gap-2">
                        <span class="do-icon-box bg-primary bg-opacity-10 text-primary">
                            <i class="bi bi-bezier2"></i>
                        </span>
                        <span class="do-card-title text-body-secondary">Alur Pelacakan Status Pengiriman</span>
                    </div>
                    <div class="row g-3">
                        <div class="col-md-4">
                            <div class="p-3 rounded-3 bg-body border border-secondary-subtle h-100 d-flex flex-column gap-1.5">
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
                            <div class="p-3 rounded-3 bg-body border border-secondary-subtle h-100 d-flex flex-column gap-1.5">
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
                            <div class="p-3 rounded-3 bg-body border border-secondary-subtle h-100 d-flex flex-column gap-1.5">
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

                <!-- Swipe Hint Banner untuk Mobile -->
                <div class="d-flex d-md-none align-items-center justify-content-between px-3 py-2.5 mb-2.5 rounded-3 do-mobile-swipe-banner shadow-2xs">
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi bi-arrow-left-right text-primary fs-6"></i>
                        <span class="fw-semibold small">Geser tabel untuk melihat item &amp; serial number</span>
                    </div>
                    <span class="badge rounded-pill px-2.5 py-1 fw-bold do-swipe-pill">Swipe &rarr;</span>
                </div>

                <!-- Tabel Item Barang yang Dikirim -->
                <div class="do-modern-info-card p-0 overflow-hidden mb-3">
                    <div class="card-header bg-transparent py-3 px-3 px-md-4 border-bottom d-flex align-items-center justify-content-between">
                        <div class="fw-bold text-body-emphasis small text-uppercase d-flex align-items-center gap-2">
                            <span class="do-icon-box bg-primary bg-opacity-10 text-primary"><i class="bi bi-boxes"></i></span>
                            <span class="do-card-title text-body-emphasis">Rincian Produk &amp; Serial Number Terkirim</span>
                        </div>
                    </div>
                    <div class="table-responsive" style="overflow-x: auto !important; -webkit-overflow-scrolling: touch !important;">
                        <table class="table table-hover align-middle mb-0" id="detail_do_table" style="width: 100%; min-width: 760px;">
                            <thead>
                                <tr class="small text-uppercase fw-bold text-white" style="background: linear-gradient(135deg, #1e3a8a 0%, #1d4ed8 100%) !important;">
                                    <th style="width: 50px;" class="text-center py-3">No</th>
                                    <th class="py-3 px-3" style="min-width: 240px;">Nama Produk / Barang &amp; Serial Number</th>
                                    <th class="text-center py-3 text-nowrap" style="width: 130px;">Qty Order</th>
                                    <th class="text-center py-3 text-nowrap" style="width: 140px;">Qty Dikirim</th>
                                    <th class="py-3 px-3" style="min-width: 170px;">Keterangan Item</th>
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
            <div class="modal-footer border-top px-3 px-sm-4 py-3 d-flex justify-content-between align-items-center bg-body-tertiary">
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
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content do-jurnal-modal-content border-0 shadow-2xl rounded-4 overflow-hidden">
            <!-- Modal Header -->
            <div class="modal-header do-jurnal-modal-header py-3 px-3 px-sm-4 d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center gap-2.5">
                    <div class="do-jurnal-icon-badge" style="width: 38px; height: 38px;">
                        <i class="bi bi-journal-check fs-5"></i>
                    </div>
                    <div>
                        <div class="d-flex align-items-center gap-2 mb-0.5">
                            <h5 class="modal-title fw-bold text-body-emphasis mb-0 fs-6 fs-sm-5" id="modalJurnalDOLabel">Jurnal Akuntansi Pengiriman</h5>
                            <span class="badge do-jurnal-auto-badge text-uppercase fw-bold font-monospace d-none d-sm-inline">Auto-Posted</span>
                        </div>
                        <div class="d-flex align-items-center gap-1.5 flex-wrap">
                            <span class="text-muted small" style="font-size: 0.76rem;">No. Voucher:</span>
                            <span class="do-jurnal-badge-code fw-bold font-monospace px-2 py-0.5 rounded-2" id="do_jurnal_nomor" style="font-size: 0.78rem;">-</span>
                            <span id="do_jurnal_tender_badge" class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle font-monospace px-2 py-0.5 rounded-pill fw-bold" style="display: none; font-size: 0.72rem;"></span>
                        </div>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <a href="#" target="_blank" class="btn btn-sm rounded-pill px-3 py-1.5 d-inline-flex align-items-center gap-1.5 shadow-xs text-white" id="btn_print_do_jurnal" style="background: linear-gradient(135deg, #059669 0%, #10b981 100%); border: none;">
                        <i class="bi bi-printer"></i> <span class="d-none d-sm-inline">Cetak Voucher</span>
                    </a>
                    <button type="button" class="do-btn-close" data-bs-dismiss="modal" aria-label="Close">
                        <i class="bi bi-x-lg"></i>
                    </button>
                </div>
            </div>

            <!-- Modal Body -->
            <div class="modal-body p-3 p-sm-4">
                <!-- Info Header Jurnal Grid -->
                <div class="do-jurnal-meta-grid mb-3.5">
                    <div class="row g-2.5 g-md-3">
                        <div class="col-md-6">
                            <div class="do-jurnal-meta-item h-100">
                                <div class="d-flex align-items-center gap-1.5 mb-1">
                                    <i class="bi bi-calendar3 text-success fs-6"></i>
                                    <span class="do-jurnal-meta-label">Tanggal Jurnal</span>
                                </div>
                                <div class="do-jurnal-meta-value font-monospace text-body-emphasis" id="do_jurnal_tanggal">-</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="do-jurnal-meta-item h-100">
                                <div class="d-flex align-items-center gap-1.5 mb-1">
                                    <i class="bi bi-building text-primary fs-6"></i>
                                    <span class="do-jurnal-meta-label">Entitas Pembukuan</span>
                                </div>
                                <div id="do_jurnal_entitas_badge">-</div>
                            </div>
                        </div>
                        <div class="col-12" id="do_jurnal_tender_row" style="display: none;">
                            <div class="do-jurnal-meta-item">
                                <div class="d-flex align-items-center gap-1.5 mb-1">
                                    <i class="bi bi-briefcase text-warning-emphasis fs-6"></i>
                                    <span class="do-jurnal-meta-label">Proyek / Tender Terkait</span>
                                </div>
                                <div class="do-jurnal-meta-value font-monospace fw-bold" id="do_jurnal_tender_text" style="color: #d97706;">-</div>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="do-jurnal-meta-item">
                                <div class="d-flex align-items-center gap-1.5 mb-1">
                                    <i class="bi bi-card-text text-secondary fs-6"></i>
                                    <span class="do-jurnal-meta-label">Keterangan Transaksi</span>
                                </div>
                                <div class="do-jurnal-meta-value text-break text-body" id="do_jurnal_keterangan" style="font-size: 0.84rem; line-height: 1.5;">-</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Swipe Hint Banner untuk Mobile Jurnal -->
                <div class="d-flex d-md-none align-items-center justify-content-between px-3 py-2 mb-2.5 rounded-3 do-jurnal-swipe-banner shadow-2xs">
                    <div class="d-flex align-items-center gap-1.5">
                        <i class="bi bi-arrow-left-right text-success"></i>
                        <span class="small fw-semibold">Geser tabel untuk melihat rincian debit/kredit</span>
                    </div>
                    <span class="badge rounded-pill px-2.5 py-1 fw-bold do-jurnal-swipe-pill">Swipe &rarr;</span>
                </div>

                <!-- Tabel Baris Jurnal (Bisa Digeser & Stabil) -->
                <div class="do-modern-info-card p-0 overflow-hidden mb-3">
                    <div class="table-responsive" style="overflow-x: auto !important; -webkit-overflow-scrolling: touch !important; width: 100%;">
                        <table class="table do-jurnal-table align-middle mb-0 w-100" style="min-width: 680px;">
                            <thead>
                                <tr>
                                    <th class="text-center" style="width: 44px;">No</th>
                                    <th style="min-width: 220px;">Kode &amp; Nama Akun COA</th>
                                    <th class="text-end text-nowrap" style="width: 155px;">Debit (Rp)</th>
                                    <th class="text-end text-nowrap" style="width: 155px;">Kredit (Rp)</th>
                                    <th style="min-width: 180px;">Catatan / Keterangan</th>
                                </tr>
                            </thead>
                            <tbody id="do_jurnal_rows_container">
                                <!-- Populated via JS -->
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="2" class="text-end fw-bold text-uppercase do-jurnal-total-label text-nowrap">TOTAL:</td>
                                    <td class="text-end font-monospace fw-bold do-jurnal-debit-val fs-6 text-nowrap" id="do_jurnal_total_debit">Rp 0</td>
                                    <td class="text-end font-monospace fw-bold do-jurnal-kredit-val fs-6 text-nowrap" id="do_jurnal_total_kredit">Rp 0</td>
                                    <td class="text-center text-nowrap">
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
            </div>

            <!-- Modal Footer -->
            <div class="modal-footer do-jurnal-modal-footer py-2.5 px-3 px-sm-4 d-flex justify-content-between align-items-center">
                <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill px-3.5 py-1.5 d-inline-flex align-items-center gap-1.5" id="btn_back_to_do">
                    <i class="bi bi-arrow-left"></i> Kembali ke Rincian DO
                </button>
                <button type="button" class="btn btn-secondary btn-sm rounded-pill px-4 py-1.5 fw-semibold" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>
