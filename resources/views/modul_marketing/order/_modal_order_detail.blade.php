<style>
/* Scoped Styling for Modal Detail Order (SO) */
#modalDetailOrder .modal-dialog {
    max-width: 95vw;
    width: 95vw;
    transition: all 0.25s ease;
}

@media (max-width: 768px) {
    #modalDetailOrder .modal-dialog {
        width: calc(100% - 16px) !important;
        max-width: calc(100% - 16px) !important;
        margin: 8px auto !important;
    }
    #modalDetailOrder .modal-body {
        padding: 0.95rem !important;
    }
}

.so-modern-card {
    background: var(--bs-card-bg, #ffffff);
    border: 1px solid rgba(226, 232, 240, 0.9);
    border-radius: 1rem;
    padding: 1.25rem 1.35rem;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.03);
    transition: all 0.2s ease;
}
@media (max-width: 576px) {
    .so-modern-card {
        padding: 1rem 1.05rem;
        border-radius: 0.85rem;
    }
}

[data-bs-theme="dark"] .so-modern-card {
    background: #111827;
    border-color: rgba(255, 255, 255, 0.08);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.25);
}

.so-card-title {
    font-size: 0.8rem;
    font-weight: 700;
    letter-spacing: 0.5px;
    text-transform: uppercase;
}

.so-icon-box {
    width: 28px;
    height: 28px;
    border-radius: 8px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 0.85rem;
    flex-shrink: 0;
}

.so-detail-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.62rem 0;
    border-bottom: 1px dashed rgba(226, 232, 240, 0.85);
    gap: 12px;
}
[data-bs-theme="dark"] .so-detail-row {
    border-bottom-color: rgba(255, 255, 255, 0.08);
}
.so-detail-row:last-child {
    border-bottom: none;
    padding-bottom: 0;
}
.so-detail-row:first-child {
    padding-top: 0.15rem;
}

.so-detail-label {
    width: 100px;
    flex-shrink: 0;
    font-size: 0.82rem;
    color: var(--bs-secondary-color, #64748b);
    display: flex;
    align-items: center;
    gap: 6px;
    font-weight: 500;
}
.so-detail-label i {
    font-size: 0.85rem;
    opacity: 0.85;
}

.so-detail-value {
    font-size: 0.84rem;
    text-align: right;
    word-break: break-word;
    flex-grow: 1;
}

.so-table-card {
    background: var(--bs-card-bg, #ffffff);
    overflow: hidden;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.03);
}
[data-bs-theme="dark"] .so-table-card {
    background: #111827;
    border-color: rgba(255, 255, 255, 0.08) !important;
}

#so_detail_table thead th {
    border: none;
    letter-spacing: 0.5px;
    font-size: 0.76rem;
}
#so_detail_table tbody td {
    padding-top: 0.85rem;
    padding-bottom: 0.85rem;
    border-bottom: 1px solid rgba(226, 232, 240, 0.6);
}
[data-bs-theme="dark"] #so_detail_table tbody td {
    border-bottom-color: rgba(255, 255, 255, 0.05);
}

.so-swipe-banner {
    background: rgba(37, 99, 235, 0.08);
    border: 1px dashed rgba(37, 99, 235, 0.3);
    color: #2563eb;
}
[data-bs-theme="dark"] .so-swipe-banner {
    background: rgba(59, 130, 246, 0.12);
    border-color: rgba(59, 130, 246, 0.35);
    color: #60a5fa;
}
.so-swipe-pill {
    background: rgba(37, 99, 235, 0.15);
    color: #1d4ed8;
    font-size: 0.72rem;
}
[data-bs-theme="dark"] .so-swipe-pill {
    background: rgba(59, 130, 246, 0.25);
    color: #93c5fd;
}

