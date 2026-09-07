{{-- ===== MODAL DETAIL PO ===== --}}
<div class="modal fade" id="modalDetailPO" tabindex="-1" aria-hidden="true" data-bs-focus="false">
    <div class="modal-dialog modal-fullscreen-xl-down modal-xl po-modal-dialog">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 20px;">
            <!-- Loading Overlay -->
            <div class="modal-loading-overlay d-none" id="detail_modal_loader">
                <div class="text-center">
                    <div class="spinner-border text-primary" role="status"></div>
                    <div class="mt-2 fw-semibold text-secondary">Memuat detail PO...</div>
                </div>
            </div>
            <div class="modal-header border-0 py-4 px-4 bg-light">
                <div class="d-flex align-items-center gap-3">
                    <div class="bg-primary bg-opacity-10 text-primary rounded-circle d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                        <i class="bi bi-cart-check fs-4"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold text-dark mb-0" id="detail_nomor_po">Nomor PO</h5>
                        <small class="text-muted">Rincian pesanan pembelian</small>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <div id="detail_status_badge_header"></div>
                    <button type="button" class="btn-close ms-2" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
            </div>

            <div class="modal-body p-3 p-md-4 detail-modal-body">

                {{-- === INFO GRID === --}}
                <div class="card border-0 shadow-sm p-3 mb-4" style="border-radius: 14px;">
                    <h6 class="fw-bold mb-3 text-uppercase" style="font-size:.75rem; letter-spacing:.06em; color:#64748b;">
                        <i class="bi bi-info-circle me-1 text-primary"></i> Informasi Umum PO
                    </h6>
                    <div class="row g-2">
                        <div class="col-6 col-md-3">
                            <div class="info-grid-card">
                                <small class="text-muted d-block mb-1"><i class="bi bi-calendar3 me-1 text-primary"></i> Tanggal PO</small>
                                <span class="fw-semibold text-dark" id="detail_tanggal_po">-</span>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="info-grid-card">
                                <small class="text-muted d-block mb-1"><i class="bi bi-calendar-check me-1 text-primary"></i> Tgl. Pengiriman</small>
                                <span class="fw-semibold text-dark" id="detail_tanggal_pengiriman">-</span>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="info-grid-card">
                                <small class="text-muted d-block mb-1"><i class="bi bi-person-badge me-1 text-primary"></i> Supplier</small>
                                <span class="fw-bold text-dark" id="detail_supplier">-</span>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="info-grid-card">
                                <small class="text-muted d-block mb-1"><i class="bi bi-building me-1 text-primary"></i> Perusahaan (PT)</small>
                                <span class="fw-bold text-dark" id="detail_company">-</span>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="info-grid-card">
                                <small class="text-muted d-block mb-1"><i class="bi bi-buildings me-1 text-primary"></i> Cabang / Unit</small>
                                <span class="fw-semibold text-dark" id="detail_cabang">-</span>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="info-grid-card">
                                <small class="text-muted d-block mb-1"><i class="bi bi-file-earmark-text me-1 text-primary"></i> Sumber PR</small>
                                <span class="fw-semibold text-primary" id="detail_pr">-</span>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="info-grid-card">
                                <small class="text-muted d-block mb-1"><i class="bi bi-cash-coin me-1 text-primary"></i> Mata Uang & Kurs</small>
                                <span class="fw-semibold text-dark" id="detail_currency">-</span>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="info-grid-card">
                                <small class="text-muted d-block mb-1"><i class="bi bi-calendar-range me-1 text-primary"></i> Tenor</small>
                                <span class="fw-semibold text-dark" id="detail_payment_term">-</span>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="info-grid-card">
                                <small class="text-muted d-block mb-1"><i class="bi bi-credit-card me-1 text-primary"></i> Metode Bayar</small>
                                <span class="fw-semibold text-dark" id="detail_payment_method">-</span>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="info-grid-card" id="detail_tender_card">
                                <small class="text-muted d-block mb-1"><i class="bi bi-briefcase me-1 text-primary"></i> Tender / Kontrak Project</small>
                                <span class="fw-semibold text-dark" id="detail_tender">-</span>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="info-grid-card">
                                <small class="text-muted d-block mb-1"><i class="bi bi-person-fill-gear me-1 text-primary"></i> Dibuat Oleh (Pengaju)</small>
                                <span class="fw-bold text-dark d-block" id="detail_dibuat_oleh">-</span>
                                <small class="text-muted" id="detail_created_at" style="font-size: 0.75rem;">-</small>
                            </div>
                        </div>
                        <div class="col-6 col-md-3" id="detail_updated_wrapper" style="display: none;">
                            <div class="info-grid-card border-warning border-opacity-25" style="background: rgba(245, 158, 11, 0.04);">
                                <small class="text-muted d-block mb-1"><i class="bi bi-pencil-square me-1 text-warning"></i> Diubah Oleh (Pengedit)</small>
                                <span class="fw-bold text-dark d-block" id="detail_diubah_oleh">-</span>
                                <small class="text-muted" id="detail_updated_at" style="font-size: 0.75rem;">-</small>
                            </div>
                        </div>
                        <div class="col-6 col-md-3" id="detail_approved_wrapper" style="display: none;">
                            <div class="info-grid-card border-success border-opacity-25" style="background: rgba(16, 185, 129, 0.05);">
                                <small class="text-muted d-block mb-1"><i class="bi bi-check-circle-fill me-1 text-success"></i> Disetujui Oleh (Atasan)</small>
                                <span class="fw-bold text-dark d-block" id="detail_disetujui_oleh">-</span>
                                <small class="text-muted" id="detail_approved_at" style="font-size: 0.75rem;">-</small>
                            </div>
                        </div>
                    </div>

                    {{-- Alasan Penolakan (hanya muncul jika Rejected) --}}
                    <div id="detail_alasan_ditolak_wrapper" class="mt-3" style="display: none;">
                        <div class="alert alert-danger border-danger border-opacity-50 rounded-3 d-flex gap-3 align-items-start mb-0 py-3" style="background: rgba(239,68,68,0.07);">
                            <i class="bi bi-x-octagon-fill fs-4 text-danger mt-1 flex-shrink-0"></i>
                            <div>
                                <div class="fw-bold text-danger mb-1" style="font-size:.85rem;">Alasan Penolakan</div>
                                <div id="detail_alasan_ditolak_text" class="text-dark" style="font-size:.9rem; white-space: pre-wrap;"></div>
                            </div>
                        </div>
                    </div>
                </div>


                {{-- === DETAIL BARANG + SUMMARY SIDE BY SIDE === --}}
                <div class="row g-3 mb-4">
                    {{-- Tabel Barang --}}
                    <div class="col-lg-8">
                        <div class="card border-0 shadow-sm p-3 h-100" style="border-radius: 14px;">
                            <h6 class="fw-bold mb-3 text-uppercase" style="font-size:.75rem; letter-spacing:.06em; color:#64748b;">
                                <i class="bi bi-box-seam me-1 text-primary"></i> Daftar Barang Dipesan
                            </h6>
                            <div class="table-responsive">
                                <table class="table align-middle table-modern mb-0" style="font-size: 0.85rem; min-width: 820px;">
                                    <thead>
                                        <tr class="bg-light">
                                            <th style="min-width: 220px;">Barang</th>
                                            <th class="text-center" style="min-width: 110px;">Satuan</th>
                                            <th class="text-center" style="min-width: 80px;">Qty PO</th>
                                            <th class="text-center" style="min-width: 120px;">Qty Diterima</th>
                                            <th class="text-end" style="min-width: 130px;">Harga Satuan</th>
                                            <th class="text-end" style="min-width: 100px;">Diskon</th>
                                            <th class="text-end" style="min-width: 140px;">Subtotal</th>
                                        </tr>
                                    </thead>
                                    <tbody id="detail_items_tbody">
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    {{-- Summary --}}
                    <div class="col-lg-4">
                        <div class="card border-0 shadow-sm p-3 h-100" style="border-radius: 14px;">
                            <h6 class="fw-bold mb-3 text-uppercase" style="font-size:.75rem; letter-spacing:.06em; color:#64748b;">
                                <i class="bi bi-calculator me-1 text-primary"></i> Perhitungan
                            </h6>

                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-secondary small">Sub Total Barang</span>
                                <span class="fw-semibold text-dark" id="detail_subtotal">-</span>
                            </div>

                            <div class="d-flex justify-content-between mb-2 d-none" id="detail_biaya_inventory_wrapper">
                                <span class="text-secondary small">Biaya Inventory (Persediaan)</span>
                                <span class="fw-semibold text-success" id="detail_biaya_inventory">-</span>
                            </div>

                            <div class="d-flex justify-content-between mb-2 d-none" id="detail_biaya_expense_wrapper">
                                <span class="text-secondary small">Biaya Operasional (Expense)</span>
                                <span class="fw-semibold text-warning" id="detail_biaya_expense">-</span>
                            </div>

                            <div class="d-flex justify-content-between mb-2 d-none" id="detail_diskon_wrapper">
                                <span class="text-secondary small" id="detail_diskon_label">Diskon Header (PO)</span>
                                <span class="fw-semibold text-danger" id="detail_diskon">-</span>
                            </div>

                            <hr class="my-2 opacity-25">

                            <div class="d-flex justify-content-between mb-2">
                                <span class="fw-bold small text-dark">DPP (Dasar Pengenaan Pajak)</span>
                                <span class="fw-bold text-dark" id="detail_dpp">-</span>
                            </div>

                            <div class="d-flex justify-content-between mb-2" id="detail_pajak_wrapper">
                                <span class="text-secondary small" id="detail_pajak_label">PPN (0%)</span>
                                <span class="fw-semibold text-dark" id="detail_total_pajak">-</span>
                            </div>

                            <hr class="my-2 opacity-25">

                            <div class="d-flex justify-content-between mb-2">
                                <span class="fw-bold text-dark">Grand Total</span>
                                <span class="fw-bold text-primary fs-5" id="detail_grand_total">-</span>
                            </div>

                            <div class="d-flex justify-content-between mb-2 d-none" id="detail_down_payment_wrapper">
                                <span class="text-secondary small">Uang Muka (DP)</span>
                                <span class="fw-semibold text-danger" id="detail_down_payment">-</span>
                            </div>

                            <div class="d-none" id="detail_sisa_bayar_wrapper">
                                <hr class="my-2 opacity-25">
                                <div class="d-flex justify-content-between">
                                    <span class="fw-bold text-dark">Sisa Pembayaran</span>
                                    <span class="fw-bold text-success fs-5" id="detail_sisa_bayar">-</span>
                                </div>
                            </div>

                            <div class="border-top pt-2 mt-2 d-none" id="detail_idr_wrapper">
                                <div class="d-flex justify-content-between mb-1">
                                    <span class="text-secondary small">Nilai Kurs Rupiah</span>
                                    <span class="fw-semibold text-dark" id="detail_kurs_val">-</span>
                                </div>
                                <div class="d-flex justify-content-between mb-1">
                                    <span class="fw-bold small text-dark">Grand Total (Rupiah)</span>
                                    <span class="fw-bold text-dark" id="detail_grand_total_idr">-</span>
                                </div>
                                <div class="d-flex justify-content-between d-none" id="detail_idr_sisa_bayar_wrapper">
                                    <span class="text-secondary small">Sisa Pembayaran (Rupiah)</span>
                                    <span class="fw-semibold text-success" id="detail_sisa_bayar_idr">-</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- === BIAYA LAIN-LAIN BREAKDOWN === --}}
                <div id="detail_biaya_lain_section" style="display:none;" class="mb-4">
                    <div class="d-flex align-items-center gap-2 mb-3">
                        <div class="rounded-circle d-flex align-items-center justify-content-center" style="width:32px;height:32px;background:linear-gradient(135deg,#6366f1,#8b5cf6);">
                            <i class="bi bi-receipt text-white" style="font-size:.8rem;"></i>
                        </div>
                        <h6 class="fw-bold mb-0 text-uppercase" style="font-size:.75rem;letter-spacing:.06em;color:#64748b;">Biaya Lain-lain</h6>
                        <span class="badge rounded-pill bg-primary bg-opacity-10 text-primary" id="detail_biaya_lain_count">0 item</span>
                    </div>
                    <div class="row g-3" id="detail_biaya_lain_cards"></div>
                    {{-- Subtotal row --}}
                    <div class="d-flex justify-content-between align-items-center px-3 py-2 rounded-3 mt-2 detail-idr-box">
                        <span class="fw-semibold text-dark small">Total Biaya Lain-lain</span>
                        <span class="fw-bold text-primary" id="detail_biaya_lain_total">-</span>
                    </div>
                </div>

                {{-- === KETERANGAN === --}}
                <div class="card border-0 shadow-sm p-3 mb-3" style="border-radius: 14px;">
                    <small class="text-muted d-block mb-1"><i class="bi bi-chat-left-text me-1 text-primary"></i> Keterangan / Catatan PO</small>
                    <span class="text-dark" id="detail_keterangan">-</span>
                </div>

                {{-- Approval Buttons Area (Conditional) --}}
                @can('approve-po')
                <div class="p-3 bg-warning bg-opacity-10 border border-warning border-opacity-25 rounded-3 d-none" id="po_approval_action_panel">
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <div>
                            <h6 class="fw-bold text-warning-emphasis mb-1"><i class="bi bi-exclamation-triangle-fill me-1"></i> Panel Persetujuan Dokumen PO</h6>
                            <p class="small text-muted mb-0">Sebagai Direktur/Pemeriksa, Anda berhak menyetujui atau menolak dokumen PO ini.</p>
                        </div>
                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-danger rounded-pill px-4 btn-po-reject-action"><i class="bi bi-x-circle me-1"></i> Tolak</button>
                            <button type="button" class="btn btn-success rounded-pill px-4 btn-po-approve-action"><i class="bi bi-check-circle me-1"></i> Setujui</button>
                        </div>
                    </div>
                </div>
                @endcan
            </div>

            <div class="modal-footer po-detail-modal-footer border-0 justify-content-between">
                <a id="btn_print_po_modal" href="#" target="_blank" class="btn btn-info text-white rounded-pill px-4">
                    <i class="bi bi-printer-fill me-1"></i> Cetak PO
                </a>
                <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    $(document).ready(function () {
        // ===== DETAIL — exposed as window function so viewpo.blade.php can bind the click =====
        window.openDetailPO = function (id) {
            if (!id) { console.error('openDetailPO: id kosong'); return; }

            // Show modal and loader immediately
            let modalEl = document.getElementById('modalDetailPO');
            if (modalEl) {
                if (modalEl.parentElement !== document.body) {
                    document.body.appendChild(modalEl);
                }
                bootstrap.Modal.getOrCreateInstance(modalEl).show();
            }
            $('#detail_modal_loader').removeClass('d-none').addClass('active');

            // Reset dynamic calculation and summary elements to prevent ghost/stale data from previous POs
            $('#detail_subtotal').text('-');
            $('#detail_dpp').text('-');
            $('#detail_total_pajak').text('-');
            $('#detail_grand_total').text('-');
            $('#detail_kurs_val').text('-');
            $('#detail_grand_total_idr').text('-');
            $('#detail_sisa_bayar').text('-');
            $('#detail_sisa_bayar_idr').text('-');

            $('#detail_diskon_label').text('Diskon Header (PO)');
            $('#detail_diskon').text('-');
            $('#detail_diskon_wrapper').addClass('d-none');

            $('#detail_down_payment').text('-');
            $('#detail_down_payment_wrapper').addClass('d-none');

            $('#detail_biaya_inventory').text('-');
            $('#detail_biaya_inventory_wrapper').addClass('d-none');

            $('#detail_biaya_expense').text('-');
            $('#detail_biaya_expense_wrapper').addClass('d-none');

            $('#detail_sisa_bayar_wrapper').addClass('d-none');
            $('#detail_idr_wrapper').addClass('d-none');
            $('#detail_idr_sisa_bayar_wrapper').addClass('d-none');
            $('#detail_items_tbody').html('<tr><td colspan="7" class="text-center py-4 text-muted"><div class="spinner-border spinner-border-sm text-primary me-2"></div>Memuat rincian barang...</td></tr>');

            $.get('/purchase-order/' + id, function (res) {
                let po = res.po;
                let sym = (po.currency ? po.currency.simbol : null) || 'Rp';

                let statusBadgeMap = {
                    'Draft':              `<span class="badge px-3 py-2 rounded-pill fw-semibold" style="background: rgba(249, 115, 22, 0.15); color: #ea580c; border: 1px solid rgba(249, 115, 22, 0.25);"><i class="bi bi-file-earmark-text me-1"></i>Draft</span>`,
                    'Pending':            `<span class="badge bg-warning bg-opacity-10 text-warning px-3 py-2 rounded-pill fw-semibold"><i class="bi bi-clock-history me-1"></i>Pending</span>`,
                    'Ordered':            `<span class="badge bg-success bg-opacity-10 text-success px-3 py-2 rounded-pill fw-semibold"><i class="bi bi-check-circle me-1"></i>Ordered</span>`,
                    'Partially Received': `<span class="badge bg-info bg-opacity-10 text-info px-3 py-2 rounded-pill fw-semibold"><i class="bi bi-hourglass-split me-1"></i>Partially Received</span>`,
                    'Received':           `<span class="badge bg-primary bg-opacity-10 text-primary px-3 py-2 rounded-pill fw-semibold"><i class="bi bi-check-all me-1"></i>Received</span>`,
                    'Rejected':           `<span class="badge bg-danger bg-opacity-10 text-danger px-3 py-2 rounded-pill fw-semibold"><i class="bi bi-x-circle me-1"></i>Rejected</span>`,
                };
                $('#detail_status_badge_header').html(statusBadgeMap[po.status] || '');
                $('#btn_print_po_modal').attr('href', '/purchase-order/' + po.id + '/print');
                $('#detail_nomor_po').text(po.nomor_po);

                let tgl = po.tanggal_po ? new Date(po.tanggal_po).toLocaleDateString('id-ID', { day: '2-digit', month: 'long', year: 'numeric' }) : '-';
                $('#detail_tanggal_po').text(tgl);

                let tglKirim = '-';
                if (po.tanggal_pengiriman) {
                    tglKirim = new Date(po.tanggal_pengiriman).toLocaleDateString('id-ID', { day: '2-digit', month: 'long', year: 'numeric' });
                }
                $('#detail_tanggal_pengiriman').text(tglKirim);

                let rawName = po.supplier ? po.supplier.nama_supplier : ('Supplier #' + po.supplier_id);
                let formattedName = rawName.toLowerCase().replace(/\b[a-z]/g, l => l.toUpperCase())
                    .replace(/^Pt\b/i, 'PT').replace(/^Cv\b/i, 'CV');
                $('#detail_supplier').text(formattedName);
                
                let companyName = po.company ? (po.company.nama_perusahaan || po.company.nama) : 'PT Persada Aman Sentosa';
                $('#detail_company').text(companyName);
                
                let branchHtml = po.branch 
                    ? `<span class="badge bg-primary bg-opacity-10 text-primary px-2 py-1 rounded-pill"><i class="bi bi-geo-alt me-1"></i>${escapeHtml(po.branch.nama_cabang)}</span>` 
                    : `<span class="badge bg-secondary bg-opacity-10 text-secondary px-2 py-1 rounded-pill"><i class="bi bi-building me-1"></i>Pusat (Head Office)</span>`;
                $('#detail_cabang').html(branchHtml);

                let prText = 'Manual (Tanpa PR)';
                if (po.pr) {
                    prText = po.pr.nomor_pr;
                    if (po.pr.requester) prText += ' — ' + po.pr.requester.nama_lengkap;
                } else if (po.manual_pr_number) {
                    prText = po.manual_pr_number + ' (Manual)';
                }
                $('#detail_pr').text(prText);

                let kursInfo = po.currency ? po.currency.kode_currency : 'IDR';
                if (po.currency && po.currency.kode_currency !== 'IDR') {
                    kursInfo += ' (Kurs: Rp ' + formatNumber(po.kurs) + ')';
                }
                $('#detail_currency').text(kursInfo);

                let tarif = po.pajak ? parseFloat(po.pajak.tarif) : 0;
                $('#detail_pajak_label').text('PPN (' + tarif + '%)');
                $('#detail_payment_term').text(po.payment_term ? po.payment_term.nama_term : '-');
                $('#detail_payment_method').text(po.payment_method ? po.payment_method.nama_metode : '-');

                if (po.tender) {
                    let tenderText = po.tender.nama_tender;
                    if (po.tender.nomor_kontrak) tenderText += ' (' + po.tender.nomor_kontrak + ')';
                    $('#detail_tender').text(tenderText);
                    $('#detail_tender_card').addClass('border-info border-opacity-25').css('background', 'rgba(14, 165, 233, 0.06)');
                } else {
                    $('#detail_tender').text('-');
                    $('#detail_tender_card').removeClass('border-info border-opacity-25').css('background', '');
                }

                // Dibuat Oleh (Pengaju)
                let creatorName = po.creator ? po.creator.nama_lengkap : 'Sistem';
                $('#detail_dibuat_oleh').text(creatorName);
                let createdTime = po.created_at ? new Date(po.created_at).toLocaleString('id-ID', { day: '2-digit', month: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit' }) + ' WIB' : '-';
                $('#detail_created_at').text(createdTime);

                // 1. Disetujui Oleh (Penyetuju / Atasan)
                let isApprovedStatus = ['Ordered', 'Closed'].includes(po.status);
                if (isApprovedStatus || po.approved_by || po.approved_at) {
                    let approverName = po.approver ? po.approver.nama_lengkap : 'Atasan';
                    let appTimeStr = po.approved_at || po.updated_at;
                    let approvedTime = appTimeStr ? new Date(appTimeStr).toLocaleString('id-ID', { day: '2-digit', month: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit' }) + ' WIB' : '-';
                    $('#detail_disetujui_oleh').text(approverName);
                    $('#detail_approved_at').text(approvedTime);
                    $('#detail_approved_wrapper').show();
                } else {
                    $('#detail_approved_wrapper').hide();
                }

                // 1b. Alasan Penolakan (hanya jika Rejected)
                if (po.status === 'Rejected' && po.alasan_ditolak) {
                    $('#detail_alasan_ditolak_text').text(po.alasan_ditolak);
                    $('#detail_alasan_ditolak_wrapper').show();
                } else {
                    $('#detail_alasan_ditolak_text').text('');
                    $('#detail_alasan_ditolak_wrapper').hide();
                }

                // 2. Diubah Oleh (Pengedit)
                let isEdited = false;
                if (po.updated_by && po.creator && po.updated_by != po.created_by) {
                    isEdited = true;
                } else if (po.updated_at && po.created_at) {
                    let cTime = new Date(po.created_at).getTime();
                    let uTime = new Date(po.updated_at).getTime();
                    let aTime = po.approved_at ? new Date(po.approved_at).getTime() : 0;
                    if (uTime > cTime + 2000 && Math.abs(uTime - aTime) > 2000) {
                        isEdited = true;
                    }
                }

                if (isEdited && po.updater) {
                    let updaterName = po.updater ? po.updater.nama_lengkap : '-';
                    let updatedTime = po.updated_at ? new Date(po.updated_at).toLocaleString('id-ID', { day: '2-digit', month: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit' }) + ' WIB' : '-';
                    $('#detail_diubah_oleh').text(updaterName);
                    $('#detail_updated_at').text(updatedTime);
                    $('#detail_updated_wrapper').show();
                } else {
                    $('#detail_updated_wrapper').hide();
                }

                if (po.status === 'Pending') {
                    $('#po_approval_action_panel').removeClass('d-none').data('id', po.id);
                } else {
                    $('#po_approval_action_panel').addClass('d-none');
                }

                $('#detail_keterangan').text(po.keterangan || 'Tidak ada catatan.');

                let html = '';
                if (po.details && po.details.length) {
                    po.details.forEach(function (det) {
                        let barangNama = det.barang ? det.barang.nama_barang : ('Barang #' + det.barang_id);
                        let barangKode = det.barang ? det.barang.kode_barang : '-';
                        let fotoUrl = (det.barang && det.barang.primary_foto) ? '/storage/' + det.barang.primary_foto.foto_path : '';
                        let satuanName = det.satuan ? det.satuan.nama_satuan : ((det.barang && det.barang.satuan) ? det.barang.satuan.nama_satuan : '-');
                        let satuanDasar = (det.barang && det.barang.satuan) ? det.barang.satuan.nama_satuan : satuanName;
                        let faktorPengali = parseFloat(det.faktor_pengali) || 1;

                        let konversiHtml = '';
                        if (faktorPengali !== 1) {
                            let jumlahDasar = parseFloat(det.jumlah_dasar) || ((parseFloat(det.jumlah) || 0) * faktorPengali);
                            let fmtDasar = formatQty(jumlahDasar);
                            konversiHtml = `<small class="text-muted d-block mt-0.5" style="font-size:0.75rem;">Setara ${fmtDasar} ${escapeHtml(satuanDasar)}</small>`;
                        }

                        let qtyDiterima = parseFloat(det.qty_diterima || det.jumlah_diterima || 0);
                        let qtyPO = parseFloat(det.jumlah) || 0;
                        let pct = qtyPO > 0 ? Math.min(100, Math.round((qtyDiterima / qtyPO) * 100)) : 0;
                        let progressColor = pct >= 100 ? '#10b981' : (pct > 0 ? '#3b82f6' : '#94a3b8');
                        let diskonItem = parseFloat(det.diskon_item) || 0;
                        let hargaItem = parseFloat(det.harga_satuan) || 0;
                        let diskonCell = `<span class="text-muted">-</span>`;
                        if (diskonItem > 0) {
                            let itemPct = (hargaItem > 0) ? (diskonItem / hargaItem) * 100 : 0;
                            let roundedItemPct = Math.round(itemPct * 100) / 100;
                            let pctBadge = '';
                            if (roundedItemPct > 0 && roundedItemPct <= 100 && Math.abs(hargaItem * (roundedItemPct / 100) - diskonItem) < 0.01) {
                                let fmtPct = (roundedItemPct % 1 === 0) ? roundedItemPct.toFixed(0) : parseFloat(roundedItemPct.toFixed(2)).toString().replace('.', ',');
                                pctBadge = `<span class="badge bg-danger bg-opacity-10 text-danger ms-1" style="font-size:0.7rem;">${fmtPct}%</span>`;
                            }
                            let totalItemDiskon = diskonItem * qtyPO;
                            diskonCell = `<span class="text-danger fw-semibold">${sym} ${formatNumber(diskonItem)}</span>${pctBadge}`;
                            if (qtyPO > 1 && Math.abs(totalItemDiskon - diskonItem) > 0.001) {
                                diskonCell += `<br><small class="text-muted" style="font-size:0.75rem;">Total: ${sym} ${formatNumber(totalItemDiskon)}</small>`;
                            }
                        }
                        
                        let fotosArray = [];
                        if (det.barang && det.barang.fotos && det.barang.fotos.length) {
                            fotosArray = det.barang.fotos.map(f => '/storage/' + f.foto_path);
                        } else if (fotoUrl) {
                            fotosArray = [fotoUrl];
                        }

                        html += `
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="product-img-wrapper cursor-pointer" data-fotos='${escapeHtml(JSON.stringify(fotosArray))}' style="cursor: pointer;">
                                            ${fotoUrl ? `<img src="${fotoUrl}" alt="${escapeHtml(barangNama)}" class="product-img">` : `<i class="bi bi-image text-muted"></i>`}
                                        </div>
                                        <div>
                                            <span class="fw-semibold text-dark d-block">${escapeHtml(barangNama)}</span>
                                            <small class="text-muted">${escapeHtml(barangKode)}</small>
                                        </div>
                                    </div>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-light text-secondary border px-2 py-1">${escapeHtml(satuanName)}</span>
                                    ${konversiHtml}
                                </td>
                                <td class="text-center fw-bold">${parseFloat(qtyPO)}</td>
                                <td class="text-center">
                                    <div class="fw-semibold mb-1 small" style="color:${progressColor}">${parseFloat(qtyDiterima)} / ${parseFloat(qtyPO)}</div>
                                    <div style="height:5px;background:#e2e8f0;border-radius:4px;overflow:hidden;">
                                        <div style="height:100%;width:${pct}%;background:${progressColor};border-radius:4px;transition:.3s;"></div>
                                    </div>
                                </td>
                                <td class="text-end text-secondary">${sym} ${formatNumber(det.harga_satuan)}</td>
                                <td class="text-end">${diskonCell}</td>
                                <td class="text-end fw-bold text-dark">${sym} ${formatNumber(det.subtotal)}</td>
                            </tr>
                        `;
                    });
                } else {
                    html = '<tr><td colspan="7" class="text-center py-3 text-muted">Tidak ada rincian item barang.</td></tr>';
                }
                $('#detail_items_tbody').html(html);

                let subtotal        = parseFloat(po.subtotal) || 0;
                let headerDiskon    = parseFloat(po.total_diskon) || 0;
                let totalPajak      = parseFloat(po.total_pajak) || 0;
                let grandTotal      = parseFloat(po.grand_total) || 0;
                let grandTotalIdr   = parseFloat(po.grand_total_idr) || 0;
                let dp              = parseFloat(po.down_payment) || 0;
                let dpp             = Math.max(0, subtotal - headerDiskon);

                let biayaLainDetails = po.biaya_lain_details || [];
                if (typeof biayaLainDetails === 'string') {
                    try { biayaLainDetails = JSON.parse(biayaLainDetails); } catch(e) { biayaLainDetails = []; }
                }
                let biayaInventory = 0;
                let biayaExpense   = 0;
                if (Array.isArray(biayaLainDetails)) {
                    biayaLainDetails.forEach(function(item) {
                        let nom = parseFloat(item.nominal) || 0;
                        let isHpp = String(item.masuk_hpp);
                        if (isHpp === '1' || isHpp === 'true') biayaInventory += nom;
                        else biayaExpense += nom;
                    });
                }

                // Sub Total Barang (Total subtotal dari tabel barang)
                $('#detail_subtotal').text(sym + ' ' + formatNumber(subtotal));

                if (biayaInventory > 0) {
                    $('#detail_biaya_inventory').text(sym + ' ' + formatNumber(biayaInventory));
                    $('#detail_biaya_inventory_wrapper').removeClass('d-none');
                } else {
                    $('#detail_biaya_inventory').text('-');
                    $('#detail_biaya_inventory_wrapper').addClass('d-none');
                }

                if (biayaExpense > 0) {
                    $('#detail_biaya_expense').text(sym + ' ' + formatNumber(biayaExpense));
                    $('#detail_biaya_expense_wrapper').removeClass('d-none');
                } else {
                    $('#detail_biaya_expense').text('-');
                    $('#detail_biaya_expense_wrapper').addClass('d-none');
                }

                // Diskon Header (PO) - HANYA tampil jika ada diskon header (> 0), jika tidak ada otomatis disembunyikan
                if (headerDiskon > 0) {
                    let headerPct = parseFloat(po.diskon_persen) || 0;
                    if (headerPct <= 0 && subtotal > 0) {
                        headerPct = (headerDiskon / subtotal) * 100;
                    }
                    let fmtHeaderDisc = (headerPct % 1 === 0) ? headerPct.toFixed(0) : parseFloat(headerPct.toFixed(2)).toString().replace('.', ',');
                    let headerDiscLabel = headerPct > 0 ? ('Diskon (' + fmtHeaderDisc + '%)') : 'Diskon Header (PO)';
                    $('#detail_diskon_label').text(headerDiscLabel);
                    $('#detail_diskon').text('- ' + sym + ' ' + formatNumber(headerDiskon));
                    $('#detail_diskon_wrapper').removeClass('d-none');
                } else {
                    $('#detail_diskon_label').text('Diskon Header (PO)');
                    $('#detail_diskon').text('-');
                    $('#detail_diskon_wrapper').addClass('d-none');
                }

                $('#detail_dpp').text(sym + ' ' + formatNumber(dpp));

                let taxRate = po.pajak ? parseFloat(po.pajak.tarif) : 0;
                let taxName = po.pajak ? po.pajak.nama_pajak : 'PPN';
                $('#detail_pajak_label').text(taxName + ' (' + taxRate + '%)');
                $('#detail_total_pajak').text(sym + ' ' + formatNumber(totalPajak));
                $('#detail_pajak_wrapper').removeClass('d-none');

                $('#detail_grand_total').text(sym + ' ' + formatNumber(grandTotal));

                if (dp > 0) {
                    $('#detail_down_payment').text(sym + ' ' + formatNumber(dp));
                    $('#detail_down_payment_wrapper').removeClass('d-none');

                    let sisa = Math.max(0, grandTotal - dp);
                    $('#detail_sisa_bayar').text(sym + ' ' + formatNumber(sisa));
                    $('#detail_sisa_bayar_wrapper').removeClass('d-none');
                } else {
                    $('#detail_down_payment').text('-');
                    $('#detail_down_payment_wrapper').addClass('d-none');
                    $('#detail_sisa_bayar').text('-');
                    $('#detail_sisa_bayar_wrapper').addClass('d-none');
                }

                let currCode = po.currency ? po.currency.kode_currency : 'IDR';
                if (currCode !== 'IDR') {
                    let rateVal = parseFloat(po.kurs) || 1;
                    let displayGrandTotalIdr = grandTotalIdr > 0 ? grandTotalIdr : (grandTotal * rateVal);
                    $('#detail_kurs_val').text('Rp ' + formatKurs(rateVal));
                    $('#detail_grand_total_idr').text('Rp ' + formatNumber(displayGrandTotalIdr));
                    
                    if (dp > 0) {
                        let sisaIdr = Math.max(0, displayGrandTotalIdr - (dp * rateVal));
                        $('#detail_sisa_bayar_idr').text('Rp ' + formatNumber(sisaIdr));
                        $('#detail_idr_sisa_bayar_wrapper').removeClass('d-none');
                    } else {
                        $('#detail_idr_sisa_bayar_wrapper').addClass('d-none');
                    }
                    $('#detail_idr_wrapper').removeClass('d-none');
                } else {
                    $('#detail_idr_wrapper').addClass('d-none');
                }

                if (Array.isArray(biayaLainDetails) && biayaLainDetails.length > 0) {
                    let cardsHtml = '';
                    let totalBiayaLain = 0;
                    biayaLainDetails.forEach(function(item) {
                        let nom = parseFloat(item.nominal) || 0;
                        totalBiayaLain += nom;
                        let isHpp = String(item.masuk_hpp);
                        let isInventory = (isHpp === '1' || isHpp === 'true');
                        let nama = item.nama_jenis_biaya || item.jenis_biaya_nama || item.nama_jenis || 'Biaya';
                        let kode = item.kode_biaya || '';
                        let ket  = item.keterangan || '';
                        let treatColor   = isInventory ? '#10b981' : '#f59e0b';
                        let treatBg      = isInventory ? 'rgba(16,185,129,.08)' : 'rgba(245,158,11,.08)';
                        let treatBorder  = isInventory ? 'rgba(16,185,129,.25)' : 'rgba(245,158,11,.25)';
                        let treatIcon    = isInventory ? 'bi-boxes'              : 'bi-graph-up-arrow';
                        let treatLabel   = isInventory ? 'Inventory'             : 'Expense';

                        cardsHtml += `
                            <div class="col-md-6 col-lg-4">
                                <div class="h-100 p-3 rounded-3" style="background:${treatBg};border:1px solid ${treatBorder};">
                                    <div class="d-flex align-items-start gap-3">
                                        <div class="rounded-2 d-flex align-items-center justify-content-center flex-shrink-0"
                                             style="width:40px;height:40px;background:${treatColor}20;">
                                            <i class="bi ${treatIcon}" style="color:${treatColor};font-size:1.1rem;"></i>
                                        </div>
                                        <div class="flex-grow-1 min-w-0">
                                            <div class="d-flex align-items-center justify-content-between gap-2 mb-1">
                                                <span class="fw-bold text-dark" style="font-size:.9rem;word-break:break-word;">${escapeHtml(nama)}</span>
                                                <span class="badge rounded-pill px-2 py-1 flex-shrink-0"
                                                      style="background:${treatColor}20;color:${treatColor};font-size:.7rem;">
                                                    <i class="bi ${treatIcon} me-1"></i>${treatLabel}
                                                </span>
                                            </div>
                                            ${kode ? `<div class="text-muted mb-1" style="font-size:.75rem;"><i class="bi bi-tag me-1"></i>${escapeHtml(kode)}</div>` : ''}
                                            ${ket  ? `<div class="text-secondary mb-2" style="font-size:.8rem;line-height:1.3;">${escapeHtml(ket)}</div>` : ''}
                                            <div class="fw-bold" style="color:${treatColor};font-size:.95rem;">
                                                ${sym} ${formatNumber(nom)}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        `;
                    });
                    $('#detail_biaya_lain_cards').html(cardsHtml);
                    $('#detail_biaya_lain_count').text(biayaLainDetails.length + ' item');
                    $('#detail_biaya_lain_total').text(sym + ' ' + formatNumber(totalBiayaLain));
                    $('#detail_biaya_lain_section').show();
                } else {
                    $('#detail_biaya_lain_section').hide();
                }

                $('#detail_modal_loader').removeClass('active').addClass('d-none');
            }).fail(function(xhr) {
                $('#detail_modal_loader').removeClass('active').addClass('d-none');
                let msg = xhr.responseJSON ? xhr.responseJSON.message : 'Gagal memuat detail PO.';
                Swal.fire('Gagal!', msg, 'error');
            });
        };

        $(document).on('click', '.btn-po-approve-action', function () {
            let id = $('#po_approval_action_panel').data('id');
            if (!id) return;

            Swal.fire({
                title: 'Setujui Purchase Order?',
                text: "Dokumen PO ini akan ditandai sebagai disetujui (Ordered).",
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#10b981',
                cancelButtonColor: '#64748b',
                confirmButtonText: 'Ya, Setujui!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    $('#modalDetailPO').modal('hide');
                    Swal.fire({
                        title: 'Disetujui!',
                        text: 'Purchase Order berhasil disetujui.',
                        icon: 'success',
                        timer: 1000,
                        showConfirmButton: false
                    });

                    if (typeof window.reloadTablePO === 'function') window.reloadTablePO();

                    $.post('/purchase-order/' + id + '/approve', function (res) {
                        if (typeof window.reloadTablePO === 'function') window.reloadTablePO();
                    }).fail(function (xhr) {
                        let msg = xhr.responseJSON ? xhr.responseJSON.message : 'Gagal menyetujui PO.';
                        if (xhr.status === 403) msg = 'Akun Anda tidak memiliki izin untuk menyetujui Purchase Order ini.';
                        Swal.fire('Gagal!', msg, 'error');
                        if (typeof window.reloadTablePO === 'function') window.reloadTablePO();
                    });
                }
            });
        });

        // ===== REJECT PO ACTION =====
        $(document).on('click', '.btn-po-reject-action', function () {
            let id = $('#po_approval_action_panel').data('id');
            if (!id) return;

            Swal.fire({
                title: 'Tolak Purchase Order',
                html: `
                    <p class="text-muted mb-3">Berikan alasan penolakan agar pembuat PO mengetahui penyebabnya.</p>
                    <textarea id="swal-alasan-ditolak" class="form-control" rows="4"
                        placeholder="Contoh: Harga tidak sesuai negosiasi, supplier belum diverifikasi, dll..."></textarea>
                `,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                cancelButtonColor: '#64748b',
                confirmButtonText: '<i class="bi bi-x-circle me-1"></i> Tolak PO',
                cancelButtonText: 'Batal',
                didOpen: () => {
                    document.getElementById('swal-alasan-ditolak').focus();
                },
                preConfirm: () => {
                    const alasan = document.getElementById('swal-alasan-ditolak').value.trim();
                    if (!alasan) {
                        Swal.showValidationMessage('Alasan penolakan tidak boleh kosong!');
                        return false;
                    }
                    return alasan;
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    const alasan = result.value;
                    $('#modalDetailPO').modal('hide');
                    Swal.fire({
                        title: 'Ditolak!',
                        text: 'Purchase Order berhasil ditolak.',
                        icon: 'success',
                        timer: 1000,
                        showConfirmButton: false
                    });

                    if (typeof window.reloadTablePO === 'function') window.reloadTablePO();

                    $.ajax({
                        url: '/purchase-order/' + id + '/reject',
                        type: 'POST',
                        data: {
                            _token: $('meta[name="csrf-token"]').attr('content'),
                            alasan_ditolak: alasan
                        },
                        success: function (res) {
                            if (typeof window.reloadTablePO === 'function') window.reloadTablePO();
                        },
                        error: function (xhr) {
                            let msg = xhr.responseJSON ? xhr.responseJSON.message : 'Gagal menolak PO.';
                            if (xhr.status === 403) msg = 'Akun Anda tidak memiliki izin untuk menolak Purchase Order ini.';
                            Swal.fire('Gagal!', msg, 'error');
                            if (typeof window.reloadTablePO === 'function') window.reloadTablePO();
                        }
                    });
                }
            });
        });
    });
</script>
@endpush
