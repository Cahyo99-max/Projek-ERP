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
                                      <th width="22%">Nama Barang</th>
                                      <th width="15%" class="text-center">Lokasi Rak</th>
                                      <th width="9%" class="text-center">Qty PO</th>
                                      <th width="9%" class="text-center">Qty Diterima</th>
                                      <th width="9%" class="text-center">Qty Reject</th>
                                      <th width="14%" class="text-end">Harga Satuan</th>
                                      <th width="8%" class="text-center">Satuan</th>
                                      <th width="14%" class="text-end">Total</th>
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

            <div class="modal-footer border-0 py-3 px-4 d-flex justify-content-end gap-2">
                <a href="#" id="btn_print_detail_gr" target="_blank" class="btn btn-primary rounded-pill px-4 shadow-sm fw-bold">
                    <i class="bi bi-printer me-1.5"></i>Cetak Surat Jalan Masuk
                </a>
                <button type="button" class="btn btn-secondary rounded-pill px-4 fw-semibold" data-bs-dismiss="modal">Tutup</button>
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
                    let statusBadgeHtml = gr.status === 'Draft'
                        ? '<span class="badge bg-warning text-dark border border-warning rounded-pill px-3 py-1 fw-bold"><i class="bi bi-pencil-square me-1"></i>Draft</span>'
                        : '<span class="badge bg-success text-white border border-success rounded-pill px-3 py-1 fw-bold"><i class="bi bi-check-circle me-1"></i>Diposting</span>';
                    $('#detail_status_badge').html(statusBadgeHtml);
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
                    $('#detail_tender_name').text(gr.po && gr.po.tender ? gr.po.tender.nama_tender : (gr.po && gr.po.manual_pr_number ? gr.po.manual_pr_number : '-'));
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

                    let taxRateDet = (gr.po && gr.po.pajak) ? parseFloat(gr.po.pajak.tarif) : 0;
                    let taxNameDet = (gr.po && gr.po.pajak) ? gr.po.pajak.nama_pajak : 'PPN';
                    let taxDisplayText = taxRateDet > 0 ? `${taxNameDet} (${taxRateDet}%)` : 'Non PPN (0%)';
                    $('#detail_pajak_info').text(taxDisplayText);
                    $('#detail_tax_info').text(taxDisplayText);

                    let rawBiayaLainDet = (gr.po && gr.po.biaya_lain) ? parseFloat(gr.po.biaya_lain) : 0;
                    let deskBiayaLainDet = (gr.po && gr.po.deskripsi_biaya_lain) ? gr.po.deskripsi_biaya_lain : 'Biaya Lain';
                    $('#detail_biaya_lain_info').text(rawBiayaLainDet > 0 ? `${detailCurrencySymbol} ${fmt(rawBiayaLainDet)} (${deskBiayaLainDet})` : '-');

                    $('#detail_keterangan').text(gr.keterangan ? gr.keterangan : 'Tidak ada catatan.');
                    $('#detail_total_items_badge').text((gr.details && gr.details.length) ? (gr.details.length + ' Item') : '0 Item');
                    $('#btn_print_detail_gr').attr('href', '/goods-receipt/' + gr.id + '/print');

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
                            let diskonPct = 0;
                            if (det.po_detail && parseFloat(det.po_detail.diskon_persen || 0) > 0) {
                                diskonPct = parseFloat(det.po_detail.diskon_persen);
                            } else if (gr.po && gr.po.diskon_persen) {
                                if (parseFloat(gr.po.diskon_persen || 0) > 0) {
                                    diskonPct  = parseFloat(gr.po.diskon_persen);
                                    diskonItem = hargaSatuan * (diskonPct / 100);
                                } else if (parseFloat(gr.po.total_diskon || 0) > 0 && parseFloat(gr.po.subtotal || 0) > 0) {
                                    diskonItem = (parseFloat(gr.po.total_diskon) / parseFloat(gr.po.subtotal)) * hargaSatuan;
                                    diskonPct = (hargaSatuan > 0) ? (diskonItem / hargaSatuan) * 100 : 0;
                                }
                            } else if (diskonItem > 0 && hargaSatuan > 0) {
                                diskonPct = (diskonItem / hargaSatuan) * 100;
                            }

                            let totalHarga = qtyGr * hargaSatuan;

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
                                <td class="text-center fw-semibold text-secondary small">${window.escapeHtml(satuanNama)}</td>
                                <td class="text-end fw-bold text-success">${detailCurrencySymbol} ${fmt(totalHarga)}${totalIdrSubHtml}</td>
                            </tr>`;
                        });
                    } else {
                        html = '<tr><td colspan="8" class="text-center text-muted py-4">Tidak ada rincian barang.</td></tr>';
                    }

                    // Tfoot calculation
                    let subtotal = 0;
                    if (gr.details) {
                        gr.details.forEach(function (det) {
                            let qtyGr = parseFloat(det.jumlah || 0);
                            let poFaktor = det.po_detail && parseFloat(det.po_detail.faktor_pengali) > 0 ? parseFloat(det.po_detail.faktor_pengali) : 1;
                            let detFaktor = parseFloat(det.faktor_pengali || 1);
                            let poHargaSatuan = det.po_detail ? parseFloat(det.po_detail.harga_satuan || 0) : 0;
                            let hargaSatuan = (poFaktor > 0) ? ((poHargaSatuan / poFaktor) * detFaktor) : poHargaSatuan;
                            subtotal += qtyGr * hargaSatuan;
                        });
                    }

                    let poSubtotal = gr.po ? parseFloat(gr.po.subtotal || 0) : 0;
                    let ratio = (poSubtotal > 0) ? (subtotal / poSubtotal) : 0;
                    let isFull = Math.abs(ratio - 1) < 0.00001;

                    let discountVal = 0;
                    if (gr.po) {
                        if (parseFloat(gr.po.total_diskon || 0) > 0 && parseFloat(gr.po.subtotal || 0) > 0) {
                            discountVal = isFull ? parseFloat(gr.po.total_diskon) : (subtotal * (parseFloat(gr.po.total_diskon) / parseFloat(gr.po.subtotal)));
                        } else if (parseFloat(gr.po.diskon_persen || 0) > 0) {
                            discountVal = subtotal * (parseFloat(gr.po.diskon_persen) / 100);
                        }
                    }

                    let taxRate = (gr.po && gr.po.pajak) ? parseFloat(gr.po.pajak.tarif) : 0;
                    let taxName = (gr.po && gr.po.pajak) ? gr.po.pajak.nama_pajak : 'PPN';
                    let taxVal = 0;
                    if (taxRate > 0) {
                        taxVal = (isFull && gr.po && parseFloat(gr.po.total_pajak || 0) > 0) ? parseFloat(gr.po.total_pajak) : ((subtotal - discountVal) * (taxRate / 100));
                    }
                    let rawBiayaLain = gr.po ? parseFloat(gr.po.biaya_lain || 0) : 0;
                    let biayaLain = isFull ? rawBiayaLain : (rawBiayaLain * ratio);
                    let deskripsiBiayaLain = (gr.po && gr.po.deskripsi_biaya_lain) ? gr.po.deskripsi_biaya_lain : 'Biaya Lain-lain';
                    let dpRatioValue = isFull ? dpValue : (dpValue * ratio);
                    let finalGrandTotal = (subtotal - discountVal) + taxVal + biayaLain - dpRatioValue;

                    let subtotalIdrSubHtml = detailCurrencySymbol !== 'Rp' ? (`<br><small class="text-muted" style="font-size: 0.7rem;">Rp ${fmt(subtotal * detailKurs)}</small>`) : '';
                    let finalGrandTotalIdrSubHtml = detailCurrencySymbol !== 'Rp' ? (`<br><small class="text-muted" style="font-size: 0.75rem;">Rp ${fmt(finalGrandTotal * detailKurs)}</small>`) : '';

                    let tfootHtml = `<tr>
                        <td colspan="7" class="text-end fw-semibold text-secondary small py-2">Subtotal Barang (Kotor):</td>
                        <td class="text-end fw-bold text-dark py-2">${detailCurrencySymbol} ${fmt(subtotal)}${subtotalIdrSubHtml}</td>
                    </tr>`;

                    if (discountVal > 0) {
                        let discLabel = (gr.po && parseFloat(gr.po.diskon_persen) > 0) ? (`Total Diskon (${parseFloat(gr.po.diskon_persen)}%):`) : 'Total Diskon:';
                        let discIdrSubHtml = detailCurrencySymbol !== 'Rp' ? (`<br><small class="text-muted" style="font-size: 0.7rem;">-Rp ${fmt(discountVal * detailKurs)}</small>`) : '';
                        tfootHtml += `<tr>
                            <td colspan="7" class="text-end fw-semibold text-danger small py-1">${discLabel}</td>
                            <td class="text-end fw-bold text-danger py-1">-${detailCurrencySymbol} ${fmt(discountVal)}${discIdrSubHtml}</td>
                        </tr>`;
                    }

                    if (taxVal > 0) {
                        let taxIdrSubHtml = detailCurrencySymbol !== 'Rp' ? (`<br><small class="text-muted" style="font-size: 0.7rem;">Rp ${fmt(taxVal * detailKurs)}</small>`) : '';
                        tfootHtml += `<tr>
                            <td colspan="7" class="text-end fw-semibold text-secondary small py-1">${window.escapeHtml(taxName)} (${parseFloat(taxRate)}%):</td>
                            <td class="text-end fw-bold text-dark py-1">${detailCurrencySymbol} ${fmt(taxVal)}${taxIdrSubHtml}</td>
                        </tr>`;
                    }

                    if (biayaLain > 0) {
                        let biayaLainIdrSubHtml = detailCurrencySymbol !== 'Rp' ? (`<br><small class="text-muted" style="font-size: 0.7rem;">Rp ${fmt(biayaLain * detailKurs)}</small>`) : '';
                        tfootHtml += `<tr>
                            <td colspan="7" class="text-end fw-semibold text-secondary small py-1">${window.escapeHtml(deskripsiBiayaLain)}:</td>
                            <td class="text-end fw-bold text-dark py-1">${detailCurrencySymbol} ${fmt(biayaLain)}${biayaLainIdrSubHtml}</td>
                        </tr>`;
                    }

                    let totalSebelumDp = (subtotal - discountVal) + taxVal + biayaLain;
                    let totalSebelumDpIdrSubHtml = detailCurrencySymbol !== 'Rp' ? (`<br><small class="text-muted" style="font-size: 0.7rem;">Rp ${fmt(totalSebelumDp * detailKurs)}</small>`) : '';
                    if (dpRatioValue > 0) {
                        tfootHtml += `<tr>
                            <td colspan="7" class="text-end fw-semibold text-info small py-1">Total Sebelum DP:</td>
                            <td class="text-end fw-bold text-info py-1">${detailCurrencySymbol} ${fmt(totalSebelumDp)}${totalSebelumDpIdrSubHtml}</td>
                        </tr>`;
                    }

                    let dpText = dpRatioValue > 0 ? (`-${detailCurrencySymbol} ${fmt(dpRatioValue)}`) : `${detailCurrencySymbol} 0,00`;
                    let dpIdrSubHtml = (detailCurrencySymbol !== 'Rp') ? (`<br><small class="text-muted" style="font-size: 0.7rem;">${dpRatioValue > 0 ? '-Rp ' : 'Rp '}${fmt(dpRatioValue * detailKurs)}</small>`) : '';
                    tfootHtml += `<tr>
                        <td colspan="7" class="text-end fw-semibold text-warning small py-1"><i class="bi bi-cash-coin me-1"></i>Uang Muka (DP PO):</td>
                        <td class="text-end fw-bold text-warning py-1">${dpText}${dpIdrSubHtml}</td>
                    </tr>`;

                    tfootHtml += `<tr>
                        <td colspan="7" class="text-end fw-bold text-dark py-3 border-top border-2">Total Akhir Penerimaan:</td>
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

    }); // end document.ready [modal_detail]
</script>
@endpush
