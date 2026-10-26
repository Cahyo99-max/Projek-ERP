<!-- MODAL DETAIL RECEIPT (CINEMATIC DESIGN) -->
<div class="modal fade" id="modalDetailGR" role="dialog" aria-labelledby="modalDetailGRLabel">
    <div class="modal-dialog modal-fullscreen-xl-down" style="max-width: 96vw; width: 96vw;">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 24px; overflow: hidden;">
            <!-- CINEMATIC HERO HEADER -->
            <div class="modal-header border-0 py-4 px-4 position-relative overflow-hidden" style="background: linear-gradient(135deg, #0f172a 0%, #1e293b 60%, #020617 100%);">
                <div class="position-absolute top-0 end-0 p-3 opacity-10 pointer-events-none" style="transform: translate(20%, -20%) scale(2.2);">
                    <i class="bi bi-box-seam display-1 text-white"></i>
                </div>
                <div class="d-flex align-items-center justify-content-between w-100 me-3 position-relative" style="z-index: 2;">
                    <div class="d-flex align-items-center gap-3">
                        <div class="bg-primary bg-opacity-20 border border-primary border-opacity-25 rounded-4 d-flex align-items-center justify-content-center shadow-lg" style="width: 56px; height: 56px; backdrop-filter: blur(8px);">
                            <i class="bi bi-box-seam fs-3 text-white"></i>
                        </div>
                        <div>
                            <div class="d-flex align-items-center gap-2 mb-1">
                                <span class="badge bg-primary bg-opacity-20 text-white border border-primary border-opacity-25 rounded-pill px-3 py-1 fw-bold" style="font-size: 0.75rem; letter-spacing: 0.5px;">GOODS RECEIPT</span>
                                <span id="detail_status_badge">-</span>
                            </div>
                            <h4 class="modal-title fw-extrabold text-white mb-0" id="detail_nomor_gr" style="letter-spacing: -0.5px;">-</h4>
                        </div>
                    </div>
                    <div class="d-none d-md-flex align-items-center gap-4 text-white pe-3">
                        <div class="text-end">
                            <small class="text-white-50 d-block text-uppercase fw-semibold" style="font-size: 0.68rem; letter-spacing: 1px;">Tanggal Penerimaan</small>
                            <span class="fw-bold fs-6 text-white" id="detail_tanggal_gr">-</span>
                        </div>
                        <div class="border-start border-white border-opacity-10 h-100 py-3"></div>
                        <div class="text-end">
                            <small class="text-white-50 d-block text-uppercase fw-semibold" style="font-size: 0.68rem; letter-spacing: 1px;">No. PO Asal</small>
                            <span class="fw-bold fs-6 text-info" id="detail_nomor_po">-</span>
                        </div>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white position-relative" style="z-index: 2;" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body p-4">
                <!-- CANCELLATION NOTICE BANNER -->
                <div id="detail_cancellation_banner" class="alert alert-danger border-0 rounded-4 shadow-sm mb-4 p-3 d-none" style="background: linear-gradient(135deg, rgba(239, 68, 68, 0.12) 0%, rgba(185, 28, 28, 0.18) 100%); border-left: 5px solid #ef4444 !important;">
                    <div class="d-flex align-items-start gap-3">
                        <div class="bg-danger text-white rounded-3 d-flex align-items-center justify-content-center shadow-sm flex-shrink-0" style="width: 40px; height: 40px;">
                            <i class="bi bi-x-octagon-fill fs-5"></i>
                        </div>
                        <div class="flex-grow-1">
                            <div class="d-flex align-items-center gap-2 mb-1">
                                <span class="badge bg-danger text-white rounded-pill px-2.5 py-1 fw-bold" style="font-size: 0.72rem;">TRANSAKSI DIBATALKAN / VOID</span>
                                <span class="text-muted small" style="font-size: 0.75rem;"><i class="bi bi-clock-history me-1"></i><span id="detail_canceled_at">-</span></span>
                            </div>
                            <div class="fw-bold text-danger fs-6 mb-1">
                                Alasan: <span id="detail_cancellation_reason" class="text-dark fw-semibold">-</span>
                            </div>
                            <div class="small text-muted" style="font-size: 0.78rem;">
                                <i class="bi bi-person-x me-1"></i>Dibatalkan oleh: <span id="detail_canceler_name" class="fw-bold text-dark">-</span> • <span class="text-secondary">Stok gudang telah dikembalikan dan jurnal akuntansi telah dibatalkan secara otomatis.</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 3 INFO CARDS (FIXED LAYOUT) -->
                <div class="row g-3 mb-4">
                    <!-- CARD 1: INFORMASI PENERIMAAN -->
                    <div class="col-md-6 col-xl-4">
                        <div class="card h-100 border-0 shadow-sm rounded-4 overflow-hidden">
                            <div class="card-header border-0 py-2 px-3 d-flex align-items-center gap-2 detail-card-blue-header">
                                <div class="bg-primary bg-opacity-15 rounded-3 d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                                    <i class="bi bi-file-earmark-text text-primary fs-6"></i>
                                </div>
                                <h6 class="fw-bold mb-0 text-primary" style="font-size: 0.85rem;">Informasi Penerimaan</h6>
                            </div>
                            <div class="card-body p-3">
                                <div class="row g-2">
                                    <div class="col-6">
                                        <div class="detail-field-box">
                                            <div class="text-muted mb-1" style="font-size: 0.71rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;"><i class="bi bi-building me-1"></i>Perusahaan (PT)</div>
                                            <div class="fw-bold text-dark" style="font-size: 0.85rem;" id="detail_pt">-</div>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="detail-field-box">
                                            <div class="text-muted mb-1" style="font-size: 0.71rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;"><i class="bi bi-diagram-3 me-1"></i>Cabang</div>
                                            <div class="fw-bold text-dark" style="font-size: 0.85rem;" id="detail_cabang">-</div>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <div class="detail-field-box">
                                            <div class="text-muted mb-1" style="font-size: 0.71rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;"><i class="bi bi-inbox me-1"></i>Gudang Penerima</div>
                                            <div class="fw-bold text-dark" style="font-size: 0.88rem;" id="detail_gudang">-</div>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="detail-field-box">
                                            <div class="text-muted mb-1" style="font-size: 0.71rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;"><i class="bi bi-truck me-1"></i>Surat Jalan</div>
                                            <div class="fw-bold text-dark" style="font-size: 0.85rem;" id="detail_surat_jalan">-</div>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="detail-field-box">
                                            <div class="text-muted mb-1" style="font-size: 0.71rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;"><i class="bi bi-person-check me-1"></i>Pembuat / User</div>
                                            <div class="fw-bold text-dark" style="font-size: 0.85rem;" id="detail_pembuat">-</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- CARD 2: SUPPLIER & PO -->
                    <div class="col-md-6 col-xl-4">
                        <div class="card h-100 border-0 shadow-sm rounded-4 overflow-hidden">
                            <div class="card-header border-0 py-2 px-3 d-flex align-items-center gap-2 detail-card-green-header">
                                <div class="bg-success bg-opacity-15 rounded-3 d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                                    <i class="bi bi-shop text-success fs-6"></i>
                                </div>
                                <h6 class="fw-bold mb-0 text-success" style="font-size: 0.85rem;">Supplier & PO</h6>
                            </div>
                            <div class="card-body p-3">
                                <div class="row g-2">
                                    <div class="col-12">
                                        <div class="detail-field-box">
                                            <div class="text-muted mb-1" style="font-size: 0.71rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;"><i class="bi bi-briefcase me-1"></i>Nama Supplier</div>
                                            <div class="fw-bold text-dark" style="font-size: 0.88rem;" id="detail_supplier">-</div>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <div class="detail-field-box">
                                            <div class="text-muted mb-1" style="font-size: 0.71rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;"><i class="bi bi-kanban me-1"></i>Tender / Proyek</div>
                                            <div class="fw-bold text-info" style="font-size: 0.85rem;" id="detail_tender_name">-</div>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="detail-field-box">
                                            <div class="text-muted mb-1" style="font-size: 0.71rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;"><i class="bi bi-cash-coin me-1"></i>Uang Muka (DP)</div>
                                            <div class="fw-bold text-warning" style="font-size: 0.85rem;" id="detail_dp_info">-</div>
                                        </div>
                                    </div>
                                    <div class="col-6" id="detail_kurs_col">
                                        <div class="detail-field-box">
                                            <div class="text-muted mb-1" style="font-size: 0.71rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;"><i class="bi bi-currency-exchange me-1"></i>Kurs</div>
                                            <div class="fw-bold text-dark" style="font-size: 0.85rem;" id="detail_kurs">-</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- CARD 3: KETENTUAN PO -->
                    <div class="col-md-12 col-xl-4">
                        <div class="card h-100 border-0 shadow-sm rounded-4 overflow-hidden">
                            <div class="card-header border-0 py-2 px-3 d-flex align-items-center gap-2 detail-card-yellow-header">
                                <div class="bg-warning bg-opacity-15 rounded-3 d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                                    <i class="bi bi-percent text-warning fs-6"></i>
                                </div>
                                <h6 class="fw-bold mb-0 text-warning" style="font-size: 0.85rem;">Ketentuan PO</h6>
                            </div>
                            <div class="card-body p-3">
                                <div class="row g-2">
                                    <div class="col-6">
                                        <div class="detail-field-box">
                                            <div class="text-muted mb-1" style="font-size: 0.71rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;"><i class="bi bi-receipt me-1"></i>PPN / Pajak</div>
                                            <div class="fw-bold text-dark" style="font-size: 0.85rem;" id="detail_pajak_info">-</div>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="detail-field-box">
                                            <div class="text-muted mb-1" style="font-size: 0.71rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;"><i class="bi bi-box-arrow-in-right me-1"></i>Biaya Lain</div>
                                            <div class="fw-bold text-dark" style="font-size: 0.85rem;" id="detail_biaya_lain_info">-</div>
                                        </div>
                                    </div>
                                    <div class="col-12 d-none" id="detail_pph_wrapper">
                                        <div class="detail-field-box" style="border-color: rgba(245, 158, 11, 0.3); background: rgba(245, 158, 11, 0.04);">
                                            <div class="text-warning mb-1" style="font-size: 0.71rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;"><i class="bi bi-percent me-1"></i>Potongan PPh (Withholding)</div>
                                            <div class="fw-bold text-warning" style="font-size: 0.85rem;" id="detail_pph_info">-</div>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <div class="detail-field-box">
                                            <div class="text-muted mb-1" style="font-size: 0.71rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;"><i class="bi bi-chat-square-text me-1"></i>Catatan / Keterangan</div>
                                            <div class="fw-medium text-dark" style="font-size: 0.85rem; white-space: pre-wrap;" id="detail_keterangan">-</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ITEMS TABLE CARD -->
                <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-3">
                    <div class="card-header py-3 px-4 d-flex align-items-center justify-content-between border-bottom">
                        <div class="d-flex align-items-center gap-2">
                            <i class="bi bi-list-stars text-primary fs-5"></i>
                            <h6 class="fw-bold mb-0 text-dark">Daftar Barang yang Diterima</h6>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            @can('lihat-jurnal-gr')
                            <button type="button" class="btn btn-sm btn-outline-info rounded-pill px-3 fw-bold d-none shadow-sm" id="btn_open_modal_jurnal" style="font-size: 0.8rem; padding-top: 4px; padding-bottom: 4px;">
                                <i class="bi bi-journal-bookmark-fill me-1.5"></i>Lihat Jurnal Akuntansi
                            </button>
                            @endcan
                            <span class="badge bg-primary bg-opacity-10 text-primary rounded-pill px-3 py-1.5 fw-bold small" id="detail_total_items_badge">0 Item</span>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table align-middle table-modern mb-0">
                             <thead>
                                 <tr>
                                      <th width="20%">Nama Barang</th>
                                      <th width="13%" class="text-center">Lokasi Rak</th>
                                      <th width="8%" class="text-center">Qty PO</th>
                                      <th width="8%" class="text-center">Qty Diterima</th>
                                      <th width="8%" class="text-center">Qty Reject</th>
                                      <th width="11%" class="text-end">Harga Satuan</th>
                                      <th width="9%" class="text-end">Diskon Item</th>
                                      <th width="8%" class="text-center">PPN</th>
                                      <th width="7%" class="text-center">Satuan</th>
                                      <th width="13%" class="text-end">Total</th>
                                   </tr>
                             </thead>
                            <tbody id="detail_gr_items_tbody">
                                <!-- Loaded dynamically -->
                            </tbody>
                            <tfoot id="detail_gr_items_tfoot">
                                <!-- Loaded dynamically -->
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>

            <div class="modal-footer border-0 py-3 px-4 d-flex justify-content-between align-items-center">
                <div>
                    @php
                        $isAuthorizedAdmin = auth()->check() && (
                            auth()->user()->hasRole('Super Admin') || 
                            auth()->user()->hasRole('Superadmin') || 
                            auth()->user()->hasRole('superadmin') ||
                            auth()->user()->hasRole('Admin') || 
                            auth()->user()->hasRole('admin') || 
                            (property_exists(auth()->user(), 'role') && in_array(strtolower(strval(auth()->user()->role)), ['superadmin', 'admin']))
                        );
                    @endphp
                    @if($isAuthorizedAdmin)
                    <button type="button" class="btn btn-outline-danger btn-sm rounded-pill px-3 py-1.5 fw-semibold d-none" id="btn_cancel_from_detail" style="font-size: 0.82rem; border-color: rgba(239, 68, 68, 0.4);">
                        <i class="bi bi-x-octagon me-1"></i>Batalkan Transaksi (Void)
                    </button>
                    @endif
                </div>
                <div class="d-flex align-items-center gap-2">
                    <a href="#" id="btn_print_detail_gr" target="_blank" class="btn btn-primary rounded-pill px-4 shadow-sm fw-bold">
                        <i class="bi bi-printer me-1.5"></i>Cetak Surat Jalan Masuk
                    </a>
                    <button type="button" class="btn btn-secondary rounded-pill px-4 fw-semibold" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    $(document).ready(function () {

        // Move modal elements to body on document ready
        let detailModalEl = document.getElementById('modalDetailGR');
        if (detailModalEl && detailModalEl.parentElement !== document.body) {
            document.body.appendChild(detailModalEl);
        }

        // Safe helper escapeHtml
        window.escapeHtml = function (str) {
            if (str === null || str === undefined) return '';
            return String(str)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');
        };

        // ===== VIEW GR DETAIL =====
        $(document).on('click', '.btn-view-gr', function (e) {
            if (e) e.preventDefault();
            let id = $(this).attr('data-id') || $(this).data('id');
            if (!id) return;

            let modalEl = document.getElementById('modalDetailGR');

            // Reset modal & show instant spinner in table
            $('#detail_nomor_gr').text('Memuat...');
            $('#detail_tanggal_gr').text('...');
            $('#detail_nomor_po').text('...');
            $('#detail_status_badge').html('<span class="badge bg-secondary">Memuat...</span>');
            $('#detail_gr_items_tbody').html('<tr><td colspan="8" class="text-center py-4"><span class="spinner-border spinner-border-sm text-primary me-2"></span> Memuat detail penerimaan...</td></tr>');
            $('#detail_gr_items_tfoot').empty();

            let bsModal = bootstrap.Modal.getOrCreateInstance(modalEl);
            bsModal.show();

            $.get('/goods-receipt/' + id, function (res) {
                try {
                    if (!res || !res.gr) {
                        bsModal.hide();
                        Swal.fire('Gagal!', 'Data penerimaan barang tidak ditemukan.', 'error');
                        return;
                    }

                    let gr = res.gr;
                    let detailCurrencySymbol = (gr.po && gr.po.currency) ? gr.po.currency.simbol : 'Rp';
                    let detailKurs = parseFloat(gr.kurs) || (gr.po ? parseFloat(gr.po.kurs) : 1);

                    if (detailCurrencySymbol === 'Rp') {
                        $('#detail_kurs_col').addClass('d-none');
                    } else {
                        $('#detail_kurs_col').removeClass('d-none');
                        $('#detail_kurs').text(new Intl.NumberFormat('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 4 }).format(detailKurs));
                    }

                    // Header mapping
                    let statusBadgeHtml = '';
                    if (gr.status === 'Draft') {
                        statusBadgeHtml = '<span class="badge bg-warning text-dark border border-warning rounded-pill px-3 py-1 fw-bold"><i class="bi bi-pencil-square me-1"></i>Draft</span>';
                    } else if (gr.status === 'Dibatalkan') {
                        statusBadgeHtml = '<span class="badge bg-danger text-white border border-danger rounded-pill px-3 py-1 fw-bold shadow-sm"><i class="bi bi-x-circle me-1"></i>Dibatalkan</span>';
                    } else {
                        statusBadgeHtml = '<span class="badge bg-success text-white border border-success rounded-pill px-3 py-1 fw-bold"><i class="bi bi-check-circle me-1"></i>Diposting</span>';
                    }
                    $('#detail_status_badge').html(statusBadgeHtml);

                    // Cancellation Banner
                    if (gr.status === 'Dibatalkan') {
                        $('#detail_cancellation_banner').removeClass('d-none');
                        $('#detail_cancellation_reason').text(gr.alasan_pembatalan || 'Tidak ada catatan alasan khusus.');
                        $('#detail_canceler_name').text(gr.canceler ? gr.canceler.nama_lengkap : (gr.creator ? gr.creator.nama_lengkap : 'Admin'));
                        let canceledAtFormatted = '-';
                        if (gr.canceled_at) {
                            let d = new Date(gr.canceled_at);
                            canceledAtFormatted = isNaN(d.getTime()) ? gr.canceled_at : d.toLocaleString('id-ID', { dateStyle: 'medium', timeStyle: 'short' });
                        }
                        $('#detail_canceled_at').text(canceledAtFormatted);
                    } else {
                        $('#detail_cancellation_banner').addClass('d-none');
                    }

                    $('#detail_nomor_gr').text(gr.nomor_gr || '-');

                    let formattedDate = '-';
                    if (gr.tanggal_gr) {
                        let parts = String(gr.tanggal_gr).substring(0, 10).split('-');
                        if (parts.length === 3) {
                            formattedDate = parts[2] + '/' + parts[1] + '/' + parts[0];
                        } else {
                            formattedDate = String(gr.tanggal_gr).substring(0, 10);
                        }
                    }

                    $('#detail_tanggal_gr').text(formattedDate);
                    $('#detail_nomor_po').text(gr.po ? gr.po.nomor_po : '-');
                    let tenderData = gr.tender || (gr.po ? gr.po.tender : null);
                    let tenderDisplayText = '-';
                    if (tenderData) {
                        let tenderNo = tenderData.nomor_kontrak || tenderData.nomor_tender || '';
                        tenderDisplayText = (tenderNo ? tenderNo + ' - ' : '') + (tenderData.nama_tender || '');
                        if (!tenderDisplayText) tenderDisplayText = 'Tender #' + tenderData.id;
                    } else if (gr.po && gr.po.manual_pr_number) {
                        tenderDisplayText = gr.po.manual_pr_number;
                    }
                    $('#detail_tender_name').text(tenderDisplayText);
                    $('#detail_pt').text(gr.company ? gr.company.nama_perusahaan : (gr.po && gr.po.company ? gr.po.company.nama_perusahaan : 'PT Utama'));
                    $('#detail_cabang').text(gr.branch ? gr.branch.nama_cabang : (gr.po && gr.po.branch ? gr.po.branch.nama_cabang : 'Pusat'));
                    $('#detail_gudang').text(gr.gudang ? gr.gudang.nama_gudang : '-');
                    $('#detail_supplier').text(gr.po && gr.po.supplier ? gr.po.supplier.nama_supplier : '-');
                    $('#detail_surat_jalan').text(gr.nomor_surat_jalan ? gr.nomor_surat_jalan : '-');
                    $('#detail_pembuat').text(gr.creator ? gr.creator.nama_lengkap : '-');

                    let fmt = (v) => new Intl.NumberFormat('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(v);
                    let fmtQ = (v) => { let n = parseFloat(v) || 0; return Number.isInteger(n) ? n.toString() : n.toString(); };

                    let dpValue = (gr.po && gr.po.down_payment) ? parseFloat(gr.po.down_payment) : 0;
                    $('#detail_dp_info').text(dpValue > 0 ? (detailCurrencySymbol + ' ' + fmt(dpValue)) : 'Rp 0,00');

                    let uniqueTaxes = new Set();
                    if (gr.details && gr.details.length > 0) {
                        gr.details.forEach(function (det) {
                            let t = det.po_detail ? parseFloat(det.po_detail.tarif_ppn || 0) : 0;
                            uniqueTaxes.add(t);
                        });
                    }
                    let taxDisplayText = 'Non PPN (0%)';
                    if (uniqueTaxes.size === 1) {
                        let singleTax = Array.from(uniqueTaxes)[0];
                        taxDisplayText = singleTax > 0 ? `PPN (${singleTax}%)` : 'Non PPN (0%)';
                    } else if (uniqueTaxes.size > 1) {
                        taxDisplayText = 'PPN Campur (Per Item)';
                    }
                    $('#detail_pajak_info').text(taxDisplayText);
                    $('#detail_tax_info').text(taxDisplayText);

                    let rawBiayaLainDet = (gr.po && gr.po.biaya_lain) ? parseFloat(gr.po.biaya_lain) : 0;
                    let deskBiayaLainDet = (gr.po && gr.po.deskripsi_biaya_lain) ? gr.po.deskripsi_biaya_lain : 'Biaya Lain';
                    $('#detail_biaya_lain_info').text(rawBiayaLainDet > 0 ? `${detailCurrencySymbol} ${fmt(rawBiayaLainDet)} (${deskBiayaLainDet})` : '-');

                    let poPphTarif = (gr.po && gr.po.tarif_pph) ? parseFloat(gr.po.tarif_pph) : 0;
                    let poPphNominal = (gr.po && (gr.po.pph_nominal || gr.po.total_pph)) ? parseFloat(gr.po.pph_nominal || gr.po.total_pph) : 0;
                    let poPphNama = (gr.po && gr.po.pph_pajak) ? gr.po.pph_pajak.nama_pajak : (poPphTarif > 0 ? `PPh ${poPphTarif}%` : 'PPh');

                    if (poPphTarif > 0 || poPphNominal > 0) {
                        let pphText = `${poPphNama}`;
                        if (poPphTarif > 0) pphText += ` (${poPphTarif}%)`;
                        if (poPphNominal > 0) pphText += ` - ${detailCurrencySymbol} ${fmt(poPphNominal)}`;
                        $('#detail_pph_info').text(pphText);
                        $('#detail_pph_wrapper').removeClass('d-none');
                    } else {
                        $('#detail_pph_wrapper').addClass('d-none');
                    }

                    $('#detail_keterangan').text(gr.keterangan ? gr.keterangan : 'Tidak ada catatan.');
                    $('#detail_total_items_badge').text((gr.details && gr.details.length) ? (gr.details.length + ' Item') : '0 Item');
                    $('#btn_print_detail_gr').attr('href', '/goods-receipt/' + gr.id + '/print');

                    // Set up Void / Cancel button state (subdued in detail modal)
                    if (gr.status === 'Posted') {
                        $('#btn_cancel_from_detail')
                            .removeClass('d-none')
                            .data('id', gr.id)
                            .data('nama', gr.nomor_gr || '-');
                    } else {
                        $('#btn_cancel_from_detail')
                            .addClass('d-none')
                            .removeData('id')
                            .removeData('nama');
                    }

                    // Detail items mapping
                    let html = '';
                    if (gr.details && gr.details.length > 0) {
                        gr.details.forEach(function (det) {
                            let barangNama = det.barang ? det.barang.nama_barang : 'Barang';
                            let barangKode = det.barang ? det.barang.kode_barang : '-';
                            let fotoUrl = (det.barang && det.barang.primary_foto) ? ('/storage/' + det.barang.primary_foto.foto_path) : '';
                            
                            let poFaktor = det.po_detail && parseFloat(det.po_detail.faktor_pengali) > 0 ? parseFloat(det.po_detail.faktor_pengali) : 1;
                            let detFaktor = parseFloat(det.faktor_pengali || 1);

                            let satuanNama = det.satuan ? (det.satuan.singkatan || det.satuan.nama_satuan) : (det.barang && det.barang.satuan ? (det.barang.satuan.singkatan || det.barang.satuan.nama_satuan) : 'Pcs');
                            let satuanDasarNama = (det.barang && det.barang.satuan) ? (det.barang.satuan.singkatan || det.barang.satuan.nama_satuan) : satuanNama;
                            let poSatuanNama = (det.po_detail && det.po_detail.satuan) ? (det.po_detail.satuan.singkatan || det.po_detail.satuan.nama_satuan) : satuanNama;

                            let qtyPo = det.po_detail ? parseFloat(det.po_detail.jumlah || 0) : 0;
                            let qtyPoDasar = det.po_detail ? (parseFloat(det.po_detail.jumlah_dasar || 0) || (qtyPo * poFaktor)) : 0;
                            let qtyGr = parseFloat(det.jumlah || 0);
                            let qtyGrDasar = parseFloat(det.jumlah_dasar || (qtyGr * detFaktor));
                            let qtyReject = parseFloat(det.jumlah_reject || 0);
                            let qtyRejectDasar = parseFloat(det.jumlah_dasar_reject || (qtyReject * detFaktor));

                            let poHargaSatuan = det.po_detail ? parseFloat(det.po_detail.harga_satuan || 0) : 0;
                            let hargaSatuan = (poFaktor > 0) ? ((poHargaSatuan / poFaktor) * detFaktor) : poHargaSatuan;

                            let diskonItem = det.po_detail ? parseFloat(det.po_detail.diskon_item || 0) : 0;
                            let diskonPct = det.po_detail ? parseFloat(det.po_detail.diskon_persen || 0) : 0;
                            let tarifPpn = det.po_detail ? parseFloat(det.po_detail.tarif_ppn || 0) : 0;
                            let namaPpn = (det.po_detail && det.po_detail.pajak) ? det.po_detail.pajak.nama_pajak : (tarifPpn > 0 ? 'PPN ' + tarifPpn + '%' : 'Non PPN');

                            let netPrice = Math.max(0, hargaSatuan - diskonItem);
                            let lineGross = qtyGr * hargaSatuan;
                            let lineDiskon = qtyGr * diskonItem;
                            let lineNet = qtyGr * netPrice;
                            let linePpn = Math.round(lineNet * (tarifPpn / 100) * 100) / 100;
                            let totalHarga = lineNet;

                            let discHtml = '-';
                            if (diskonItem > 0) {
                                let pctLabel = diskonPct > 0 ? `-${diskonPct % 1 === 0 ? diskonPct.toFixed(0) : diskonPct.toFixed(2)}% ` : '';
                                discHtml = `<span class="text-danger fw-bold">${pctLabel}</span><br><small class="text-muted" style="font-size:0.72rem;">(${detailCurrencySymbol} ${fmt(diskonItem)})</small>`;
                            }

                            let ppnBadgeHtml = tarifPpn > 0 
                                ? `<span class="badge bg-warning bg-opacity-10 text-warning px-2 py-0.5 rounded-pill fw-semibold" style="font-size: 0.72rem;">${window.escapeHtml(namaPpn)}</span>` 
                                : `<span class="badge bg-light text-secondary border px-2 py-0.5 rounded-pill" style="font-size: 0.72rem;">0%</span>`;
                            if (linePpn > 0) {
                                ppnBadgeHtml += `<br><small class="text-muted" style="font-size:0.68rem;">+ ${detailCurrencySymbol} ${fmt(linePpn)}</small>`;
                            }

                            let rakBadgeHtml = det.lokasi_rak
                                ? ('<span class="badge bg-success bg-opacity-10 text-success fw-semibold" style="font-size: 0.78rem;"><i class="bi bi-geo-alt me-1"></i>[' + window.escapeHtml(det.lokasi_rak.kode_rak) + '] ' + window.escapeHtml(det.lokasi_rak.nama_rak) + '</span>')
                                : '<span class="text-muted small">-</span>';

                            let rejectBadgeHtml = qtyReject > 0
                                ? ('<span class="badge bg-danger bg-opacity-10 text-danger fw-bold" style="font-size: 0.78rem;">' + fmtQ(qtyReject) + '</span>')
                                : '<span class="text-muted small">0</span>';

                            let hargaIdrSubHtml = detailCurrencySymbol !== 'Rp' ? (`<br><small class="text-muted" style="font-size: 0.72rem;">Rp ${fmt(hargaSatuan * detailKurs)}</small>`) : '';
                            let totalIdrSubHtml = detailCurrencySymbol !== 'Rp' ? (`<br><small class="text-muted" style="font-size: 0.72rem;">Rp ${fmt(totalHarga * detailKurs)}</small>`) : '';
                            let fotoHtml = fotoUrl ? (`<img src="${fotoUrl}" alt="${window.escapeHtml(barangNama)}" class="product-img zoomable-img" style="cursor: zoom-in;">`) : '<i class="bi bi-image text-muted"></i>';

                            html += `<tr>
                                <td>
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="product-img-wrapper">${fotoHtml}</div>
                                        <div>
                                            <span class="fw-semibold text-dark">${window.escapeHtml(barangNama)}</span><br>
                                            <small class="text-muted">${window.escapeHtml(barangKode)}</small>
                                        </div>
                                    </div>
                                </td>
                                <td class="text-center">${rakBadgeHtml}</td>
                                <td class="text-center fw-medium text-secondary">
                                    ${fmtQ(qtyPo)} <small class="text-muted">${window.escapeHtml(poSatuanNama)}</small>
                                    ${poFaktor > 1 ? `<br><small class="text-muted" style="font-size: 0.68rem;">(${fmtQ(qtyPoDasar)} ${window.escapeHtml(satuanDasarNama)})</small>` : ''}
                                </td>
                                <td class="text-center fw-bold text-dark">
                                    ${fmtQ(qtyGr)}
                                    ${detFaktor > 1 ? `<br><small class="text-muted" style="font-size: 0.68rem;">(= ${fmtQ(qtyGrDasar)} ${window.escapeHtml(satuanDasarNama)})</small>` : ''}
                                </td>
                                <td class="text-center">
                                    ${rejectBadgeHtml}
                                    ${qtyReject > 0 && detFaktor > 1 ? `<br><small class="text-muted" style="font-size: 0.68rem;">(= ${fmtQ(qtyRejectDasar)} ${window.escapeHtml(satuanDasarNama)})</small>` : ''}
                                </td>
                                <td class="text-end fw-medium">${detailCurrencySymbol} ${fmt(hargaSatuan)}${hargaIdrSubHtml}</td>
                                <td class="text-end fw-medium">${discHtml}</td>
                                <td class="text-center text-nowrap">${ppnBadgeHtml}</td>
                                <td class="text-center fw-semibold text-secondary small">${window.escapeHtml(satuanNama)}</td>
                                <td class="text-end fw-bold text-success">${detailCurrencySymbol} ${fmt(totalHarga)}${totalIdrSubHtml}</td>
                            </tr>`;
                        });
                    } else {
                        html = '<tr><td colspan="10" class="text-center text-muted py-4">Tidak ada rincian barang.</td></tr>';
                    }

                    // Tfoot calculation
                    let subtotalGross = 0;
                    let totalDiskonBarang = 0;
                    let totalDpp = 0;
                    let totalTaxVal = 0;

                    if (gr.details) {
                        gr.details.forEach(function (det) {
                            let qtyGr = parseFloat(det.jumlah || 0);
                            let poFaktor = det.po_detail && parseFloat(det.po_detail.faktor_pengali) > 0 ? parseFloat(det.po_detail.faktor_pengali) : 1;
                            let detFaktor = parseFloat(det.faktor_pengali || 1);
                            let poHargaSatuan = det.po_detail ? parseFloat(det.po_detail.harga_satuan || 0) : 0;
                            let hargaSatuan = (poFaktor > 0) ? ((poHargaSatuan / poFaktor) * detFaktor) : poHargaSatuan;
                            let diskonItem = det.po_detail ? parseFloat(det.po_detail.diskon_item || 0) : 0;
                            let tarifPpn = det.po_detail ? parseFloat(det.po_detail.tarif_ppn || 0) : 0;

                            let netPrice = Math.max(0, hargaSatuan - diskonItem);
                            let lineGross = qtyGr * hargaSatuan;
                            let lineDiskon = qtyGr * diskonItem;
                            let lineNet = qtyGr * netPrice;
                            let linePpn = Math.round(lineNet * (tarifPpn / 100) * 100) / 100;

                            subtotalGross += lineGross;
                            totalDiskonBarang += lineDiskon;
                            totalDpp += lineNet;
                            totalTaxVal += linePpn;
                        });
                    }

                    subtotalGross = Math.round(subtotalGross * 100) / 100;
                    totalDiskonBarang = Math.round(totalDiskonBarang * 100) / 100;
                    totalDpp = Math.round(totalDpp * 100) / 100;
                    totalTaxVal = Math.round(totalTaxVal * 100) / 100;

                    let poSubtotal = gr.po ? parseFloat(gr.po.subtotal || 0) : 0;
                    let ratio = (poSubtotal > 0) ? (totalDpp / poSubtotal) : 0;
                    let isFull = Math.abs(ratio - 1) < 0.00001;

                    let rawBiayaLain = gr.po ? parseFloat(gr.po.biaya_lain || 0) : 0;
                    let biayaLain = isFull ? rawBiayaLain : (rawBiayaLain * ratio);
                    let deskripsiBiayaLain = (gr.po && gr.po.deskripsi_biaya_lain) ? gr.po.deskripsi_biaya_lain : 'Biaya Lain-lain';
                    let dpRatioValue = isFull ? dpValue : (dpValue * ratio);

                    let pphRatioValue = 0;
                    if (poPphTarif > 0) {
                        pphRatioValue = Math.round(totalDpp * (poPphTarif / 100) * 100) / 100;
                    } else if (poPphNominal > 0) {
                        pphRatioValue = isFull ? poPphNominal : Math.round(poPphNominal * ratio * 100) / 100;
                    }

                    let totalSebelumDp = Math.max(0, totalDpp + totalTaxVal + biayaLain - pphRatioValue);
                    let finalGrandTotal = Math.max(0, totalSebelumDp - dpRatioValue);

                    let subtotalIdrSubHtml = detailCurrencySymbol !== 'Rp' ? (`<br><small class="text-muted" style="font-size: 0.7rem;">Rp ${fmt(subtotalGross * detailKurs)}</small>`) : '';
                    let finalGrandTotalIdrSubHtml = detailCurrencySymbol !== 'Rp' ? (`<br><small class="text-muted" style="font-size: 0.75rem;">Rp ${fmt(finalGrandTotal * detailKurs)}</small>`) : '';

                    let tfootHtml = `<tr>
                        <td colspan="9" class="text-end fw-semibold text-secondary small py-2">Subtotal Barang (Kotor):</td>
                        <td class="text-end fw-bold text-dark py-2">${detailCurrencySymbol} ${fmt(subtotalGross)}${subtotalIdrSubHtml}</td>
                    </tr>`;

                    if (totalDiskonBarang > 0) {
                        let discIdrSubHtml = detailCurrencySymbol !== 'Rp' ? (`<br><small class="text-muted" style="font-size: 0.7rem;">-Rp ${fmt(totalDiskonBarang * detailKurs)}</small>`) : '';
                        tfootHtml += `<tr>
                            <td colspan="9" class="text-end fw-semibold text-danger small py-1">Total Diskon Item:</td>
                            <td class="text-end fw-bold text-danger py-1">-${detailCurrencySymbol} ${fmt(totalDiskonBarang)}${discIdrSubHtml}</td>
                        </tr>`;
                    }

                    if (totalTaxVal > 0) {
                        let taxIdrSubHtml = detailCurrencySymbol !== 'Rp' ? (`<br><small class="text-muted" style="font-size: 0.7rem;">Rp ${fmt(totalTaxVal * detailKurs)}</small>`) : '';
                        tfootHtml += `<tr>
                            <td colspan="9" class="text-end fw-semibold text-secondary small py-1">Total PPN (Pajak):</td>
                            <td class="text-end fw-bold text-dark py-1">${detailCurrencySymbol} ${fmt(totalTaxVal)}${taxIdrSubHtml}</td>
                        </tr>`;
                    }

                    if (pphRatioValue > 0 || poPphNominal > 0) {
                        let pphIdrSubHtml = detailCurrencySymbol !== 'Rp' ? (`<br><small class="text-muted" style="font-size: 0.7rem;">-Rp ${fmt(pphRatioValue * detailKurs)}</small>`) : '';
                        tfootHtml += `<tr>
                            <td colspan="9" class="text-end fw-semibold text-warning small py-1"><i class="bi bi-percent me-1"></i>${window.escapeHtml(poPphNama)}:</td>
                            <td class="text-end fw-bold text-warning py-1">-${detailCurrencySymbol} ${fmt(pphRatioValue)}${pphIdrSubHtml}</td>
                        </tr>`;
                    }

                    if (biayaLain > 0) {
                        let biayaLainIdrSubHtml = detailCurrencySymbol !== 'Rp' ? (`<br><small class="text-muted" style="font-size: 0.7rem;">Rp ${fmt(biayaLain * detailKurs)}</small>`) : '';
                        tfootHtml += `<tr>
                            <td colspan="9" class="text-end fw-semibold text-secondary small py-1">${window.escapeHtml(deskripsiBiayaLain)}:</td>
                            <td class="text-end fw-bold text-dark py-1">${detailCurrencySymbol} ${fmt(biayaLain)}${biayaLainIdrSubHtml}</td>
                        </tr>`;
                    }

                    let totalSebelumDpIdrSubHtml = detailCurrencySymbol !== 'Rp' ? (`<br><small class="text-muted" style="font-size: 0.7rem;">Rp ${fmt(totalSebelumDp * detailKurs)}</small>`) : '';
                    if (dpRatioValue > 0) {
                        tfootHtml += `<tr>
                            <td colspan="9" class="text-end fw-semibold text-info small py-1">Total Sebelum DP:</td>
                            <td class="text-end fw-bold text-info py-1">${detailCurrencySymbol} ${fmt(totalSebelumDp)}${totalSebelumDpIdrSubHtml}</td>
                        </tr>`;
                    }

                    let dpText = dpRatioValue > 0 ? (`-${detailCurrencySymbol} ${fmt(dpRatioValue)}`) : `${detailCurrencySymbol} 0,00`;
                    let dpIdrSubHtml = (detailCurrencySymbol !== 'Rp') ? (`<br><small class="text-muted" style="font-size: 0.7rem;">${dpRatioValue > 0 ? '-Rp ' : 'Rp '}${fmt(dpRatioValue * detailKurs)}</small>`) : '';
                    tfootHtml += `<tr>
                        <td colspan="9" class="text-end fw-semibold text-warning small py-1"><i class="bi bi-cash-coin me-1"></i>Uang Muka (DP PO):</td>
                        <td class="text-end fw-bold text-warning py-1">${dpText}${dpIdrSubHtml}</td>
                    </tr>`;

                    tfootHtml += `<tr>
                        <td colspan="9" class="text-end fw-bold text-dark py-3 border-top border-2">Total Akhir Penerimaan:</td>
                        <td class="text-end fw-extrabold text-success fs-6 py-3 border-top border-2">${detailCurrencySymbol} ${fmt(finalGrandTotal)}${finalGrandTotalIdrSubHtml}</td>
                    </tr>`;

                    $('#detail_gr_items_tbody').html(html);
                    $('#detail_gr_items_tfoot').html(tfootHtml);

                    // Render Jurnal Akuntansi if available and user has permission
                    const canViewGrJournal = {{ (auth()->check() && auth()->user()->can('lihat-jurnal-gr')) ? 'true' : 'false' }};
                    if (canViewGrJournal && gr.jurnal && gr.jurnal.details && gr.jurnal.details.length > 0) {
                        $('#btn_open_modal_jurnal').removeClass('d-none');
                        let supplierNameVal = (gr.po && gr.po.supplier) ? (gr.po.supplier.nama_supplier || '-') : '-';
                        let totalD = 0, totalK = 0;
                        gr.jurnal.details.forEach(function (jd) {
                            totalD += parseFloat(jd.debit || 0);
                            totalK += parseFloat(jd.kredit || 0);
                        });

                        window.activeJournalData = {
                            nomor_jurnal: gr.jurnal.nomor_jurnal || 'JNL',
                            nomor_gr: gr.nomor_gr || '-',
                            nomor_tender: gr.jurnal.no_tender || (tenderDisplayText !== '-' ? tenderDisplayText : null),
                            tanggal: formattedDate || '-',
                            supplier: supplierNameVal || '-',
                            rows: gr.jurnal.details || [],
                            totalDebit: totalD,
                            totalKredit: totalK
                        };
                    } else {
                        $('#btn_open_modal_jurnal').addClass('d-none');
                        window.activeJournalData = null;
                    }
                } catch (err) {
                    console.error('Error rendering GR Detail Modal:', err);
                    Swal.fire('Gagal!', 'Terjadi kesalahan saat menampilkan detail: ' + err.message, 'error');
                }
            }).fail(function (xhr) {
                let msg = 'Gagal memuat detail penerimaan barang.';
                if (xhr.status === 403) {
                    msg = 'Akun Anda tidak memiliki izin untuk melihat Penerimaan Barang.';
                }
                Swal.fire('Gagal!', msg, 'error');
            });
        });

        // Open Modal Jurnal via SweetAlert2 (Layered & Conflict-Free)
        $(document).on('click', '#btn_open_modal_jurnal', function (e) {
            e.preventDefault();
            e.stopPropagation();

            if (!window.activeJournalData || !window.activeJournalData.rows || window.activeJournalData.rows.length === 0) {
                Swal.fire('Info', 'Data jurnal belum tersedia untuk penerimaan ini.', 'info');
                return;
            }

            let fmt = (v) => new Intl.NumberFormat('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(v);
            let jData = window.activeJournalData;

            let rowsHtml = '';
            jData.rows.forEach(function (jd) {
                let code = jd.account ? jd.account.code : '-';
                let name = jd.account ? jd.account.name : 'Akun';
                let d = parseFloat(jd.debit || 0);
                let k = parseFloat(jd.kredit || 0);
                rowsHtml += `
                    <tr style="border-bottom: 1px solid rgba(255,255,255,0.08);">
                        <td style="padding: 10px 14px; text-align: left; font-weight: 700; color: #38bdf8; font-family: monospace; font-size: 0.88rem;">${window.escapeHtml(code)}</td>
                        <td style="padding: 10px 14px; text-align: left; font-weight: 600; color: #f1f5f9; font-size: 0.88rem;">${window.escapeHtml(name)}</td>
                        <td style="padding: 10px 14px; text-align: right; font-weight: 700; color: #4ade80; font-size: 0.88rem;">${d > 0 ? ('Rp ' + fmt(d)) : '-'}</td>
                        <td style="padding: 10px 14px; text-align: right; font-weight: 700; color: #f87171; font-size: 0.88rem;">${k > 0 ? ('Rp ' + fmt(k)) : '-'}</td>
                    </tr>
                `;
            });

            let tenderHeaderCol = jData.nomor_tender ? `
                <div>
                    <div style="font-size: 0.72rem; color: #94a3b8; font-weight: 600; text-transform: uppercase;">Proyek / Tender</div>
                    <div style="font-size: 0.9rem; color: #38bdf8; font-weight: 700;">${window.escapeHtml(jData.nomor_tender)}</div>
                </div>` : '';

            let swalHtml = `
                <div style="text-align: left; font-family: inherit;">
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(130px, 1fr)); gap: 10px; margin-bottom: 16px; background: rgba(15, 23, 42, 0.6); padding: 12px 16px; border-radius: 12px; border: 1px solid rgba(255,255,255,0.1);">
                        <div>
                            <div style="font-size: 0.72rem; color: #94a3b8; font-weight: 600; text-transform: uppercase;">Nomor Jurnal</div>
                            <div style="font-size: 0.9rem; color: #38bdf8; font-weight: 700;">${window.escapeHtml(jData.nomor_jurnal)}</div>
                        </div>
                        <div>
                            <div style="font-size: 0.72rem; color: #94a3b8; font-weight: 600; text-transform: uppercase;">Nomor Penerimaan</div>
                            <div style="font-size: 0.9rem; color: #e2e8f0; font-weight: 700;">${window.escapeHtml(jData.nomor_gr)}</div>
                        </div>
                        ${tenderHeaderCol}
                        <div>
                            <div style="font-size: 0.72rem; color: #94a3b8; font-weight: 600; text-transform: uppercase;">Tanggal</div>
                            <div style="font-size: 0.9rem; color: #e2e8f0; font-weight: 700;">${window.escapeHtml(jData.tanggal)}</div>
                        </div>
                        <div>
                            <div style="font-size: 0.72rem; color: #94a3b8; font-weight: 600; text-transform: uppercase;">Supplier</div>
                            <div style="font-size: 0.9rem; color: #e2e8f0; font-weight: 700;">${window.escapeHtml(jData.supplier)}</div>
                        </div>
                        <div>
                            <div style="font-size: 0.72rem; color: #94a3b8; font-weight: 600; text-transform: uppercase;">Status</div>
                            <div><span style="display: inline-block; background: rgba(34, 197, 94, 0.2); color: #4ade80; border: 1px solid rgba(34, 197, 94, 0.4); padding: 2px 8px; border-radius: 20px; font-size: 0.75rem; font-weight: 700;">Balanced</span></div>
                        </div>
                    </div>

                    <div style="border-radius: 12px; overflow: hidden; border: 1px solid rgba(255,255,255,0.1); background: #0f172a;">
                        <table style="width: 100%; border-collapse: collapse;">
                            <thead>
                                <tr style="background: #1e293b; color: #94a3b8; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.5px;">
                                    <th style="padding: 10px 14px; text-align: left;">KODE AKUN</th>
                                    <th style="padding: 10px 14px; text-align: left;">NAMA AKUN</th>
                                    <th style="padding: 10px 14px; text-align: right; color: #4ade80;">DEBIT (IDR)</th>
                                    <th style="padding: 10px 14px; text-align: right; color: #f87171;">KREDIT (IDR)</th>
                                </tr>
                            </thead>
                            <tbody>
                                ${rowsHtml}
                            </tbody>
                            <tfoot>
                                <tr style="background: #1e293b; border-top: 2px solid rgba(255,255,255,0.2);">
                                    <td colspan="2" style="padding: 12px 14px; text-align: right; font-weight: 800; color: #f1f5f9; font-size: 0.85rem;">TOTAL JURNAL (BALANCED):</td>
                                    <td style="padding: 12px 14px; text-align: right; font-weight: 800; color: #4ade80; font-size: 0.95rem;">Rp ${fmt(jData.totalDebit)}</td>
                                    <td style="padding: 12px 14px; text-align: right; font-weight: 800; color: #f87171; font-size: 0.95rem;">Rp ${fmt(jData.totalKredit)}</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            `;

            Swal.fire({
                title: '<div style="display: flex; align-items: center; justify-content: center; gap: 8px; font-size: 1.25rem; font-weight: 800; color: #fff;"><i class="bi bi-journal-text" style="color: #38bdf8;"></i> Jurnal Akuntansi (General Ledger)</div>',
                html: swalHtml,
                width: '820px',
                background: '#1e293b',
                color: '#fff',
                showCloseButton: true,
                showCancelButton: false,
                confirmButtonText: '<i class="bi bi-x-circle me-1"></i> Tutup Jurnal',
                confirmButtonColor: '#0284c7',
                customClass: {
                    popup: 'rounded-4 shadow-lg border border-secondary border-opacity-25'
                }
            });
        });

        // ===== CANCEL GR FROM DETAIL MODAL =====
        $(document).on('click', '#btn_cancel_from_detail', function (e) {
            e.preventDefault();
            let id = $(this).data('id');
            let nama = $(this).data('nama') || '-';
            let detailModalEl = document.getElementById('modalDetailGR');

            // Deactivate Bootstrap Modal focus trap so SweetAlert input can be typed freely
            if (detailModalEl) {
                detailModalEl.removeAttribute('tabindex');
                if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                    const inst = bootstrap.Modal.getInstance(detailModalEl) || bootstrap.Modal.getOrCreateInstance(detailModalEl);
                    if (inst && inst._focustrap) {
                        inst._focustrap.deactivate();
                    }
                }
            }
            $(document).off('focusin.bs.modal focusin.modal');

            Swal.fire({
                title: 'Batalkan Penerimaan Barang (Void)?',
                html: `
                    <div class="text-start">
                        <div class="p-3 rounded-3 mb-3" style="background: rgba(255, 255, 255, 0.05); border: 1px solid rgba(255, 255, 255, 0.1);">
                            <div class="d-flex align-items-center gap-2 mb-1">
                                <i class="bi bi-exclamation-triangle-fill text-warning fs-6"></i>
                                <span style="color: #ffffff; font-weight: 700; font-size: 13.5px;">Nomor: ${window.escapeHtml(nama)}</span>
                            </div>
                            <div style="color: #cbd5e1; font-size: 12px; line-height: 1.5;">
                                Stok gudang akan ditarik kembali dan jurnal akuntansi penerimaan otomatis dibatalkan.
                            </div>
                        </div>
                        <label for="swal_input_alasan_gr" class="d-block mb-1.5 fw-bold" style="color: #ffffff !important; font-size: 13px;">
                            Alasan Pembatalan <span style="color: #fb7185;">*</span>
                        </label>
                        <textarea id="swal_input_alasan_gr" class="form-control" rows="2" 
                            placeholder="Tulis alasan pembatalan (5 - 40 karakter)..."
                            maxlength="40" style="color: #ffffff !important; background-color: #0b1320 !important; border: 1px solid rgba(255, 255, 255, 0.28) !important; font-size: 13px; border-radius: 8px; line-height: 1.5; padding: 10px 12px; resize: none;"></textarea>
                        <div class="d-flex justify-content-between align-items-center mt-1.5" style="color: #94a3b8; font-size: 11.5px;">
                            <span><i class="bi bi-shield-lock me-1 text-info"></i>Wajib diisi (5 - 40 karakter)</span>
                            <span id="swal_alasan_gr_counter" style="font-family: monospace; color: #e2e8f0; font-weight: 600;">0 / 40</span>
                        </div>
                    </div>
                `,
                icon: 'warning',
                background: '#111b2b',
                color: '#f8fafc',
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                cancelButtonColor: '#475569',
                confirmButtonText: '<i class="bi bi-x-octagon me-1"></i> Ya, Batalkan (Void)',
                cancelButtonText: '<i class="bi bi-arrow-left me-1"></i> Tutup',
                reverseButtons: true,
                focusConfirm: false,
                didOpen: () => {
                    $(document).off('focusin.bs.modal focusin.modal');
                    const textarea = document.getElementById('swal_input_alasan_gr');
                    const counter = document.getElementById('swal_alasan_gr_counter');
                    if (textarea) {
                        setTimeout(() => {
                            textarea.focus();
                        }, 60);

                        // Prevent focus bubbling to the underlying Bootstrap modal
                        textarea.addEventListener('keydown', function(ev) {
                            ev.stopPropagation();
                        });
                        textarea.addEventListener('keypress', function(ev) {
                            ev.stopPropagation();
                        });
                        textarea.addEventListener('keyup', function(ev) {
                            ev.stopPropagation();
                        });
                        textarea.addEventListener('focusin', function(ev) {
                            ev.stopPropagation();
                        });
                        textarea.addEventListener('input', function() {
                            if (counter) counter.textContent = `${this.value.length} / 40`;
                        });
                    }
                },
                willClose: () => {
                    // Restore Bootstrap modal focus trap if modal is still open
                    if (detailModalEl) {
                        detailModalEl.setAttribute('tabindex', '-1');
                        if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                            const inst = bootstrap.Modal.getInstance(detailModalEl);
                            if (inst && inst._focustrap && $(detailModalEl).hasClass('show')) {
                                inst._focustrap.activate();
                            }
                        }
                    }
                },
                preConfirm: () => {
                    const textarea = document.getElementById('swal_input_alasan_gr');
                    const val = textarea ? textarea.value.trim() : '';
                    if (!val) {
                        Swal.showValidationMessage('Alasan pembatalan wajib diisi!');
                        return false;
                    }
                    if (val.length < 5) {
                        Swal.showValidationMessage('Alasan pembatalan minimal 5 karakter!');
                        return false;
                    }
                    if (val.length > 40) {
                        Swal.showValidationMessage('Alasan pembatalan maksimal 40 karakter! (saat ini: ' + val.length + ' karakter)');
                        return false;
                    }
                    return val;
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    let alasan = result.value;

                    Swal.fire({
                        title: 'Memproses Pembatalan...',
                        text: 'Mohon tunggu, sistem sedang membalikkan stok dan membatalkan jurnal...',
                        allowOutsideClick: false,
                        didOpen: () => { Swal.showLoading(); }
                    });

                    $.ajax({
                        url: "/goods-receipt/" + id + "/cancel",
                        type: 'POST',
                        data: {
                            _token: $('meta[name="csrf-token"]').attr('content') || '{{ csrf_token() }}',
                            alasan_pembatalan: alasan
                        },
                        success: function (res) {
                            $('#modalDetailGR').modal('hide');
                            Swal.fire({
                                title: 'Berhasil Dibatalkan!',
                                text: res.message || 'Penerimaan barang berhasil dibatalkan (void).',
                                icon: 'success',
                                confirmButtonColor: '#10b981'
                            }).then(() => {
                                if (typeof window.reloadTableGR === 'function') {
                                    window.reloadTableGR();
                                } else {
                                    location.reload();
                                }
                            });
                        },
                        error: function (xhr) {
                            let msg = 'Terjadi kesalahan saat membatalkan transaksi.';
                            if (xhr.responseJSON && xhr.responseJSON.message) {
                                msg = xhr.responseJSON.message;
                            } else if (xhr.status === 403) {
                                msg = 'Akun Anda tidak memiliki hak akses untuk membatalkan penerimaan barang.';
                            }
                            Swal.fire('Gagal Membatalkan!', msg, 'error');
                        }
                    });
                }
            });
        });

    }); // end document.ready [modal_detail]
</script>
@endpush