.so-btn-close {
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
.so-btn-close:hover {
    background: #e2e8f0;
    color: #0f172a;
    transform: rotate(90deg);
}
[data-bs-theme="dark"] .so-btn-close {
    background: #1e293b;
    border-color: rgba(255, 255, 255, 0.12);
    color: #94a3b8;
}
[data-bs-theme="dark"] .so-btn-close:hover {
    background: #334155;
    color: #f8fafc;
}
</style>

<!-- Modal Detail Surat Pesanan / Sales Invoice -->
<div class="modal modal-blur fade" id="modalDetailOrder" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-xl">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            
            <!-- Modal Header Toolbar -->
            <div class="modal-header border-bottom px-3 px-sm-4 py-3 d-flex justify-content-between align-items-center bg-body-tertiary">
                <div class="d-flex align-items-center gap-2.5">
                    <div class="p-2 p-sm-2.5 rounded-3 text-white d-inline-flex align-items-center justify-content-center shadow-sm" style="background: linear-gradient(135deg, #1e3a8a 0%, #2563eb 100%); width: 38px; height: 38px;">
                        <i class="bi bi-receipt-cutoff fs-5"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold mb-0 text-body-emphasis fs-6 fs-sm-5">Detail Pesanan (SO)</h5>
                        <div class="text-body-secondary small d-none d-sm-block" style="font-size: 0.78rem;">Ringkasan transaksi dan rincian produk pesanan.</div>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <a href="#" target="_blank" class="btn btn-primary btn-sm rounded-pill px-3 py-1.5 fw-bold d-flex align-items-center gap-1.5 shadow-sm text-white" id="btn_print_order" style="background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%); border: none;">
                        <i class="bi bi-printer-fill"></i> <span class="d-none d-sm-inline">Cetak SO</span>
                    </a>
                    <button type="button" class="so-btn-close" data-bs-dismiss="modal" aria-label="Close">
                        <i class="bi bi-x-lg"></i>
                    </button>
                </div>
            </div>
            
            <div class="modal-body p-3 p-sm-4" id="printable_order_area">
                
                <!-- Judul Dokumen & Status Grid -->
                <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3.5 pb-2.5 border-bottom border-secondary-subtle">
                    <div class="d-flex flex-wrap align-items-center gap-2">
                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle font-monospace px-3 py-1.5 rounded-pill fw-bold" id="so_detail_nomor" style="font-size: 0.82rem;">
                            SO: -
                        </span>
                        <span class="text-body-secondary small" id="so_detail_tanggal">
                            <i class="bi bi-calendar3 me-1"></i> -
                        </span>
                        <span id="so_detail_quotation_badge" style="display: none;"></span>
                        <span id="so_detail_tender_badge" style="display: none;"></span>
                        <span id="so_detail_invoice_badge" style="display: none;"></span>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <div id="so_detail_status_badge"></div>
                        <span class="badge bg-secondary-subtle text-body border border-secondary-subtle px-2.5 py-1 rounded-pill small font-monospace" id="so_detail_tier_badge">Tier: <strong id="so_detail_tier">-</strong></span>
                    </div>
                </div>

                <!-- Info Pelanggan & Pembayaran Grid (Dengan Jarak Antar Card yang Lega) -->
                <div class="row g-3.5 g-md-4 mb-4">
                    <!-- Kartu Informasi Pembeli -->
                    <div class="col-md-6">
                        <div class="so-modern-card h-100">
                            <div class="so-card-header mb-3 pb-2.5 border-bottom border-secondary-subtle d-flex align-items-center gap-2">
                                <span class="so-icon-box bg-primary bg-opacity-10 text-primary">
                                    <i class="bi bi-person-fill"></i>
                                </span>
                                <span class="so-card-title text-primary">Info Pelanggan</span>
                            </div>
                            <div class="d-flex flex-column">
                                <div class="so-detail-row" id="so_detail_instansi_wrap" style="display: none;">
                                    <span class="so-detail-label"><i class="bi bi-building"></i> Instansi</span>
                                    <span class="so-detail-value fw-bold text-primary" id="so_detail_customer_pt">-</span>
                                </div>
                                <div class="so-detail-row">
                                    <span class="so-detail-label"><i class="bi bi-person"></i> Pemesan</span>
                                    <span class="so-detail-value fw-bold text-body-emphasis" id="so_detail_customer_nama">-</span>
                                </div>
                                <div class="so-detail-row">
                                    <span class="so-detail-label"><i class="bi bi-telephone"></i> Telepon</span>
                                    <span class="so-detail-value font-monospace text-body" id="so_detail_customer_telepon">-</span>
                                </div>
                                <div class="so-detail-row align-items-start">
                                    <span class="so-detail-label mt-0.5"><i class="bi bi-geo-alt"></i> Alamat</span>
                                    <span class="so-detail-value text-body" id="so_detail_customer_alamat">-</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Kartu Informasi Pembayaran -->
                    <div class="col-md-6">
                        <div class="so-modern-card h-100">
                            <div class="so-card-header mb-3 pb-2.5 border-bottom border-secondary-subtle d-flex align-items-center gap-2">
                                <span class="so-icon-box bg-success bg-opacity-10 text-success">
                                    <i class="bi bi-credit-card-fill"></i>
                                </span>
                                <span class="so-card-title text-success">Info Pembayaran</span>
                            </div>
                            <div class="d-flex flex-column">
                                <div class="so-detail-row">
                                    <span class="so-detail-label"><i class="bi bi-wallet2"></i> Metode</span>
                                    <span class="so-detail-value fw-bold text-body-emphasis" id="so_detail_metode_bayar">-</span>
                                </div>
                                <div class="so-detail-row">
                                    <span class="so-detail-label"><i class="bi bi-hourglass-split"></i> Termin</span>
                                    <span class="so-detail-value fw-bold text-body-emphasis" id="so_detail_termin">-</span>
                                </div>
                                <div class="so-detail-row">
                                    <span class="so-detail-label"><i class="bi bi-person-badge"></i> Sales</span>
                                    <span class="so-detail-value text-body" id="so_detail_sales_info">-</span>
                                </div>
                                <div class="so-detail-row">
                                    <span class="so-detail-label"><i class="bi bi-person-check"></i> Dibuat</span>
                                    <span class="so-detail-value font-monospace small text-body" id="so_detail_creator_info">-</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Swipe Hint Banner untuk Mobile -->
                <div class="d-flex d-md-none align-items-center justify-content-between px-3 py-2.5 mb-2.5 rounded-3 so-swipe-banner shadow-2xs">
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi bi-arrow-left-right text-primary fs-6"></i>
                        <span class="fw-semibold small">Geser tabel untuk melihat rincian lengkap</span>
                    </div>
                    <span class="badge rounded-pill px-2.5 py-1 fw-bold so-swipe-pill">Swipe &rarr;</span>
                </div>

                <!-- Tabel Item Barang (Bisa Digeser & Stabil) -->
                <div class="so-table-card border border-secondary-subtle rounded-4 mb-4">
                    <div class="table-responsive" style="overflow-x: auto !important; -webkit-overflow-scrolling: touch !important; width: 100%;">
                        <table class="table table-hover align-middle mb-0" id="so_detail_table" style="width: 100%; min-width: 820px;">
                            <thead>
                                <tr class="small text-uppercase fw-bold text-white" style="background: linear-gradient(135deg, #1e3a8a 0%, #1d4ed8 100%) !important;">
                                    <th style="width: 50px;" class="text-center py-3">No</th>
                                    <th style="width: 60px;" class="text-center py-3">Foto</th>
                                    <th class="py-3 px-3" style="min-width: 220px;">Produk</th>
                                    <th class="text-center py-3 text-nowrap" style="width: 110px;">Qty</th>
                                    <th class="text-end py-3 px-3 text-nowrap" style="width: 135px;">Harga</th>
                                    <th class="text-center py-3 text-nowrap" style="width: 115px;">Diskon</th>
                                    <th class="text-end py-3 px-3 text-nowrap" style="width: 140px;">Subtotal</th>
                                    <th class="text-center py-3 text-nowrap" style="width: 115px;">Pajak (PPN)</th>
                                </tr>
                            </thead>
                            <tbody id="so_detail_items_tbody">
                                <!-- Items Injected Dynamically by JS -->
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Ringkasan Total & Ketentuan (Dengan Jarak Antar Card yang Lega) -->
                <div class="row g-3.5 g-md-4 mb-2">
                    <!-- Box Kiri: Syarat & Ketentuan, Catatan, Terbilang -->
                    <div class="col-md-6">
                        <div class="so-modern-card h-100 d-flex flex-column justify-content-between">
                            <div class="d-flex flex-column flex-grow-1">
                                <!-- Syarat & Ketentuan -->
                                <div class="mb-3 d-flex flex-column flex-grow-1" id="so_detail_syarat_ketentuan_wrap">
                                    <div class="so-card-header mb-2.5 pb-2 border-bottom border-secondary-subtle d-flex align-items-center gap-2">
                                        <span class="so-icon-box bg-primary bg-opacity-10 text-primary">
                                            <i class="bi bi-file-earmark-text"></i>
                                        </span>
                                        <span class="so-card-title text-body-secondary">Syarat &amp; Ketentuan</span>
                                    </div>
                                    <div class="small text-body p-3.5 rounded-3 bg-body border border-secondary-subtle font-sans flex-grow-1" id="so_detail_syarat_ketentuan" style="font-size: 0.82rem; line-height: 1.65; white-space: pre-line; min-height: 85px;">
                                        -
                                    </div>
                                </div>

                                <!-- Catatan Khusus (jika ada) -->
                                <div class="mb-3" id="so_detail_catatan_wrap" style="display: none;">
                                    <div class="so-card-header mb-2 pb-1.5 border-bottom border-secondary-subtle d-flex align-items-center gap-2">
                                        <span class="so-icon-box bg-warning bg-opacity-10 text-warning">
                                            <i class="bi bi-sticky"></i>
                                        </span>
                                        <span class="so-card-title text-warning">Catatan Tambahan</span>
                                    </div>
                                    <div class="small text-body p-3 rounded-3 bg-body border border-secondary-subtle fst-italic" id="so_detail_catatan" style="font-size: 0.82rem; line-height: 1.55;">
                                        -
                                    </div>
                                </div>
                            </div>

                            <!-- Terbilang Box Pinned at Bottom -->
                            <div class="p-3 px-3.5 rounded-3 bg-body border border-secondary-subtle mt-2.5 shadow-2xs">
                                <div class="d-flex align-items-center gap-1.5 mb-1.5">
                                    <i class="bi bi-chat-quote-fill text-primary" style="font-size: 0.85rem;"></i>
                                    <span class="fw-bold text-uppercase text-body-secondary" style="font-size: 0.72rem; letter-spacing: 0.5px;">Terbilang:</span>
                                </div>
                                <div class="fw-bold fst-italic text-body-emphasis ps-1" id="so_detail_terbilang" style="font-size: 0.85rem; line-height: 1.5;">-</div>
                            </div>
                        </div>
                    </div>

                    <!-- Box Kanan: Rincian Finansial -->
                    <div class="col-md-6">
                        <div class="so-modern-card h-100 d-flex flex-column justify-content-between">
                            <div>
                                <div class="so-card-header mb-3 pb-2.5 border-bottom border-secondary-subtle d-flex align-items-center gap-2">
                                    <span class="so-icon-box bg-primary bg-opacity-10 text-primary">
                                        <i class="bi bi-calculator-fill"></i>
                                    </span>
                                    <span class="so-card-title text-primary">Ringkasan Biaya</span>
                                </div>

                                <div class="d-flex flex-column mb-3 px-1">
                                    <div class="so-detail-row">
                                        <span class="text-body-secondary fw-semibold small">Subtotal Item</span>
                                        <span class="font-monospace fw-bold text-body-emphasis" id="so_detail_subtotal">Rp 0,00</span>
                                    </div>

                                    <div class="so-detail-row" id="so_detail_pajak_row" style="display: none;">
                                        <span class="text-body-secondary fw-semibold small" id="so_detail_pajak_label">Total Pajak (PPN)</span>
                                        <span class="font-monospace fw-bold text-info" id="so_detail_pajak">+ Rp 0,00</span>
                                    </div>

                                    <div class="so-detail-row" id="so_detail_ongkir_row" style="display: none;">
                                        <span class="text-body-secondary fw-semibold small">Biaya Kirim (Ongkir)</span>
                                        <span class="font-monospace fw-bold text-primary" id="so_detail_ongkir">+ Rp 0,00</span>
                                    </div>
                                </div>
                            </div>

                            <div>
                                <!-- Grand Total Box -->
                                <div class="d-flex justify-content-between align-items-center p-3.5 px-4 rounded-3 text-white mb-2.5 shadow-sm" style="background: linear-gradient(135deg, #1e3a8a 0%, #2563eb 100%);">
                                    <div>
                                        <span class="fw-bold fs-6 d-block text-white mb-0.5">Grand Total</span>
                                        <span class="small text-white-50" style="font-size: 0.72rem;">Total Akhir Tagihan</span>
                                    </div>
                                    <span class="font-monospace fw-bold fs-4 text-white" id="so_detail_grand_total">Rp 0,00</span>
                                </div>
                                
                                {{-- Baris DP (Muncul jika ada DP) --}}
                                <div id="so_detail_dp_section" style="display: none;">
                                    <div class="d-flex justify-content-between align-items-center small p-3 px-3.5 rounded-3 bg-body border border-secondary-subtle">
                                        <span class="text-body-secondary fw-semibold">Uang Muka (DP) <span id="so_detail_dp_label" class="font-monospace text-primary fw-semibold" style="font-size:0.75rem;"></span>:</span>
                                        <span class="font-monospace fw-bold text-primary fs-6" id="so_detail_dp_nominal">Rp 0,00</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            <!-- Modal Footer -->
            <div class="modal-footer border-top px-3 px-sm-4 py-3 d-flex justify-content-between align-items-center bg-body-tertiary">
                <div class="d-flex gap-2" id="so_detail_action_buttons">
                    <button type="button" class="btn btn-warning btn-sm rounded-pill px-3 py-1.5 fw-semibold d-none" id="btn_edit_order_from_detail">
                        <i class="bi bi-pencil-square me-1"></i> Edit Pesanan
                    </button>
                    <button type="button" class="btn btn-outline-danger btn-sm rounded-pill px-3 py-1.5 fw-semibold d-none" id="btn_cancel_order_from_detail">
                        <i class="bi bi-x-circle me-1"></i> Batalkan
                    </button>
                    @can('tambah-delivery-order')
                    <a href="{{ route('marketing.delivery-order.index') }}" class="btn btn-primary btn-sm rounded-pill px-3.5 py-1.5 fw-bold shadow-sm d-flex align-items-center gap-1.5" id="btn_do_from_order_detail" style="background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%); border: none;">
                        <i class="bi bi-truck"></i> Buat Surat Jalan
                    </a>
                    @endcan
                </div>
                <button type="button" class="btn btn-secondary btn-sm rounded-pill px-4 py-1.5 fw-semibold" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>
